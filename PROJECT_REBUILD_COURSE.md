# GIÁO TRÌNH: XÂY DỰNG GIFT SHOP E-COMMERCE TỪ CON SỐ 0
**Tác giả:** Senior Software Engineer Mentor

Chào bạn, giả sử toàn bộ source code của project Gift Shop hiện tại bị xóa sạch. Không sao cả, một Senior Engineer không phụ thuộc vào code cũ, mà phụ thuộc vào tư duy hệ thống. Hôm nay, tôi sẽ mentor cho bạn cách xây dựng lại chính project này từng bước một, như cách một developer thực thụ làm việc ở môi trường thực tế.

Tài liệu này là cẩm nang chi tiết nhất. Hãy đọc kỹ, code theo và suy ngẫm những câu hỏi mở (Trade-off) ở mỗi phần.

---

## ===================================
## PHẦN 1: CHUẨN BỊ MÔI TRƯỜNG
## ===================================

Trước khi viết dòng code đầu tiên, môi trường phải chuẩn. Một môi trường sai lệch phiên bản sẽ gây ra 90% các bug "chạy được trên máy em nhưng tạch trên server".

### 1.1 Các công cụ cần cài đặt
- **PHP ^8.3**: Project dùng Laravel 11/13, yêu cầu cú pháp mới của PHP 8.2/8.3.
- **Composer ^2.7**: Trình quản lý package của PHP.
- **Node.js ^20.x & npm**: Để compile frontend assets (Vite & TailwindCSS).
- **SQLite (Dev) / PostgreSQL (Prod)**: Dùng SQLite cho môi trường local để phát triển nhanh, không cần setup server DB phức tạp. Lên production sẽ dùng PostgreSQL.
- **Git**: Quản lý source code.

### 1.2 IDE & Extensions (VS Code)
Khuyến nghị dùng **Visual Studio Code (VS Code)** hoặc **PhpStorm**.
Nếu dùng VS Code, hãy cài các extensions sau:
- **PHP Intelephense**: Auto-complete và báo lỗi syntax PHP cực chuẩn.
- **Laravel Blade Snippets**: Hỗ trợ syntax highlight cho file `.blade.php`.
- **Tailwind CSS IntelliSense**: Gợi ý class Tailwind khi gõ.
- **Alpine.js IntelliSense**: Hỗ trợ viết Alpine.js trên frontend.

### 1.3 Lệnh khởi tạo môi trường (Nếu clone về)
Nếu bạn bắt đầu một project Laravel mới toanh, hãy chạy lệnh:
```bash
composer create-project laravel/laravel gift-shop
cd gift-shop
npm install
```
**Giải thích:**
- `composer create-project laravel/laravel`: Tải bộ khung (skeleton) mới nhất của Laravel từ repository chính thức.
- `npm install`: Cài đặt các package frontend (Vite, Axios) được định nghĩa trong `package.json`.

---

## ===================================
## PHẦN 2: THIẾT KẾ DỰ ÁN TRƯỚC KHI CODE
## ===================================

Senior không cắm đầu vào code ngay. Chúng ta phải mổ xẻ bài toán.

### 2.1 Bài toán
Xây dựng một hệ thống thương mại điện tử chuyên bán Quà Tặng (Gift Shop). Hệ thống gồm 2 phần:
1. **Storefront (Dành cho khách hàng):** Xem sản phẩm, phân loại theo danh mục, thêm vào giỏ hàng, đặt hàng (Checkout), xem lịch sử mua hàng.
2. **Admin Dashboard (Dành cho chủ shop):** Quản lý danh mục, quản lý sản phẩm, xử lý đơn hàng, xem thống kê doanh thu.

### 2.2 Các Module Chính & Lý do cần thiết

| Module | Chức năng | Tại sao cần? |
|--------|-----------|--------------|
| **Auth** | Đăng ký, Đăng nhập, Phân quyền (Admin/Customer). | Xác thực người dùng, bảo mật các chức năng quản trị. |
| **Category** | Quản lý danh mục quà tặng. | Giúp khách hàng lọc sản phẩm dễ dàng. |
| **Product** | Quản lý thông tin, giá, tồn kho, hình ảnh. | Core entity của bất kỳ hệ thống E-commerce nào. |
| **Cart** | Lưu trữ tạm thời sản phẩm khách muốn mua. | Nâng cao trải nghiệm mua sắm, lưu trong Session để khách chưa đăng nhập vẫn dùng được. |
| **Order** | Ghi nhận đơn hàng, tính tổng tiền, theo dõi trạng thái. | Lưu lại giao dịch pháp lý và tài chính giữa shop và khách. |

---

## ===================================
## PHẦN 3: THỨ TỰ XÂY DỰNG DỰ ÁN
## ===================================

Việc xây dựng dự án cần đi từ lõi (Core) ra ngoài (UI/UX). Đây là timeline phát triển thực tế:

- **Bước 1: Khởi tạo Project & Cài đặt thư viện** (Tạo nền móng).
- **Bước 2: Setup Database & Migrations** (Xây dựng cấu trúc dữ liệu - Model, Schema).
- **Bước 3: Tích hợp Authentication** (Xây dựng lớp bảo mật đầu tiên bằng Laravel Breeze).
- **Bước 4: Module Danh Mục (Category)** (Tạo danh mục trước vì Sản phẩm cần thuộc về Danh mục).
- **Bước 5: Module Sản Phẩm (Product)** (Entity lõi, quản lý tồn kho, upload ảnh).
- **Bước 6: Xây dựng Storefront & Catalog** (Hiển thị UI cho khách hàng xem hàng).
- **Bước 7: Module Giỏ Hàng (Cart)** (Dùng Session để lưu giỏ hàng).
- **Bước 8: Module Đặt Hàng (Checkout & Order)** (Xử lý giao dịch, trừ tồn kho an toàn).
- **Bước 9: Admin Dashboard & Thống kê** (Dành cho chủ shop theo dõi).
- **Bước 10: Viết Unit/Feature Test** (Đảm bảo hệ thống không gãy khi update).

**Tại sao phải theo thứ tự này?**
Bạn không thể làm "Đặt hàng" nếu chưa có "Sản phẩm" để mua. Bạn không thể tạo "Sản phẩm" nếu chưa có "Danh mục" để phân loại. Authentication cần có ngay từ đầu để tách biệt luồng Admin và Customer. Lộ trình này đảm bảo bạn code đến đâu, test được ngay đến đó.

---

## ===================================
## PHẦN 4: HƯỚNG DẪN TỪNG BƯỚC
## ===================================

### BƯỚC 1: Khởi tạo & Cài đặt
**Mục tiêu:** Set up Laravel, Tailwind CSS, Alpine.js.

**Lệnh cần chạy:**
```bash
composer create-project laravel/laravel gift-shop
cd gift-shop
composer require laravel/breeze --dev
php artisan breeze:install blade
npm install
npm run dev
```

**Giải thích:**
- `laravel/breeze`: Scaffold sẵn hệ thống Login/Register dùng Tailwind và Blade. Rất nhẹ và phù hợp cho dự án này.
- `npm run dev`: Chạy Vite server để biên dịch CSS/JS realtime.

### BƯỚC 2: Database Design (Models & Migrations)
**Mục tiêu:** Tạo các bảng Categories, Products, Orders, Order_Items.

**Lệnh:**
```bash
php artisan make:model Category -m
php artisan make:model Product -m
php artisan make:model Order -m
php artisan make:model OrderItem -m
```

**Code: Migration của Product (`database/migrations/xxx_create_products_table.php`)**
```php
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->foreignId('category_id')->constrained()->restrictOnDelete();
    $table->string('name', 200);
    $table->string('slug')->unique();
    $table->text('short_description')->nullable();
    $table->integer('price');
    $table->integer('compare_price')->nullable();
    $table->integer('stock')->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->softDeletes(); // Xóa mềm để không mất lịch sử đơn hàng
});
```

**Giải thích:**
- `restrictOnDelete()` trên `category_id`: Không cho phép xóa Danh mục nếu Danh mục đó vẫn còn Sản phẩm. Đảm bảo toàn vẹn dữ liệu.
- `softDeletes()`: Nếu shop bỏ bán 1 món, ta chỉ "ẩn" nó đi thay vì xóa cứng, tránh làm lỗi các đơn hàng cũ đã mua món này.
- **Test:** Chạy `php artisan migrate` để tạo DB.

### BƯỚC 3: Middleware Phân Quyền Admin
**Mục tiêu:** Tách biệt User thường và Admin.

**Lệnh:**
```bash
php artisan make:middleware EnsureUserIsAdmin
```

**Code: `EnsureUserIsAdmin.php`**
```php
public function handle(Request $request, Closure $next)
{
    if (!auth()->check() || auth()->user()->role !== 'admin') {
        abort(403, 'Unauthorized Access.');
    }
    return $next($request);
}
```
**Giải thích:** Thêm field `role` vào bảng `users`. Middleware này sẽ chặn mọi request vào route `/admin/*` nếu user không phải là admin.

---

## ===================================
## PHẦN 5: LUỒNG PHÁT TRIỂN THỰC TẾ (VÍ DỤ: ĐẶT HÀNG)
## ===================================

Đây là cách một Senior xây dựng luồng phức tạp như Checkout:

1. **Migration & Model:** Đảm bảo bảng `orders` có `idempotency_key` (tránh double-submit), `status`, `total_amount`.
2. **Form Request (`CheckoutRequest`):**
   - Validate thông tin giao hàng: `recipient_name`, `phone`, `address` phải bắt buộc (`required`).
3. **Service Layer (`OrderService`):** KHÔNG viết logic tính toán trong Controller.
   ```php
   public function createFromCart(User $user, array $shippingData, string $idempotencyKey) {
       // 1. Kiểm tra idempotency_key xem đơn này đã tạo chưa
       // 2. Dùng DB::transaction() để đảm bảo tính nguyên vẹn
       // 3. Loop qua cart items, trừ tồn kho (atomic decrement)
       // 4. Tạo Order và OrderItem
       // 5. Xóa Giỏ hàng
   }
   ```
4. **Controller (`CheckoutController`):** Chỉ làm nhiệm vụ nhận Request, gọi Service, và trả về View hoặc Redirect.
5. **Route:** Định nghĩa `POST /checkout` trỏ vào Controller.
6. **Test (`CheckoutTest`):** Viết Feature test giả lập giỏ hàng có 2 món, gọi POST `/checkout`, assert DB sinh ra 1 order và tồn kho bị trừ đúng số lượng.

---

## ===================================
## PHẦN 6: CHECKPOINT SAU MỖI GIAI ĐOẠN
## ===================================

**Checkpoint Giai đoạn Lõi (Phần 1-3):**
- **Kiến thức đã học:** Schema Builder, Eloquent Relationships (1-N giữa Category và Product), Middleware phân quyền.
- **Cách kiểm tra:**
  - Vào Tinker (`php artisan tinker`), gõ `User::factory()->create(['role' => 'admin'])`.
  - Thử login và truy cập route Admin. Nếu không phải Admin thì bị văng 403.

---

## ===================================
## PHẦN 7: BÀI TẬP THỰC HÀNH
## ===================================

Dựa trên project Gift Shop, đây là bài tập để rèn luyện:

- **Mức Dễ:** Thêm cột `view_count` vào bảng `products`. Mỗi lần user vào xem chi tiết sản phẩm (`ProductController@show`), tăng biến này lên 1.
- **Mức Trung bình:** Xây dựng tính năng "Mã giảm giá" (Coupon). Tạo model Coupon, logic tính toán giảm giá ở `OrderService` và lưu `discount_amount` vào bảng `orders`.
- **Mức Khó:** Đổi hệ thống Cart từ Session sang Database (để khách hàng đăng nhập ở máy tính khác vẫn thấy giỏ hàng đang chọn dở trên điện thoại).

---

## ===================================
## PHẦN 8: NHỮNG LỖI THƯỜNG GẶP
## ===================================

1. **Lỗi N+1 Query (Performance Killer)**
   - **Dấu hiệu:** Tải trang danh sách Đơn hàng bị chậm. Gắn Laravel Debugbar thấy chạy hàng trăm query SQL.
   - **Cách sửa:** Thay vì `Order::all()`, hãy dùng Eager Loading: `Order::with('items.product')->get()`.

2. **Lỗi Double Checkout (Trừ tồn kho âm)**
   - **Dấu hiệu:** User bấm nút "Đặt hàng" 3 lần liên tục do mạng lag. Hệ thống sinh ra 3 đơn hàng và trừ tồn kho 3 lần.
   - **Cách sửa:** Dùng `idempotency_key` sinh ra ở form ẩn. Ở backend, `Order::where('idempotency_key', $key)->exists()` để chặn.

3. **Mass Assignment Vulnerability**
   - **Lỗi:** Khách hàng cố tình gửi field `role=admin` qua form cập nhật Profile.
   - **Cách sửa:** Chỉ định rõ `$fillable = ['name', 'phone', 'address']` trong User model. Đừng bao giờ bỏ `$guarded = []` bừa bãi.

---

## ===================================
## PHẦN 9: TƯ DUY SENIOR
## ===================================

Trong project này, có những dòng code trông đơn giản nhưng ẩn chứa tư duy hệ thống:

**1. Tại sao dùng `decrement('stock', $qty)` thay vì `$product->stock -= $qty; $product->save();`?**
- **Trade-off:** Cách 1 (Atomic query) cập nhật trực tiếp dưới DB bằng câu lệnh SQL `UPDATE ... SET stock = stock - 1`. Cách 2 tải data lên memory PHP rồi save lại.
- **Lý do:** Ở hệ thống có traffic cao, nếu 2 user mua cùng 1 sản phẩm ở cùng 1 mili-giây, Cách 2 sẽ gây ra lỗi Race Condition (cả 2 đều thấy kho còn 1, và đều trừ đi 1, kho trở về 0 thay vì âm 1). Cách 1 nhờ Database Lock sẽ an toàn tuyệt đối.

**2. Nếu hệ thống scale lên 100,000 user, bạn sẽ làm gì tiếp theo?**
- Chuyển Session sang Redis (không lưu Session qua file nữa).
- Indexing Database: Thêm Index vào cột `status` của bảng `orders`, và cột `is_active` của `products`.
- Bắn Email xác nhận đơn hàng qua **Queue/Job** thay vì đồng bộ (Synchronous), để khách không phải đợi xoay loading vòng vòng.

> *Hành trình vạn dặm bắt đầu từ những dòng code đầu tiên. Hãy bắt tay vào code!*
