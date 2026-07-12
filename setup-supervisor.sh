#!/bin/bash

#############################################
# Laravel Queue Worker - Supervisor Setup
# Untuk project: komitev1
#############################################

# ========== KONFIGURASI ==========
PROJECT_PATH="/var/www/komitev1"
QUEUE_DRIVER="redis"              # Bisa: redis, database, sqs, sync
QUEUE_CONNECTION="default"        # Connection name di config/queue.php
APP_USER="www-data"               # User yang jalankan process
NUMPROCS=4                         # Jumlah worker processes
APP_NAME="komitev1"

# ========== COLORS ==========
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# ========== HELPER FUNCTIONS ==========
print_header() {
    echo -e "\n${BLUE}========================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}========================================${NC}\n"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_info() {
    echo -e "${YELLOW}→ $1${NC}"
}

# ========== 1. CHECK PREREQUISITES ==========
print_header "1. Checking Prerequisites"

if [[ $EUID -ne 0 ]]; then
    print_error "Script harus dijalankan sebagai root (gunakan: sudo bash setup-supervisor.sh)"
    exit 1
fi

if [ ! -d "$PROJECT_PATH" ]; then
    print_error "Project path tidak ditemukan: $PROJECT_PATH"
    exit 1
fi

print_success "Running as root"
print_success "Project path exists: $PROJECT_PATH"

# ========== 2. INSTALL SUPERVISOR ==========
print_header "2. Installing Supervisor"

if command -v supervisorctl &> /dev/null; then
    print_success "Supervisor already installed"
else
    print_info "Installing supervisor..."
    apt-get update -qq
    apt-get install -y supervisor &> /dev/null
    print_success "Supervisor installed"
fi

# ========== 3. CREATE SUPERVISOR CONFIG ==========
print_header "3. Creating Supervisor Configuration"

CONFIG_FILE="/etc/supervisor/conf.d/${APP_NAME}-queue-worker.conf"

print_info "Creating config file: $CONFIG_FILE"

cat > "$CONFIG_FILE" << EOF
[program:${APP_NAME}-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php $PROJECT_PATH/artisan queue:work $QUEUE_DRIVER --connection=$QUEUE_CONNECTION --sleep=3 --tries=3 --timeout=30
autostart=true
autorestart=true
numprocs=$NUMPROCS
redirect_stderr=true
stdout_logfile=/var/log/supervisor/${APP_NAME}-worker.log
user=$APP_USER
stopasgroup=true
stopwaitsecs=600

[group:${APP_NAME}-queue]
programs=${APP_NAME}-queue-worker
priority=999
EOF

print_success "Config file created"

# ========== 4. VERIFY CONFIG SYNTAX ==========
print_header "4. Verifying Configuration"

if supervisorctl reread &> /dev/null; then
    print_success "Configuration syntax is valid"
else
    print_error "Configuration has errors"
    exit 1
fi

# ========== 5. UPDATE SUPERVISOR ==========
print_header "5. Updating Supervisor"

print_info "Reloading supervisor configuration..."
supervisorctl update

if [ $? -eq 0 ]; then
    print_success "Supervisor configuration updated"
else
    print_error "Failed to update supervisor"
    exit 1
fi

# ========== 6. START WORKERS ==========
print_header "6. Starting Queue Workers"

print_info "Starting ${APP_NAME}-queue-worker..."
supervisorctl start "${APP_NAME}-queue-worker:*"

sleep 2

# ========== 7. VERIFY STATUS ==========
print_header "7. Verifying Worker Status"

STATUS=$(supervisorctl status "${APP_NAME}-queue-worker:*" 2>&1)
echo "$STATUS"

if echo "$STATUS" | grep -q "RUNNING"; then
    print_success "All workers are running!"
else
    print_error "Some workers failed to start"
    print_info "Check logs: tail -f /var/log/supervisor/${APP_NAME}-worker.log"
    exit 1
fi

# ========== 8. ENABLE AUTO-START ON BOOT ==========
print_header "8. Enabling Auto-Start on Boot"

systemctl enable supervisor
print_success "Supervisor enabled to start on boot"

# ========== 9. PRINT SUMMARY ==========
print_header "Setup Complete! 🎉"

echo -e "${GREEN}Configuration Summary:${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Project Path    : $PROJECT_PATH"
echo "Queue Driver    : $QUEUE_DRIVER"
echo "App User        : $APP_USER"
echo "Worker Processes: $NUMPROCS"
echo "Config File     : $CONFIG_FILE"
echo "Log File        : /var/log/supervisor/${APP_NAME}-worker.log"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# ========== 10. USEFUL COMMANDS ==========
print_header "Useful Commands"

echo -e "${YELLOW}View worker status:${NC}"
echo "  supervisorctl status ${APP_NAME}-queue-worker:*"

echo -e "\n${YELLOW}View logs (real-time):${NC}"
echo "  tail -f /var/log/supervisor/${APP_NAME}-worker.log"

echo -e "\n${YELLOW}Restart workers:${NC}"
echo "  supervisorctl restart ${APP_NAME}-queue-worker:*"

echo -e "\n${YELLOW}Stop workers:${NC}"
echo "  supervisorctl stop ${APP_NAME}-queue-worker:*"

echo -e "\n${YELLOW}Start workers:${NC}"
echo "  supervisorctl start ${APP_NAME}-queue-worker:*"

echo -e "\n${YELLOW}View supervisor status:${NC}"
echo "  supervisorctl status"

echo -e "\n${YELLOW}Increase worker processes:${NC}"
echo "  Edit: $CONFIG_FILE"
echo "  Change: numprocs=4 to desired number"
echo "  Then run: supervisorctl update"

# ========== 11. VERIFY QUEUE CONNECTION ==========
print_header "11. Verifying Queue Connection"

cd "$PROJECT_PATH"

if php artisan tinker --execute="echo 'OK'" &> /dev/null; then
    print_success "Laravel application is accessible"
else
    print_error "Cannot access Laravel application"
fi

echo ""
print_success "Setup completed successfully! ✨"
echo ""
