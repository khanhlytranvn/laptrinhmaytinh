<?php
require 'config.php';
require 'includes/functions.php';
require_login();
$uid = current_user_id();
$datePattern = '/^\d{4}-\d{2}-\d{2}$/';

function valid_debt_date(string $date, string $pattern): bool {
    if (!preg_match($pattern, $date)) return false;
    $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    return $parsed && $parsed->format('Y-m-d') === $date;
}

function valid_debt_amount(string $amount): bool {
    return (bool)preg_match('/^(?:0|[1-9]\d{0,12})(?:\.\d{1,2})?$/', $amount) && (float)$amount > 0;
}

verify_csrf();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    if ($action === 'save_debt') {
        $direction = $_POST['direction'] ?? '';
        $person = trim($_POST['person_name'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $principal = trim($_POST['principal_amount'] ?? '');
        $dueDate = trim($_POST['due_date'] ?? '');
        $note = trim($_POST['note'] ?? '');
        if (!in_array($direction, ['payable', 'receivable'], true) || $person === '' || mb_strlen($person) > 120 || $title === '' || mb_strlen($title) > 150 || !valid_debt_amount($principal) || ($dueDate !== '' && !valid_debt_date($dueDate, $datePattern)) || mb_strlen($note) > 255) {
            set_flash('Thông tin khoản nợ chưa hợp lệ. Vui lòng kiểm tra lại các trường.', 'error');
            redirect('debts.php' . ($id ? '?edit=' . $id : ''));
        }
        try {
            if ($id > 0) {
                $pdo->beginTransaction();
                $lock = $pdo->prepare('SELECT principal_amount,direction FROM debts WHERE id=? AND user_id=? FOR UPDATE');
                $lock->execute([$id, $uid]);
                $old = $lock->fetch();
                if (!$old) throw new RuntimeException('Không tìm thấy khoản nợ để cập nhật.');
                $paid = $pdo->prepare('SELECT COALESCE(SUM(amount),0) FROM debt_entries WHERE debt_id=? AND user_id=?');
                $paid->execute([$id, $uid]);
                $paidAmount = $paid->fetchColumn();
                if ((float)$paidAmount > 0 && $old['direction'] !== $direction) throw new RuntimeException('Không thể đổi loại khoản nợ sau khi đã có lịch sử giao dịch.');
                $check = $pdo->prepare('SELECT CAST(? AS DECIMAL(15,2)) >= CAST(? AS DECIMAL(15,2))');
                $check->execute([$principal, $paidAmount]);
                if (!(int)$check->fetchColumn()) throw new RuntimeException('Số nợ gốc không thể thấp hơn tổng số đã thanh toán/thu hồi.');
                $update = $pdo->prepare('UPDATE debts SET direction=?,person_name=?,title=?,principal_amount=?,due_date=?,note=? WHERE id=? AND user_id=?');
                $update->execute([$direction, $person, $title, $principal, $dueDate ?: null, $note ?: null, $id, $uid]);
                $pdo->commit();
                set_flash('Đã cập nhật khoản nợ.');
            } else {
                $insert = $pdo->prepare('INSERT INTO debts(user_id,direction,person_name,title,principal_amount,due_date,note) VALUES(?,?,?,?,?,?,?)');
                $insert->execute([$uid, $direction, $person, $title, $principal, $dueDate ?: null, $note ?: null]);
                set_flash('Đã tạo khoản nợ cần theo dõi.');
            }
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            set_flash($e instanceof RuntimeException ? $e->getMessage() : 'Không thể lưu khoản nợ. Vui lòng thử lại.', 'error');
        }
        redirect('debts.php');
    }

    if ($action === 'delete_debt') {
        try {
            $pdo->beginTransaction();
            $lock = $pdo->prepare('SELECT id FROM debts WHERE id=? AND user_id=? FOR UPDATE');
            $lock->execute([$id, $uid]);
            if (!$lock->fetch()) throw new RuntimeException('Không tìm thấy khoản nợ.');
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM debt_entries WHERE debt_id=? AND user_id=?');
            $stmt->execute([$id, $uid]);
            if ((int)$stmt->fetchColumn() > 0) throw new RuntimeException('Khoản nợ đã có lịch sử thanh toán/thu hồi nên không thể xóa.');
            $stmt = $pdo->prepare('DELETE FROM debts WHERE id=? AND user_id=?');
            $stmt->execute([$id, $uid]);
            $pdo->commit();
            set_flash('Đã xóa khoản nợ.');
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            set_flash($e instanceof RuntimeException ? $e->getMessage() : 'Không thể xóa khoản nợ.', 'error');
        }
        redirect('debts.php');
    }

    if ($action === 'payment') {
        $debtId = (int)($_POST['debt_id'] ?? 0);
        $amount = trim($_POST['amount'] ?? '');
        $entryDate = trim($_POST['entry_date'] ?? '');
        $entryNote = trim($_POST['entry_note'] ?? '');
        if (!valid_debt_amount($amount) || !valid_debt_date($entryDate, $datePattern) || mb_strlen($entryNote) > 255) {
            set_flash('Thông tin thanh toán/thu hồi chưa hợp lệ.', 'error');
            redirect('debts.php');
        }
        try {
            $pdo->beginTransaction();
            $lock = $pdo->prepare('SELECT id FROM debts WHERE id=? AND user_id=? FOR UPDATE');
            $lock->execute([$debtId, $uid]);
            if (!$lock->fetch()) throw new RuntimeException('Không tìm thấy khoản nợ.');
            $paid = $pdo->prepare('SELECT COALESCE(SUM(amount),0) FROM debt_entries WHERE debt_id=? AND user_id=?');
            $paid->execute([$debtId, $uid]);
            $balanceQuery = $pdo->prepare('SELECT principal_amount FROM debts WHERE id=? AND user_id=?');
            $balanceQuery->execute([$debtId, $uid]);
            $principal = $balanceQuery->fetchColumn();
            $check = $pdo->prepare('SELECT CAST(? AS DECIMAL(15,2)) <= (CAST(? AS DECIMAL(15,2)) - CAST(? AS DECIMAL(15,2)))');
            $check->execute([$amount, $principal, $paid->fetchColumn()]);
            if (!(int)$check->fetchColumn()) throw new RuntimeException('Số tiền vượt quá số dư khoản nợ còn lại.');
            $insert = $pdo->prepare('INSERT INTO debt_entries(debt_id,user_id,amount,entry_date,note) VALUES(?,?,?,?,?)');
            $insert->execute([$debtId, $uid, $amount, $entryDate, $entryNote ?: null]);
            $pdo->commit();
            set_flash('Đã ghi nhận thanh toán/thu hồi.');
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            set_flash($e instanceof RuntimeException ? $e->getMessage() : 'Không thể lưu giao dịch. Vui lòng thử lại.', 'error');
        }
        redirect('debts.php');
    }
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM debts WHERE id=? AND user_id=?');
    $stmt->execute([(int)$_GET['edit'], $uid]);
    $edit = $stmt->fetch() ?: null;
}
$stmt = $pdo->prepare("SELECT d.*, COALESCE(SUM(e.amount),0) AS paid_amount, GREATEST(d.principal_amount-COALESCE(SUM(e.amount),0),0) AS balance FROM debts d LEFT JOIN debt_entries e ON e.debt_id=d.id AND e.user_id=d.user_id WHERE d.user_id=? GROUP BY d.id ORDER BY (d.due_date IS NULL),d.due_date,d.created_at DESC");
$stmt->execute([$uid]);
$debts = $stmt->fetchAll();
$stmt = $pdo->prepare('SELECT e.*,d.direction,d.person_name,d.title FROM debt_entries e JOIN debts d ON d.id=e.debt_id AND d.user_id=e.user_id WHERE e.user_id=? ORDER BY e.entry_date DESC,e.id DESC LIMIT 100');
$stmt->execute([$uid]);
$history = $stmt->fetchAll();
$activeDebts = array_values(array_filter($debts, static fn($debt) => (float)$debt['balance'] > 0));
$payableTotal = 0.0;
$receivableTotal = 0.0;
$payableCount = 0;
$receivableCount = 0;
foreach ($debts as $debt) {
    if ($debt['direction'] === 'payable') { $payableTotal += (float)$debt['balance']; if ((float)$debt['balance'] > 0) $payableCount++; }
    else { $receivableTotal += (float)$debt['balance']; if ((float)$debt['balance'] > 0) $receivableCount++; }
}
$pageTitle = 'Theo dõi nợ';
require 'includes/header.php';
?>
<div class="grid stats debt-stats">
  <div class="card module-stat debt-stat"><div class="module-stat-icon rose"><i data-lucide="arrow-up-right"></i></div><div class="stat-label">Tôi cần trả</div><div class="stat-value red"><?= money($payableTotal) ?></div><div class="stat-note"><?= $payableCount ?> khoản còn dư nợ phải trả</div></div>
  <div class="card module-stat debt-stat"><div class="module-stat-icon mint"><i data-lucide="arrow-down-left"></i></div><div class="stat-label">Người khác cần trả tôi</div><div class="stat-value green"><?= money($receivableTotal) ?></div><div class="stat-note"><?= $receivableCount ?> khoản còn dư nợ cần thu hồi</div></div>
</div>
<div class="grid two-col debt-layout">
  <section class="card form-card"><div class="section-head"><h2><?= $edit ? 'Chỉnh sửa khoản nợ' : 'Thêm khoản nợ' ?></h2><?php if ($edit): ?><a class="btn btn-light btn-small" href="debts.php">Hủy</a><?php endif; ?></div>
    <form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="save_debt"><input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
      <div class="form-grid"><div class="field"><label for="direction">Loại khoản nợ</label><select id="direction" name="direction"><option value="payable" <?= ($edit['direction'] ?? 'payable') === 'payable' ? 'selected' : '' ?>>Tôi đi vay / cần trả</option><option value="receivable" <?= ($edit['direction'] ?? '') === 'receivable' ? 'selected' : '' ?>>Tôi cho vay / cần thu</option></select></div>
      <div class="field"><label for="person">Người liên quan</label><input id="person" name="person_name" maxlength="120" required value="<?= e($edit['person_name'] ?? '') ?>" placeholder="Tên người hoặc đơn vị"></div>
      <div class="field full"><label for="title">Nội dung khoản nợ</label><input id="title" name="title" maxlength="150" required value="<?= e($edit['title'] ?? '') ?>" placeholder="Ví dụ: Vay mua laptop"></div>
      <div class="field"><label for="principal">Số tiền gốc (VND)</label><input id="principal" type="number" name="principal_amount" min="0.01" step="0.01" required value="<?= e($edit['principal_amount'] ?? '') ?>"></div>
      <div class="field"><label for="due-date">Ngày đến hạn</label><input id="due-date" type="date" name="due_date" value="<?= e($edit['due_date'] ?? '') ?>"></div>
      <div class="field full"><label for="debt-note">Ghi chú</label><input id="debt-note" name="note" maxlength="255" value="<?= e($edit['note'] ?? '') ?>"></div></div>
      <div class="form-actions"><button class="btn"><?= $edit ? 'Lưu khoản nợ' : 'Thêm khoản nợ' ?></button></div>
    </form>
  </section>
  <section class="card"><div class="section-head"><h2>Ghi nhận thanh toán hoặc thu hồi</h2></div>
    <?php if (!$activeDebts): ?><div class="empty">Thêm khoản nợ còn số dư để ghi nhận giao dịch.</div><?php else: ?><form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="payment">
      <div class="field"><label for="debt-choice">Khoản nợ</label><select id="debt-choice" name="debt_id" required><?php foreach ($activeDebts as $debt): ?><option value="<?= (int)$debt['id'] ?>"><?= $debt['direction'] === 'payable' ? 'Cần trả' : 'Cần thu' ?> · <?= e($debt['person_name']) ?> · <?= e($debt['title']) ?> (<?= money($debt['balance']) ?>)</option><?php endforeach; ?></select></div>
      <div class="form-grid" style="margin-top:14px"><div class="field"><label for="payment-amount">Số tiền (VND)</label><input id="payment-amount" type="number" name="amount" min="0.01" step="0.01" required></div><div class="field"><label for="payment-date">Ngày giao dịch</label><input id="payment-date" type="date" name="entry_date" value="<?= date('Y-m-d') ?>" required></div><div class="field full"><label for="payment-note">Ghi chú</label><input id="payment-note" name="entry_note" maxlength="255"></div></div>
      <div class="form-actions"><button class="btn">Lưu giao dịch</button></div>
    </form><?php endif; ?>
  </section>
</div>
<section class="card" style="margin-top:18px"><div class="section-head"><h2>Các khoản nợ (<?= count($debts) ?>)</h2></div><div class="table-scroll"><table><thead><tr><th>LOẠI</th><th>NGƯỜI LIÊN QUAN</th><th>NỘI DUNG</th><th>ĐẾN HẠN</th><th>GỐC</th><th>CÒN LẠI</th><th>THAO TÁC</th></tr></thead><tbody>
<?php if (!$debts): ?><tr><td colspan="7" class="empty">Chưa có khoản nợ nào được ghi nhận.</td></tr><?php else: foreach ($debts as $debt): ?><tr><td><span class="pill <?= $debt['direction'] === 'payable' ? 'expense' : 'income' ?>"><?= $debt['direction'] === 'payable' ? 'Cần trả' : 'Cần thu' ?></span></td><td><strong><?= e($debt['person_name']) ?></strong></td><td><?= e($debt['title']) ?><?php if ($debt['note']): ?><div class="stat-note"><?= e($debt['note']) ?></div><?php endif; ?></td><td><?= $debt['due_date'] ? date('d/m/Y', strtotime($debt['due_date'])) : '—' ?></td><td><?= money($debt['principal_amount']) ?></td><td class="<?= (float)$debt['balance'] > 0 ? 'red' : 'green' ?>"><strong><?= money($debt['balance']) ?></strong><div class="stat-note">Đã giao dịch <?= money($debt['paid_amount']) ?></div></td><td><div class="actions"><a class="btn btn-light btn-small" href="debts.php?edit=<?= (int)$debt['id'] ?>">Sửa</a><?php if ((float)$debt['paid_amount'] <= 0): ?><form method="post" onsubmit="return confirm('Xóa khoản nợ này?')"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete_debt"><input type="hidden" name="id" value="<?= (int)$debt['id'] ?>"><button class="btn btn-danger btn-small">Xóa</button></form><?php endif; ?></div></td></tr><?php endforeach; endif; ?>
</tbody></table></div><p class="stat-note">Khoản nợ đã có thanh toán/thu hồi được giữ lại để bảo toàn lịch sử.</p></section>
<section class="card" style="margin-top:18px"><div class="section-head"><h2>Lịch sử thanh toán và thu hồi</h2><span class="tag">Tối đa 100 giao dịch gần nhất</span></div><div class="table-scroll"><table><thead><tr><th>NGÀY</th><th>LOẠI</th><th>NGƯỜI LIÊN QUAN</th><th>KHOẢN NỢ</th><th>GHI CHÚ</th><th>SỐ TIỀN</th></tr></thead><tbody><?php if (!$history): ?><tr><td colspan="6" class="empty">Chưa có lịch sử thanh toán hoặc thu hồi.</td></tr><?php else: foreach ($history as $entry): ?><tr><td><?= date('d/m/Y', strtotime($entry['entry_date'])) ?></td><td><span class="pill <?= $entry['direction'] === 'payable' ? 'expense' : 'income' ?>"><?= $entry['direction'] === 'payable' ? 'Trả nợ' : 'Thu hồi' ?></span></td><td><?= e($entry['person_name']) ?></td><td><?= e($entry['title']) ?></td><td><?= e($entry['note'] ?: '—') ?></td><td><strong><?= money($entry['amount']) ?></strong></td></tr><?php endforeach; endif; ?></tbody></table></div></section>
<?php require 'includes/footer.php'; ?>
