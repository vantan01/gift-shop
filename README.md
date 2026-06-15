# 🎁 Gift Shop

> Web ecommerce bán quà tặng — thú bông, hoa sáp, hộp quà, chocolate và nhiều hơn nữa.

Dự án cá nhân xây dựng từ đầu để học Laravel theo hướng production-ready: thiết kế database, bảo mật, testing, và quy trình làm việc thực tế.

---

## ✨ Tính năng

### Storefront
- Product grid với filter theo danh mục, khoảng giá, tình trạng tồn kho
- Search sản phẩm
- Sort: mới nhất, bán chạy, giá tăng/giảm
- Pagination
- Badge: đang giảm giá, sắp hết hàng, hết hàng
- Product detail với sản phẩm liên quan
- Giỏ hàng session-based (không cần đăng nhập)

### Auth & Account
- Đăng ký / Đăng nhập / Đăng xuất
- Role-based: CUSTOMER và ADMIN
- Trang account cá nhân

### Checkout & Orders
- Checkout form với gift message, ngày giao hàng
- Backend tự tính total — không tin giá từ frontend
- Snapshot product name + price tại thời điểm mua
- Database transaction khi tạo order và trừ stock
- Idempotency key chống tạo đơn trùng
- Order timeline: PENDING → PAID → PACKING → SHIPPED → DELIVERED
- Hủy đơn khi còn PENDING hoặc PAID

### Admin Panel
- Dashboard: doanh thu, số đơn, top sản phẩm, đơn mới nhất
- Quản lý sản phẩm: thêm, sửa, ẩn/hiện, soft delete
- Quản lý danh mục
- Quản lý đơn hàng: xem chi tiết, cập nhật trạng thái

### Security
- CSRF protection trên tất cả form
- Password hashing (bcrypt)
- Role-based access control
- IDOR protection: user chỉ xem order của mình
- Mass assignment protection ($fillable)
- Validation toàn bộ input qua Form Request
- Không tin user_id, role, price từ frontend

---

## 🛠 Tech Stack

| Layer | Technology | Lý do chọn |
|-------|-----------|------------|
| Framework | Laravel 11 | PHP framework production-ready, ecosystem phong phú |
| Frontend | Blade + Tailwind CSS | Server-side rendering, đơn giản, không over-engineer |
| Database | PostgreSQL 16 | Mạnh hơn MySQL, hỗ trợ JSON, UUID tốt hơn |
| ORM | Eloquent | Tích hợp sẵn với Laravel, query builder linh hoạt |
| Auth | Laravel Breeze | Lightweight, dễ customize |
| Asset bundling | Vite | Nhanh hơn Webpack, hot reload tốt |
| Container | Docker Compose | DB nhất quán giữa dev/prod, không ô nhiễm máy |
| Testing | Pest | Cú pháp ngắn gọn hơn PHPUnit, readable hơn |

---

## 🚀 Cài đặt

### Yêu cầu
- PHP >= 8.2
- Composer >= 2.x
- Node.js >= 20.x
- Docker Desktop
- Git

### Các bước

**1. Clone repository**
```bash
git clone https://github.com/your-username/gift-shop.git
cd gift-shop
```

**2. Cài dependencies**
```bash
composer install
npm install
```

**3. Cấu hình môi trường**
```bash
cp .env.example .env
php artisan key:generate
```

Mở `.env`, cập nhật thông tin database:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=gift_shop
DB_USERNAME=gift_shop_user
DB_PASSWORD=secret123
```

**4. Khởi động PostgreSQL**
```bash
docker compose up -d
```

**5. Chạy migration và seed**
```bash
php artisan migrate
php artisan db:seed
```

**6. Chạy development server**

Mở 2 terminal:
```bash
# Terminal 1
php artisan serve

# Terminal 2
npm run dev
```

Vào: **http://localhost:8000**

---

## 👤 Demo Accounts

| Role      | Email                  | Password    |
|-----------|------------------------|-------------|
| Admin     | admin@giftshop.test    | password123 |
| Customer  | customer@giftshop.test | password123 |

---

## 🧪 Chạy Tests

**Tạo database test (chỉ cần làm 1 lần):**
```bash
docker exec -it gift_shop_db psql -U gift_shop_user -d postgres -c "CREATE DATABASE gift_shop_test;"
```

**Chạy toàn bộ test suite:**
```bash
vendor/bin/pest
```

**Chạy một file test cụ thể:**
```bash
vendor/bin/pest tests/Feature/CartTest.php
```

**Chạy với verbose output:**
```bash
vendor/bin/pest --verbose
```

### Test coverage

| Test file             | Nội dung                                                          |
|-----------------------|-------------------------------------------------------------------|
| `CatalogTest`         | Product listing, filter, search, 404 cho inactive product         |
| `AuthTest`            | Register, login, logout, password hashing                         |
| `AuthorizationTest`   | Customer bị chặn admin routes, guest bị chặn protected routes     |
| `CartTest`            | Add/remove/update, stock validation, giá từ DB                    |
| `CheckoutTest`        | Transaction, snapshot, stock decrement, idempotency, validation   |
| `OrderSecurityTest`   | IDOR protection, cancel rules, stock restore                      |
| `AdminTest`           | Product CRUD, order status update, phân quyền                     |

---

## 🏗 Kiến trúc

```
app/
├── Enums/                  # PHP 8.1 Enums: UserRole, OrderStatus
├── Http/
│   ├── Controllers/
│   │   ├── Admin/          # Admin controllers (protected by admin middleware)
│   │   └── ...             # Storefront controllers
│   ├── Middleware/
│   │   └── EnsureUserIsAdmin.php
│   └── Requests/           # Form Request validation
│       ├── Admin/
│       └── CheckoutRequest.php
├── Models/                 # Eloquent Models với relationships, scopes, helpers
├── Services/               # Business logic tách khỏi Controller
│   ├── CartService.php     # Session-based cart
│   ├── OrderService.php    # Checkout transaction, cancel logic
│   └── DashboardService.php# Admin stats, top products
└── Providers/
    └── AppServiceProvider.php  # Service bindings, pagination config

database/
├── migrations/             # Schema version control
├── factories/              # Fake data cho testing
└── seeders/                # Dữ liệu mẫu thực tế

resources/views/
├── layouts/
│   ├── app.blade.php       # Storefront layout
│   └── admin.blade.php     # Admin layout với sidebar
├── components/
│   └── product-card.blade.php  # Reusable component
├── pages/                  # Storefront views
└── admin/                  # Admin views

tests/Feature/              # HTTP-level integration tests
```

---

## 🔒 Business Rules

### Pricing
- Giá lưu dưới dạng **integer (VNĐ)** — tránh floating point rounding
- Backend **không bao giờ tin giá từ frontend** — luôn tra từ DB
- `OrderItem` lưu snapshot `unit_price` và `product_name` tại thời điểm mua
- Sản phẩm bị xóa hay đổi giá sau — lịch sử đơn hàng vẫn đúng

### Stock
- Trừ stock bằng atomic SQL: `WHERE stock >= quantity` trong cùng 1 query
- Tránh oversell khi 2 người checkout cùng lúc (race condition)
- Khi hủy đơn → stock được hoàn lại trong transaction

### Orders
- `idempotency_key` unique per user — bấm checkout 2 lần không tạo 2 đơn
- User chỉ hủy được khi status là `PENDING` hoặc `PAID`
- User A không thể xem order của User B (IDOR protection)
- Order number format: `GS-YYYYMMDD-XXXX`

### Auth & Roles
- User tự đăng ký luôn nhận role `CUSTOMER`
- `ADMIN` chỉ tạo thủ công — không có endpoint public để tạo admin
- Role cast thành PHP Enum — không thể có giá trị ngoài `customer`/`admin`
- Password hash bằng bcrypt qua Laravel cast `hashed`

### Soft Delete
- Product dùng `SoftDeletes` — xóa chỉ set `deleted_at`, không xóa thật
- OrderItem vẫn hiển thị đúng dù product bị xóa (nhờ snapshot)
- Không cho xóa product đang có đơn hàng chưa hoàn thành

---

## 🔐 Security Checklist

- [x] CSRF token trên tất cả POST/PATCH/DELETE forms
- [x] Password hash bằng bcrypt (Laravel `hashed` cast)
- [x] Role-based access control với middleware
- [x] IDOR protection: `WHERE user_id = Auth::id()` cho tất cả order queries
- [x] Mass assignment protection qua `$fillable`
- [x] Tất cả input validate qua Form Request
- [x] Không tin `user_id`, `role`, `price`, `total` từ client
- [x] Slug validation: chỉ chấp nhận `[a-z0-9-]`
- [x] Gift message giới hạn 300 ký tự
- [x] Ngày giao hàng không thể là ngày quá khứ
- [x] Idempotency key ngăn tạo đơn trùng
- [x] Atomic stock decrement ngăn oversell
- [x] Soft delete bảo toàn lịch sử đơn hàng
- [x] Admin routes yêu cầu role ADMIN — customer nhận 403
- [x] Không xóa cứng Category nếu còn Product (restrictOnDelete)
- [x] `.env` trong `.gitignore` — không commit secret

---

## 📚 What I Learned

### Laravel & PHP
- **Eloquent relationships**: `hasMany`, `belongsTo`, eager loading để tránh N+1 problem
- **Query Scopes**: `scopeActive()`, `scopeInStock()` giúp tái sử dụng query logic
- **PHP 8.1 Enums**: Type-safe constants cho role và order status
- **Form Request**: Tách validation ra khỏi controller, thêm custom messages
- **Service Container & Singleton**: Dependency injection, tránh tạo instance thừa
- **Database Migrations**: Schema version control, `up()` và `down()`, `restrictOnDelete`
- **SoftDeletes**: Xóa mềm để bảo toàn lịch sử, `withTrashed()` trong admin
- **Database Transactions**: `DB::transaction()` để đảm bảo tính toàn vẹn khi checkout

### Ecommerce Business Logic
- **Không bao giờ tin giá từ frontend** — backend tự tính từ DB
- **Snapshot pattern**: Lưu `product_name` và `unit_price` vào `order_items`
- **Idempotency**: Một token unique ngăn tạo đơn trùng khi double-click
- **Atomic operations**: `WHERE stock >= qty` trong cùng 1 SQL thay vì check rồi update
- **IDOR**: Luôn filter `user_id = Auth::id()` khi query order

### Security
- **CSRF**: Hiểu tại sao cần và cách Laravel xử lý tự động
- **Mass assignment**: `$fillable` vs `$guarded`, tại sao không dùng `$guarded = []`
- **Role-based access**: Middleware approach vs Policy/Gate approach
- **Password hashing**: Tại sao không MD5, tại sao bcrypt, cost factor

### Architecture
- **Service class**: Tách business logic khỏi controller cho dễ test và tái sử dụng
- **MVC trong thực tế**: Controller chỉ điều phối, không chứa logic phức tạp
- **Blade components**: `<x-product-card>` tái sử dụng được khắp nơi
- **Route grouping**: Prefix, name, middleware group cho admin routes

### Testing
- **Feature tests vs Unit tests**: Khi nào dùng cái nào
- **RefreshDatabase**: Mỗi test chạy trong transaction riêng
- **Test isolation**: Factory tạo data độc lập, không phụ thuộc seed
- **Testing security**: Verify IDOR, role restrictions, validation rules

### DevOps & Tooling
- **Docker Compose**: PostgreSQL container, data persistence với volumes
- **Vite**: Asset bundling, hot reload, production build
- **Git workflow**: Commit nhỏ, message rõ ràng, mỗi feature một commit

---

## 🗺 Lộ trình phát triển tiếp theo

- [ ] Coupon/discount engine
- [ ] Email notifications (order confirmation, status updates)
- [ ] Image upload cho product
- [ ] Wishlist
- [ ] Product reviews (chỉ user đã mua)
- [ ] AI Gift Assistant
- [ ] CSV export đơn hàng
- [ ] Queue cho email sending
- [ ] Redis cache cho product catalog

---

## 📝 License

MIT License — free to use for learning and portfolio purposes.