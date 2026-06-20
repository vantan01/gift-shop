# ============================================================
# SCRIPT DEMO TỰ ĐỘNG - AN TOÀN ĐIỆN TOÁN ĐÁM MÂY
# Tiểu luận: Mô phỏng lỗ hổng cấu hình Docker
# Sinh viên: Nguyen Van Tan - MSSV: 22004329
# ============================================================
# Cach chay: Mo PowerShell, goi lenh:
#   cd d:\projects\gift-shop
#   .\baocao\demo_script.ps1
# ============================================================

param(
    [string]$Demo = "all"   # "1", "2", "3", "fix", "all"
)

# Mau sac cho output
function Write-Header {
    param([string]$Text)
    Write-Host "`n" -NoNewline
    Write-Host ("=" * 60) -ForegroundColor Cyan
    Write-Host "  $Text" -ForegroundColor Cyan
    Write-Host ("=" * 60) -ForegroundColor Cyan
}

function Write-Step {
    param([string]$Text)
    Write-Host "`n[BUOC] $Text" -ForegroundColor Yellow
}

function Write-Good {
    param([string]$Text)
    Write-Host "[OK] $Text" -ForegroundColor Green
}

function Write-Bad {
    param([string]$Text)
    Write-Host "[LO HONG] $Text" -ForegroundColor Red
}

function Write-Info {
    param([string]$Text)
    Write-Host "[INFO] $Text" -ForegroundColor White
}

function Pause-Demo {
    param([string]$Msg = "Nhan ENTER de tiep tuc...")
    Write-Host "`n$Msg" -ForegroundColor Magenta
    Read-Host
}

# ============================================================
# KIEM TRA DOCKER DANG CHAY
# ============================================================
function Check-Docker {
    Write-Header "KIEM TRA MOI TRUONG"
    
    $dockerVersion = docker --version 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Good "Docker da cai dat: $dockerVersion"
    } else {
        Write-Host "[LOI] Docker chua chay! Hay mo Docker Desktop truoc." -ForegroundColor Red
        exit 1
    }

    $composeVersion = docker compose version 2>&1
    Write-Good "Docker Compose: $composeVersion"

    Write-Info "Thu muc hien tai: $(Get-Location)"
}

# ============================================================
# DEMO LO HONG 1: POSTGRESQL EXPOSURE
# ============================================================
function Demo-PostgreSQL {
    Write-Header "LO HONG 1: POSTGRESQL EXPOSURE (CVSS 8.8 HIGH)"
    Write-Host @"
Mo ta: Cong 5432 cua PostgreSQL duoc map truc tiep ra ngoai.
       Bat ky may nao tren mang co the ket noi vao co so du lieu,
       bypass hoan toan Nginx va Laravel!
"@ -ForegroundColor White

    Write-Step "Khoi dong he thong CO LO HONG..."
    docker compose down --remove-orphans 2>$null
    docker compose -f docker-compose.insecure.yml up -d

    Start-Sleep -Seconds 3

    Write-Step "Kiem tra container dang chay..."
    docker ps --format "table {{.Names}}`t{{.Image}}`t{{.Status}}`t{{.Ports}}"

    Write-Host "`n" -NoNewline
    Write-Bad "Phat hien: Cong 0.0.0.0:5432->5432/tcp dang MO ra ngoai!"

    Pause-Demo "Nhan ENTER de demo ket noi truc tiep vao Database..."

    Write-Step "Thu ket noi truc tiep vao PostgreSQL (khong qua ung dung)..."
    Write-Info "Lenh: docker run --rm postgres:16-alpine psql -h host.docker.internal -p 5432 -U gift_shop_user -d gift_shop"
    Write-Host ""
    Write-Host "Khi duoc hoi password, nhap: secret123" -ForegroundColor Yellow
    Write-Host ""

    # Ket noi bang docker postgres client
    docker run --rm -it postgres:16-alpine psql -h host.docker.internal -p 5432 -U gift_shop_user -d gift_shop -c "\dt" -c "SELECT table_name FROM information_schema.tables WHERE table_schema='public';" 2>$null

    if ($LASTEXITCODE -eq 0) {
        Write-Bad "NGUY HIEM: Da ket noi thanh cong vao Database tu ben ngoai!"
        Write-Bad "Ke tan cong co the doc, sua, xoa moi du lieu!"
    } else {
        Write-Info "Luu y: Neu ket noi that bai, hay chay lenh psql thu cong (xem file HUONG_DAN_DEMO_LO_HONG.md)"
    }

    Write-Host "`nTom tat tac dong CIA Triad:" -ForegroundColor Red
    Write-Host "  [C] Confidentiality: Du lieu nguoi dung, don hang bi lo" -ForegroundColor Red
    Write-Host "  [I] Integrity:       Co the sua/xoa du lieu tuy y" -ForegroundColor Red
    Write-Host "  [A] Availability:    Co the lam DB qua tai, tu choi dich vu" -ForegroundColor Red

    Pause-Demo "Nhan ENTER de xem cach khac phuc..."

    Write-Header "KHAC PHUC LO HONG 1"
    Write-Host @"
Giai phap: Xoa dong 'ports: 5432:5432' trong docker-compose.yml
           + Su dung Docker internal network (backend_net)

Truoc (CO LO HONG):
  postgres:
    ports:
      - "5432:5432"   <- LO HONG!

Sau (DA BAO MAT):
  postgres:
    # Khong co ports -> cong 5432 chi noi bo Docker
    networks:
      - backend_net
"@ -ForegroundColor Green

    docker compose -f docker-compose.insecure.yml down 2>$null
    docker compose up -d
    Start-Sleep -Seconds 3

    Write-Step "Quet lai cong sau khi khac phuc..."
    docker ps --format "table {{.Names}}`t{{.Ports}}"
    Write-Good "Khong con thay cong 5432 bi lo ra ngoai!"
}

# ============================================================
# DEMO LO HONG 2: DOCKER SOCKET EXPOSURE
# ============================================================
function Demo-DockerSocket {
    Write-Header "LO HONG 2: DOCKER SOCKET EXPOSURE (CVSS 9.8 CRITICAL)"
    Write-Host @"
Mo ta: File /var/run/docker.sock duoc mount vao container.
       Day la dieu khien toan bo Docker Engine.
       Container co the tao container moi, xoa container, doc file tren host!
       Day la LO HONG NGUY HIEM NHAT trong bai (CVSS 9.8 Critical - Scope: Changed)
"@ -ForegroundColor White

    Write-Step "Khoi dong container CO LO HONG Docker Socket..."
    docker compose down --remove-orphans 2>$null
    docker compose -f docker-compose.socket-demo.yml up -d

    Start-Sleep -Seconds 5

    Write-Step "Kiem tra container..."
    docker ps

    Pause-Demo "Nhan ENTER de vao container va kiem tra socket..."

    Write-Step "Vao container CO LO HONG va kiem tra socket..."
    Write-Info "Lenh: docker exec -it app_with_socket ls -la /var/run/docker.sock"
    docker exec app_with_socket ls -la /var/run/docker.sock

    if ($LASTEXITCODE -eq 0) {
        Write-Bad "Docker Socket TON TAI trong container!"
    }

    Pause-Demo "Nhan ENTER de demo ket noi Docker API tu ben trong container..."

    Write-Step "Tu ben trong container, ket noi vao Docker Engine cua Host..."
    Write-Info "Su dung curl goi Docker API qua Unix Socket"
    
    # Ket noi Docker API tu ben trong container
    docker exec app_with_socket sh -c "curl -s --unix-socket /var/run/docker.sock http://localhost/version | head -c 200" 2>$null

    Write-Host ""
    Write-Bad "Da ket noi thanh cong vao Docker Engine tu ben trong container!"

    Pause-Demo "Nhan ENTER de xem danh sach container tu ben trong container..."

    Write-Step "Xem tat ca container dang chay TREN HOST tu ben trong container..."
    docker exec app_with_socket sh -c "curl -s --unix-socket /var/run/docker.sock http://localhost/containers/json" 2>$null | python3 -m json.tool 2>$null
    
    Write-Host ""
    Write-Bad "Ke tan cong co the thay VA KIEM SOAT tat ca container tren host!"
    Write-Bad "Day la Container Escape - pha vo hoan toan su co lap cua container!"

    Write-Host "`nTom tat tac dong CIA Triad:" -ForegroundColor Red
    Write-Host "  [C] Confidentiality: Doc file .env, SSH key, mat khau tren host" -ForegroundColor Red
    Write-Host "  [I] Integrity:       Sua doi ma nguon, chen backdoor, tao user root" -ForegroundColor Red
    Write-Host "  [A] Availability:    Xoa tat ca container, tat Docker Engine" -ForegroundColor Red

    Pause-Demo "Nhan ENTER de xem cach khac phuc..."

    Write-Header "KHAC PHUC LO HONG 2"
    Write-Host @"
Giai phap: Xoa hoan toan dong mount docker.sock khoi docker-compose.yml

Truoc (CO LO HONG):
  app:
    volumes:
      - /var/run/docker.sock:/var/run/docker.sock  <- LO HONG!

Sau (DA BAO MAT):
  app:
    volumes:
      - .:/var/www/html   # Chi mount nhung gi can thiet
      # KHONG mount docker.sock!
"@ -ForegroundColor Green

    Write-Step "Kiem tra container KHONG CO socket (app_without_socket)..."
    Write-Info "Lenh: docker exec -it app_without_socket ls /var/run/docker.sock"
    docker exec app_without_socket ls /var/run/docker.sock 2>&1
    Write-Good "Khong tim thay socket - Container duoc bao ve!"

    docker compose -f docker-compose.socket-demo.yml down 2>$null
}

# ============================================================
# DEMO LO HONG 3: CONTAINER ROOT + FLAT NETWORK
# ============================================================
function Demo-RootFlatNetwork {
    Write-Header "LO HONG 3: CONTAINER CHAY QUYEN ROOT + FLAT NETWORK (CVSS 8.6 HIGH)"
    Write-Host @"
Mo ta: Lo hong kep:
  1. Container chay voi uid=0 (root) - neu bi khai thac, ke tan cong co toan quyen
  2. Flat Network - tat ca container trong cung mang, co the noi chuyen truc tiep
  Ket hop tao ra con duong tan cong Lateral Movement rat nguy hiem!
"@ -ForegroundColor White

    Write-Step "Khoi dong he thong CO LO HONG (Flat Network)..."
    docker compose -f docker-compose.insecure.yml up -d
    Start-Sleep -Seconds 3

    # --- PHAN A: QUYEN ROOT ---
    Write-Host "`n--- PHAN A: DEMO QUYEN ROOT ---" -ForegroundColor Cyan
    
    Write-Step "Kiem tra user dang chay trong container app..."
    Write-Info "Lenh: docker exec -it gift_app whoami"
    docker exec gift_app whoami 2>$null

    Write-Host ""
    Write-Bad "Container dang chay voi quyen ROOT (uid=0)!"
    Write-Bad "Neu ung dung bi khai thac, ke tan cong co TOAN QUYEN trong container!"

    Pause-Demo "Nhan ENTER de demo nhung gi ke tan cong co the lam voi quyen root..."

    Write-Step "Demo quyen root: doc file nhay cam..."
    docker exec gift_app sh -c "cat /var/www/html/.env 2>/dev/null | head -20 || echo '.env khong ton tai nhung trong thuc te se co!'" 2>$null
    
    Write-Step "Demo quyen root: kiem tra thu muc he thong..."
    docker exec gift_app id 2>$null
    docker exec gift_app sh -c "ls -la /var/www/html/" 2>$null

    # --- PHAN B: FLAT NETWORK ---
    Pause-Demo "Nhan ENTER de demo Flat Network..."

    Write-Host "`n--- PHAN B: DEMO FLAT NETWORK ---" -ForegroundColor Cyan

    Write-Step "Kiem tra container app co the thay container PostgreSQL khong..."
    Write-Info "Lenh: docker exec gift_app ping -c 2 gift_shop_db"
    docker exec gift_app ping -c 2 gift_shop_db 2>$null

    if ($LASTEXITCODE -eq 0) {
        Write-Bad "Container App co the PING truc tiep toi PostgreSQL!"
        Write-Bad "Flat Network: khong co ro can, tat ca container thay nhau!"
    }

    Write-Host "`nTom tat Lateral Movement Attack:" -ForegroundColor Red
    Write-Host "  Buoc 1: Ke tan cong khai thac lo hong trong Laravel (RCE)" -ForegroundColor Red
    Write-Host "  Buoc 2: Quyen root cho phep cai cong cu quet mang (nmap)" -ForegroundColor Red
    Write-Host "  Buoc 3: Flat Network cho phep phat hien PostgreSQL" -ForegroundColor Red
    Write-Host "  Buoc 4: Doc .env lay credentials, ket noi thang vao DB" -ForegroundColor Red
    Write-Host "  Buoc 5: Trich xuat TOAN BO du lieu!" -ForegroundColor Red

    Pause-Demo "Nhan ENTER de xem cach khac phuc..."

    Write-Header "KHAC PHUC LO HONG 3"
    Write-Host @"
Giai phap 1 - Non-Root User (Dockerfile):
  RUN adduser --uid 1000 --disabled-password appuser
  USER appuser

Giai phap 2 - Phan tach mang (docker-compose.yml):
  networks:
    frontend_net:   # Chi Nginx
    backend_net:    # Chi App + PostgreSQL (internal: true)
"@ -ForegroundColor Green

    Write-Step "Khoi dong he thong DA BAO MAT..."
    docker compose -f docker-compose.insecure.yml down 2>$null
    docker compose up -d --build
    Start-Sleep -Seconds 5

    Write-Step "Kiem tra user sau khi khac phuc..."
    docker exec gift_app whoami 2>$null
    Write-Good "Chay voi user khong phai root!"

    Write-Step "Kiem tra Nginx KHONG the thay PostgreSQL (phan tach mang)..."
    docker exec gift_nginx ping -c 1 gift_shop_db 2>&1
    Write-Good "Nginx KHONG the thay PostgreSQL - Mang da duoc phan tach!"

    Write-Step "Kiem tra App VAN ket noi duoc voi PostgreSQL (can thiet)..."
    docker exec gift_app ping -c 1 gift_shop_db 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Good "App van ket noi duoc voi PostgreSQL - Hoat dong binh thuong!"
    }
}

# ============================================================
# TONG HOP KET QUA
# ============================================================
function Show-Summary {
    Write-Header "TONG HOP KET QUA DEMO"
    Write-Host @"

+---------------------------+------------------+------------------+------------+
| Tieu chi                  | TRUOC khac phuc  | SAU khac phuc    | CVSS Score |
+---------------------------+------------------+------------------+------------+
| PostgreSQL Exposure       | Cong 5432 lo     | Dong, noi bo     | 8.8 HIGH   |
| Docker Socket Exposure    | Mount vao cont.  | Loai bo hoan toan| 9.8 CRIT.  |
| Container chay Root       | uid=0 (root)     | uid=1000 (user)  | 8.6 HIGH   |
| Flat Network              | Tat ca thay nhau | frontend/backend | (trong 8.6)|
+---------------------------+------------------+------------------+------------+

Nguyen tac bao mat da ap dung:
  [1] Least Privilege    - Moi thanh phan chi co quyen toi thieu can thiet
  [2] Defense in Depth   - Nhieu lop bao ve doc lap
  [3] Default Deny       - Mac dinh tu choi tat ca, chi cho phep can thiet
  [4] Network Isolation  - Phan tach mang theo tang ung dung
"@ -ForegroundColor White

    Write-Host "`nCIA Triad - Trang thai SAU khi hardening:" -ForegroundColor Green
    Write-Host "  [C] Confidentiality: PostgreSQL co lap, .env bao ve, non-root user" -ForegroundColor Green
    Write-Host "  [I] Integrity:       Least Privilege, han che quyen ghi, khong socket" -ForegroundColor Green
    Write-Host "  [A] Availability:    Phan tach mang, giam be mat tan cong" -ForegroundColor Green
}

# ============================================================
# MAIN - CHAY DEMO
# ============================================================
Set-Location "d:\projects\gift-shop"

Write-Host @"
╔══════════════════════════════════════════════════════════╗
║         DEMO LO HONG BAO MAT DOCKER                      ║
║         Mon hoc: An Toan Dien Toan Dam May               ║
║         DH Su Pham Ky Thuat Vinh Long - 2026             ║
╚══════════════════════════════════════════════════════════╝
"@ -ForegroundColor Cyan

Check-Docker

switch ($Demo) {
    "1"   { Demo-PostgreSQL }
    "2"   { Demo-DockerSocket }
    "3"   { Demo-RootFlatNetwork }
    "fix" { Show-Summary }
    "all" {
        Demo-PostgreSQL
        Pause-Demo "=== CHUYEN SANG LO HONG 2 === Nhan ENTER..."
        Demo-DockerSocket
        Pause-Demo "=== CHUYEN SANG LO HONG 3 === Nhan ENTER..."
        Demo-RootFlatNetwork
        Show-Summary
    }
    default {
        Write-Host "Su dung: .\demo_script.ps1 -Demo [1|2|3|fix|all]" -ForegroundColor Yellow
        Write-Host "  1   = Demo Lo hong PostgreSQL Exposure" -ForegroundColor Yellow
        Write-Host "  2   = Demo Lo hong Docker Socket Exposure" -ForegroundColor Yellow
        Write-Host "  3   = Demo Lo hong Container Root + Flat Network" -ForegroundColor Yellow
        Write-Host "  fix = Chi xem ket qua tong hop" -ForegroundColor Yellow
        Write-Host "  all = Chay tat ca (mac dinh)" -ForegroundColor Yellow
    }
}

Write-Host "`nHoan tat demo!" -ForegroundColor Green
