<?php
function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
function is_logged_in(): bool {
    return isset($_SESSION['user']);
}
function require_login(): void {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}
function current_user_id(): int {
    return (int)($_SESSION['user']['id'] ?? 0);
}
function is_admin(): bool {
    return ($_SESSION['user']['role'] ?? '') === 'admin';
}
function money($amount): string {
    return number_format((float)$amount, 0, ',', '.') . ' VND';
}
function set_flash(string $message, string $type='success'): void {
    $_SESSION['flash'] = ['message'=>$message, 'type'=>$type];
}
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function verify_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf'] ?? '';
        if (!hash_equals($_SESSION['csrf'] ?? '', $token)) {
            http_response_code(403);
            exit('Yêu cầu không hợp lệ. Vui lòng tải lại trang.');
        }
    }
}
function redirect(string $url): never {
    header("Location: $url");
    exit;
}
// Tự động chọn icon theo tên danh mục
function category_icon($name, $type) {
    $n = mb_strtolower(trim($name), 'UTF-8');

    $icons = [
        'ăn uống' => '🍜',
        'đồ ăn' => '🍜',
        'thực phẩm' => '🥗',
        'mua sắm' => '🛍️',
        'quần áo' => '👕',
        'di chuyển' => '🚗',
        'xăng xe' => '⛽',
        'học tập' => '📚',
        'giáo dục' => '🎓',
        'nhà cửa' => '🏠',
        'tiền nhà' => '🏠',
        'sức khỏe' => '💊',
        'y tế' => '💊',
        'giải trí' => '🎮',
        'du lịch' => '✈️',
        'lương' => '💰',
        'tiền lương' => '💰',
        'thưởng' => '🎁',
        'đầu tư' => '📈',
        'kinh doanh' => '💼',
        'quà tặng' => '🎀',
        'khác' => '📦'
    ];

    foreach ($icons as $keyword => $icon) {
        if (mb_strpos($n, $keyword, 0, 'UTF-8') !== false) {
            return $icon;
        }
    }

    return $type === 'income' ? '💵' : '📌';
}