# Security Policy

## Reporting a Vulnerability

Đây là project học tập. Nếu phát hiện lỗ hổng bảo mật, vui lòng tạo Issue trên GitHub.

## Security Measures Implemented

### Authentication & Authorization
- Passwords hashed với bcrypt (Laravel default cost factor 12)
- Session-based authentication, không dùng JWT cho web
- Role-based middleware: `EnsureUserIsAdmin`
- Route protection theo từng nhóm

### Input Validation
- Tất cả input validate qua `FormRequest` classes
- Slug chỉ chấp nhận `[a-z0-9-]`
- Integer casting cho price fields
- Max length trên tất cả string fields

### Data Integrity
- `$fillable` trên tất cả Models — không dùng `$guarded = []`
- CSRF token bắt buộc cho mọi state-changing request
- Foreign key constraints ở database level

### Business Logic Security
- Giá không bao giờ lấy từ frontend request
- `user_id` không bao giờ lấy từ frontend — luôn dùng `Auth::id()`
- Order queries luôn filter theo `user_id` (IDOR prevention)
- Atomic stock decrement để tránh race condition

### What's NOT implemented (production checklist)
- Rate limiting (cần thêm cho login, register, checkout)
- HTTPS enforcement
- Content Security Policy headers
- SQL injection — đã mitigated bởi Eloquent parameterized queries
- XSS — Blade `{{ }}` tự escape, raw `{!! !!}` không dùng cho user input