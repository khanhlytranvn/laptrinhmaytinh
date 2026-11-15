# ME & MONEY – ỨNG DỤNG QUẢN LÝ TÀI CHÍNH CÁ NHÂN

## 1. Giới thiệu

**Me & Money** là ứng dụng quản lý tài chính cá nhân trên nền tảng Web. Ứng dụng hỗ trợ người dùng ghi nhận thu nhập, chi tiêu, thiết lập ngân sách, theo dõi mục tiêu tiết kiệm, quản lý các khoản nợ và xem báo cáo tổng hợp.

Đây là dự án học tập, được thiết kế để chạy trên máy tính cá nhân thông qua môi trường XAMPP.

## 2. Công nghệ sử dụng

| Thành phần | Công nghệ |
|---|---|
| Giao diện | HTML5, CSS3, JavaScript |
| Xử lý phía máy chủ | PHP 8.x |
| Kết nối cơ sở dữ liệu | PDO |
| Hệ quản trị cơ sở dữ liệu | MySQL/MariaDB |
| Máy chủ web cục bộ | Apache – XAMPP |
| Biểu đồ | Chart.js (tải từ CDN) |
| Công cụ soạn thảo | Visual Studio Code |

**Lưu ý:** Dashboard sử dụng Chart.js từ CDN, vì vậy máy tính cần có kết nối Internet để tải thư viện và hiển thị biểu đồ.

## 3. Yêu cầu trước khi cài đặt

- Máy tính sử dụng Windows.
- Đã cài XAMPP có PHP 8.x và MySQL/MariaDB.
- Có trình duyệt web.
- Có Visual Studio Code (khuyến nghị, không bắt buộc).
- Có kết nối Internet khi cần tải Chart.js từ CDN.

## 4. Cài đặt và chạy ứng dụng

### Bước 1. Khởi động XAMPP

1. Mở **XAMPP Control Panel**.
2. Nhấn **Start** tại hai dịch vụ:
   - Apache
   - MySQL
3. Đảm bảo cả hai dịch vụ đã chạy.

### Bước 2. Đưa mã nguồn vào thư mục htdocs

1. Giải nén thư mục dự án `laptrinhmaytinh`.
2. Sao chép toàn bộ thư mục vào thư mục cài đặt XAMPP:

   `C:\xampp\htdocs\`

3. Kiểm tra cấu trúc đường dẫn phải có tệp:

   `C:\xampp\htdocs\laptrinhmaytinh\index.php`

Nếu cài XAMPP ở vị trí khác, thay `C:\xampp` bằng đường dẫn cài đặt thực tế.

### Bước 3. Tạo cơ sở dữ liệu

1. Mở trình duyệt và truy cập:

   `http://localhost/phpmyadmin`

2. Chọn tab **Import**.
3. Nhấn **Choose File** và chọn tệp:

   `laptrinhmaytinh/database/quan_ly_chi_tieu.sql`

4. Nhấn **Import** hoặc **Go** để thực thi.
5. Tệp SQL sẽ tự tạo cơ sở dữ liệu có tên `quan_ly_chi_tieu` và các bảng dữ liệu ban đầu.

### Bước 4. Cài đặt phân hệ tiết kiệm và theo dõi nợ

Sau khi nhập thành công tệp SQL khởi tạo:

1. Trong phpMyAdmin, chọn cơ sở dữ liệu `quan_ly_chi_tieu`.
2. Chọn tab **Import**.
3. Chọn tệp:

   `laptrinhmaytinh/database/migration_savings_debts.sql`

4. Nhấn **Import** hoặc **Go**.

Đây là tệp migration bổ sung các bảng phục vụ chức năng tiết kiệm và theo dõi nợ. Chỉ chạy migration sau khi đã tạo cơ sở dữ liệu bằng `quan_ly_chi_tieu.sql`. Không cần xóa hoặc tạo lại cơ sở dữ liệu hiện có.

### Bước 5. Kiểm tra cấu hình kết nối

Mở tệp `config.php` bằng Visual Studio Code. Cấu hình mặc định trong dự án:

```php
$host = 'localhost';
$db   = 'quan_ly_chi_tieu';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
```

Trong XAMPP, tài khoản MySQL `root` thường không có mật khẩu. Nếu máy tính đã đặt mật khẩu cho MySQL, sửa giá trị `$pass` cho phù hợp.

Không thay đổi tên cơ sở dữ liệu nếu vẫn sử dụng database được tạo từ tệp SQL của dự án.

### Bước 6. Truy cập ứng dụng

Mở trình duyệt và truy cập:

`http://localhost/laptrinhmaytinh/`

Nếu trang đăng nhập xuất hiện, ứng dụng đã được Apache phục vụ.

## 5. Tài khoản đăng nhập

Tệp SQL khởi tạo có khai báo tài khoản mẫu:

| Thông tin | Giá trị |
|---|---|
| Email | `admin@example.com` |
| Mật khẩu | `password` |
| Vai trò | Admin |

Nếu tài khoản mẫu không đăng nhập được, có thể đăng ký tài khoản mới bằng chức năng **Đăng ký**. Tài khoản đăng ký mới mặc định có vai trò User.

**Khuyến nghị:** Tài khoản mẫu chỉ dùng để chạy thử trong môi trường học tập. Nên đổi mật khẩu và không sử dụng thông tin mẫu khi triển khai trên môi trường có dữ liệu thật.

## 6. Các chức năng chính

### 6.1. Tài khoản

- Đăng ký tài khoản.
- Đăng nhập và đăng xuất.
- Kiểm tra trạng thái đăng nhập khi truy cập các trang nghiệp vụ.
- Phân biệt vai trò User và Admin.

### 6.2. Dashboard

- Hiển thị thông tin tổng quan về thu nhập và chi tiêu.
- Theo dõi số dư.
- Hiển thị biểu đồ thống kê.
- Theo dõi tình hình sử dụng ngân sách.

### 6.3. Quản lý giao dịch

- Thêm giao dịch thu nhập hoặc chi tiêu.
- Chỉnh sửa và xóa giao dịch.
- Chọn danh mục, ngày giao dịch và nhập ghi chú.
- Tìm kiếm hoặc lọc lịch sử giao dịch.

### 6.4. Quản lý danh mục

- Xem danh sách danh mục.
- Tạo và quản lý danh mục cá nhân.
- Phân loại danh mục thu nhập và chi tiêu.
- Hệ thống có các danh mục mặc định dùng chung.

### 6.5. Quản lý ngân sách

- Thiết lập ngân sách theo danh mục và tháng.
- Theo dõi số tiền đã chi.
- So sánh chi tiêu với ngân sách.
- Hiển thị tình trạng vượt ngân sách.

### 6.6. Quản lý tiết kiệm

- Tạo mục tiêu tiết kiệm.
- Thiết lập số tiền mục tiêu và ngày dự kiến.
- Ghi nhận các lần gửi thêm hoặc rút tiền.
- Theo dõi số dư, tiến độ và lịch sử.
- Hệ thống từ chối thao tác rút vượt quá số dư hiện có.

### 6.7. Theo dõi khoản nợ

- Tạo khoản cần trả hoặc khoản cần thu hồi.
- Lưu tên người liên quan, nội dung, số tiền gốc và hạn thanh toán.
- Ghi nhận nhiều lần thanh toán hoặc thu hồi.
- Theo dõi số dư còn lại và lịch sử.
- Hệ thống từ chối ghi nhận số tiền vượt quá dư nợ còn lại.

### 6.8. Báo cáo

- Xem thông tin tổng hợp tài chính.
- Theo dõi dữ liệu thu nhập và chi tiêu.
- Xuất dữ liệu báo cáo định dạng CSV để mở bằng phần mềm bảng tính.

### 6.9. Quản lý tài khoản dành cho Admin

- Xem danh sách tài khoản.
- Khóa tài khoản người dùng.
- Mở khóa tài khoản người dùng.
- Người dùng thông thường không được phép truy cập trang quản lý tài khoản.

## 7. Cấu trúc thư mục và mã nguồn

```text
laptrinhmaytinh/
│
├── assets/
│   ├── logo qltc.jpg
│   └── style.css
│
├── database/
│   ├── ERD.mmd
│   ├── USE_CASE.mmd
│   ├── quan_ly_chi_tieu.sql
│   └── migration_savings_debts.sql
│
├── includes/
│   ├── footer.php
│   ├── functions.php
│   └── header.php
│
├── budgets.php
├── categories.php
├── config.php
├── debts.php
├── index.php
├── login.php
├── logout.php
├── register.php
├── reports.php
├── savings.php
├── settings.php
├── transactions.php
└── users.php
```

### Vai trò các tệp quan trọng

| Tệp/thư mục | Chức năng |
|---|---|
| `config.php` | Khởi tạo session và kết nối PDO tới MySQL. |
| `includes/functions.php` | Các hàm dùng chung, xác thực, phân quyền, CSRF và định dạng dữ liệu. |
| `includes/header.php` | Phần đầu trang và menu điều hướng dùng chung. |
| `includes/footer.php` | Phần chân trang dùng chung. |
| `index.php` | Dashboard và thông tin tổng quan. |
| `login.php`, `register.php`, `logout.php` | Đăng nhập, đăng ký và đăng xuất. |
| `transactions.php` | Quản lý giao dịch. |
| `categories.php` | Quản lý danh mục. |
| `budgets.php` | Quản lý ngân sách. |
| `savings.php` | Quản lý mục tiêu tiết kiệm. |
| `debts.php` | Theo dõi khoản nợ. |
| `reports.php` | Báo cáo và xuất CSV. |
| `users.php` | Quản lý tài khoản dành cho Admin. |
| `settings.php` | Trang cài đặt. |
| `assets/style.css` | Định dạng giao diện. |
| `database/quan_ly_chi_tieu.sql` | Tạo cơ sở dữ liệu và các bảng ban đầu. |
| `database/migration_savings_debts.sql` | Tạo các bảng tiết kiệm và theo dõi nợ. |
| `database/ERD.mmd` | Sơ đồ thực thể – liên kết. |
| `database/USE_CASE.mmd` | Sơ đồ Use Case. |

## 8. Mô hình dữ liệu

Cơ sở dữ liệu sử dụng tên `quan_ly_chi_tieu`.

Các bảng chính:

- `users`: thông tin tài khoản và vai trò.
- `categories`: danh mục thu nhập và chi tiêu.
- `transactions`: các giao dịch tài chính.
- `budgets`: ngân sách theo danh mục và tháng.
- `savings_goals`: mục tiêu tiết kiệm.
- `savings_entries`: lịch sử gửi và rút tiền tiết kiệm.
- `debts`: thông tin khoản cần trả và khoản cần thu hồi.
- `debt_entries`: lịch sử thanh toán hoặc thu hồi.

Sơ đồ chi tiết được lưu trong `database/ERD.mmd`. Sơ đồ Use Case được lưu trong `database/USE_CASE.mmd`.

## 9. Kiểm thử đề xuất

Các trường hợp dưới đây là hướng dẫn kiểm thử thủ công. Nhóm cần thực hiện trên ứng dụng đang chạy và ghi nhận kết quả thực tế trước khi kết luận Đạt/Không đạt.

| Mã | Thao tác kiểm thử | Kết quả mong đợi |
|---|---|---|
| TC01 | Đăng ký bằng email mới. | Tài khoản được tạo và lưu trong bảng `users`. |
| TC02 | Đăng nhập bằng mật khẩu sai. | Hệ thống từ chối đăng nhập. |
| TC03 | Đăng nhập bằng thông tin chính xác. | Người dùng được chuyển vào ứng dụng. |
| TC04 | Thêm khoản thu 10.000.000 VND. | Giao dịch được lưu; tổng thu tăng tương ứng. |
| TC05 | Thêm khoản chi 500.000 VND. | Giao dịch được lưu; tổng chi và số dư cập nhật đúng. |
| TC06 | Sửa một giao dịch. | Thông tin mới được lưu; số liệu tổng hợp cập nhật. |
| TC07 | Xóa một giao dịch. | Giao dịch bị xóa khỏi danh sách; số liệu cập nhật. |
| TC08 | Tạo ngân sách 300.000 VND và ghi nhận chi 500.000 VND cùng danh mục. | Hệ thống thể hiện tình trạng vượt ngân sách. |
| TC09 | Lọc giao dịch theo ngày hoặc từ khóa. | Danh sách phù hợp điều kiện lọc. |
| TC10 | Xuất báo cáo CSV. | Tệp CSV được tải và mở được bằng phần mềm bảng tính. |
| TC11 | Đăng nhập User B và thử truy cập giao dịch thuộc User A. | User B không được xem hoặc chỉnh sửa dữ liệu của User A. |
| TC12 | Đăng nhập User thường và truy cập `users.php`. | Hệ thống từ chối truy cập, trả mã HTTP 403. |
| TC13 | Admin khóa hoặc mở khóa tài khoản User. | Trạng thái tài khoản thay đổi theo thao tác. |
| TC14 | Gửi/rút tiền tiết kiệm. | Số dư và lịch sử cập nhật; rút vượt số dư bị từ chối. |
| TC15 | Thanh toán/thu hồi khoản nợ. | Số dư còn lại cập nhật; ghi nhận vượt dư nợ bị từ chối. |

### Hướng dẫn kiểm thử bảo mật TC11

1. Tạo hai tài khoản User A và User B.
2. Đăng nhập User A, tạo một giao dịch và ghi lại ID giao dịch.
3. Đăng xuất User A.
4. Đăng nhập User B.
5. Thử mở trang sửa giao dịch bằng ID của User A.
6. Kiểm tra xem User B có xem hoặc thay đổi được dữ liệu của User A hay không.

Kết quả mong đợi: User B không thể truy cập hoặc chỉnh sửa giao dịch thuộc User A.

### Hướng dẫn kiểm thử phân quyền TC12

1. Đăng nhập bằng tài khoản User thông thường.
2. Nhập trực tiếp đường dẫn `http://localhost/laptrinhmaytinh/users.php`.
3. Kiểm tra phản hồi của hệ thống.

Kết quả mong đợi: hệ thống từ chối truy cập và trả mã HTTP 403.

### Kiểm thử phân hệ tiết kiệm

1. Tạo một mục tiêu tiết kiệm và ghi nhận một khoản gửi.
2. Kiểm tra số dư và lịch sử gửi tiền.
3. Thử rút số tiền lớn hơn số dư hiện có; hệ thống phải từ chối.
4. Rút một khoản nhỏ hơn số dư và kiểm tra số tiền còn lại.

### Kiểm thử phân hệ theo dõi nợ

1. Tạo một khoản cần trả và một khoản cần thu hồi.
2. Ghi nhận một phần thanh toán hoặc thu hồi.
3. Kiểm tra số dư còn lại và lịch sử.
4. Thử ghi nhận số tiền vượt quá số dư còn lại; hệ thống phải từ chối.

## 10. Một số lưu ý khi sử dụng

- Đây là dự án học tập chạy trong môi trường local, không phải ứng dụng tài chính đã được triển khai công khai.
- Mật khẩu người dùng được xử lý bằng cơ chế `password_hash` của PHP.
- Các truy vấn cơ sở dữ liệu sử dụng PDO Prepared Statements.
- Các thao tác ghi dữ liệu có kiểm tra CSRF token.
- Người dùng chỉ được thao tác trên dữ liệu thuộc tài khoản của mình theo cơ chế kiểm tra quyền sở hữu trong các trang nghiệp vụ.
- Trang `users.php` dành cho Admin.
- Nên sao lưu cơ sở dữ liệu trước khi thử nghiệm thay đổi lớn.
- Không sử dụng tài khoản mẫu hoặc dữ liệu tài chính thật trên máy tính dùng chung.
- Khi gặp lỗi kết nối, kiểm tra Apache, MySQL, tên database và thông tin trong `config.php`.

## 11. Xử lý một số lỗi thường gặp

### Lỗi không kết nối được cơ sở dữ liệu

- Kiểm tra MySQL đã được Start trong XAMPP chưa.
- Kiểm tra database `quan_ly_chi_tieu` đã được import chưa.
- Kiểm tra `$host`, `$db`, `$user` và `$pass` trong `config.php`.

### Lỗi không tìm thấy trang

- Kiểm tra thư mục dự án nằm trực tiếp trong `htdocs`.
- Kiểm tra tên thư mục trong URL có đúng là `laptrinhmaytinh` không.
- Kiểm tra Apache đã được Start chưa.

### Không đăng nhập được tài khoản mẫu

- Kiểm tra đã import đúng tệp `quan_ly_chi_tieu.sql` chưa.
- Thử đăng ký tài khoản mới.
- Nếu tài khoản mẫu đã bị thay đổi hoặc xóa trong cơ sở dữ liệu, thông tin đăng nhập mẫu sẽ không còn sử dụng được.

### Không hiển thị biểu đồ

- Kiểm tra kết nối Internet do Chart.js được tải từ CDN.
- Tải lại trang sau khi kết nối Internet ổn định.

---

**Tên dự án:** Me & Money  
**Thư mục dự án:** `laptrinhmaytinh`  
**Cơ sở dữ liệu:** `quan_ly_chi_tieu`  
**Phiên bản tài liệu:** README hướng dẫn cài đặt, sử dụng và kiểm thử.
