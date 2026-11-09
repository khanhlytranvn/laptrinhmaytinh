
<?php
require 'config.php';
require 'includes/functions.php';
require_login();

$pageTitle = 'Tổng quan tài chính';
$uid = current_user_id();
$month = date('Y-m');

// 1. TINH TONG THU VA TONG CHI
$stmt = $pdo->prepare("
    SELECT type, COALESCE(SUM(amount), 0) AS total
    FROM transactions
    WHERE user_id = ?
    AND DATE_FORMAT(transaction_date, '%Y-%m') = ?
    GROUP BY type
");
$stmt->execute([$uid, $month]);

$totals = ['income' => 0, 'expense' => 0];

foreach ($stmt as $r) {
    $totals[$r['type']] = (float)$r['total'];
}

$balance = $totals['income'] - $totals['expense'];

// 2. LAY 7 GIAO DICH GAN NHAT
$stmt = $pdo->prepare("
    SELECT t.*, c.name AS category_name
    FROM transactions t
    JOIN categories c ON c.id = t.category_id
    WHERE t.user_id = ?
    ORDER BY t.transaction_date DESC, t.id DESC
    LIMIT 7
");
$stmt->execute([$uid]);
$recent = $stmt->fetchAll();

// 3. LAY DANH SACH NGAN SACH THANG NAY
$stmt = $pdo->prepare("
    SELECT
        b.amount,
        c.name,
        c.id,
        COALESCE(SUM(t.amount), 0) AS spent
    FROM budgets b
    JOIN categories c ON c.id = b.category_id
    LEFT JOIN transactions t
        ON t.user_id = b.user_id
        AND t.category_id = b.category_id
        AND t.type = 'expense'
        AND DATE_FORMAT(t.transaction_date, '%Y-%m') = b.budget_month
    WHERE b.user_id = ?
    AND b.budget_month = ?
    GROUP BY b.id, b.amount, c.name, c.id
    ORDER BY c.name
");
$stmt->execute([$uid, $month]);
$budgets = $stmt->fetchAll();

// 4. DU LIEU BIEU DO 7 NGAY GAN NHAT
$chartStmt = $pdo->prepare("
    SELECT
        transaction_date,
        type,
        SUM(amount) AS total
    FROM transactions
    WHERE user_id = ?
    AND transaction_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    AND transaction_date <= CURDATE()
    GROUP BY transaction_date, type
    ORDER BY transaction_date
");
$chartStmt->execute([$uid]);
$chartRows = $chartStmt->fetchAll();

$labels = [];
$income = [];
$expense = [];

for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $label = date('d/m', strtotime($date));

    $labels[] = $label;
    $income[$date] = 0;
    $expense[$date] = 0;
}

foreach ($chartRows as $r) {
    $date = $r['transaction_date'];

    if (isset($income[$date])) {
        if ($r['type'] === 'income') {
            $income[$date] = (float)$r['total'];
        } else {
            $expense[$date] = (float)$r['total'];
        }
    }
}

require 'includes/header.php';
?>

<!-- CAC THE TONG QUAN -->
<div class="grid stats dashboard-stats">

    <div class="card stat-card">
        <div class="stat-top">
            <span class="stat-label">Tổng thu tháng này</span>
            <span class="stat-icon">↗</span>
        </div>

        <div class="stat-value green">
            <?= money($totals['income']) ?>
        </div>

        <div class="stat-note">
            Thu nhập từ đầu tháng
        </div>
    </div>

    <div class="card stat-card">
        <div class="stat-top">
            <span class="stat-label">Tổng chi tháng này</span>
            <span class="stat-icon">↙</span>
        </div>

        <div class="stat-value red">
            <?= money($totals['expense']) ?>
        </div>

        <div class="stat-note">
            Các khoản chi đã ghi nhận
        </div>
    </div>

    <div class="card stat-card">
        <div class="stat-top">
            <span class="stat-label">Số dư tháng này</span>
            <span class="stat-icon">◈</span>
        </div>

        <div class="stat-value <?= $balance >= 0 ? 'blue' : 'red' ?>">
            <?= money($balance) ?>
        </div>

        <div class="stat-note">
            Tổng thu trừ tổng chi
        </div>
    </div>

    <div class="card stat-card">
        <div class="stat-top">
            <span class="stat-label">Ngân sách đã thiết lập</span>
            <span class="stat-icon">▤</span>
        </div>

        <div class="stat-value">
            <?= count($budgets) ?>
        </div>

        <div class="stat-note">
            Danh mục trong tháng <?= date('m/Y') ?>
        </div>
    </div>

</div>

<!-- BIEU DO VA NGAN SACH -->
<div class="grid two-col dashboard-content">

    <!-- BIEU DO -->
    <section class="card dashboard-chart">

        <div class="section-head">
            <div>
                <h2>Biến động thu – chi</h2>
                <p class="section-description">
                    Thống kê trong 7 ngày gần đây
                </p>
            </div>

            <span class="tag">7 ngày</span>
        </div>

        <div class="chart-wrap">
            <canvas id="weeklyChart"></canvas>
        </div>

    </section>

    <!-- NGAN SACH -->
    <section class="card dashboard-budget">

        <div class="section-head">
            <div>
                <h2>Theo dõi ngân sách</h2>
                <p class="section-description">
                    Tình hình sử dụng ngân sách tháng này
                </p>
            </div>

            <a href="budgets.php" class="btn btn-light btn-small">
                Quản lý
            </a>
        </div>

        <?php if (!$budgets): ?>

            <div class="empty">
                Chưa có ngân sách tháng này.
                <br><br>
                <a href="budgets.php" class="btn btn-light">
                    Thiết lập ngân sách
                </a>
            </div>

        <?php else: ?>

            <?php foreach ($budgets as $b):

                $amount = (float)$b['amount'];
                $spent = (float)$b['spent'];

                $pct = $amount > 0
                    ? ($spent / $amount * 100)
                    : 0;

                $status = 'normal';

                if ($pct >= 100) {
                    $status = 'over';
                } elseif ($pct >= 80) {
                    $status = 'warning';
                }

            ?>

                <div class="budget-row">

                    <div class="budget-meta">
                        <strong><?= e($b['name']) ?></strong>

                        <span>
                            <?= money($spent) ?>
                            /
                            <?= money($amount) ?>
                        </span>
                    </div>

                    <div class="progress <?= $status ?>">
                        <span style="width:<?= min(100, $pct) ?>%"></span>
                    </div>

                    <div class="budget-status <?= $status ?>">
                        <?php if ($status === 'over'): ?>

                            Đã vượt ngân sách
                            <?= money($spent - $amount) ?>

                        <?php elseif ($status === 'warning'): ?>

                            Sắp vượt ngân sách –
                            <?= number_format($pct, 1) ?>% đã sử dụng

                        <?php else: ?>

                            <?= number_format($pct, 1) ?>%
                            ngân sách đã sử dụng

                        <?php endif; ?>
                    </div>

                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </section>

</div>

<!-- GIAO DICH GAN DAY -->
<section class="card dashboard-transactions">

    <div class="section-head">
        <div>
            <h2>Giao dịch gần đây</h2>
            <p class="section-description">
                7 giao dịch mới nhất của bạn
            </p>
        </div>

        <a href="transactions.php" class="btn btn-light btn-small">
            Xem tất cả
        </a>
    </div>

    <div class="table-scroll">

        <table>
            <thead>
                <tr>
                    <th>NGÀY</th>
                    <th>GIAO DỊCH</th>
                    <th>DANH MỤC</th>
                    <th>SỐ TIỀN</th>
                    <th>LOẠI</th>
                </tr>
            </thead>

            <tbody>

                <?php if (!$recent): ?>

                    <tr>
                        <td colspan="5" class="empty">
                            Chưa có giao dịch nào.
                            Hãy thêm khoản thu hoặc chi đầu tiên.
                        </td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($recent as $t): ?>

                        <tr>

                            <td>
                                <?= date(
                                    'd/m/Y',
                                    strtotime($t['transaction_date'])
                                ) ?>
                            </td>

                            <td>
                                <strong>
                                    <?= e($t['note'] ?: $t['category_name']) ?>
                                </strong>
                            </td>

                            <td>
                                <?= e($t['category_name']) ?>
                            </td>

                            <td class="<?= $t['type'] === 'income' ? 'green' : 'red' ?>">
                                <strong>
                                    <?= $t['type'] === 'income' ? '+' : '-' ?>
                                    <?= money($t['amount']) ?>
                                </strong>
                            </td>

                            <td>
                                <span class="pill <?= $t['type'] ?>">
                                    <?= $t['type'] === 'income'
                                        ? 'Thu nhập'
                                        : 'Chi tiêu' ?>
                                </span>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

            </tbody>
        </table>

    </div>

</section>

<!-- BIEU DO CHART.JS -->
<script>
const chartLabels = <?= json_encode($labels) ?>;
const incomeData = <?= json_encode(array_values($income)) ?>;
const expenseData = <?= json_encode(array_values($expense)) ?>;

const chartCanvas = document.getElementById('weeklyChart');

new Chart(chartCanvas, {
    type: 'bar',

    data: {
        labels: chartLabels,

        datasets: [
            {
                label: 'Thu nhập',
                data: incomeData,
                backgroundColor: '#31ad83',
                borderRadius: 6,
                barPercentage: 0.65
            },
            {
                label: 'Chi tiêu',
                data: expenseData,
                backgroundColor: '#ed777e',
                borderRadius: 6,
                barPercentage: 0.65
            }
        ]
    },

    options: {
        responsive: true,
        maintainAspectRatio: false,

        interaction: {
            mode: 'index',
            intersect: false
        },

        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    usePointStyle: true,
                    padding: 20
                }
            },

            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ' +
                            context.raw.toLocaleString('vi-VN') + ' ₫';
                    }
                }
            }
        },

        scales: {
            y: {
                beginAtZero: true,

                ticks: {
                    callback: function(value) {
                        return value.toLocaleString('vi-VN');
                    }
                },

                grid: {
                    color: '#edf0f5'
                }
            },

            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});
</script>

<?php require 'includes/footer.php'; ?>
