# Danh Sách Các Chỗ Đã Chỉnh Sửa (Repaired List)

Tài liệu này ghi nhận toàn bộ các chỉnh sửa đã được thực hiện để giải quyết các lỗi nghiêm trọng được phát hiện trong đợt Audit. Tất cả các chỉnh sửa đều tuân thủ nguyên tắc **giữ nguyên code cũ dưới dạng comment** để tiện so sánh và đối chiếu.

---

## 1. Sửa lỗi View Not Found khi Admin xem đơn hàng
* **Tệp tin:** `resources/views/admin/orders/index.blad.php`
* **Thay đổi:** Đổi tên tệp tin thành [index.blade.php](file:///d:/projects/gift-shop/resources/views/admin/orders/index.blade.php) (sửa lỗi gõ sai hậu tố `.blad.php`).
* **Trạng thái:** Hoàn thành.

---

## 2. Hoàn tồn kho và số lượng bán khi Admin hủy đơn
* **Tệp tin:** [app/Http/Controllers/Admin/OrderController.php](file:///d:/projects/gift-shop/app/Http/Controllers/Admin/OrderController.php)
* **Dòng thay đổi:** 47 - 73
* **Chi tiết thay đổi:**
  * Thêm constructor inject `OrderService` vào controller.
  * Trong hàm `updateStatus()`, thay vì chỉ cập nhật cột `status` trong DB trực tiếp, hệ thống sẽ kiểm tra xem có chuyển trạng thái sang `cancelled` không. Nếu có, gọi qua `OrderService::cancel()` để hoàn tồn kho tự động.
* **Mã nguồn thay đổi:**
  ```php
  // [Antigravity EDIT - Start] - Hoàn lại kho bằng OrderService nếu trạng thái đổi sang CANCELLED
  /* Code cũ:
  $order->update([
      'status'        => $newStatus,
      'internal_note' => $request->internal_note ?? $order->internal_note,
  ]);
  */
  if ($newStatus === OrderStatus::CANCELLED && $order->status !== OrderStatus::CANCELLED) {
      $result = $this->orderService->cancel($order);
      if (! $result['success']) {
          return back()->with('error', $result['message']);
      }
      if ($request->filled('internal_note')) {
          $order->update(['internal_note' => $request->internal_note]);
      }
  } else {
      $order->update([
          'status'        => $newStatus,
          'internal_note' => $request->internal_note ?? $order->internal_note,
      ]);
  }
  // [Antigravity EDIT - End]
  ```

---

## 3. Khắc phục lỗi tương thích SQLite (Tìm kiếm bằng ilike)
* **Tệp tin 1:** [app/Http/Controllers/ProductController.php](file:///d:/projects/gift-shop/app/Http/Controllers/ProductController.php) (Dòng 27 - 35)
* **Tệp tin 2:** [app/Http/Controllers/Admin/ProductController.php](file:///d:/projects/gift-shop/app/Http/Controllers/Admin/ProductController.php) (Dòng 20 - 27)
* **Tệp tin 3:** [app/Http/Controllers/Admin/OrderController.php](file:///d:/projects/gift-shop/app/Http/Controllers/Admin/OrderController.php) (Dòng 19 - 34)
* **Chi tiết thay đổi:**
  * Đọc `DB::connection()->getDriverName()`.
  * Nếu là driver `pgsql` thì sử dụng `ilike`, ngược lại dùng `like` để tương thích tốt với SQLite khi chạy test suite.
* **Mã nguồn thay đổi mẫu (ở ProductController):**
  ```php
  // [Antigravity EDIT - Start] - Chọn like/ilike tương thích SQLite vs pgsql
  /* Code cũ:
  $query->where(function ($q) use ($search) {
      $q->where('name', 'ilike', "%{$search}%")
        ->orWhere('short_description', 'ilike', "%{$search}%");
  });
  */
  $driver = DB::connection()->getDriverName();
  $likeOperator = $driver === 'pgsql' ? 'ilike' : 'like';

  $query->where(function ($q) use ($search, $likeOperator) {
      $q->where('name', $likeOperator, "%{$search}%")
        ->orWhere('short_description', $likeOperator, "%{$search}%");
  });
  // [Antigravity EDIT - End]
  ```

---

## 4. Khắc phục lỗi tương thích SQLite (Định dạng doanh thu theo tháng bằng TO_CHAR)
* **Tệp tin:** [app/Services/DashboardService.php](file:///d:/projects/gift-shop/app/Services/DashboardService.php)
* **Dòng thay đổi:** 63 - 90
* **Chi tiết thay đổi:**
  * Kiểm tra DB driver. Nếu là `sqlite`, sử dụng hàm `strftime('%m/%Y', created_at)` thay cho `TO_CHAR(created_at, 'MM/YYYY')`.
* **Mã nguồn thay đổi:**
  ```php
  // [Antigravity EDIT - Start] - Phân nhánh định dạng ngày tháng tương thích SQLite và pgsql
  /* Code cũ:
  return Order::where('status', OrderStatus::DELIVERED->value)
      ...
      ->select(
          DB::raw("TO_CHAR(created_at, 'MM/YYYY') as month"),
          ...
      )
  */
  $driver = DB::connection()->getDriverName();
  $monthFormat = $driver === 'sqlite'
      ? "strftime('%m/%Y', created_at) as month"
      : "TO_CHAR(created_at, 'MM/YYYY') as month";

  return Order::where('status', OrderStatus::DELIVERED->value)
      ->where('created_at', '>=', now()->subMonths($months))
      ->select(
          DB::raw($monthFormat),
          DB::raw('SUM(total) as revenue'),
          DB::raw('COUNT(*) as order_count')
      )
      ...
  ```

---

## 5. Đảm bảo Unique Slug kiểm tra cả sản phẩm đã xóa mềm
* **Tệp tin:** [app/Http/Controllers/Admin/ProductController.php](file:///d:/projects/gift-shop/app/Http/Controllers/Admin/ProductController.php)
* **Dòng thay đổi:** 129
* **Chi tiết thay đổi:**
  * Hàm `generateSlug` sử dụng `Product::withTrashed()` thay vì `Product::where()` để tránh trùng lặp slug với sản phẩm đã xóa mềm.
* **Mã nguồn thay đổi:**
  ```php
  // [Antigravity EDIT - Start] - Kiểm tra trùng lặp slug bao gồm cả các sản phẩm đã bị xóa mềm (Soft Deleted)
  /* Code cũ:
  while (Product::where('slug', $slug)->exists()) {
  */
  while (Product::withTrashed()->where('slug', $slug)->exists()) {
      // [Antigravity EDIT - End]
      $slug = $original . '-' . $count++;
  }
  ```

---

## 6. Sửa lỗi Null Reference khi hiển thị ảnh của sản phẩm đã bị xóa
* **Tệp tin:** [resources/views/pages/orders/show.blade.php](file:///d:/projects/gift-shop/resources/views/pages/orders/show.blade.php)
* **Dòng thay đổi:** 78
* **Chi tiết thay đổi:**
  * Sử dụng optional chaining `{{ $item->product?->image }}` phòng trường hợp sản phẩm cũ bị xóa cứng.
* **Mã nguồn thay đổi:**
  ```html
  @if ($item->product?->image)
      {{-- [Antigravity EDIT - Start] - Null safe image access
      Code cũ:
      <img src="{{ $item->product->image }}" class="w-full h-full object-cover rounded-lg">
      --}}
      <img src="{{ $item->product?->image }}" class="w-full h-full object-cover rounded-lg">
      {{-- [Antigravity EDIT - End] --}}
  @else
  ```

---

## 7. Khắc phục lỗi Crash JS khi sản phẩm hết hàng
* **Tệp tin:** [resources/views/pages/products/show.blade.php](file:///d:/projects/gift-shop/resources/views/pages/products/show.blade.php)
* **Dòng thay đổi:** 152 - 165
* **Chi tiết thay đổi:**
  * Dùng optional chaining `?.` cho `document.getElementById('qty-minus')` và `qty-plus` để không bị ném ra lỗi Null Reference khi hai nút này không có trong DOM (do hết hàng).
* **Mã nguồn thay đổi:**
  ```javascript
  // [Antigravity EDIT - Start] - Sử dụng optional chaining tránh crash JS khi phần tử không tồn tại (sản phẩm hết hàng)
  /* Code cũ:
  document.getElementById('qty-minus').addEventListener('click', () => { ... });
  document.getElementById('qty-plus').addEventListener('click', () => { ... });
  */
  document.getElementById('qty-minus')?.addEventListener('click', () => {
      const current = parseInt(qtyInput.value);
      if (current > 1) qtyInput.value = current - 1;
  });

  document.getElementById('qty-plus')?.addEventListener('click', () => {
      const current = parseInt(qtyInput.value);
      if (current < maxStock) qtyInput.value = current + 1;
  });
  // [Antigravity EDIT - End]
  ```

---

## 8. Tích hợp Header & Footer của Gift Shop vào Layout chính
* **Tệp tin:** [resources/views/layouts/app.blade.php](file:///d:/projects/gift-shop/resources/views/layouts/app.blade.php)
* **Dòng thay đổi:** 18 - 45
* **Chi tiết thay đổi:**
  * Loại bỏ Breeze navigation và header mặc định thô sơ.
  * Tích hợp `@include('layouts.header')` và `@include('layouts.footer')`.
  * Bọc layout trong flex-col để đẩy footer dính chân trang.
* **Mã nguồn thay đổi:**
  ```html
  <body class="font-sans antialiased">
      {{-- [Antigravity EDIT - Start] - Chuyển đổi layout Breeze mặc định sang layout Gift Shop có header/footer --}}
      {{-- Code cũ:
      <div class="min-h-screen bg-gray-100">
          @include('layouts.navigation')
          ...
      </div>
      --}}
      <div class="min-h-screen bg-gray-50 flex flex-col justify-between">
          @include('layouts.header')

          <!-- Page Content -->
          <main class="flex-grow">
              @yield('content')
          </main>

          @include('layouts.footer')
      </div>
      {{-- [Antigravity EDIT - End] --}}
  </body>
  ```
