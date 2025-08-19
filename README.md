# 🏪 POS Application with Online Store & iPaymu Integration

Modern Point of Sale (POS) application built with Laravel 12, featuring integrated online store and iPaymu payment gateway.

## 📋 Table of Contents
- [Features](#features)
- [System Requirements](#system-requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [API Documentation](#api-documentation)
- [iPaymu Integration](#ipaymu-integration)
- [Screenshots](#screenshots)
- [Contributing](#contributing)
- [License](#license)

## ✨ Features

### 🏪 POS System
- **Multi-user Role Management** (Admin, Supervisor, Kasir)
- **Real-time Transaction Processing**
- **Inventory Management** with low stock alerts
- **Customer Management** with transaction history
- **Draft Transaction** save and resume functionality
- **Receipt Printing** with thermal printer support
- **Comprehensive Reporting** (Sales, Products, Revenue)
- **Dark/Light Mode** toggle

### 🛒 Online Store
- **Responsive E-commerce Frontend**
- **Product Catalog** with categories and search
- **Shopping Cart** with session persistence
- **Mobile-first Design** with hamburger menu
- **SEO Optimized** meta tags and structure

### 💳 iPaymu Payment Gateway
- **Multiple Payment Methods** (QRIS, Virtual Account, E-wallet, Credit Card)
- **Real-time Payment Processing**
- **Webhook Callback Handler** for payment status updates
- **Transaction Dashboard** with detailed analytics
- **Automatic Stock Management** after successful payment
- **Payment Timeline Tracking**

### 📊 Analytics & Reporting
- **Sales Reports** with date range filtering
- **Product Performance** analytics
- **Revenue Tracking** with payment method breakdown
- **Export Functionality** (PDF, Excel)
- **Real-time Dashboard** with key metrics

## 🔧 System Requirements

### Server Requirements
- **PHP >= 8.2**
- **Composer**
- **Node.js >= 16.x**
- **NPM or Yarn**

### PHP Extensions
- BCMath PHP Extension
- Ctype PHP Extension
- cURL PHP Extension
- DOM PHP Extension
- Fileinfo PHP Extension
- JSON PHP Extension
- Mbstring PHP Extension
- OpenSSL PHP Extension
- PCRE PHP Extension
- PDO PHP Extension
- Tokenizer PHP Extension
- XML PHP Extension
- GD PHP Extension (for image processing)

### Database
- **SQLite** (default, included)
- **MySQL 8.0+** (optional)
- **PostgreSQL 13+** (optional)

### Web Server
- **Apache 2.4+** with mod_rewrite
- **Nginx 1.18+**
- **Built-in PHP Server** (for development)

## 🚀 Installation

### 1. Clone Repository
```bash
git clone https://github.com/your-username/pos-app.git
cd pos-app
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Setup
```bash
# Run database migrations
php artisan migrate

# Seed database with sample data (optional)
php artisan db:seed
```

### 5. Storage Setup
```bash
# Create storage link
php artisan storage:link

# Set proper permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### 6. Build Assets
```bash
# Development build
npm run dev

# Production build
npm run build
```

### 7. Start Development Server
```bash
# Laravel development server
php artisan serve

# Access application at: http://localhost:8000
```

## ⚙️ Configuration

### Environment Variables (.env)
```env
# Application
APP_NAME="POS Application"
APP_ENV=local
APP_KEY=base64:generated_key_here
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database (SQLite default)
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite

# For MySQL
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=pos_app
# DB_USERNAME=root
# DB_PASSWORD=

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# iPaymu Configuration
IPAYMU_VA=your_va_number
IPAYMU_SECRET_KEY=your_secret_key
IPAYMU_ENVIRONMENT=sandbox # or production
IPAYMU_CALLBACK_URL="${APP_URL}/api/payment/callback"
IPAYMU_RETURN_URL="${APP_URL}/payment/success"
IPAYMU_CANCEL_URL="${APP_URL}/payment/cancel"
```

### Default User Accounts
After running `php artisan db:seed`:

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@example.com | password |
| Supervisor | supervisor@example.com | password |
| Kasir | kasir@example.com | password |

## 📖 Usage

### 1. Admin Dashboard
- Access: `/dashboard`
- Manage users, products, categories
- View comprehensive reports
- Configure application settings
- Monitor iPaymu transactions

### 2. POS Interface
- Access: `/pos`
- Process sales transactions
- Search products quickly
- Save/load draft transactions
- Print receipts

### 3. Online Store
- Access: `/store` or `/`
- Browse products by category
- Add items to cart
- Checkout with iPaymu payment

### 4. iPaymu Dashboard
- Access: `/ipaymu/transactions`
- Monitor payment status
- View transaction details
- Track payment analytics

## 🔌 API Documentation

### Callback API Endpoints

#### Payment Callback
```http
POST /api/payment/callback
Content-Type: application/json

{
  "trx_id": 175670,
  "sid": "91755016-ac8a-4929-8479-af386e32f447",
  "reference_id": "TRX20250818084136509",
  "status": "berhasil",
  "status_code": 1,
  "total": "33000",
  "amount": "33000",
  "fee": "231",
  "via": "qris",
  "channel": "mpm",
  "buyer_name": "John Doe",
  "buyer_email": "john@example.com",
  "buyer_phone": "08123456789",
  "paid_at": "2025-08-18 15:44:54"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Callback processed successfully",
  "transaction_id": 31,
  "transaction_number": "TRX20250818084136509",
  "status": "berhasil"
}
```

#### Get iPaymu Transactions
```http
GET /api/ipaymu/transactions
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 31,
      "transaction_number": "TRX20250818084136509",
      "ipaymu_transaction_id": "175674",
      "status": "completed",
      "total": "33000.00",
      "ipaymu_fee": "231.00",
      "ipaymu_payment_method": "qris",
      "customer": {
        "name": "John Doe",
        "email": "john@example.com"
      }
    }
  ],
  "total": 1
}
```

### Web Routes

| Method | URI | Description | Auth Required |
|--------|-----|-------------|---------------|
| GET | `/` | Online Store Homepage | No |
| GET | `/store` | Online Store | No |
| GET | `/store/product/{id}` | Product Detail | No |
| POST | `/store/add-to-cart/{id}` | Add to Cart | No |
| GET | `/store/checkout` | Checkout Page | No |
| POST | `/store/process-order` | Process Order | No |
| GET | `/dashboard` | Admin Dashboard | Yes |
| GET | `/pos` | POS Interface | Yes |
| GET | `/ipaymu/transactions` | iPaymu Dashboard | Yes |

## 💳 iPaymu Integration

### Setup Process

1. **Register iPaymu Account**
   - Visit [iPaymu Dashboard](https://my.ipaymu.com)
   - Complete business verification
   - Obtain VA number and Secret Key

2. **Configure Environment**
   ```env
   IPAYMU_VA=1179001899
   IPAYMU_SECRET_KEY=your_secret_key
   IPAYMU_ENVIRONMENT=sandbox
   ```

3. **Set Callback URL**
   - In iPaymu dashboard, set callback URL to:
   - `https://yourdomain.com/api/payment/callback`

### Payment Flow

1. **Customer places order** in online store
2. **System creates transaction** with pending status
3. **Redirect to iPaymu** payment page
4. **Customer completes payment** on iPaymu
5. **iPaymu sends callback** to your application
6. **System updates transaction** status and reduces stock
7. **Customer redirected** to success page

### Supported Payment Methods

- **QRIS** - Quick Response Code Indonesian Standard
- **Virtual Account** - BCA, Mandiri, BNI, BRI, CIMB Niaga
- **E-Wallet** - OVO, DANA, LinkAja, ShopeePay
- **Credit Card** - Visa, Mastercard
- **Convenience Store** - Indomaret, Alfamart

### Testing

Use iPaymu sandbox environment for testing:
- Test VA: `1179001899`
- Use provided test credentials
- All payments will be simulated

## 📱 Mobile Responsiveness

The application is fully responsive and optimized for:
- **Desktop** - Full feature set with sidebar navigation
- **Tablet** - Optimized layout with collapsible menus
- **Mobile** - Touch-friendly interface with hamburger menu
- **PWA Ready** - Can be installed as Progressive Web App

## 🔒 Security Features

- **CSRF Protection** on all forms
- **XSS Prevention** with Laravel's built-in protection
- **SQL Injection** prevention with Eloquent ORM
- **Role-based Access Control** with middleware
- **Password Hashing** with bcrypt
- **Input Validation** on all user inputs
- **File Upload Security** with type validation

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage
```

## 📊 Performance Optimization

- **Database Indexing** on frequently queried columns
- **Lazy Loading** for relationships
- **Query Optimization** with eager loading
- **Caching** for frequently accessed data
- **Asset Minification** in production
- **Image Optimization** with WebP support

## 🚀 Production Deployment

### 1. Server Setup
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2
sudo apt install php8.2-fpm php8.2-mysql php8.2-xml php8.2-gd php8.2-curl php8.2-mbstring

# Install Nginx
sudo apt install nginx

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs
```

### 2. Application Deployment
```bash
# Clone repository
git clone https://github.com/your-username/pos-app.git
cd pos-app

# Install dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run build

# Configure environment
cp .env.example .env
php artisan key:generate

# Set permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Run migrations
php artisan migrate --force

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3. Nginx Configuration
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/pos-app/public;
    
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.ht {
        deny all;
    }
}
```

### 4. SSL Certificate
```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Obtain SSL certificate
sudo certbot --nginx -d yourdomain.com
```

## 🔧 Troubleshooting

### Common Issues

#### 1. Database Connection Error
```bash
# Check database file exists
ls -la database/database.sqlite

# If not exists, create it
touch database/database.sqlite
php artisan migrate
```

#### 2. Storage Permission Error
```bash
# Fix storage permissions
sudo chown -R www-data:www-data storage
sudo chmod -R 775 storage
```

#### 3. iPaymu Callback Not Working
- Check callback URL in iPaymu dashboard
- Ensure CSRF exception is added for `/api/payment/callback`
- Verify webhook endpoint is accessible from internet

#### 4. Assets Not Loading
```bash
# Rebuild assets
npm run build

# Clear cache
php artisan cache:clear
php artisan view:clear
```

## 📞 Support

For support and questions:
- **GitHub Issues**: [Create an issue](https://github.com/your-username/pos-app/issues)
- **Documentation**: Check this README and inline code comments
- **Community**: Join our Discord/Slack community

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- **Laravel Framework** - The PHP framework for web artisans
- **iPaymu** - Indonesian payment gateway
- **Tailwind CSS** - Utility-first CSS framework
- **Alpine.js** - Lightweight JavaScript framework
- **Font Awesome** - Icon library

---

**Made with ❤️ for Indonesian businesses**

*Last updated: August 2025*
