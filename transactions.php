<?php
require 'config.php';require 'includes/functions.php';require_login();verify_csrf();
$uid=current_user_id();$type=$_GET['type']??'';if(!in_array($type,['income','expense'],true))$type='';
$edit=null;
if(isset($_GET['edit'])){$s=$pdo->prepare("SELECT * FROM transactions WHERE id=? AND user_id=?");$s->execute([(int)$_GET['edit'],$uid]);$edit=$s->fetch();if($edit)$type=$edit['type'];}
if($_SERVER['REQUEST_METHOD']==='POST'){
 $action=$_POST['action']??'save';
 if($action==='delete'){$s=$pdo->prepare("DELETE FROM transactions WHERE id=? AND user_id=?");$s->execute([(int)$_POST['id'],$uid]);set_flash('Đã xóa giao dịch.');redirect('transactions.php');}
 $id=(int)($_POST['id']??0);$t=$_POST['type']??'expense';$cat=(int)($_POST['category_id']??0);$amount=(float)($_POST['amount']??0);$date=$_POST['transaction_date']??'';$note=trim($_POST['note']??'');
 $valid=$pdo->prepare("SELECT id FROM categories WHERE id=? AND type=? AND (user_id IS NULL OR user_id=?)");$valid->execute([$cat,$t,$uid]);
 if(!in_array($t,['income','expense'],true)||$amount<=0||!$valid->fetch()||!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)){set_flash('Dữ liệu không hợp lệ. Hãy kiểm tra loại, danh mục, ngày và số tiền.','error');redirect('transactions.php');}
 if($id){$s=$pdo->prepare("UPDATE transactions SET category_id=?,type=?,amount=?,transaction_date=?,note=? WHERE id=? AND user_id=?");$s->execute([$cat,$t,$amount,$date,$note,$id,$uid]);set_flash('Đã cập nhật giao dịch.');}
 else{$s=$pdo->prepare("INSERT INTO transactions(user_id,category_id,type,amount,transaction_date,note) VALUES(?,?,?,?,?,?)");$s->execute([$uid,$cat,$t,$amount,$date,$note]);set_flash('Đã thêm giao dịch.');}
 redirect('transactions.php');
}
$cats=$pdo->prepare("SELECT * FROM categories WHERE user_id IS NULL OR user_id=? ORDER BY name");$cats->execute([$uid]);$allCats=$cats->fetchAll();
$where="t.user_id=?";$params=[$uid];if($type){$where.=" AND t.type=?";$params[]=$type;}
if(!empty($_GET['from'])){$where.=" AND t.transaction_date>=?";$params[]=$_GET['from'];}if(!empty($_GET['to'])){$where.=" AND t.transaction_date<=?";$params[]=$_GET['to'];}
if(!empty($_GET['q'])){$where.=" AND (t.note LIKE ? OR c.name LIKE ?)";$params[]='%'.$_GET['q'].'%';$params[]='%'.$_GET['q'].'%';}
$s=$pdo->prepare("SELECT t.*,c.name category_name FROM transactions t JOIN categories c ON c.id=t.category_id WHERE $where ORDER BY t.transaction_date DESC,t.id DESC");$s->execute($params);$rows=$s->fetchAll();
$pageTitle=$edit?'Sửa giao dịch':($type==='income'?'Quản lý khoản thu':($type==='expense'?'Quản lý khoản chi':'Lịch sử giao dịch'));require 'includes/header.php';
?>
<section class="card form-card"><div class="section-head"><h2><?=$edit?'Cập nhật giao dịch':'Thêm giao dịch mới'?></h2><?php if($edit):?><a class="btn btn-light btn-small" href="transactions.php">Hủy sửa</a><?php endif;?></div>
<form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="id" value="<?=e($edit['id']??'')?>"><div class="form-grid">
<div class="field"><label>Loại giao dịch</label><select name="type" id="type" required><option value="income" <?=($edit['type']??$type)==='income'?'selected':''?>>Thu nhập</option><option value="expense" <?=($edit['type']??$type)==='expense'?'selected':''?>>Chi tiêu</option></select></div>
<div class="field"><label>Danh mục</label><select name="category_id" id="category" required><?php foreach($allCats as $c):?><option value="<?=$c['id']?>" data-type="<?=$c['type']?>" <?=($edit['category_id']??'')==$c['id']?'selected':''?>><?=e($c['name'])?></option><?php endforeach;?></select></div>
<div class="field"><label>Số tiền (VND)</label><input type="number" name="amount" min="1" step="1" value="<?=e($edit['amount']??'')?>" required placeholder="Ví dụ: 500000"></div>
<div class="field"><label>Ngày giao dịch</label><input type="date" name="transaction_date" value="<?= !empty($edit['transaction_date']) ? date('d/m/Y', strtotime($edit['transaction_date'])) : date('d/m/Y') ?>" placeholder="dd/mm/yyyy" required></div>
<div class="field full"><label>Ghi chú</label><input name="note" maxlength="255" value="<?=e($edit['note']??'')?>" placeholder="Ví dụ: Lương tháng 11, ăn trưa..."></div>
</div><div class="form-actions"><button class="btn"><?= $edit?'Lưu thay đổi':'Thêm giao dịch'?></button></div></form></section>
<section class="card" style="margin-top:18px"><div class="section-head"><h2>Danh sách giao dịch (<?=count($rows)?>)</h2></div>
<form class="filter" method="get"><div class="field"><label>Từ ngày</label><input type="date" name="from" value="<?=e($_GET['from']??'')?>"></div><div class="field"><label>Đến ngày</label><input type="date" name="to" value="<?=e($_GET['to']??'')?>"></div><div class="field"><label>Tìm kiếm</label><input name="q" value="<?=e($_GET['q']??'')?>" placeholder="Ghi chú, danh mục"></div><button class="btn">Lọc</button><a class="btn btn-light" href="transactions.php">Xóa lọc</a></form>
<div class="table-scroll"><table><thead><tr><th>NGÀY</th><th>GHI CHÚ</th><th>DANH MỤC</th><th>LOẠI</th><th>SỐ TIỀN</th><th>THAO TÁC</th></tr></thead><tbody>
<?php if(!$rows):?><tr><td colspan="6" class="empty">Chưa có dữ liệu phù hợp.</td></tr><?php else:foreach($rows as $r):?><tr><td><?=date('d/m/Y',strtotime($r['transaction_date']))?></td><td><?=e($r['note']?:'—')?></td><td><div class="category-name"><span class="category-icon <?=$r['type']?>"><?=e(category_icon($r['category_name'], $r['type']))?></span><strong><?=e($r['category_name'])?></strong></div></td><td><span class="pill <?=$r['type']?>"><?=$r['type']==='income'?'Thu':'Chi'?></span></td><td class="<?=$r['type']==='income'?'green':'red'?>"><strong><?=money($r['amount'])?></strong></td><td><div class="actions"><a class="btn btn-light btn-small" href="transactions.php?edit=<?=$r['id']?>">Sửa</a><form method="post" onsubmit="return confirm('Bạn chắc chắn muốn xóa giao dịch này?')"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="btn btn-danger btn-small">Xóa</button></form></div></td></tr><?php endforeach;endif;?>
</tbody></table></div></section>
<script>
const typeSelect=document.getElementById('type'),catSelect=document.getElementById('category');
function filterCats(){let opts=[...catSelect.options];opts.forEach(o=>o.hidden=o.dataset.type!==typeSelect.value);let selected=catSelect.selectedOptions[0];if(!selected||selected.hidden){let first=opts.find(o=>!o.hidden);if(first)catSelect.value=first.value;}}
typeSelect.addEventListener('change',filterCats);filterCats();
</script>
<?php require 'includes/footer.php';?>
