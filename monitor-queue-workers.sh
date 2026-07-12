#!/bin/bash

###########################################################
# Queue Worker Monitoring Script
# Cek status workers dan restart otomatis jika crash
###########################################################

APP_NAME="komitev1"
LOG_FILE="/var/log/queue-monitor.log"
ALERT_EMAIL="admin@example.com"  # Ganti dengan email kamu

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Logging function
log_message() {
    TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')
    echo "[$TIMESTAMP] $1" >> "$LOG_FILE"
    echo -e "$1"
}

# ========== CHECK WORKERS ==========
log_message "🔍 Checking queue workers status..."

STATUS=$(supervisorctl status "${APP_NAME}-queue-worker:*" 2>&1)

# Count running processes
RUNNING=$(echo "$STATUS" | grep -c "RUNNING")
TOTAL_PROCESSES=$(echo "$STATUS" | grep -c "queue-worker")

log_message "📊 Status: $RUNNING/$TOTAL_PROCESSES workers running"
echo "$STATUS" >> "$LOG_FILE"

# ========== CHECK FOR FAILURES ==========
if echo "$STATUS" | grep -q "STOPPED\|FATAL\|EXITED"; then
    log_message "${RED}⚠️  WARNING: Some workers are not running!${NC}"
    echo "$STATUS"

    # Attempt restart
    log_message "🔄 Attempting to restart workers..."
    supervisorctl restart "${APP_NAME}-queue-worker:*" >> "$LOG_FILE" 2>&1

    # Wait for restart
    sleep 3

    # Verify restart
    NEW_STATUS=$(supervisorctl status "${APP_NAME}-queue-worker:*" 2>&1)
    NEW_RUNNING=$(echo "$NEW_STATUS" | grep -c "RUNNING")

    if [ "$NEW_RUNNING" -eq "$TOTAL_PROCESSES" ]; then
        log_message "${GREEN}✓ All workers restarted successfully${NC}"
    else
        log_message "${RED}✗ Failed to restart all workers!${NC}"

        # Send alert email (optional)
        # echo "Queue workers failed to restart. Check logs: tail -f $LOG_FILE" | \
        #     mail -s "🚨 Alert: Queue Workers Failed" "$ALERT_EMAIL"
    fi
else
    log_message "${GREEN}✓ All workers are running${NC}"
fi

# ========== CHECK LOG SIZE ==========
LOG_SIZE=$(du -m "$LOG_FILE" 2>/dev/null | awk '{print $1}')
if [ "$LOG_SIZE" -gt 50 ]; then
    log_message "📁 Log file size: ${LOG_SIZE}MB - Archiving..."
    ARCHIVE_DATE=$(date '+%Y%m%d_%H%M%S')
    mv "$LOG_FILE" "${LOG_FILE}.${ARCHIVE_DATE}"
    > "$LOG_FILE"  # Reset log
fi

log_message "✅ Monitoring check complete\n"
