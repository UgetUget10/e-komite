#!/bin/bash

echo "🔧 Fixing 'no such group' error..."
echo ""

# Step 1: Stop supervisor
echo "1️⃣ Stopping supervisor service..."
sudo systemctl stop supervisor
sleep 2
echo "✓ Supervisor stopped"

# Step 2: Remove old config
echo ""
echo "2️⃣ Checking config files..."
CONFIG_FILE="/etc/supervisor/conf.d/komitev1-queue-worker.conf"

if [ -f "$CONFIG_FILE" ]; then
    echo "  Found: $CONFIG_FILE"
    echo "  Backing up..."
    sudo cp "$CONFIG_FILE" "${CONFIG_FILE}.bak"
    echo "  ✓ Backup created"
fi

# Step 3: Create fresh config WITHOUT group (simpler)
echo ""
echo "3️⃣ Creating new config (without group)..."

sudo tee "$CONFIG_FILE" > /dev/null << 'EOF'
[program:komitev1-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/komitev1/artisan queue:work redis --sleep=3 --tries=3 --timeout=30 --queue=default
autostart=true
autorestart=true
numprocs=4
redirect_stderr=true
stdout_logfile=/var/log/supervisor/komitev1-worker.log
user=www-data
stopasgroup=true
stopwaitsecs=600
EOF

echo "✓ New config created"
echo ""
echo "📄 Config content:"
echo "---"
cat "$CONFIG_FILE"
echo "---"

# Step 4: Start supervisor
echo ""
echo "4️⃣ Starting supervisor service..."
sudo systemctl start supervisor
sleep 3
echo "✓ Supervisor started"

# Step 5: Reread config
echo ""
echo "5️⃣ Reading supervisor config..."
sudo supervisorctl reread
sleep 1

# Step 6: Update
echo ""
echo "6️⃣ Updating supervisor..."
sudo supervisorctl update
sleep 2

# Step 7: List available programs
echo ""
echo "7️⃣ Available programs in supervisor:"
sudo supervisorctl avail
echo ""

# Step 8: Start workers
echo ""
echo "8️⃣ Starting workers..."
sudo supervisorctl start komitev1-queue-worker:*
sleep 3

# Step 9: Check status
echo ""
echo "9️⃣ Worker status:"
STATUS=$(sudo supervisorctl status komitev1-queue-worker:*)
echo "$STATUS"
echo ""

if echo "$STATUS" | grep -q "RUNNING"; then
    echo "✅ SUCCESS! Workers are running!"
    echo ""
    echo "✨ Summary:"
    echo "   - 4 queue workers started"
    echo "   - Auto-restart enabled"
    echo "   - Logs: /var/log/supervisor/komitev1-worker.log"
else
    echo "⚠️  Workers not running yet. Checking logs..."
    echo ""
    sudo tail -n 20 /var/log/supervisor/komitev1-worker.log
fi

echo ""
echo "📝 Commands:"
echo "   Check status:    sudo supervisorctl status"
echo "   View logs:       sudo tail -f /var/log/supervisor/komitev1-worker.log"
echo "   Restart:         sudo supervisorctl restart komitev1-queue-worker:*"
echo "   Stop:            sudo supervisorctl stop komitev1-queue-worker:*"
