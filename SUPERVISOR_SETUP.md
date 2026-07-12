# Laravel Queue Worker Setup dengan Supervisor

Setup otomatis Laravel Queue Worker di Ubuntu menggunakan Supervisor.

## Quick Start

### 1. Copy Script ke Server Ubuntu
```bash
scp setup-supervisor.sh user@server:/tmp/
```

### 2. Run Setup Script
```bash
ssh user@server
sudo bash /tmp/setup-supervisor.sh
```

Script akan:
- ✅ Install supervisor (jika belum ada)
- ✅ Buat config file untuk queue worker
- ✅ Start 4 worker processes (configurable)
- ✅ Enable auto-start on boot
- ✅ Verify semua workers running

---

## Manual Setup (jika tidak bisa run script)

### 1. Install Supervisor
```bash
sudo apt-get update
sudo apt-get install supervisor
```

### 2. Buat Config File
```bash
sudo nano /etc/supervisor/conf.d/komitev1-queue-worker.conf
```

Copy paste:
```ini
[program:komitev1-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/komitev1/artisan queue:work redis --sleep=3 --tries=3 --timeout=30
autostart=true
autorestart=true
numprocs=4
redirect_stderr=true
stdout_logfile=/var/log/supervisor/komitev1-worker.log
user=www-data
stopasgroup=true
stopwaitsecs=600
```

### 3. Update & Start
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start komitev1-queue-worker:*
```

### 4. Verify
```bash
sudo supervisorctl status
```

---

## Configuration Options

Edit `/etc/supervisor/conf.d/komitev1-queue-worker.conf`:

### Mengubah Queue Driver
```ini
# Untuk Redis (default)
command=php /var/www/komitev1/artisan queue:work redis ...

# Untuk Database
command=php /var/www/komitev1/artisan queue:work database ...

# Untuk SQS
command=php /var/www/komitev1/artisan queue:work sqs ...
```

### Mengubah Jumlah Worker Processes
```ini
numprocs=4  # Ganti ke angka lain (1-8 recommended)
```

Setelah edit, reload:
```bash
sudo supervisorctl update
```

### Tuning Parameters
```ini
--sleep=3          # Detik tunggu jika queue kosong
--tries=3          # Berapa kali retry jika job gagal
--timeout=30       # Timeout per job (detik)
--max-jobs=1000    # Max jobs sebelum restart
--max-time=3600    # Max waktu berjalan sebelum restart (detik)
```

---

## Monitoring & Management

### 1. Check Worker Status
```bash
sudo supervisorctl status komitev1-queue-worker:*
```

Output:
```
komitev1-queue-worker:00    RUNNING    uptime 2:30:15
komitev1-queue-worker:01    RUNNING    uptime 2:30:15
komitev1-queue-worker:02    RUNNING    uptime 2:30:15
komitev1-queue-worker:03    RUNNING    uptime 2:30:15
```

### 2. View Live Logs
```bash
tail -f /var/log/supervisor/komitev1-worker.log
```

### 3. View Last N Lines
```bash
tail -n 100 /var/log/supervisor/komitev1-worker.log
```

### 4. Restart All Workers
```bash
sudo supervisorctl restart komitev1-queue-worker:*
```

### 5. Restart Single Worker
```bash
sudo supervisorctl restart komitev1-queue-worker:00
```

### 6. Stop All Workers
```bash
sudo supervisorctl stop komitev1-queue-worker:*
```

### 7. Start All Workers
```bash
sudo supervisorctl start komitev1-queue-worker:*
```

---

## Troubleshooting

### ❌ Workers tidak jalan
```bash
# Lihat error di logs
tail -f /var/log/supervisor/komitev1-worker.log

# Verify config syntax
sudo supervisorctl reread

# Check supervisor status
sudo supervisorctl status
```

### ❌ "Command not found: php"
**Fix:** Gunakan full path ke PHP
```ini
command=/usr/bin/php /var/www/komitev1/artisan queue:work redis ...
```

### ❌ "Permission denied"
**Fix:** Pastikan user www-data punya akses ke project
```bash
sudo chown -R www-data:www-data /var/www/komitev1
```

### ❌ Jobs tidak di-process
**Fix:** Check queue driver di `.env`
```bash
# Verify driver matches config
cat .env | grep QUEUE_DRIVER

# Test queue connection
php artisan queue:failed
```

### ❌ Worker process sering crash
**Fix:** Check logs dan restart dengan debug
```bash
# Lihat failed jobs
php artisan queue:failed

# Test single job
php artisan queue:work --tries=1 --timeout=60
```

---

## Advanced: Multiple Queue Drivers

Jika ingin process multiple queues:

```ini
[program:komitev1-queue-default]
command=php /var/www/komitev1/artisan queue:work redis --queue=default ...
numprocs=2

[program:komitev1-queue-high]
command=php /var/www/komitev1/artisan queue:work redis --queue=high ...
numprocs=4

[program:komitev1-queue-low]
command=php /var/www/komitev1/artisan queue:work redis --queue=low ...
numprocs=1

[group:komitev1-queues]
programs=komitev1-queue-default,komitev1-queue-high,komitev1-queue-low
```

---

## Advanced: Health Check Script

Buat file `/opt/check-queue-workers.sh`:

```bash
#!/bin/bash

# Check if queue workers are running
STATUS=$(supervisorctl status komitev1-queue-worker:* 2>&1)

if echo "$STATUS" | grep -q "STOPPED"; then
    echo "⚠️ Some queue workers are stopped!"
    echo "$STATUS"
    supervisorctl start komitev1-queue-worker:*
    echo "✓ Workers restarted"
elif echo "$STATUS" | grep -q "FATAL"; then
    echo "❌ Fatal error in queue workers"
    echo "$STATUS"
    exit 1
else
    echo "✓ All queue workers running"
fi
```

Jalankan via cron setiap 5 menit:
```bash
*/5 * * * * /bin/bash /opt/check-queue-workers.sh
```

---

## Best Practices

✅ **DO:**
- Monitor logs regularly
- Restart workers saat deploy
- Set appropriate `--timeout` sesuai job duration
- Use multiple workers (numprocs=2-4)
- Keep PHP dan dependencies updated

❌ **DON'T:**
- Run workers sebagai root
- Set timeout terlalu kecil (<30 detik)
- Ignore failed jobs terus-menerus
- Overload server dengan terlalu banyak workers

---

## Quick Reference

```bash
# Status
sudo supervisorctl status

# Start/Stop/Restart
sudo supervisorctl restart komitev1-queue-worker:*
sudo supervisorctl stop komitev1-queue-worker:*
sudo supervisorctl start komitev1-queue-worker:*

# Logs
tail -f /var/log/supervisor/komitev1-worker.log

# Reload config
sudo supervisorctl reread
sudo supervisorctl update

# View all supervisor processes
sudo supervisorctl
```

---

## Links
- [Laravel Queue Documentation](https://laravel.com/docs/queues)
- [Supervisor Documentation](http://supervisord.org/)
- [Redis Queue Driver](https://laravel.com/docs/redis)
