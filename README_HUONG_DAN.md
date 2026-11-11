# WEBSITE QUẢN LÝ THU NHẬP VÀ CHI TIÊU CÁ NHÂN

## 1. Công nghệ
- Front-end: HTML5, CSS3, JavaScript.
- Back-end: PHP 8.x (PDO).
- Cơ sở dữ liệu: MySQL/MariaDB trên XAMPP.
- Biểu đồ: Chart.js (tải từ CDN, cần Internet khi hiển thị biểu đồ).

## 2. Cài đặt và chạy trên Windows
1. Cài XAMPP (bản có PHP 8.x và MariaDB/MySQL).
2. Mở XAMPP Control Panel, nhấn **Start** ở Apache và MySQL.
3. Giải nén thư mục `quan_ly_thu_chi_ca_nhan`.
4. Chép cả thư mục vào `C:\xampp\htdocs\`. Đường dẫn cuối cùng cần là:
   `C:\xampp\htdocs\quan_ly_thu_chi_ca_nhan\index.php`
5. Mở trình duyệt: `http://localhost/phpmyadmin`.
6. Chọn tab **Import** → **Choose File** → chọn `database/quan_ly_chi_tieu.sql` → **Import**.
   Script tự tạo database `quan_ly_chi_tieu` và các bảng.
7. Mở thư mục dự án bằng Visual Studio Code: File → Open Folder.
8. Mở `config.php`; nếu MySQL XAMPP có mật khẩu thì sửa biến `$pass`.
9. Truy cập `http://localhost/quan_ly_thu_chi_ca_nhan/`.
10. Đăng nhập tài khoản mẫu bên dưới hoặc chọn Đăng ký để tạo tài khoản riêng.

## 3. Tài khoản mẫu
- Email: `admin@example.com`
- Mật khẩu: `password`

Nếu tài khoản mẫu không đăng nhập được, hãy tạo tài khoản mới qua chức năng Đăng ký; tài khoản đăng ký mặc định có vai trò user. Nên đổi thông tin tài khoản mẫu khi sử dụng thật.

## 4. Cấu trúc thư mục
- `assets/style.css`: giao diện và responsive.
- `config.php`: kết nối PDO tới MySQL.
- `includes/functions.php`: hàm dùng chung, xác thực đăng nhập, CSRF, định dạng tiền.
- `includes/header.php`, `footer.php`: bố cục trang và menu.
- `database/quan_ly_chi_tieu.sql`: cấu trúc bảng và dữ liệu danh mục mẫu.
- `login.php`, `register.php`, `logout.php`: xác thực.
- `index.php`: dashboard, tổng thu/chi/số dư, biểu đồ và ngân sách.
- `transactions.php`: CRUD giao dịch, tìm kiếm và lọc ngày.
- `categories.php`: CRUD danh mục do người dùng tạo.
- `budgets.php`: thiết lập ngân sách và cảnh báo vượt mức.
- `reports.php`: tổng hợp và xuất CSV mở được bằng Excel.
- `users.php`: quản trị và khóa/mở khóa tài khoản (admin).

## 5. Mô hình dữ liệu (ERD dạng văn bản)
- USERS (1) —— (N) TRANSACTIONS
- CATEGORIES (1) —— (N) TRANSACTIONS
- USERS (1) —— (N) CATEGORIES (danh mục riêng; danh mục mặc định có user_id NULL)
- USERS (1) —— (N) BUDGETS
- CATEGORIES (1) —— (N) BUDGETS

Khóa chính: id ở mỗi bảng.
Khóa ngoại: transactions.user_id → users.id; transactions.category_id → categories.id;
categories.user_id → users.id; budgets.user_id → users.id; budgets.category_id → categories.id.
Ràng buộc duy nhất: (user_id, category_id, budget_month) trong budgets.

## 6. Use Case chính
- Khách: đăng ký, đăng nhập.
- Người dùng: đăng xuất; xem dashboard; thêm/sửa/xóa giao dịch; quản lý danh mục;
  thiết lập ngân sách; xem cảnh báo; lọc lịch sử; xem và xuất báo cáo.
- Quản trị viên: các quyền người dùng và xem danh sách tài khoản, khóa/mở khóa tài khoản.

## 7. Kiểm thử đề xuất
1. Đăng ký bằng email mới; thử đăng nhập sai và đúng.
2. Thêm khoản thu 10.000.000 đồng; kiểm tra tổng thu.
3. Thêm khoản chi 500.000 đồng danh mục Ăn uống; kiểm tra tổng chi và số dư.
4. Sửa giao dịch; xác nhận số liệu dashboard thay đổi.
5. Xóa giao dịch; xác nhận giao dịch biến mất.
6. Tạo ngân sách Ăn uống 300.000 đồng; ghi khoản chi 500.000 đồng; kiểm tra cảnh báo vượt ngân sách.
7. Lọc giao dịch theo ngày và từ khóa.
8. Xuất báo cáo CSV, mở bằng Excel.
9. Tạo tài khoản user thứ hai; xác nhận user không xem được dữ liệu của tài khoản thứ nhất.
10. Đăng nhập admin; khóa/mở khóa tài khoản user.

## 8. Lưu ý
- Đây là phiên bản đồ án học tập chạy local, không phải ứng dụng tài chính triển khai công khai.
- Mật khẩu người dùng được lưu bằng `password_hash`; truy vấn dùng PDO Prepared Statements.
- Không nhập dữ liệu tài chính thật nếu máy tính dùng chung. Hãy đổi mật khẩu mẫu và sao lưu database.
- Biểu đồ cần kết nối Internet để tải Chart.js từ CDN.

## 9. Cài đặt phân hệ tiết kiệm và theo dõi nợ
Sau khi website hiện tại đã chạy, mở `http://localhost/phpmyadmin`, chọn database `quan_ly_chi_tieu`, vào tab **Import** và chạy `database/migration_savings_debts.sql`. Đây là migration bổ sung, không nhập lại tệp SQL khởi tạo và không xóa dữ liệu đang có.

Menu **Tiết kiệm** cho phép tạo mục tiêu, đặt số tiền và ngày dự kiến; ghi nhận gửi thêm hoặc rút tiền; xem số dư, tiến độ và lịch sử. Hệ thống chặn rút quá số dư. Xóa một mục tiêu cũng xóa lịch sử của mục tiêu đó.

Menu **Theo dõi nợ** phân biệt khoản cần trả và khoản cần thu hồi. Mỗi khoản có người liên quan, nội dung, số tiền gốc, hạn thanh toán và ghi chú. Có thể ghi nhiều lần thanh toán/thu hồi; số dư còn lại được tính từ lịch sử. Hệ thống chặn giao dịch vượt dư nợ, không cho giảm số tiền gốc thấp hơn số đã thanh toán và giữ lại khoản nợ đã có lịch sử để bảo toàn dữ liệu.

Mỗi thao tác ghi đều cần đăng nhập và CSRF token; các truy vấn giới hạn theo tài khoản hiện tại. Giao dịch cập nhật số dư dùng transaction và khóa bản ghi cha để tránh ghi vượt số tiền khi có nhiều yêu cầu cùng lúc.

### Kiểm tra thủ công sau khi cài migration
1. Đăng nhập và tạo mục tiêu tiết kiệm; gửi một khoản tiền rồi kiểm tra số dư và lịch sử.
2. Thử rút số tiền lớn hơn số dư; ứng dụng cần báo lỗi và không thêm dòng lịch sử. Rút một khoản nhỏ hơn số dư và kiểm tra số tiền còn lại.
3. Tạo một khoản **Tôi đi vay / cần trả** và một khoản **Tôi cho vay / cần thu**; xác nhận tổng dư nợ hiển thị đúng ở hai ô riêng.
4. Ghi nhận một phần thanh toán hoặc thu hồi; kiểm tra số dư và lịch sử. Thử ghi số tiền vượt số dư, ứng dụng phải từ chối.
5. Tạo tài khoản thứ hai và xác nhận tài khoản đó không nhìn thấy mục tiêu, khoản nợ hoặc lịch sử của tài khoản đầu tiên.
