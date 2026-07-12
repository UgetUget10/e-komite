#!/bin/bash

##########################################################
# Setup Cron Job untuk Monitoring Queue Workers
##########################################################

# Configuration
MONITOR_SCRIPT="/var/www/komitev1/monitor-queue-workers.sh"
MONITOR_INTERVAL="*/5"  # Check every 5 minutes

echo "🔧 Setting up cron job for queue worker monitoring..."

# Make script executable
chmod +x "$MONITOR_SCRIPT"
echo "✓ Script made executable"

# Install cron job
(crontab -l 2>/dev/null; echo "$MONITOR_INTERVAL * * * * $MONITOR_SCRIPT") | crontab -
echo "✓ Cron job installed"

# Verify cron job
echo ""
echo "📋 Current cron jobs for root:"
crontab -l | grep "monitor-queue-workers"

echo ""
echo "✅ Setup complete!"
echo ""
echo "📝 Cron job details:"
echo "  - Runs every 5 minutes"
echo "  - Checks queue worker status"
echo "  - Auto-restarts if any worker is down"
echo "  - Logs to: /var/log/queue-monitor.log"
echo ""
echo "🔍 View monitoring logs:"
echo "   tail -f /var/log/queue-monitor.log"
echo ""
echo "🔧 To edit cron interval:"
echo "   sudo crontab -e"
echo ""
