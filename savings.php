<?php
require 'config.php';
require 'includes/functions.php';
require_login();
$uid = current_user_id();
$datePattern = '/^\d{4}-\d{2}-\d{2}$/';

function valid_savings_date(string $date, string $pattern): bool {
    if (!preg_match($pattern, $date)) return false;
    $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    return $parsed && $parsed->format('Y-m-d') === $date;
}

function valid_savings_amount(string $amount): bool {
    return (bool)preg_match('/^(?:0|[1-9]\d{0,12})(?:\.\d{1,2})?$/', $amount) && (float)$amount > 0;
}

verify_csrf();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $target = trim($_POST['target_amount'] ?? '');
    $targetDate = trim($_POST['target_date'] ?? '');
    $note = trim($_POST['note'] ?? '');

    if ($action === 'save_goal') {
        if ($name === '' || mb_strlen($name) > 120 || !valid_savings_amount($target) || ($targetDate !== '' && !valid_savings_date($targetDate, $datePattern)) || mb_strlen($note) > 255) {
            set_flash('Thông tin mục tiêu chưa hợp lệ. Vui lòng kiểm tra tên, số tiền và ngày hoàn thành.', 'error');
            redirect('savings.php' . ($id ? '?edit=' . $id : ''));
        }
        if ($id > 0) {
            $stmt = $pdo->prepare('UPDATE savings_goals SET name=?, target_amount=?, target_date=?, note=? WHERE id=? AND user_id=?');
            $stmt->execute([$name, $target, $targetDate ?: null, $note ?: null, $id, $uid]);
            set_flash($stmt->rowCount() ? 'Đã cập nhật mục tiêu tiết kiệm.' : 'Không tìm thấy mục tiêu để cập nhật.', $stmt->rowCount() ? 'success' : 'error');
        } else {
            $stmt = $pdo->prepare('INSERT INTO savings_goals(user_id,name,target_amount,target_date,note) VALUES(?,?,?,?,?)');
            $stmt->execute([$uid, $name, $target, $targetDate ?: null, $note ?: null]);
            set_flash('Đã tạo mục tiêu tiết kiệm.');
        }
        redirect('savings.php');
    }

    if ($action === 'delete_goal') {
        $stmt = $pdo->prepare('DELETE FROM savings_goals WHERE id=? AND user_id=?');
        $stmt->execute([$id, $uid]);
        set_flash($stmt->rowCount() ? 'Đã xóa mục tiêu và lịch sử liên quan.' : 'Không tìm thấy mục tiêu.', $stmt->rowCount() ? 'success' : 'error');
        redirect('savings.php');
    }

    if ($action === 'entry') {
        $goalId = (int)($_POST['goal_id'] ?? 0);
        $entryType = $_POST['entry_type'] ?? '';
        $amount = trim($_POST['amount'] ?? '');
        $entryDate = trim($_POST['entry_date'] ?? '');
        $entryNote = trim($_POST['entry_note'] ?? '');
        if (!in_array($entryType, ['deposit', 'withdrawal'], true) || !valid_savings_amount($amount) || !valid_savings_date($entryDate, $datePattern) || mb_strlen($entryNote) > 255) {
            set_flash('Thông tin giao dịch tiết kiệm chưa hợp lệ.', 'error');
            redirect('savings.php');
        }
        try {
            $pdo->beginTransaction();
            $lock = $pdo->prepare('SELECT id FROM savings_goals WHERE id=? AND user_id=? FOR UPDATE');
            $lock->execute([$goalId, $uid]);
            if (!$lock->fetch()) throw new RuntimeException('Không tìm thấy mục tiêu tiết kiệm.');
            if ($entryType === 'withdrawal') {
                $balance = $pdo->prepare("SELECT COALESCE(SUM(CASE WHEN entry_type='deposit' THEN amount ELSE -amount END),0) FROM savings_entries WHERE goal_id=? AND user_id=?");
                $balance->execute([$goalId, $uid]);
                $check = $pdo->prepare('SELECT CAST(? AS DECIMAL(15,2)) <= CAST(? AS DECIMAL(15,2))');
                $check->execute([$amount, $balance->fetchColumn()]);
                if (!(int)$check->fetchColumn()) throw new RuntimeException('Số tiền rút vượt quá số dư của mục tiêu.');
            }
            $insert = $pdo->prepare('INSERT INTO savings_entries(goal_id,user_id,entry_type,amount,entry_date,note) VALUES(?,?,?,?,?,?)');
            $insert->execute([$goalId, $uid, $entryType, $amount, $entryDate, $entryNote ?: null]);
            $pdo->commit();
            set_flash($entryType === 'deposit' ? 'Đã ghi nhận khoản gửi tiết kiệm.' : 'Đã ghi nhận khoản rút tiết kiệm.');
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            set_flash($e instanceof RuntimeException ? $e->getMessage() : 'Không thể lưu giao dịch. Vui lòng thử lại.', 'error');
        }
        redirect('savings.php');
    }
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM savings_goals WHERE id=? AND user_id=?');
    $stmt->execute([(int)$_GET['edit'], $uid]);
    $edit = $stmt->fetch() ?: null;
}
$stmt = $pdo->prepare("SELECT g.*, COALESCE(SUM(CASE WHEN e.entry_type='deposit' THEN e.amount ELSE -e.amount END),0) AS balance FROM savings_goals g LEFT JOIN savings_entries e ON e.goal_id=g.id AND e.user_id=g.user_id WHERE g.user_id=? GROUP BY g.id ORDER BY g.created_at DESC, g.id DESC");
$stmt->execute([$uid]);
$goals = $stmt->fetchAll();
$totalTarget = 0.0;
$totalSaved = 0.0;
foreach ($goals as $goal) {
    $totalTarget += (float)$goal['target_amount'];
    $totalSaved += (float)$goal['balance'];
}
$overallProgress = $totalTarget > 0 ? min(100, max(0, $totalSaved / $totalTarget * 100)) : 0;
$stmt = $pdo->prepare('SELECT e.*, g.name AS goal_name FROM savings_entries e JOIN savings_goals g ON g.id=e.goal_id AND g.user_id=e.user_id WHERE e.user_id=? ORDER BY e.entry_date DESC,e.id DESC LIMIT 100');
$stmt->execute([$uid]);
$history = $stmt->fetchAll();
$pageTitle = 'Mục tiêu tiết kiệm';
require 'includes/header.php';
?>
<div class="grid stats module-stats savings-stats">
  <div class="card module-stat"><div class="module-stat-icon mint"><i data-lucide="landmark"></i></div><div class="stat-label">Tổng đã tiết kiệm</div><div class="stat-value green"><?= money($totalSaved) ?></div><div class="stat-note">Cộng dồn từ các lần gửi và rút</div></div>
  <div class="card module-stat"><div class="module-stat-icon blue-soft"><i data-lucide="flag"></i></div><div class="stat-label">Tổng mục tiêu</div><div class="stat-value"><?= money($totalTarget) ?></div><div class="stat-note"><?= count($goals) ?> mục tiêu đang theo dõi</div></div>
  <div class="card module-stat module-progress-stat"><div class="module-stat-top"><div><div class="stat-label">Tiến độ chung</div><div class="stat-value"><?= number_format($overallProgress, 1) ?>%</div></div><div class="module-stat-icon violet"><i data-lucide="chart-no-axes-column-increasing"></i></div></div><div class="progress"><span style="width:<?= number_format($overallProgress, 2, '.', '') ?>%"></span></div><div class="stat-note">Tổng số dư so với tổng mục tiêu</div></div>
</div>
<div class="grid two-col savings-layout">
  <section class="card form-card">
    <div class="section-head"><h2><?= $edit ? 'Chỉnh sửa mục tiêu' : 'Tạo mục tiêu mới' ?></h2><?php if ($edit): ?><a class="btn btn-light btn-small" href="savings.php">Hủy</a><?php endif; ?></div>
    <form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="save_goal"><input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
      <div class="form-grid">
        <div class="field full"><label for="goal-name">Tên mục tiêu</label><input id="goal-name" name="name" maxlength="120" required value="<?= e($edit['name'] ?? '') ?>" placeholder="Ví dụ: Quỹ du lịch"></div>
        <div class="field"><label for="goal-target">Số tiền mục tiêu (VND)</label><input id="goal-target" type="number" name="target_amount" min="0.01" step="0.01" required value="<?= e($edit['target_amount'] ?? '') ?>"></div>
        <div class="field"><label for="goal-date">Ngày dự kiến</label><input id="goal-date" type="date" name="target_date" value="<?= e($edit['target_date'] ?? '') ?>"></div>
        <div class="field full"><label for="goal-note">Ghi chú</label><input id="goal-note" name="note" maxlength="255" value="<?= e($edit['note'] ?? '') ?>"></div>
      </div><div class="form-actions"><button class="btn"><?= $edit ? 'Lưu mục tiêu' : 'Tạo mục tiêu' ?></button></div>
    </form>
    <p class="stat-note">Tiền hiện có được tính từ lịch sử gửi và rút. Mỗi giao dịch đều được lưu riêng.</p>
  </section>
  <section class="card">
    <div class="section-head"><h2>Ghi nhận gửi hoặc rút</h2></div>
    <?php if (!$goals): ?><div class="empty">Tạo mục tiêu trước khi thêm giao dịch.</div><?php else: ?>
    <form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="entry">
      <div class="field"><label for="entry-goal">Mục tiêu</label><select id="entry-goal" name="goal_id" required><?php foreach ($goals as $goal): ?><option value="<?= (int)$goal['id'] ?>"><?= e($goal['name']) ?> · <?= money($goal['balance']) ?></option><?php endforeach; ?></select></div>
      <div class="form-grid" style="margin-top:14px"><div class="field"><label for="entry-type">Loại giao dịch</label><select id="entry-type" name="entry_type"><option value="deposit">Gửi thêm</option><option value="withdrawal">Rút tiền</option></select></div><div class="field"><label for="entry-amount">Số tiền (VND)</label><input id="entry-amount" type="number" name="amount" min="0.01" step="0.01" required></div><div class="field"><label for="entry-date">Ngày giao dịch</label><input id="entry-date" type="date" name="entry_date" value="<?= date('Y-m-d') ?>" required></div><div class="field"><label for="entry-note">Ghi chú</label><input id="entry-note" name="entry_note" maxlength="255"></div></div>
      <div class="form-actions"><button class="btn">Lưu giao dịch</button></div>
    </form><?php endif; ?>
  </section>
</div>
<section class="card" style="margin-top:18px"><div class="section-head"><h2>Các mục tiêu của bạn (<?= count($goals) ?>)</h2></div>
  <?php if (!$goals): ?><div class="empty">Bạn chưa tạo mục tiêu tiết kiệm nào.</div><?php else: ?><div class="goal-grid">
    <?php foreach ($goals as $goal): $balance = (float)$goal['balance']; $target = (float)$goal['target_amount']; $pct = $target > 0 ? min(100, max(0, $balance / $target * 100)) : 0; ?>
    <article class="goal-card"><div class="section-head"><div><h3><?= e($goal['name']) ?></h3><span class="muted"><?= $goal['target_date'] ? 'Mục tiêu đến ' . date('d/m/Y', strtotime($goal['target_date'])) : 'Không đặt ngày dự kiến' ?></span></div><span class="pill <?= $balance >= $target ? 'income' : 'neutral' ?>"><?= $balance >= $target ? 'Đã đạt' : 'Đang tiết kiệm' ?></span></div>
      <div class="budget-meta"><span>Đã có <strong><?= money($goal['balance']) ?></strong></span><span>Mục tiêu <strong><?= money($goal['target_amount']) ?></strong></span></div><div class="progress"><span style="width:<?= number_format($pct, 2, '.', '') ?>%"></span></div><div class="budget-meta"><span class="muted">Còn thiếu</span><strong><?= money(max(0, $target - $balance)) ?></strong></div><?php if ($goal['note']): ?><p class="stat-note"><?= e($goal['note']) ?></p><?php endif; ?>
      <div class="actions" style="margin-top:12px"><a class="btn btn-light btn-small" href="savings.php?edit=<?= (int)$goal['id'] ?>">Sửa</a><form method="post" onsubmit="return confirm('Xóa mục tiêu này cùng toàn bộ lịch sử gửi và rút?')"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete_goal"><input type="hidden" name="id" value="<?= (int)$goal['id'] ?>"><button class="btn btn-danger btn-small">Xóa</button></form></div>
    </article><?php endforeach; ?>
  </div><?php endif; ?>
</section>
<section class="card" style="margin-top:18px"><div class="section-head"><h2>Lịch sử gửi và rút</h2><span class="tag">Tối đa 100 giao dịch gần nhất</span></div><div class="table-scroll"><table><thead><tr><th>NGÀY</th><th>MỤC TIÊU</th><th>LOẠI</th><th>GHI CHÚ</th><th>SỐ TIỀN</th></tr></thead><tbody><?php if (!$history): ?><tr><td colspan="5" class="empty">Chưa có giao dịch tiết kiệm.</td></tr><?php else: foreach ($history as $entry): ?><tr><td><?= date('d/m/Y', strtotime($entry['entry_date'])) ?></td><td><?= e($entry['goal_name']) ?></td><td><span class="pill <?= $entry['entry_type'] === 'deposit' ? 'income' : 'expense' ?>"><?= $entry['entry_type'] === 'deposit' ? 'Gửi thêm' : 'Rút tiền' ?></span></td><td><?= e($entry['note'] ?: '—') ?></td><td class="<?= $entry['entry_type'] === 'deposit' ? 'green' : 'red' ?>"><strong><?= $entry['entry_type'] === 'deposit' ? '+' : '−' ?><?= money($entry['amount']) ?></strong></td></tr><?php endforeach; endif; ?></tbody></table></div></section>
<?php require 'includes/footer.php'; ?>
