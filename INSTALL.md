# 🚀 Quick Installation Guide

Panduan instalasi cepat untuk aplikasi POS dengan iPaymu integration.

## 📋 Prerequisites

Pastikan sistem Anda memiliki:
- PHP >= 8.2
- Composer
- Node.js >= 16.x
- NPM atau Yarn

## ⚡ Quick Install

### 1. Download & Setup
```bash
# Clone repository
git https://github.com/krisnaxyoga/pos-universal.git
cd pos-app

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate
```

### 2. Database & Storage
```bash
# Setup database
php artisan migrate
php artisan db:seed  # Optional: Sample data

# Setup storage
php artisan storage:link
chmod -R 775 storage bootstrap/cache
```

### 3. Build & Run
```bash
# Build assets
npm run dev

# Start server
php artisan serve
```

### 4. Access Application
- **Online Store**: http://localhost:8000
- **Admin Login**: http://localhost:8000/login
- **Default Admin**: admin@example.com / password

## ⚙️ iPaymu Configuration

### 1. Get iPaymu Credentials
1. Register at [iPaymu](https://my.ipaymu.com)
2. Get your VA number and Secret Key
3. Set callback URL: `https://yourdomain.com/api/payment/callback`

### 2. Configure .env
```env
IPAYMU_VA=your_va_number
IPAYMU_SECRET_KEY=your_secret_key
IPAYMU_ENVIRONMENT=sandbox
```

### 3. Test Payment
1. Visit `/store`
2. Add products to cart
3. Checkout with test payment

## 🔧 Troubleshooting

### Database Issues
```bash
# Create SQLite database if not exists
touch database/database.sqlite
php artisan migrate
```

### Permission Issues
```bash
# Fix storage permissions
sudo chown -R www-data:www-data storage
sudo chmod -R 775 storage
```

### Asset Issues
```bash
# Rebuild assets
npm run build
php artisan cache:clear
```

## 📚 Next Steps

1. Read the full [README.md](README.md) for detailed documentation
2. Configure your store settings in `/settings`
3. Add your products and categories
4. Test the complete payment flow
5. Deploy to production

## 🆘 Need Help?

- Check [README.md](README.md) for detailed documentation
- Create an issue on GitHub
- Check troubleshooting section

---

**Happy coding! 🎉**