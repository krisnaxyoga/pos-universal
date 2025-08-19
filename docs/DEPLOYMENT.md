# 🚀 Production Deployment Guide

Panduan lengkap deploy aplikasi POS ke production server.

## 📋 Prerequisites

### Server Requirements
- **Ubuntu 20.04+** atau **CentOS 8+**
- **RAM**: Minimal 2GB (recommended 4GB+)
- **Storage**: Minimal 10GB free space
- **Domain**: Domain name dengan SSL
- **Root/Sudo Access**

### Software Stack
- **PHP 8.2+**
- **Nginx** atau **Apache**
- **MySQL 8.0+** atau **PostgreSQL 13+**
- **Node.js 18+**
- **Composer**
- **Git**

## 🔧 Server Setup

### 1. Update System
```bash
# Ubuntu/Debian
sudo apt update && sudo apt upgrade -y

# CentOS/RHEL
sudo yum update -y
```

### 2. Install PHP 8.2
```bash
# Ubuntu/Debian
sudo apt install software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install php8.2-fpm php8.2-mysql php8.2-xml php8.2-gd \
  php8.2-curl php8.2-mbstring php8.2-zip php8.2-bcmath \
  php8.2-sqlite3 php8.2-intl php8.2-redis

# CentOS/RHEL
sudo dnf install epel-release
sudo dnf install php82 php82-php-fpm php82-php-mysql php82-php-xml \
  php82-php-gd php82-php-curl php82-php-mbstring php82-php-zip
```

### 3. Install Nginx
```bash
# Ubuntu/Debian
sudo apt install nginx

# CentOS/RHEL
sudo yum install nginx

# Start and enable
sudo systemctl start nginx
sudo systemctl enable nginx
```

### 4. Install MySQL
```bash
# Ubuntu/Debian
sudo apt install mysql-server

# CentOS/RHEL
sudo yum install mysql-server

# Secure installation
sudo mysql_secure_installation
```

### 5. Install Node.js
```bash
# Install Node.js 18
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Verify installation
node --version
npm --version
```

### 6. Install Composer
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

## 📦 Application Deployment

### 1. Create Database
```bash
# Login to MySQL
sudo mysql -u root -p

# Create database and user
CREATE DATABASE pos_app;
CREATE USER 'pos_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON pos_app.* TO 'pos_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 2. Clone Repository
```bash
# Create web directory
sudo mkdir -p /var/www/pos-app
cd /var/www/pos-app

# Clone repository
sudo git clone https://github.com/your-username/pos-app.git .

# Set ownership
sudo chown -R www-data:www-data /var/www/pos-app
```

### 3. Install Dependencies
```bash
# Install PHP dependencies
sudo -u www-data composer install --optimize-autoloader --no-dev

# Install Node.js dependencies
sudo -u www-data npm install

# Build production assets
sudo -u www-data npm run build
```

### 4. Environment Configuration
```bash
# Copy environment file
sudo -u www-data cp .env.example .env

# Generate application key
sudo -u www-data php artisan key:generate
```

Edit `.env` file:
```bash
sudo nano .env
```

```env
# Application
APP_NAME="POS Application"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pos_app
DB_USERNAME=pos_user
DB_PASSWORD=secure_password

# iPaymu Production
IPAYMU_VA=your_production_va
IPAYMU_SECRET_KEY=your_production_secret
IPAYMU_ENVIRONMENT=production
IPAYMU_CALLBACK_URL="${APP_URL}/api/payment/callback"

# Session & Cache
SESSION_DRIVER=redis
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Queue
QUEUE_CONNECTION=redis

# Mail (optional)
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
```

### 5. Run Migrations
```bash
# Run database migrations
sudo -u www-data php artisan migrate --force

# Seed initial data (optional)
sudo -u www-data php artisan db:seed --force
```

### 6. Set Permissions
```bash
# Set proper ownership
sudo chown -R www-data:www-data /var/www/pos-app

# Set directory permissions
sudo find /var/www/pos-app -type d -exec chmod 755 {} \;
sudo find /var/www/pos-app -type f -exec chmod 644 {} \;

# Set writable directories
sudo chmod -R 775 /var/www/pos-app/storage
sudo chmod -R 775 /var/www/pos-app/bootstrap/cache

# Create storage link
sudo -u www-data php artisan storage:link
```

### 7. Optimize Application
```bash
# Cache configuration
sudo -u www-data php artisan config:cache

# Cache routes
sudo -u www-data php artisan route:cache

# Cache views
sudo -u www-data php artisan view:cache

# Optimize Composer autoloader
sudo -u www-data composer dump-autoload --optimize
```

## 🌐 Nginx Configuration

### 1. Create Nginx Virtual Host
```bash
sudo nano /etc/nginx/sites-available/pos-app
```

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/pos-app/public;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    index index.php;

    charset utf-8;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types
        text/plain
        text/css
        text/xml
        text/javascript
        application/javascript
        application/xml+rss
        application/json;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Cache static assets
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Security: Deny access to sensitive files
    location ~ /\.(env|git) {
        deny all;
        return 404;
    }

    location /storage {
        alias /var/www/pos-app/storage/app/public;
    }

    # Rate limiting for API endpoints
    location /api/ {
        limit_req zone=api burst=20 nodelay;
        try_files $uri $uri/ /index.php?$query_string;
    }
}

# Rate limiting configuration
http {
    limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;
}
```

### 2. Enable Site
```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/pos-app /etc/nginx/sites-enabled/

# Remove default site
sudo rm /etc/nginx/sites-enabled/default

# Test configuration
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx
```

## 🔒 SSL Certificate

### 1. Install Certbot
```bash
# Ubuntu/Debian
sudo apt install certbot python3-certbot-nginx

# CentOS/RHEL
sudo yum install certbot python3-certbot-nginx
```

### 2. Obtain SSL Certificate
```bash
# Get certificate for your domain
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Auto-renewal setup
sudo systemctl enable certbot.timer
sudo systemctl start certbot.timer
```

### 3. Test Auto-renewal
```bash
sudo certbot renew --dry-run
```

## 📊 Performance Optimization

### 1. Install Redis
```bash
# Ubuntu/Debian
sudo apt install redis-server

# CentOS/RHEL
sudo yum install redis

# Start and enable
sudo systemctl start redis
sudo systemctl enable redis

# Test Redis
redis-cli ping
```

### 2. Configure PHP-FPM
```bash
sudo nano /etc/php/8.2/fpm/pool.d/www.conf
```

```ini
; Performance tuning
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.process_idle_timeout = 10s
pm.max_requests = 500

; Memory limits
php_admin_value[memory_limit] = 256M
php_admin_value[upload_max_filesize] = 10M
php_admin_value[post_max_size] = 10M
```

```bash
# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
```

### 3. Setup Queue Worker
```bash
# Create systemd service
sudo nano /etc/systemd/system/pos-worker.service
```

```ini
[Unit]
Description=POS Queue Worker
After=redis.service

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/pos-app/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600

[Install]
WantedBy=multi-user.target
```

```bash
# Enable and start service
sudo systemctl enable pos-worker
sudo systemctl start pos-worker
```

### 4. Setup Cron Jobs
```bash
# Edit crontab for www-data user
sudo crontab -u www-data -e
```

```bash
# Laravel Scheduler
* * * * * cd /var/www/pos-app && php artisan schedule:run >> /dev/null 2>&1

# Log rotation (optional)
0 0 * * * cd /var/www/pos-app && php artisan log:clear
```

## 🔍 Monitoring & Logging

### 1. Setup Log Rotation
```bash
sudo nano /etc/logrotate.d/pos-app
```

```
/var/www/pos-app/storage/logs/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
}
```

### 2. Nginx Logs
```bash
# Create log directory
sudo mkdir -p /var/log/nginx/pos-app

# Update Nginx config to include:
access_log /var/log/nginx/pos-app/access.log;
error_log /var/log/nginx/pos-app/error.log;
```

### 3. Monitoring Commands
```bash
# Check application status
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
sudo systemctl status mysql
sudo systemctl status redis
sudo systemctl status pos-worker

# Monitor logs
sudo tail -f /var/www/pos-app/storage/logs/laravel.log
sudo tail -f /var/log/nginx/pos-app/error.log

# Check disk space
df -h

# Check memory usage
free -h

# Check active connections
sudo netstat -an | grep :80 | wc -l
```

## 🔧 Maintenance

### 1. Backup Strategy
```bash
# Create backup script
sudo nano /usr/local/bin/pos-backup.sh
```

```bash
#!/bin/bash

# Configuration
BACKUP_DIR="/var/backups/pos-app"
APP_DIR="/var/www/pos-app"
DB_NAME="pos_app"
DB_USER="pos_user"
DB_PASS="secure_password"
DATE=$(date +%Y%m%d_%H%M%S)

# Create backup directory
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/database_$DATE.sql

# Files backup
tar -czf $BACKUP_DIR/files_$DATE.tar.gz -C $APP_DIR storage .env

# Storage backup
cp -r $APP_DIR/storage/app/public $BACKUP_DIR/storage_$DATE

# Cleanup old backups (keep 30 days)
find $BACKUP_DIR -name "*.sql" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete
find $BACKUP_DIR -name "storage_*" -mtime +30 -exec rm -rf {} \;

echo "Backup completed: $DATE"
```

```bash
# Make executable
sudo chmod +x /usr/local/bin/pos-backup.sh

# Add to cron (daily backup at 2 AM)
sudo crontab -e
0 2 * * * /usr/local/bin/pos-backup.sh
```

### 2. Update Procedure
```bash
# 1. Backup current application
/usr/local/bin/pos-backup.sh

# 2. Put application in maintenance mode
sudo -u www-data php artisan down

# 3. Pull latest changes
cd /var/www/pos-app
sudo -u www-data git pull origin main

# 4. Install dependencies
sudo -u www-data composer install --optimize-autoloader --no-dev
sudo -u www-data npm install
sudo -u www-data npm run build

# 5. Run migrations
sudo -u www-data php artisan migrate --force

# 6. Clear caches
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan cache:clear
sudo -u www-data php artisan view:clear
sudo -u www-data php artisan route:clear

# 7. Rebuild caches
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

# 8. Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl restart pos-worker

# 9. Bring application back online
sudo -u www-data php artisan up
```

## 🚨 Security Checklist

### Server Security
- [ ] Disable root SSH login
- [ ] Use SSH keys instead of passwords
- [ ] Configure firewall (UFW/iptables)
- [ ] Install fail2ban
- [ ] Keep system updated
- [ ] Use strong passwords
- [ ] Enable automatic security updates

### Application Security
- [ ] Set `APP_DEBUG=false` in production
- [ ] Use strong `APP_KEY`
- [ ] Configure proper file permissions
- [ ] Set up HTTPS with valid SSL
- [ ] Hide sensitive files (.env, .git)
- [ ] Configure rate limiting
- [ ] Set up monitoring and alerts
- [ ] Regular backups
- [ ] Keep dependencies updated

### iPaymu Security
- [ ] Use production credentials
- [ ] Verify callback URL is HTTPS
- [ ] Test callback endpoint
- [ ] Monitor transaction logs
- [ ] Set up webhook authentication (if available)

## 🔧 Troubleshooting

### Common Issues

#### 1. 500 Internal Server Error
```bash
# Check PHP-FPM logs
sudo tail -f /var/log/php8.2-fpm.log

# Check Nginx error logs
sudo tail -f /var/log/nginx/error.log

# Check Laravel logs
sudo tail -f /var/www/pos-app/storage/logs/laravel.log

# Check permissions
sudo chown -R www-data:www-data /var/www/pos-app
sudo chmod -R 775 /var/www/pos-app/storage
```

#### 2. Database Connection Error
```bash
# Test database connection
mysql -u pos_user -p pos_app

# Check MySQL status
sudo systemctl status mysql

# Verify .env database settings
```

#### 3. iPaymu Callback Not Working
```bash
# Test callback endpoint
curl -X POST https://yourdomain.com/api/payment/callback \
  -H "Content-Type: application/json" \
  -d '{"test": "data"}'

# Check callback logs
sudo grep "iPaymu Callback" /var/www/pos-app/storage/logs/laravel.log

# Verify SSL certificate
curl -I https://yourdomain.com/api/payment/callback
```

#### 4. Storage Issues
```bash
# Create storage link
sudo -u www-data php artisan storage:link

# Check storage permissions
ls -la /var/www/pos-app/storage/

# Clear storage cache
sudo -u www-data php artisan cache:clear
```

## 📞 Support

**Production Support:**
- Monitor application logs regularly
- Set up alerting for critical errors
- Maintain regular backups
- Keep security patches updated
- Monitor performance metrics

**Emergency Contacts:**
- Server Administrator
- iPaymu Support
- Domain/SSL Provider
- Hosting Provider

---

**Deployment Guide Version: 1.0**  
*Last updated: August 2025*  
**Production Ready! 🚀**