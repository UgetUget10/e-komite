#!/bin/bash

#############################################
# Fix Supervisor Config Error
#############################################

echo "🔧 Fixing Supervisor Configuration Error..."
echo ""

# Step 1: Verify config file exists
echo "1️⃣ Checking config file..."
CONFIG_FILE="/etc/supervisor/conf.d/komitev1-queue-worker.conf"

if [ -f "$CONFIG_FILE" ]; then
    echo "✓ Config file found: $CONFIG_FILE"
else
    echo "✗ Config file NOT found!"
    exit 1
fi

# Step 2: Verify config syntax
echo ""
echo "2️⃣ Verifying config syntax..."
sudo supervisorctl reread 2>&1

if [ $? -eq 0 ]; then
    echo "✓ Config syntax is valid"
else
    echo "✗ Config has syntax errors - please check manually:"
    cat "$CONFIG_FILE"
    exit 1
fi

# Step 3: Restart supervisor service
echo ""
echo "3️⃣ Restarting Supervisor service..."
sudo systemctl restart supervisor

sleep 2

# Step 4: Check supervisor status
echo ""
echo "4️⃣ Checking Supervisor status..."
sudo systemctl status supervisor --no-pager

# Step 5: Update supervisor config
echo ""
echo "5️⃣ Updating Supervisor configuration..."
sudo supervisorctl update

sleep 2

# Step 6: Check group exists
echo ""
echo "6️⃣ Checking queue group..."
sudo supervisorctl avail | grep komitev1

# Step 7: Start workers
echo ""
echo "7️⃣ Starting queue workers..."
sudo supervisorctl start komitev1-queue-worker:*

sleep 2

# Step 8: Verify status
echo ""
echo "8️⃣ Verifying worker status..."
RESULT=$(sudo supervisorctl status komitev1-queue-worker:* 2>&1)
echo "$RESULT"

if echo "$RESULT" | grep -q "RUNNING"; then
    echo ""
    echo "✅ SUCCESS! All workers are now running!"
    echo ""
    echo "📝 Next steps:"
    echo "   - Setup monitoring: sudo bash SETUP_CRON_MONITORING.sh"
    echo "   - View logs: tail -f /var/log/supervisor/komitev1-worker.log"
    echo "   - Check status: sudo supervisorctl status"
else
    echo ""
    echo "❌ Workers still not running - debugging..."
    echo ""
    echo "Check logs manually:"
    echo "   sudo tail -f /var/log/supervisor/komitev1-worker.log"
    echo ""
    echo "Check if file exists and is readable:"
    echo "   cat $CONFIG_FILE"
    echo ""
    echo "Try manual restart:"
    echo "   sudo supervisorctl restart komitev1-queue-worker:*"
fi
