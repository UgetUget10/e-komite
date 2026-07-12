#!/bin/bash

echo "🔍 Diagnosing Queue Worker Error..."
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Configuration
PROJECT_PATH="/var/www/komitev1"
PHP_PATH="/usr/bin/php"

# ========== 1. Check PHP ==========
echo "1️⃣ Checking PHP Installation..."
if command -v php &> /dev/null; then
    PHP_BIN=$(which php)
    PHP_VER=$(php -v | head -n 1)
    echo "✓ PHP found: $PHP_BIN"
    echo "✓ Version: $PHP_VER"
else
    echo "✗ PHP not found!"
    exit 1
fi

# ========== 2. Check Project Path ==========
echo ""
echo "2️⃣ Checking Project Path..."
if [ -d "$PROJECT_PATH" ]; then
    echo "✓ Project exists: $PROJECT_PATH"
    echo "  Contents:"
    ls -la "$PROJECT_PATH" | head -n 10
else
    echo "✗ Project NOT found at: $PROJECT_PATH"
    echo "  Please update PROJECT_PATH in script"
    exit 1
fi

# ========== 3. Check Laravel Installation ==========
echo ""
echo "3️⃣ Checking Laravel Installation..."
if [ -f "$PROJECT_PATH/artisan" ]; then
    echo "✓ artisan file found"
else
    echo "✗ artisan file NOT found"
    exit 1
fi

# ========== 4. Check .env File ==========
echo ""
echo "4️⃣ Checking .env Configuration..."
if [ -f "$PROJECT_PATH/.env" ]; then
    echo "✓ .env file exists"
    echo "  Queue settings:"
    grep -E "QUEUE_DRIVER|QUEUE_CONNECTION|REDIS_HOST" "$PROJECT_PATH/.env" || echo "  (settings not found)"
else
    echo "✗ .env file NOT found!"
    exit 1
fi

# ========== 5. Test Laravel Command ==========
echo ""
echo "5️⃣ Testing Laravel artisan command..."
cd "$PROJECT_PATH"

# Try to run artisan
OUTPUT=$(php artisan list 2>&1 | head -n 5)
if echo "$OUTPUT" | grep -q "Laravel Framework"; then
    echo "✓ Laravel artisan works"
else
    echo "✗ Laravel artisan failed:"
    echo "$OUTPUT"
    exit 1
fi

# ========== 6. Test Queue Command ==========
echo ""
echo "6️⃣ Testing queue:work command..."
QUEUE_TEST=$(php artisan queue:work --help 2>&1 | head -n 1)
if echo "$QUEUE_TEST" | grep -q "Description"; then
    echo "✓ queue:work command available"
else
    echo "✗ queue:work command failed:"
    echo "$QUEUE_TEST"
fi

# ========== 7. Check Redis ==========
echo ""
echo "7️⃣ Checking Redis Connection..."
REDIS_TEST=$(php artisan tinker --execute="echo 'Redis OK'" 2>&1)
if echo "$REDIS_TEST" | grep -q "OK\|redis"; then
    echo "✓ Redis connection possible"
else
    echo "⚠️  Might need to check Redis:"
    echo "   sudo systemctl status redis-server"
    echo "   or check if using database queue instead"
fi

# ========== 8. Check File Permissions ==========
echo ""
echo "8️⃣ Checking File Permissions..."
echo "  Project owner:"
ls -ld "$PROJECT_PATH"
echo ""
echo "  artisan file permissions:"
ls -l "$PROJECT_PATH/artisan"

# ========== 9. Check www-data User ==========
echo ""
echo "9️⃣ Checking www-data User..."
if id -u www-data &> /dev/null; then
    echo "✓ www-data user exists"
    echo "  Can www-data access project?"
    if sudo -u www-data test -r "$PROJECT_PATH/artisan"; then
        echo "  ✓ Yes, can read artisan"
    else
        echo "  ✗ No, permission denied!"
    fi
else
    echo "✗ www-data user NOT found"
fi

# ========== 10. Test as www-data ==========
echo ""
echo "🔟 Testing command as www-data user..."
TEST_CMD="cd $PROJECT_PATH && php artisan list 2>&1 | head -n 3"
echo "  Running: $TEST_CMD"
echo ""
RESULT=$(sudo -u www-data bash -c "$TEST_CMD")
echo "$RESULT"

# ========== 11. Check Log File Permissions ==========
echo ""
echo "1️⃣1️⃣ Checking Log Directory..."
LOG_DIR="/var/log/supervisor"
if [ -d "$LOG_DIR" ]; then
    echo "✓ Log directory exists"
    ls -ld "$LOG_DIR"
    echo "  Can www-data write here?"
    if touch "$LOG_DIR/test-write.log" 2>/dev/null; then
        rm "$LOG_DIR/test-write.log"
        echo "  ✓ Yes"
    else
        echo "  ✗ No - might need: sudo chown www-data:www-data $LOG_DIR"
    fi
else
    echo "✗ Log directory doesn't exist"
    echo "  Creating..."
    sudo mkdir -p "$LOG_DIR"
fi

# ========== 12. Full Diagnostic Command ==========
echo ""
echo "1️⃣2️⃣ Test Full Command..."
FULL_CMD="php $PROJECT_PATH/artisan queue:work redis --sleep=3 --tries=3 --timeout=30 --queue=default"
echo "  Command: $FULL_CMD"
echo ""
echo "  Testing (5 seconds)..."
timeout 5 bash -c "cd $PROJECT_PATH && $FULL_CMD" 2>&1 || echo "  (Process exited normally)"

# ========== Summary ==========
echo ""
echo "========================================="
echo "📋 DIAGNOSTIC SUMMARY"
echo "========================================="
echo ""
echo "If all checks passed, update Supervisor config:"
echo ""
echo "  sudo nano /etc/supervisor/conf.d/komitev1-queue-worker.conf"
echo ""
echo "Make sure:"
echo "  1. command= uses full paths:"
echo "     command=/usr/bin/php /var/www/komitev1/artisan ..."
echo ""
echo "  2. user=www-data (must exist)"
echo ""
echo "  3. Log directory exists:"
echo "     sudo mkdir -p /var/log/supervisor"
echo "     sudo chown www-data:www-data /var/log/supervisor"
echo ""
echo "Then restart:"
echo "  sudo supervisorctl reread"
echo "  sudo supervisorctl update"
echo "  sudo supervisorctl start komitev1-queue-worker:*"
echo ""
