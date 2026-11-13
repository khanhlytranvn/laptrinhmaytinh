
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

// 4. DU LIEU BIEU DO THEO KHOANG THOI GIAN

$period = $_GET['period'] ?? '7days';

if (!in_array($period, ['7days', '1year'], true)) {
    $period = '7days';
}

$today = new DateTimeImmutable('today');
$year = $today->format('Y');

if ($period === '7days') {
    $days = 7;
    $startDate = $today->modify('-6 days')->format('Y-m-d');
    $groupFormat = '%Y-%m-%d';
    $chartDescription = 'Thống kê trong 7 ngày gần đây';

} else {
    $days = 0;
    $startDate = $year . '-01-01';
    $groupFormat = '%Y-%m';
    $chartDescription = 'Thống kê 12 tháng năm ' . $year;
}

$chartStmt = $pdo->prepare("
    SELECT
        DATE_FORMAT(transaction_date, '$groupFormat') AS period_key,
        type,
        SUM(amount) AS total
    FROM transactions
    WHERE user_id = ?
    AND transaction_date >= ?
    AND transaction_date <= ?
    GROUP BY period_key, type
    ORDER BY period_key
");

$chartStmt->execute([
    $uid,
    $startDate,
    $today->format('Y-m-d')
]);

$chartRows = $chartStmt->fetchAll();

$labels = [];
$income = [];
$expense = [];

if ($period === '1year') {

    for ($i = 1; $i <= 12; $i++) {
        $key = sprintf('%s-%02d', $year, $i);

        $labels[] = (string)$i;
        $income[$key] = 0;
        $expense[$key] = 0;
    }

} else {

    for ($i = $days - 1; $i >= 0; $i--) {
        $date = $today->modify("-$i days")->format('Y-m-d');

        $labels[] = date('d/m', strtotime($date));
        $income[$date] = 0;
        $expense[$date] = 0;
    }
}

foreach ($chartRows as $r) {
    $key = $r['period_key'];

    if ($r['type'] === 'income') {
        if (isset($income[$key])) {
            $income[$key] = (float)$r['total'];
        }
    } else {
        if (isset($expense[$key])) {
            $expense[$key] = (float)$r['total'];
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
            <span class="stat-icon"><i data-lucide="badge-dollar-sign"></i></span>
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
            <span class="stat-icon"><i data-lucide="wallet"></i></span>
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
            <?= e($chartDescription) ?>
        </p>
    </div>

    <form method="get" style="margin:0;">
        <select name="period"
                onchange="this.form.submit()"
                style="padding:8px 12px; border:1px solid #e2e8f0; border-radius:8px; background:white; cursor:pointer;">

            <option value="7days" <?= $period === '7days' ? 'selected' : '' ?>>
                7 ngày
            </option>

            <option value="1year" <?= $period === '1year' ? 'selected' : '' ?>>
                1 năm
            </option>

        </select>
    </form>
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
                <p class="section-description" style="font-size: 14px !important;">
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
    <div class="category-name">
        <span class="category-icon expense">
            <?= e(category_icon($b['name'], 'expense')) ?>
        </span>
        <strong><?= e($b['name']) ?></strong>
    </div>

    <span>
        <?= money($spent) ?>
        /
        <?= money($amount) ?>
    </span>
</div>

                    <div class="progress <?= $status ?>">
                        <span style="width:<?= min(100, $pct) ?>%"></span>
                    </div>

                    <div class="budget-status <?= $status ?>" style="font-size: 13px !important; olor: #6B7280 !important;">
                       <?php if ($status === 'over'): ?>

                            Đã vượt ngân sách
                            <span style="font-size: 12px;">
                                <?= money($spent - $amount) ?>
                            </span>

                        <?php elseif ($status === 'warning'): ?>

                            Sắp vượt ngân sách –
                            <span style="font-size: 12px;">
                                <?= number_format($pct, 1) ?>%
                            </span>
                            đã sử dụng

                        <?php else: ?>

                            <span style="font-size: 12px;">
                                <?= number_format($pct, 1) ?>%
                            </span>
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
    <div class="category-name">
        <span class="category-icon <?=e($t['type'])?>">
            <?=e(category_icon($t['category_name'], $t['type']))?>
        </span>

        <strong>
            <?=e($t['category_name'])?>
        </strong>
    </div>
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
                borderRadius: 6
            },
            {
                label: 'Chi tiêu',
                data: expenseData,
                backgroundColor: '#ed777e',
                borderRadius: 6
            }
        ]
    },

    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});
</script>

<?php require 'includes/footer.php'; ?>
