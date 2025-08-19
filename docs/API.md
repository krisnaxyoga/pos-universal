# 📡 API Documentation

Complete API documentation for POS Application with iPaymu integration.

## 📋 Table of Contents

- [Authentication](#authentication)
- [iPaymu Callback API](#ipaymu-callback-api)
- [Public Store API](#public-store-api)
- [Admin Dashboard API](#admin-dashboard-api)
- [Error Handling](#error-handling)
- [Rate Limiting](#rate-limiting)

## 🔐 Authentication

### Web Authentication
Most dashboard endpoints require authentication via Laravel's built-in session authentication.

```bash
# Login required for dashboard routes
POST /login
```

### API Authentication
Public API endpoints (like callbacks) don't require authentication.

```bash
# No authentication required
POST /api/payment/callback
GET /api/ipaymu/transactions
GET /api/health
```

## 💳 iPaymu Callback API

### Payment Callback Webhook

Receives payment status updates from iPaymu payment gateway.

```http
POST /api/payment/callback
Content-Type: application/json
```

#### Request Body

```json
{
  "trx_id": 175670,
  "sid": "91755016-ac8a-4929-8479-af386e32f447",
  "reference_id": "TRX20250818084136509",
  "status": "berhasil",
  "status_code": 1,
  "sub_total": "33000",
  "total": "33000",
  "amount": "33000",
  "fee": "231",
  "paid_off": 32769,
  "created_at": "2025-08-18 15:44:43",
  "expired_at": "2025-08-19 15:44:41",
  "paid_at": "2025-08-18 15:44:54",
  "settlement_status": "settled",
  "transaction_status_code": 1,
  "is_escrow": false,
  "system_notes": "Sandbox notify",
  "via": "qris",
  "channel": "mpm",
  "payment_no": "",
  "buyer_name": "John Doe",
  "buyer_email": "john@example.com",
  "buyer_phone": "08123456789",
  "additional_info": [],
  "url": "http://localhost:8000/api/payment/callback"
}
```

#### Response

**Success (200)**
```json
{
  "success": true,
  "message": "Callback processed successfully",
  "transaction_id": 31,
  "transaction_number": "TRX20250818084136509",
  "status": "berhasil"
}
```

**Error (404)**
```json
{
  "success": false,
  "message": "Transaction not found"
}
```

**Error (500)**
```json
{
  "success": false,
  "message": "Internal server error",
  "error": "Error details"
}
```

#### Status Mapping

| iPaymu Status | System Status | Description |
|---------------|---------------|-------------|
| `berhasil` | `completed` | Payment successful |
| `success` | `completed` | Payment successful |
| `pending` | `processing` | Payment pending |
| `gagal` | `failed` | Payment failed |
| `failed` | `failed` | Payment failed |
| `expired` | `failed` | Payment expired |

#### Behavior

1. **Transaction Lookup**: Finds transaction by `reference_id`
2. **Status Update**: Updates transaction status and iPaymu fields
3. **Stock Management**: Reduces product stock on successful payment
4. **Logging**: Logs all callback activities for audit

### Get iPaymu Transactions

Retrieve iPaymu transaction data for monitoring and analytics.

```http
GET /api/ipaymu/transactions
```

#### Response

```json
{
  "success": true,
  "data": [
    {
      "id": 31,
      "transaction_number": "TRX20250818084136509",
      "ipaymu_transaction_id": "175674",
      "ipaymu_session_id": "91755016-ac8a-4929-8479-af386e32f451",
      "status": "completed",
      "ipaymu_status": "berhasil",
      "total": "33000.00",
      "paid": "33000.00",
      "ipaymu_fee": "231.00",
      "ipaymu_payment_method": "qris",
      "ipaymu_payment_channel": "mpm",
      "ipaymu_paid_at": "2025-08-18T15:51:00.000000Z",
      "created_at": "2025-08-18T08:41:36.000000Z",
      "customer": {
        "name": "John Doe",
        "email": "john@example.com",
        "phone": "08123456789"
      }
    }
  ],
  "total": 1
}
```

## 🛒 Public Store API

### Health Check

Check API health status.

```http
GET /api/health
```

#### Response

```json
{
  "status": "ok",
  "timestamp": "2025-08-18T08:51:07.306403Z",
  "service": "POS API"
}
```

### Store Routes (Web)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/` | Store homepage | No |
| GET | `/store` | Store homepage | No |
| GET | `/store/product/{id}` | Product detail | No |
| POST | `/store/add-to-cart/{id}` | Add to cart | No |
| POST | `/store/update-cart` | Update cart | No |
| POST | `/store/remove-from-cart` | Remove from cart | No |
| GET | `/store/cart` | View cart | No |
| GET | `/store/checkout` | Checkout page | No |
| POST | `/store/process-order` | Process order | No |
| GET | `/store/payment/{transaction}` | Payment redirect | No |
| GET | `/store/order-success/{transaction}` | Order success | No |

### Add to Cart

Add product to shopping cart.

```http
POST /store/add-to-cart/{product_id}
Content-Type: application/json
```

#### Request Body

```json
{
  "quantity": 2
}
```

#### Response

**Success (200)**
```json
{
  "success": true,
  "message": "Produk ditambahkan ke keranjang",
  "cart_count": 3
}
```

**Error (400)**
```json
{
  "success": false,
  "message": "Stok tidak mencukupi. Tersedia: 5"
}
```

### Process Order

Create new order from cart and redirect to payment.

```http
POST /store/process-order
Content-Type: application/x-www-form-urlencoded
```

#### Request Body

```
customer_name=John Doe
customer_email=john@example.com
customer_phone=08123456789
customer_address=Jl. Example No. 123
```

#### Response

**Success (302)**
```
Redirect to: /store/payment/{transaction_id}
```

**Error (422)**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "customer_email": ["The customer email field is required."]
  }
}
```

## 🏢 Admin Dashboard API

### Dashboard Routes

| Method | Endpoint | Description | Auth | Role |
|--------|----------|-------------|------|------|
| GET | `/dashboard` | Dashboard home | Yes | All |
| GET | `/pos` | POS interface | Yes | All |
| GET | `/transactions` | Transaction list | Yes | All |
| GET | `/ipaymu/transactions` | iPaymu transactions | Yes | All |
| GET | `/categories` | Category management | Yes | Admin/Supervisor |
| GET | `/products` | Product management | Yes | Admin/Supervisor |
| GET | `/customers` | Customer management | Yes | Admin/Supervisor |
| GET | `/reports` | Reports | Yes | Admin/Supervisor |
| GET | `/users` | User management | Yes | Admin |
| GET | `/settings` | Settings | Yes | Admin |

### Payment Gateway Routes

| Method | Endpoint | Description | Auth | Role |
|--------|----------|-------------|------|------|
| GET | `/payment/channels` | Get payment channels | Yes | All |
| POST | `/payment/create` | Create payment | Yes | All |
| POST | `/payment/status` | Check payment status | Yes | All |

### Create Payment

Create new iPaymu payment for transaction.

```http
POST /payment/create
Content-Type: application/json
Authorization: Bearer {token}
```

#### Request Body

```json
{
  "transaction_id": 31,
  "customer_name": "John Doe",
  "customer_phone": "08123456789",
  "customer_email": "john@example.com"
}
```

#### Response

**Success (200)**
```json
{
  "success": true,
  "message": "Payment created successfully",
  "data": {
    "transaction_id": 31,
    "transaction_number": "TRX20250818084136509",
    "session_id": "91755016-ac8a-4929-8479-af386e32f447",
    "payment_url": "https://my.ipaymu.com/payment/91755016-ac8a-4929-8479-af386e32f447",
    "amount": 33000,
    "fee": 231,
    "expired_date": "2025-08-19 15:44:41",
    "redirect_to": "https://my.ipaymu.com/payment/91755016-ac8a-4929-8479-af386e32f447"
  }
}
```

**Error (422)**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "customer_email": ["The customer email field is required."]
  }
}
```

**Error (500)**
```json
{
  "success": false,
  "message": "Failed to create payment",
  "error": "iPaymu service error details"
}
```

### Check Payment Status

Check current payment status from iPaymu.

```http
POST /payment/status
Content-Type: application/json
Authorization: Bearer {token}
```

#### Request Body

```json
{
  "transaction_id": 31
}
```

#### Response

```json
{
  "success": true,
  "data": {
    "transaction_id": 31,
    "transaction_number": "TRX20250818084136509",
    "local_status": "completed",
    "ipaymu_status": "berhasil",
    "amount": 33000,
    "paid_amount": 33000,
    "payment_method": "qris",
    "payment_channel": "mpm",
    "payment_code": "",
    "expired_date": "2025-08-19 15:44:41"
  }
}
```

## ❌ Error Handling

### HTTP Status Codes

| Code | Status | Description |
|------|--------|-------------|
| 200 | OK | Request successful |
| 201 | Created | Resource created |
| 400 | Bad Request | Invalid request data |
| 401 | Unauthorized | Authentication required |
| 403 | Forbidden | Access denied |
| 404 | Not Found | Resource not found |
| 422 | Unprocessable Entity | Validation error |
| 500 | Internal Server Error | Server error |

### Error Response Format

```json
{
  "success": false,
  "message": "Error description",
  "error": "Detailed error information",
  "errors": {
    "field_name": ["Validation error message"]
  }
}
```

### Common Errors

#### Validation Error (422)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "customer_email": ["The customer email field is required."],
    "quantity": ["The quantity must be at least 1."]
  }
}
```

#### Not Found Error (404)
```json
{
  "success": false,
  "message": "Transaction not found"
}
```

#### Server Error (500)
```json
{
  "success": false,
  "message": "Internal server error",
  "error": "Database connection failed"
}
```

## 🚦 Rate Limiting

### Default Limits

| Endpoint Type | Limit | Window |
|---------------|-------|--------|
| Web Routes | 60 requests | 1 minute |
| API Routes | 60 requests | 1 minute |
| Callback Routes | No limit | - |

### Rate Limit Headers

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1629123456
```

### Rate Limit Exceeded (429)

```json
{
  "message": "Too Many Attempts.",
  "retry_after": 60
}
```

## 📝 Request/Response Examples

### cURL Examples

#### Test Callback
```bash
curl -X POST http://localhost:8000/api/payment/callback \
  -H "Content-Type: application/json" \
  -d '{
    "trx_id": 175670,
    "reference_id": "TRX20250818084136509",
    "status": "berhasil",
    "amount": "33000",
    "via": "qris",
    "buyer_name": "John Doe",
    "buyer_email": "john@example.com"
  }'
```

#### Check Health
```bash
curl http://localhost:8000/api/health
```

#### Get Transactions
```bash
curl http://localhost:8000/api/ipaymu/transactions
```

### JavaScript Examples

#### Fetch iPaymu Transactions
```javascript
fetch('/api/ipaymu/transactions')
  .then(response => response.json())
  .then(data => {
    console.log('Transactions:', data.data);
  })
  .catch(error => {
    console.error('Error:', error);
  });
```

#### Add to Cart
```javascript
fetch(`/store/add-to-cart/${productId}`, {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
  },
  body: JSON.stringify({
    quantity: 2
  })
})
.then(response => response.json())
.then(data => {
  if (data.success) {
    console.log('Added to cart:', data.message);
    updateCartCount(data.cart_count);
  }
});
```

## 🔧 Development Tools

### API Testing

Use tools like:
- **Postman** - GUI API testing
- **Insomnia** - REST client
- **cURL** - Command line testing
- **HTTPie** - User-friendly HTTP client

### Webhook Testing

For testing iPaymu callbacks locally:
- **ngrok** - Expose local server to internet
- **localtunnel** - Alternative tunneling service
- **Postman** - Manual callback testing

### Example ngrok usage:
```bash
# Install ngrok
npm install -g ngrok

# Expose local server
ngrok http 8000

# Use the HTTPS URL for iPaymu callback
# https://abc123.ngrok.io/api/payment/callback
```

## 📊 Monitoring & Logging

### Log Files

| Log Type | Location | Description |
|----------|----------|-------------|
| Application | `storage/logs/laravel.log` | General application logs |
| Payment | Laravel Log | iPaymu callback logs |
| Error | Laravel Log | Error and exception logs |

### Log Levels

- **INFO**: Normal operations
- **WARNING**: Unexpected behavior
- **ERROR**: Error conditions
- **DEBUG**: Debug information

### Monitoring Endpoints

```bash
# Health check
GET /api/health

# Application status
GET /up  # Laravel health check
```

---

**API Documentation Version: 1.0**  
*Last updated: August 2025*