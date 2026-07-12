#!/bin/bash

#############################################################
# Setup Queue Worker Otomatis di Ubuntu Server (VERSI FINAL)
# Menggabungkan semua perbaikan yang ditemukan:
#   - Config tanpa [group:...] (penyebab "no such group")
#   - Cek & install ekstensi PHP wajib (mbstring, dll — penyebab
#     worker fatal error "Call to undefined function mb_split()")
#   - Verifikasi rclone + kredensial Google Drive
#############################################################

set -e

# ========== KONFIGURASI — SESUAIKAN INI ==========
PROJECT_PATH="/var/www/komitev1"
QUEUE_DRIVER="database"           # Sesuai .env: QUEUE_CONNECTION=database
APP_USER="www-data"
NUMPROCS=2
APP_NAME="komitev1"

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; BLUE='\033[0;34m'; NC='\033[0m'
step() { echo -e "\n${BLUE}▶ $1${NC}"; }
ok()   { echo -e "${GREEN}✓ $1${NC}"; }
warn() { echo -e "${YELLOW}⚠ $1${NC}"; }
err()  { echo -e "${RED}✗ $1${NC}"; }

if [[ $EUID -ne 0 ]]; then
    err "Jalankan dengan sudo: sudo bash setup-supervisor-final.sh"
    exit 1
fi

if [ ! -d "$PROJECT_PATH" ]; then
    err "Project tidak ditemukan di: $PROJECT_PATH — edit variabel PROJECT_PATH di script ini"
    exit 1
fi

# ========== 1. PHP & EKSTENSI WAJIB ==========
step "1. Mengecek PHP dan ekstensi wajib Laravel"

PHP_BIN=$(which php)
ok "PHP ditemukan: $PHP_BIN ($(php -v | head -n1 | awk '{print $2}'))"

REQUIRED_EXT=(mbstring bcmath ctype curl dom fileinfo filter hash json openssl pdo tokenizer xml)
MISSING_EXT=()

for ext in "${REQUIRED_EXT[@]}"; do
    if ! php -m | grep -qi "^${ext}$"; then
        MISSING_EXT+=("$ext")
    fi
done

if [ ${#MISSING_EXT[@]} -gt 0 ]; then
    warn "Ekstensi PHP hilang: ${MISSING_EXT[*]} — menginstall..."
    PHP_VERSION=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')
    apt-get update -qq
    for ext in "${MISSING_EXT[@]}"; do
        apt-get install -y "php${PHP_VERSION}-${ext}" 2>/dev/null || apt-get install -y "php-${ext}" 2>/dev/null || warn "Gagal install php-${ext}, cek manual"
    done
    ok "Ekstensi PHP diinstall. Restart PHP-FPM jika pakai FPM: sudo systemctl restart php${PHP_VERSION}-fpm"
else
    ok "Semua ekstensi PHP wajib sudah terpasang"
fi

# ========== 2. INSTALL SUPERVISOR ==========
step "2. Menginstall Supervisor"

if command -v supervisorctl &> /dev/null; then
    ok "Supervisor sudah terpasang"
else
    apt-get update -qq
    apt-get install -y supervisor
    ok "Supervisor terinstall"
fi

# ========== 3. VERIFIKASI RCLONE + KREDENSIAL GOOGLE DRIVE ==========
step "3. Verifikasi rclone & kredensial Google Drive"

RCLONE_BIN=$(grep "^RCLONE_BINARY_PATH=" "$PROJECT_PATH/.env" 2>/dev/null | cut -d '=' -f2- | tr -d '"')
RCLONE_BIN=${RCLONE_BIN:-rclone}

if command -v "$RCLONE_BIN" &> /dev/null || [ -x "$RCLONE_BIN" ]; then
    ok "rclone ditemukan: $RCLONE_BIN"
else
    warn "rclone TIDAK ditemukan di path: $RCLONE_BIN"
    warn "Install dengan: curl https://rclone.org/install.sh | sudo bash"
fi

CREDENTIALS_FILE="$PROJECT_PATH/storage/app/google/service-account.json"
if [ -f "$CREDENTIALS_FILE" ]; then
    ok "File kredensial Google Drive ditemukan: $CREDENTIALS_FILE"
else
    warn "File kredensial TIDAK ditemukan: $CREDENTIALS_FILE"
    warn "Upload file service-account.json ke path tsb agar fitur upload GDrive berfungsi"
fi

# Cek rclone remote sudah dikonfigurasi (jalan sebagai user aplikasi, bukan root,
# karena config rclone biasanya per-user)
if sudo -u "$APP_USER" "$RCLONE_BIN" listremotes 2>/dev/null | grep -q "gdrive:"; then
    ok "rclone remote 'gdrive' sudah terkonfigurasi untuk user $APP_USER"
else
    warn "rclone remote 'gdrive' belum terkonfigurasi untuk user $APP_USER"
    warn "Jalankan: sudo -u $APP_USER $RCLONE_BIN config"
fi

# ========== 4. BUAT CONFIG SUPERVISOR ==========
step "4. Membuat konfigurasi Supervisor"

CONFIG_FILE="/etc/supervisor/conf.d/${APP_NAME}-queue-worker.conf"

# PENTING: TANPA section [group:...] — itu penyebab error
# "komitev1-queue-worker: ERROR (no such group)" yang kita temukan sebelumnya.
cat > "$CONFIG_FILE" << EOF
[program:${APP_NAME}-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=${PHP_BIN} ${PROJECT_PATH}/artisan queue:work ${QUEUE_DRIVER} --sleep=3 --tries=3 --timeout=120 --max-time=3600
autostart=true
autorestart=true
numprocs=${NUMPROCS}
redirect_stderr=true
stdout_logfile=/var/log/supervisor/${APP_NAME}-worker.log
stopwaitsecs=3600
user=${APP_USER}
EOF

ok "Config dibuat: $CONFIG_FILE"

mkdir -p /var/log/supervisor
chown "$APP_USER":"$APP_USER" /var/log/supervisor 2>/dev/null || true

# ========== 5. RESTART & START ==========
step "5. Restart Supervisor dan jalankan worker"

systemctl restart supervisor
sleep 2

supervisorctl reread
supervisorctl update
sleep 2

supervisorctl start "${APP_NAME}-queue-worker:*" 2>&1 || true
sleep 2

# ========== 6. VERIFIKASI ==========
step "6. Verifikasi status"

STATUS=$(supervisorctl status "${APP_NAME}-queue-worker:*" 2>&1)
echo "$STATUS"

if echo "$STATUS" | grep -q "RUNNING"; then
    ok "Worker berjalan!"
else
    err "Worker belum RUNNING — cek log:"
    echo "  sudo tail -n 50 /var/log/supervisor/${APP_NAME}-worker.log"
fi

# ========== 7. AUTO-START SAAT REBOOT ==========
step "7. Mengaktifkan auto-start Supervisor saat server reboot"
systemctl enable supervisor
ok "Supervisor akan otomatis start saat server reboot"

# ========== RINGKASAN ==========
echo ""
echo "═══════════════════════════════════════════════════"
echo -e "${GREEN}Setup selesai!${NC}"
echo "═══════════════════════════════════════════════════"
echo "Queue driver     : $QUEUE_DRIVER"
echo "Jumlah worker     : $NUMPROCS"
echo "Config file       : $CONFIG_FILE"
echo "Log file          : /var/log/supervisor/${APP_NAME}-worker.log"
echo ""
echo "Perintah berguna:"
echo "  Status  : sudo supervisorctl status"
echo "  Restart : sudo supervisorctl restart ${APP_NAME}-queue-worker:*"
echo "  Log     : sudo tail -f /var/log/supervisor/${APP_NAME}-worker.log"
echo "  Retry job gagal : cd $PROJECT_PATH && php artisan queue:retry all"
echo "═══════════════════════════════════════════════════"
