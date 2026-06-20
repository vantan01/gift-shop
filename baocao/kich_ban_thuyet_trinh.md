# KỊCH BẢN THUYẾT TRÌNH & DEMO BẢO VỆ TIỂU LUẬN

**Đề tài:** Tự triển khai Web Server nhỏ bằng Docker và mô phỏng lỗ hổng cấu hình  
**Sinh viên:** Nguyễn Văn Tân — MSSV: 22004329  
**Giáo viên hướng dẫn:** ThS. Trần Thái Bảo

---

## PHẦN MỞ ĐẦU

Kính chào quý thầy cô trong hội đồng đánh giá. Em là Nguyễn Văn Tân, sinh viên thực hiện đề tài **"Tự triển khai Web Server nhỏ bằng Docker và mô phỏng lỗ hổng cấu hình"**. Lời đầu tiên, em xin gửi lời cảm ơn đến thầy cô đã dành thời gian tham dự buổi bảo vệ ngày hôm nay. Sau đây, em xin phép bắt đầu phần trình bày của mình.

Như quý thầy cô đã biết, công nghệ Docker và Container hóa đang trở thành tiêu chuẩn trong triển khai ứng dụng hiện đại. Tuy nhiên, sự tiện lợi của Docker thường khiến các kỹ sư mới vào nghề tập trung vào việc "làm cho hệ thống chạy được" mà quên đi công tác bảo mật cấu hình. Theo thống kê của OWASP Top 10, **Security Misconfiguration — lỗi cấu hình sai bảo mật** luôn nằm trong nhóm rủi ro nguy hiểm và phổ biến nhất.

Nhận thức được vấn đề đó, em lựa chọn đề tài này để tự tay xây dựng một hệ thống Web Server hoàn chỉnh bằng Docker, đồng thời trực tiếp tạo ra các lỗi cấu hình điển hình và phân tích tác động của chúng theo mô hình CIA Triad, từ đó đề xuất và áp dụng các biện pháp khắc phục thực tế.

Bài trình bày của em hôm nay gồm 3 phần chính:
- **Phần 1:** Giới thiệu kiến trúc hệ thống và quá trình triển khai
- **Phần 2:** Demo trực tiếp 3 lỗ hổng cấu hình (mô phỏng tấn công)
- **Phần 3:** Áp dụng giải pháp khắc phục và đánh giá kết quả

---
### [GIẢNG VIÊN CÓ THỂ HỎI — PHẦN MỞ ĐẦU]

**Hỏi:** Security Misconfiguration khác với lỗi lập trình thông thường như thế nào?
- *Trả lời ngắn gọn:* Lỗi lập trình nằm trong code, còn Security Misconfiguration nằm ở cấu hình hệ thống — như cổng mạng, phân quyền, file môi trường. Code hoàn hảo vẫn bị tấn công nếu cấu hình hệ thống sai.
- *Trả lời chi tiết:* Dạ thưa thầy/cô, lỗi lập trình thông thường như SQL Injection hay XSS xuất phát từ code không xử lý đầu vào đúng cách. Còn Security Misconfiguration là nhóm lỗi ở tầng cấu hình — ví dụ mở nhầm cổng database ra Internet, mount Docker Socket không kiểm soát, hoặc chạy container bằng quyền root. Những lỗi này không thể phát hiện qua review code, chỉ phát hiện khi kiểm tra cấu hình hệ thống. Đây là lý do OWASP xếp nó riêng thành một hạng mục độc lập.

---

## PHẦN 1 — KIẾN TRÚC HỆ THỐNG

Hệ thống em xây dựng theo kiến trúc **ba tầng** tiêu chuẩn, toàn bộ chạy trên Docker Desktop tại máy cục bộ (Windows 11).

Thành phần đầu tiên là **Nginx**, đóng vai trò Web Server và Reverse Proxy ở tầng biên. Nginx tiếp nhận mọi yêu cầu HTTP từ người dùng, phục vụ tài nguyên tĩnh như ảnh và CSS, đồng thời chuyển tiếp các request động sang PHP-FPM. Nginx hoạt động như một lá chắn bảo vệ, ẩn hoàn toàn PHP-FPM khỏi Internet.

Thành phần thứ hai là **PHP-FPM chạy Laravel**, đóng vai trò Application Server. Khi có request cần xử lý nghiệp vụ, Nginx chuyển tiếp qua giao thức FastCGI. Laravel xử lý logic, truy vấn cơ sở dữ liệu và trả kết quả về.

Thành phần thứ ba là **PostgreSQL**, nằm sâu nhất trong mạng nội bộ Docker, tuyệt đối không mở cổng ra Internet. Đây là kho lưu trữ dữ liệu người dùng, sản phẩm, đơn hàng của ứng dụng gift-shop.

Để gắn kết 3 thành phần này, em sử dụng **Docker Compose** với file `docker-compose.yml` khai báo toàn bộ cấu hình dịch vụ, mạng và volume.

---
### [GIẢNG VIÊN CÓ THỂ HỎI — PHẦN KIẾN TRÚC]

**Hỏi:** Tại sao lại tách Nginx và PHP-FPM thành 2 container riêng thay vì gộp chung?
- *Trả lời ngắn gọn:* Để áp dụng nguyên lý Least Privilege. Nginx chỉ cần đọc file tĩnh, không cần quyền thực thi PHP. Tách ra giúp cô lập phạm vi ảnh hưởng nếu một trong hai bị tấn công.
- *Trả lời chi tiết:* Dạ, ngoài lý do hiệu năng, việc tách container mang ý nghĩa bảo mật quan trọng. Nếu gộp chung, một lỗ hổng trong PHP có thể trực tiếp ảnh hưởng đến Nginx và ngược lại. Khi tách ra, container Nginx chỉ có quyền đọc file public và chuyển tiếp request. Container PHP-FPM chạy bằng user `www-data` với quyền tối thiểu. Nếu hacker khai thác được Nginx, chúng vẫn bị cô lập trong container Nginx và không thể lan sang PHP hay PostgreSQL ạ.

**Hỏi:** Docker Network có vai trò gì trong kiến trúc này?
- *Trả lời ngắn gọn:* Docker Network tạo mạng ảo cô lập giữa các container. Em phân tách thành frontend_net và backend_net để PostgreSQL không thể bị truy cập từ phía Nginx.
- *Trả lời chi tiết:* Dạ, Docker Network cho phép các container giao tiếp với nhau theo tên dịch vụ thay vì IP. Em thiết kế 2 vùng mạng: `frontend_net` chứa Nginx, `backend_net` chứa PHP-FPM và PostgreSQL. Container app (PHP) đóng vai trò cầu nối, tham gia cả hai mạng. Nhờ đó, nếu Nginx bị chiếm quyền, kẻ tấn công không thể ping hay kết nối trực tiếp đến PostgreSQL vì hai container này không cùng mạng ạ.

---

## PHẦN 2 — DEMO TRIỂN KHAI HỆ THỐNG

Bây giờ em xin phép thực hiện demo triển khai hệ thống từ đầu trên máy cục bộ.

Trên màn hình Terminal, em khởi động toàn bộ hệ thống bằng lệnh: `docker compose up -d --build`

*(Nhấn Enter và đợi build xong)*

Quá trình build đang thực thi `Dockerfile` — cài đặt PHP-FPM, các extension PostgreSQL, Composer và mã nguồn Laravel. Sau khi build xong, em kiểm tra trạng thái bằng:

```bash
docker compose ps
```

Kết quả hiển thị 3 container đều ở trạng thái **Up**:
- `gift_nginx` — Web Server
- `gift_app` — PHP-FPM + Laravel  
- `gift_shop_db` — PostgreSQL

Tiếp theo em chạy migration để tạo bảng cơ sở dữ liệu:

```bash
docker compose exec app php artisan migrate --seed
```

*(Đợi lệnh chạy xong)*

Em mở trình duyệt và truy cập `http://localhost:8080`. Website gift-shop hiển thị hoàn hảo. Hệ thống đang hoạt động trơn tru với cấu hình **chưa có bảo mật**.

Tuy nhiên, đây là lúc chúng ta bắt đầu phần quan trọng nhất của đề tài — mô phỏng các lỗ hổng cấu hình.

---

## PHẦN 3 — MÔ PHỎNG LỖ HỔNG & PHÂN TÍCH

---

### 🔴 LỖ HỔNG 1: Công khai PostgreSQL ra mạng ngoài

**Mô tả cấu hình sai:**

Em sẽ mở file `docker-compose.yml` hiện tại. Ở phần dịch vụ `db`, em thêm cấu hình:

```yaml
db:
  image: postgres:17
  ports:
    - "5432:5432"   # ← Dòng này là vấn đề!
```

Cấu hình `ports: "5432:5432"` ánh xạ cổng PostgreSQL ra máy chủ vật lý. Nghĩa là bất kỳ máy nào trên mạng LAN, thậm chí từ Internet, đều có thể kết nối trực tiếp đến cơ sở dữ liệu này — hoàn toàn bypass Nginx và Laravel.

**Demo tấn công:**

Em mở một terminal mới và thử kết nối trực tiếp đến PostgreSQL từ bên ngoài container:

```bash
psql -h localhost -p 5432 -U postgres -d giftshop
```

*(Gõ mật khẩu: `password`)*

Thưa thầy cô, em đã kết nối thành công vào cơ sở dữ liệu mà không cần đăng nhập qua website. Từ đây, em có thể xem toàn bộ dữ liệu:

```sql
SELECT * FROM users;
```

Toàn bộ tài khoản người dùng, mật khẩu hash, thông tin cá nhân đã bị lộ hoàn toàn.

**Phân tích CIA Triad:**
- **Confidentiality:** Dữ liệu người dùng bị đọc trái phép → HIGH
- **Integrity:** Có thể sửa/xóa dữ liệu → HIGH  
- **Availability:** Có thể làm treo database → HIGH
- **Điểm CVSS v3.1: 8.8 (High)**

**Khắc phục:**

Em xóa dòng `ports` trong cấu hình dịch vụ `db`, khởi động lại hệ thống:

```bash
docker compose up -d
```

Thử kết nối lại từ bên ngoài — kết nối thất bại. PostgreSQL giờ chỉ lắng nghe trong mạng nội bộ Docker, không thể truy cập từ bên ngoài.

---
### [GIẢNG VIÊN CÓ THỂ HỎI — LỖ HỔNG 1]

**Hỏi:** Nếu database không mở port ra ngoài, ứng dụng Laravel kết nối bằng cách nào?
- *Trả lời ngắn gọn:* Laravel kết nối đến PostgreSQL qua tên service trong Docker Network nội bộ. Tên host là `db` — Docker DNS tự phân giải trong mạng backend_net mà không cần mở port ra ngoài.
- *Trả lời chi tiết:* Dạ, trong Docker Compose, các container cùng network có thể giao tiếp với nhau qua tên service. Em cấu hình `DB_HOST=db` trong file `.env` của Laravel. Khi Laravel kết nối, Docker DNS trong mạng `backend_net` tự phân giải `db` thành địa chỉ IP nội bộ của container PostgreSQL. Kết nối này hoàn toàn trong mạng ảo, không qua Internet và không cần publish port ạ.

---

### 🔴 LỖ HỔNG 2: Docker Socket Exposure

**Mô tả cấu hình sai:**

Em sẽ thêm vào dịch vụ `app` trong `docker-compose.yml`:

```yaml
app:
  volumes:
    - .:/var/www/html
    - /var/run/docker.sock:/var/run/docker.sock  # ← Dòng nguy hiểm!
```

Việc mount `/var/run/docker.sock` vào container trao cho ứng dụng bên trong quyền giao tiếp trực tiếp với Docker Daemon trên máy chủ. Đây tương đương với việc cấp quyền quản trị toàn bộ Docker Host cho bất kỳ ai chiếm được container này.

**Demo tấn công:**

Em vào bên trong container app và thực thi lệnh Docker từ trong đó:

```bash
docker compose exec app bash
```

Từ bên trong container, em có thể xem và điều khiển toàn bộ Docker Host:

```bash
# Liệt kê container trên host
curl --unix-socket /var/run/docker.sock http://localhost/containers/json

# Tạo container mới mount toàn bộ filesystem của host
curl --unix-socket /var/run/docker.sock \
  -X POST http://localhost/containers/create \
  -d '{"Image":"alpine","Binds":["/:/host"]}'
```

Thưa thầy cô, từ bên trong container em đã có thể đọc toàn bộ hệ thống file của máy chủ vật lý. Container không còn được cô lập nữa.

**Phân tích CIA Triad:**
- **Confidentiality:** Đọc được file trên host → HIGH
- **Integrity:** Tạo/xóa container tùy ý → HIGH
- **Availability:** Tắt toàn bộ hệ thống → HIGH
- **Điểm CVSS v3.1: 9.0 (Critical)**

**Khắc phục:**

Xóa hoàn toàn dòng mount Docker Socket khỏi `docker-compose.yml`. Đây là giải pháp dứt khoát và an toàn nhất.

---
### [GIẢNG VIÊN CÓ THỂ HỎI — LỖ HỔNG 2]

**Hỏi:** Có trường hợp nào thực sự cần phải mount Docker Socket vào container không?
- *Trả lời ngắn gọn:* Có — trong các hệ thống CI/CD như Jenkins cần build Docker Image trong container. Tuy nhiên ngay cả khi đó, giải pháp an toàn hơn là dùng Rootless Docker hoặc Kaniko thay vì mount socket trực tiếp.
- *Trả lời chi tiết:* Dạ, thực tế có một số trường hợp cần — ví dụ Portainer (công cụ quản lý Docker có giao diện web) hay Jenkins pipeline cần build image. Tuy nhiên với ứng dụng web thông thường như gift-shop, hoàn toàn không có lý do gì cần mount socket này. Và khi thực sự cần, giải pháp an toàn hơn là dùng socket proxy với quyền chỉ đọc, hoặc chuyển sang công cụ build không cần socket như Kaniko ạ.

---

### 🔴 LỖ HỔNG 3: Container chạy quyền Root và mạng phẳng

**Mô tả cấu hình sai:**

Mặc định, container Docker chạy bằng user `root` bên trong container. Đồng thời, khi không cấu hình network riêng, Docker Compose tạo một mạng phẳng (flat network) — tất cả container đều có thể giao tiếp tự do với nhau.

**Demo — Container chạy Root:**

```bash
docker compose exec app whoami
# Kết quả: root
```

Nếu hacker khai thác được lỗ hổng trong Laravel và có được shell trong container, chúng đang có quyền root. Từ đây:

```bash
# Xem toàn bộ file cấu hình nhạy cảm
cat /var/www/html/.env

# Cài đặt công cụ tấn công vào container
apt-get install -y nmap netcat
```

**Demo — Mạng phẳng (cấu hình sai):**

Nếu không có network segmentation, từ container Nginx có thể ping thẳng đến database:

```bash
docker compose exec nginx ping gift_shop_db
# Kết quả: PING thành công → mạng phẳng, không có cô lập
```

**Khắc phục Container Root** — Thêm vào `Dockerfile`:

```dockerfile
# Tạo user riêng cho ứng dụng
RUN addgroup -S www && adduser -S www -G www
USER www
```

**Khắc phục Flat Network** — Thêm vào `docker-compose.yml`:

```yaml
networks:
  frontend_net:
  backend_net:

services:
  nginx:
    networks: [frontend_net]
  app:
    networks: [frontend_net, backend_net]
  db:
    networks: [backend_net]
```

Khởi động lại và kiểm tra:

```bash
docker compose exec nginx ping gift_shop_db
# Kết quả: Name does not resolve → Cô lập thành công!
```

Thưa thầy cô, sau khi phân tách mạng, container Nginx hoàn toàn không thể định vị được PostgreSQL. Kẻ tấn công dù chiếm được Nginx cũng bị chặn hoàn toàn tại lớp mạng.

---
### [GIẢNG VIÊN CÓ THỂ HỎI — LỖ HỔNG 3]

**Hỏi:** Container chạy root bên trong có nguy hiểm không nếu hacker chưa thoát ra ngoài host được?
- *Trả lời ngắn gọn:* Vẫn nguy hiểm vì root trong container có thể đọc toàn bộ file nhạy cảm trong container, cài công cụ tấn công, và là bước đệm để thoát ra host nếu kết hợp với lỗ hổng khác như Docker Socket.
- *Trả lời chi tiết:* Dạ, quyền root trong container dù không tương đương với root trên host, nhưng vẫn nguy hiểm ở nhiều cấp độ. Thứ nhất, hacker đọc được file `.env` chứa mật khẩu database. Thứ hai, hacker cài thêm công cụ như nmap, netcat để trinh sát mạng nội bộ. Thứ ba, khi kết hợp với Docker Socket Exposure, root trong container đồng nghĩa với root trên host. Nguyên lý Least Privilege đòi hỏi container phải chạy bằng user có quyền tối thiểu nhất có thể ạ.

**Hỏi:** Tại sao không cấu hình Network Segmentation ngay từ đầu mà phải chờ có sự cố?
- *Trả lời:* Dạ, đó chính là bài học trọng tâm của đề tài này ạ. Nguyên tắc "Secure by Default" và "Defense in Depth" yêu cầu bảo mật phải được tích hợp ngay từ giai đoạn thiết kế, không phải vá sau khi xảy ra sự cố. Em đã cố tình tái hiện lại cách tiếp cận sai lầm phổ biến để thấy rõ hậu quả, từ đó xây dựng cấu hình đúng với đầy đủ lớp bảo vệ.

---

## PHẦN KẾT LUẬN

Kính thưa Hội đồng, qua quá trình thực hiện tiểu luận, em đã hoàn thành việc tự tay xây dựng và vận hành một hệ thống Web Server theo kiến trúc ba tầng bằng Docker. Đồng thời, thông qua việc mô phỏng 3 lỗ hổng cấu hình điển hình, em rút ra những bài học sâu sắc:

**Bài học thứ nhất:** Nhiều sự cố bảo mật không đến từ lỗi lập trình mà đến từ những sai sót nhỏ trong cấu hình hệ thống. Chỉ một dòng `ports: "5432:5432"` hay mount Docker Socket không kiểm soát cũng có thể trao toàn quyền hệ thống cho kẻ tấn công.

**Bài học thứ hai:** Ba nguyên lý bảo mật cốt lõi cần áp dụng ngay từ khi thiết kế — **Least Privilege** (quyền hạn tối thiểu), **Defense in Depth** (bảo vệ nhiều lớp), và **Secure by Default** (an toàn theo mặc định) — không phải chờ xảy ra sự cố rồi mới vá.

**Bài học thứ ba:** Một hệ thống sử dụng công nghệ hiện đại như Docker sẽ không mang lại sự an toàn nếu người quản trị thiếu hiểu biết về cấu hình bảo mật cơ bản. Công nghệ chỉ là công cụ, con người cấu hình đúng mới tạo ra sự an toàn thực sự.

Phần thuyết trình của em đến đây là kết thúc. Em xin chân thành cảm ơn thầy cô đã lắng nghe. Em rất mong nhận được những góp ý, nhận xét từ phía Hội đồng để đề tài của em được hoàn thiện hơn ạ. Em xin trân trọng cảm ơn!

---

## PHỤ LỤC — CÂU HỎI TỔNG HỢP CÓ THỂ GẶP

**Hỏi:** Em đánh giá CVSS như thế nào? Tự đánh giá hay theo tiêu chuẩn?
- *Trả lời:* Dạ, em sử dụng công cụ tính điểm CVSS v3.1 Calculator chính thức của NIST tại địa chỉ nvd.nist.gov. Em nhập các thông số Attack Vector, Attack Complexity, Privileges Required, User Interaction, Scope và tác động đến CIA Triad theo đặc điểm của từng lỗ hổng, hệ thống tự động tính ra điểm số tổng hợp ạ.

**Hỏi:** Hệ thống của em đã đủ an toàn chưa sau khi khắc phục?
- *Trả lời:* Dạ, hệ thống đã được cải thiện đáng kể sau khi khắc phục 3 lỗ hổng chính. Tuy nhiên em cũng nhận thức được rằng bảo mật là quá trình liên tục. Một số điểm chưa hoàn thiện trong phạm vi đề tài như chưa triển khai HTTPS/TLS, chưa có hệ thống giám sát (monitoring) và chưa thực hiện Penetration Testing chuyên sâu. Đây sẽ là hướng phát triển tiếp theo của em ạ.

**Hỏi:** Docker Compose so với Kubernetes khác nhau thế nào?
- *Trả lời:* Dạ, Docker Compose phù hợp với môi trường phát triển và triển khai quy mô nhỏ như đề tài của em — đơn giản, dễ cấu hình, chạy trên một máy chủ. Kubernetes là nền tảng điều phối container cho môi trường doanh nghiệp — tự động mở rộng, tự phục hồi, quản lý cluster nhiều máy chủ. Kubernetes mạnh hơn nhiều nhưng cũng phức tạp hơn, phù hợp khi hệ thống cần High Availability và khả năng chịu tải lớn ạ.

---
*(Kịch bản thuyết trình kết thúc tại đây. Chúc bạn bảo vệ thành công! 🎓)*
