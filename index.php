<?php
require 'config.php'; require 'includes/functions.php'; require_login();
$pageTitle='Tổng quan tài chính';
$uid=current_user_id();
$month=date('Y-m');
$stmt=$pdo->prepare("SELECT type, COALESCE(SUM(amount),0) total FROM transactions WHERE user_id=? AND DATE_FORMAT(transaction_date,'%Y-%m')=? GROUP BY type");
$stmt->execute([$uid,$month]); $totals=['income'=>0,'expense'=>0];
foreach($stmt as $r) $totals[$r['type']] = (float)$r['total'];
$balance=$totals['income']-$totals['expense'];
$stmt=$pdo->prepare("SELECT t.*,c.name category_name FROM transactions t JOIN categories c ON c.id=t.category_id WHERE t.user_id=? ORDER BY t.transaction_date DESC,t.id DESC LIMIT 7");
$stmt->execute([$uid]); $recent=$stmt->fetchAll();
$stmt=$pdo->prepare("SELECT b.amount,c.name,c.id,COALESCE(SUM(t.amount),0) spent FROM budgets b JOIN categories c ON c.id=b.category_id LEFT JOIN transactions t ON t.user_id=b.user_id AND t.category_id=b.category_id AND t.type='expense' AND DATE_FORMAT(t.transaction_date,'%Y-%m')=b.budget_month WHERE b.user_id=? AND b.budget_month=? GROUP BY b.id");
$stmt->execute([$uid,$month]); $budgets=$stmt->fetchAll();
$chartStmt=$pdo->prepare("SELECT DATE_FORMAT(transaction_date,'%d/%m') day,type,SUM(amount) total FROM transactions WHERE user_id=? AND transaction_date>=DATE_SUB(CURDATE(),INTERVAL 6 DAY) GROUP BY day,type ORDER BY MIN(transaction_date)");
$chartStmt->execute([$uid]); $chartRows=$chartStmt->fetchAll();
$labels=[];$income=[];$expense=[];
for($i=6;$i>=0;$i--){$d=date('d/m',strtotime("-$i days"));$labels[]=$d;$income[$d]=0;$expense[$d]=0;}
foreach($chartRows as $r){if(isset($income[$r['day']])){if($r['type']==='income')$income[$r['day']]=(float)$r['total'];else $expense[$r['day']]=(float)$r['total'];}}
require 'includes/header.php';
?>
<div class="grid stats">
 <div class="card"><span class="stat-icon">↗</span><div class="stat-label">Tổng thu tháng này</div><div class="stat-value green"><?=money($totals['income'])?></div><div class="stat-note">Từ ngày 01 đến <?=date('d/m/Y')?></div></div>
 <div class="card"><span class="stat-icon">↙</span><div class="stat-label">Tổng chi tháng này</div><div class="stat-value red"><?=money($totals['expense'])?></div><div class="stat-note">Các khoản chi đã ghi nhận</div></div>
 <div class="card"><span class="stat-icon">◈</span><div class="stat-label">Số dư tháng này</div><div class="stat-value <?= $balance>=0?'blue':'red' ?>"><?=money($balance)?></div><div class="stat-note">Tổng thu trừ tổng chi</div></div>
 <div class="card"><span class="stat-icon">▤</span><div class="stat-label">Ngân sách đã thiết lập</div><div class="stat-value"><?=count($budgets)?></div><div class="stat-note">Danh mục trong tháng <?=date('m/Y')?></div></div>
</div>
<div class="grid two-col">
 <section class="card"><div class="section-head"><h2>Biến động thu – chi 7 ngày gần đây</h2><span class="tag">7 ngày</span></div><div class="chart-wrap"><canvas id="weeklyChart"></canvas></div></section>
 <section class="card"><div class="section-head"><h2>Theo dõi ngân sách</h2><a class="btn btn-light btn-small" href="budgets.php">Quản lý</a></div>
 <?php if(!$budgets): ?><div class="empty">Chưa có ngân sách tháng này.<br><br><a class="btn btn-light" href="budgets.php">Thiết lập ngân sách</a></div><?php else: foreach($budgets as $b): $pct=$b['amount']>0?($b['spent']/$b['amount']*100):0; ?>
 <div class="budget-row"><div class="budget-meta"><strong><?=e($b['name'])?></strong><span><?=money($b['spent'])?> / <?=money($b['amount'])?></span></div><div class="progress <?=$pct>100?'over':''?>"><span style="width:<?=min(100,$pct)?>%"></span></div><div class="stat-note"><?=$pct>100?'Đã vượt ngân sách':number_format($pct,1).'% ngân sách đã sử dụng'?></div></div>
 <?php endforeach; endif; ?>
 </section>
</div>
<section class="card" style="margin-top:18px"><div class="section-head"><h2>Giao dịch gần đây</h2><a class="btn btn-light btn-small" href="transactions.php">Xem tất cả</a></div>
<div class="table-scroll"><table><thead><tr><th>NGÀY</th><th>GIAO DỊCH</th><th>DANH MỤC</th><th>SỐ TIỀN</th><th>LOẠI</th></tr></thead><tbody>
<?php if(!$recent): ?><tr><td colspan="5" class="empty">Chưa có giao dịch nào. Hãy thêm khoản thu hoặc chi đầu tiên.</td></tr><?php else: foreach($recent as $t): ?><tr><td><?=date('d/m/Y',strtotime($t['transaction_date']))?></td><td><?=e($t['note']?:$t['category_name'])?></td><td><?=e($t['category_name'])?></td><td class="<?=$t['type']==='income'?'green':'red'?>"><strong><?=$t['type']==='income'?'+':'-'?><?=money($t['amount'])?></strong></td><td><span class="pill <?=$t['type']?>"><?=$t['type']==='income'?'Thu nhập':'Chi tiêu'?></span></td></tr><?php endforeach; endif; ?>
</tbody></table></div></section>
<script>
new Chart(document.getElementById('weeklyChart'),{type:'bar',data:{labels:<?=json_encode($labels)?>,datasets:[{label:'Thu nhập',data:<?=json_encode(array_values($income))?>,backgroundColor:'#37b58a',borderRadius:5},{label:'Chi tiêu',data:<?=json_encode(array_values($expense))?>,backgroundColor:'#ef8588',borderRadius:5}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom'}},scales:{y:{beginAtZero:true,ticks:{callback:v=>v.toLocaleString('vi-VN')}}}}});
</script>
<?php require 'includes/footer.php'; ?>
