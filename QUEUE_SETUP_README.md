# Laravel Queue Worker Setup - Complete Guide

Panduan lengkap setup Laravel Queue Worker otomatis di Ubuntu Server menggunakan Supervisor + Monitoring.

---

## 📋 Files Included

| File | Purpose |
|------|---------|
| `setup-supervisor.sh` | Main setup script - install dan config Supervisor |
| `monitor-queue-workers.sh` | Monitoring script - check workers & restart otomatis |
| `SETUP_CRON_MONITORING.sh` | Setup cron job untuk monitoring |
| `SUPERVISOR_SETUP.md` | Dokumentasi detail Supervisor |
| `QUEUE_SETUP_README.md` | File ini - panduan lengkap |

---

## 🚀 Quick Start (5 Menit)

### Step 1: Upload Files ke Server
```bash
scp *.sh user@your-server:/tmp/
scp *.md user@your-server:/tmp/
```

### Step 2: SSH ke Server
```bash
ssh user@your-server
cd /tmp
```

### Step 3: Run Main Setup
```bash
sudo bash setup-supervisor.sh
```

**Output yang diharapkan:**
```
✓ Supervisor installed
✓ Config file created
✓ All workers are running!
```

### Step 4: Setup Monitoring (Optional tapi Recommended)
```bash
sudo bash SETUP_CRON_MONITORING.sh
```

### Step 5: Verify
```bash
sudo supervisorctl status komitev1-queue-worker:*
```

**Done!** ✨ Queue workers sekarang running dan auto-restart jika crash.

---

## 📝 Detailed Setup Steps

### Pre-requisites
- Ubuntu 18.04+ atau Debian 10+
- PHP 7.4+ dengan CLI
- Laravel 6+ dengan queue configured
- Redis atau database untuk queue driver
- SSH access dengan sudo privileges

### Step 1: Customize Configuration (Opsional)

Edit `setup-supervisor.sh` line 12-19:
```bash
PROJECT_PATH="/var/www/komitev1"     # Path ke Laravel project
QUEUE_DRIVER="redis"                  # redis, database, sqs, sync
APP_USER="www-data"                   # User yang jalankan process
NUMPROCS=4                            # Jumlah worker processes
APP_NAME="komitev1"                   # Nama aplikasi
```

### Step 2: Run Setup
```bash
sudo bash setup-supervisor.sh
```

Script akan:
1. ✅ Install supervisor (jika belum ada)
2. ✅ Membuat config file `/etc/supervisor/conf.d/komitev1-queue-worker.conf`
3. ✅ Reload dan update supervisor
4. ✅ Start 4 worker processes
5. ✅ Enable auto-start on boot
6. ✅ Verify semua workers RUNNING

### Step 3: Setup Monitoring (Recommended)
```bash
sudo bash SETUP_CRON_MONITORING.sh
```

Ini setup cron job yang:
- ✅ Jalankan setiap 5 menit
- ✅ Check status workers
- ✅ Auto-restart jika ada yang down
- ✅ Log semua activity

---

## 🔍 Verification

### Check Workers Running
```bash
sudo supervisorctl status komitev1-queue-worker:*
```

Expected output:
```
komitev1-queue-worker:00 RUNNING   pid 1234, uptime 0:05:23
komitev1-queue-worker:01 RUNNING   pid 1235, uptime 0:05:23
komitev1-queue-worker:02 RUNNING   pid 1236, uptime 0:05:23
komitev1-queue-worker:03 RUNNING   pid 1237, uptime 0:05:23
```

### Check Supervisor Service
```bash
sudo systemctl status supervisor
```

### View Logs
```bash
# Real-time logs
tail -f /var/log/supervisor/komitev1-worker.log

# Monitoring logs
tail -f /var/log/queue-monitor.log

# Last 100 lines
tail -n 100 /var/log/supervisor/komitev1-worker.log
```

---

## 🎛️ Management Commands

### Start/Stop/Restart
```bash
# Restart all workers
sudo supervisorctl restart komitev1-queue-worker:*

# Stop all workers
sudo supervisorctl stop komitev1-queue-worker:*

# Start all workers
sudo supervisorctl start komitev1-queue-worker:*

# Restart single worker
sudo supervisorctl restart komitev1-queue-worker:00
```

### Check Supervisor Status
```bash
sudo supervisorctl status
```

### Reload Configuration
```bash
# After editing config file
sudo supervisorctl reread
sudo supervisorctl update
```

---

## ⚙️ Configuration Changes

### Increase Worker Processes

Edit `/etc/supervisor/conf.d/komitev1-queue-worker.conf`:
```ini
numprocs=8  # Change dari 4 ke 8
```

Then reload:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl restart komitev1-queue-worker:*
```

### Change Queue Driver

Edit config file:
```ini
# Redis
command=php /var/www/komitev1/artisan queue:work redis ...

# Database
command=php /var/www/komitev1/artisan queue:work database ...

# SQS
command=php /var/www/komitev1/artisan queue:work sqs ...
```

### Adjust Job Parameters

```ini
--sleep=3           # Detik tunggu jika queue kosong
--tries=3           # Retry count untuk failed jobs
--timeout=30        # Timeout per job (detik)
--max-jobs=1000     # Max jobs sebelum restart worker
--max-time=3600     # Max runtime sebelum restart worker (detik)
```

---

## 🐛 Troubleshooting

### ❌ Workers tidak jalan

```bash
# Check logs
tail -f /var/log/supervisor/komitev1-worker.log

# Verify config
sudo supervisorctl reread

# Manual restart
sudo supervisorctl restart komitev1-queue-worker:*
```

### ❌ "Command not found: php"

Update config file:
```ini
# Find PHP path
which php  # Usually /usr/bin/php

# Update config with full path
command=/usr/bin/php /var/www/komitev1/artisan queue:work ...
```

### ❌ "Permission denied"

Fix directory permissions:
```bash
sudo chown -R www-data:www-data /var/www/komitev1
sudo chmod -R 755 /var/www/komitev1
```

### ❌ Jobs tidak di-process

Check queue configuration:
```bash
# View env
cat /var/www/komitev1/.env | grep QUEUE

# Verify driver matches config
php artisan config:show queue

# Test queue
php artisan queue:failed  # Check failed jobs
```

### ❌ Workers sering crash

Check logs for errors:
```bash
# Real-time logs with errors
grep "ERROR" /var/log/supervisor/komitev1-worker.log

# Test single job with debug
php artisan queue:work --tries=1 --timeout=60 -vvv
```

---

## 📊 Monitoring Best Practices

### Regular Log Review
```bash
# Daily
tail -n 200 /var/log/queue-monitor.log

# Weekly
wc -l /var/log/supervisor/komitev1-worker.log
```

### Check Failed Jobs
```bash
# List failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Flush failed jobs
php artisan queue:flush
```

### Monitor Resource Usage
```bash
# Watch supervisor memory/CPU
watch -n 5 'ps aux | grep "artisan queue:work"'

# Check load average
uptime
```

---

## 🚨 Alerts & Notifications

### Setup Email Alerts (Optional)

Edit `monitor-queue-workers.sh` line 11-12:
```bash
ALERT_EMAIL="your-email@example.com"
```

Uncomment email section (line ~68):
```bash
# Send alert email (optional)
echo "Queue workers failed to restart. Check: tail -f $LOG_FILE" | \
    mail -s "🚨 Alert: Queue Workers Failed" "$ALERT_EMAIL"
```

Install mail utility:
```bash
sudo apt-get install mailutils
```

---

## 📈 Performance Tuning

### Optimal Worker Count
```bash
# For 4 CPU cores: 4-6 workers
# For 8 CPU cores: 8-12 workers
# Rule: 1-2 workers per CPU core
```

### Memory Optimization
```ini
# Add to config to restart worker after N jobs
--max-jobs=1000

# Restart after 1 hour runtime
--max-time=3600
```

### Queue Prioritization
```ini
# Process high-priority queue first
command=... queue:work redis --queue=high,default,low
```

---

## 🔒 Security Notes

✅ **Best Practices:**
- Run workers as `www-data` user (not root)
- Keep logs in `/var/log/` with restricted permissions
- Monitor failed jobs for security issues
- Disable auto-restart for jobs with security implications

```bash
# Secure log permissions
sudo chmod 640 /var/log/supervisor/komitev1-worker.log
sudo chown www-data:www-data /var/log/supervisor/komitev1-worker.log
```

---

## 📚 Additional Resources

- [Laravel Queue Documentation](https://laravel.com/docs/queues)
- [Supervisor Official Docs](http://supervisord.org/)
- [Redis Driver Setup](https://laravel.com/docs/redis)
- [Database Driver Setup](https://laravel.com/docs/queues#driver-prerequisites)

---

## ✅ Checklist

- [ ] Supervisor installed
- [ ] Config file created
- [ ] All workers running
- [ ] Auto-start on boot enabled
- [ ] Monitoring script setup
- [ ] Cron job configured
- [ ] Logs verified
- [ ] Email alerts configured (optional)
- [ ] Firewall rules checked
- [ ] Backup strategy in place

---

## 🎯 Summary

Anda sekarang memiliki:

✅ **Automated Queue Processing**
- Laravel queue worker berjalan 24/7
- Auto-restart jika crash
- Auto-start saat server reboot

✅ **Monitoring & Alerting**
- Cron job check setiap 5 menit
- Auto-restart jika ada worker down
- Logging untuk audit trail

✅ **Easy Management**
- Simple commands untuk start/stop/restart
- Easy to scale (tambah workers)
- Production-ready configuration

**Siap untuk Production!** 🚀

---

## Support

Jika ada masalah, check:
1. Logs: `tail -f /var/log/supervisor/komitev1-worker.log`
2. Permissions: `ls -la /var/www/komitev1/`
3. Queue driver: `php artisan config:show queue`
4. Supervisor status: `sudo supervisorctl status`

Good luck! 💪
