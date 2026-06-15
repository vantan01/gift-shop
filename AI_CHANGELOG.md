# AI Changelog — Gift Shop

Ghi lại các thay đổi do AI thực hiện theo `implementation_plan.md`, kèm mô tả trước/sau.

---

## 2026-06-14 — Triển khai toàn bộ implementation plan + UX/UI

### Phase 1 (P0) — Đã hoàn thành trước đó

#### Auth views — sửa lỗi 500
- **Trước:** Các trang auth dùng `<x-guest-layout>` và component Breeze đã bị xóa → lỗi 500.
- **Sau:** Tất cả view auth dùng `@extends('layouts.app')` với HTML/Tailwind thuần, đồng bộ với `login.blade.php`.

#### Upload ảnh sản phẩm (Admin)
- **Trước:** Form admin không có trường upload; cột `image` không được sử dụng.
- **Sau:** Input file + Alpine.js preview; validation trong Request; lưu vào `storage/app/public/products`; form có `enctype="multipart/form-data"`.

---

### Phase 2 (P1) — Hoàn thiện tính năng

#### Admin Categories CRUD
- **Trước:** Chỉ tạo mới và bật/tắt; không sửa/xóa.
- **Sau:** Đầy đủ `edit`, `update`, `destroy`; view `admin/categories/edit.blade.php`; nút Sửa/Xóa trên index.

#### Trang danh mục frontend (`/categories/{slug}`)
- **Trước:** Route và controller có nhưng **thiếu view** → lỗi khi truy cập.
- **Sau:** View `pages/categories/show.blade.php` với breadcrumb, header danh mục, sidebar danh mục khác, lưới sản phẩm, sắp xếp và phân trang.

---

### Phase 3 (P2/P3) — Refactor & chất lượng

#### ViewServiceProvider (DRY)
- **Trước:** Query `Category::active()` lặp lại ở nhiều controller/view; cart count tính inline trong header.
- **Sau:** `ViewServiceProvider` share `$navCategories` cho header/home và `$cartCount` cho header qua View Composer.

#### Route `home` & auth redirect
- **Trước:** Auth controllers redirect về `route('dashboard')` (không tồn tại cho storefront); admin sidebar link sai.
- **Sau:** Tất cả auth redirect về `route('home')`; admin "Về cửa hàng" dùng `route('home')`.

#### Route restore sản phẩm
- **Trước:** `POST /admin/products/{id}/restore` — tham số không nhất quán.
- **Sau:** `POST /admin/products/{product}/restore` — chuẩn hóa tên tham số.

#### Dashboard admin
- **Trước:** Chỉ 4 stat cards + top products + recent orders; top products tính cả đơn đã hủy; badge trạng thái dùng class Tailwind động (không compile production).
- **Sau:**
  - Biểu đồ doanh thu 6 tháng (`getRevenueByMonth`)
  - Bảng sản phẩm sắp hết hàng (`getLowStockProducts`)
  - Top products loại trừ đơn `CANCELLED`
  - `OrderStatus::badgeClasses()` — class Tailwind tĩnh, hiển thị đúng trên mọi môi trường

#### Tests
- **Trước:** `ProfileTest` (Breeze) test `/profile` — route không tồn tại; 44 test fail.
- **Sau:** `AccountTest` test `/account`; thêm test category page; **62/62 tests pass**.

#### Checkout — giỏ hàng trống
- **Trước:** POST checkout với giỏ trống → `back()` về `/` (không có referer).
- **Sau:** Validate cart trước khi đặt hàng → redirect `cart.index` với thông báo lỗi rõ ràng.

---

### Cải thiện UX/UI (không ảnh hưởng nghiệp vụ)

#### Header storefront
- **Trước:** Link cứng `/`, `/products`; không menu danh mục; không responsive mobile; cart count query inline.
- **Sau:**
  - Dùng `route()` helpers + highlight trang active
  - Dropdown "Danh mục" (desktop) với emoji từng category
  - Menu hamburger mobile (Alpine.js) với danh mục, auth links
  - Cart badge từ View Composer

#### Trang chủ
- **Trước:** 4 danh mục hardcoded, link query string `/products?category=...`.
- **Sau:** Danh mục từ DB (`$navCategories`), link `categories.show`, grid responsive 2→6 cột, CTA kép.

#### Trang sản phẩm — sidebar danh mục
- **Trước:** Filter danh mục qua query string trên `/products`.
- **Sau:** Link trực tiếp tới trang danh mục `/categories/{slug}` — điều hướng rõ ràng hơn.

#### Category model — `emoji()`
- **Trước:** Emoji hardcoded trong `product-card` theo slug.
- **Sau:** Method `Category::emoji()` tái sử dụng ở home, header, category page.

#### CSS — Alpine `x-cloak`
- **Trước:** Menu mobile có thể flash nội dung trước khi Alpine khởi tạo.
- **Sau:** Rule `[x-cloak] { display: none }` trong `app.css`.

#### Badge trạng thái đơn hàng (toàn site)
- **Trước:** `bg-{{ $order->status->color() }}-100` — Tailwind purge bỏ class động.
- **Sau:** `{{ $order->status->badgeClasses() }}` trên admin dashboard, admin orders, customer orders.

---

## Files chính đã thay đổi / tạo mới

| File | Hành động |
|------|-----------|
| `app/Providers/ViewServiceProvider.php` | Tạo mới |
| `bootstrap/providers.php` | Đăng ký provider |
| `resources/views/pages/categories/show.blade.php` | Tạo mới |
| `resources/views/layouts/header.blade.php` | UX mobile + categories |
| `resources/views/pages/home.blade.php` | Danh mục động |
| `resources/views/admin/dashboard.blade.php` | Analytics + low stock |
| `app/Services/DashboardService.php` | Fix top products, thêm low stock |
| `app/Enums/OrderStatus.php` | Thêm `badgeClasses()` |
| `app/Http/Controllers/CheckoutController.php` | Redirect cart khi trống |
| `tests/Feature/AccountTest.php` | Thay ProfileTest |
| `tests/Feature/CatalogTest.php` | Thêm test category |
| `AI_CHANGELOG.md` | File này |

---

## Kiểm tra sau triển khai

```bash
php artisan test   # 62 passed
```

**Gợi ý thủ công:** `php artisan storage:link` nếu chưa có symlink ảnh sản phẩm.
