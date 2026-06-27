# KasirPro API Documentation

## Authentication

All API endpoints (except `/api/v1/login`) require authentication using Laravel Sanctum.

### Login
```
POST /api/v1/login
Content-Type: application/json

{
    "email": "admin@kasirpro.com",
    "password": "password"
}
```

Response:
```json
{
    "token": "3|AbCdEfGhIjKlMnOpQrStUvWxYz"
}
```

### Logout
```
POST /api/v1/logout
Authorization: Bearer {token}
```

### Protected Endpoints
Add `Authorization: Bearer {token}` header to all protected requests.

---

## Endpoints

### Products
```
GET /api/v1/products
GET /api/v1/products/{id}
```

### POS
```
POST /api/v1/pos/sale
{
    "customer_id": 1,
    "payments": [...],
    "products": [...]
}

POST /api/v1/pos/search-barcode
{
    "barcode": "1234567890123"
}

GET /api/v1/pos/recent
```

### Payment
```
POST /api/v1/payment/create
{
    "transaction_id": 1
}

POST /api/v1/payment/status
{
    "order_id": "TXN-123"
}

GET /api/v1/dashboard
```

---

## Midtrans Payment Gateway

### Create Transaction
```
POST /api/v1/payment/create
Authorization: Bearer {token}
Content-Type: application/json

{
    "transaction_id": 1
}
```

Response:
```json
{
    "success": true,
    "token": "payment-token-123",
    "redirect_url": "https://app.midtrans.com/snap/v2/vtweb/...",
    "client_key": "SB-Mid-client-...",
    "is_production": false
}
```

### Midtrans Webhook
```
POST /api/v1/payment/webhook
Content-Type: application/json

{
    "order_id": "TXN-123",
    "transaction_status": "settlement",
    "signature_key": "..."
}
```

---

## Error Responses

### 401 Unauthorized
```json
{
    "error": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
    "error": "Forbidden."
}
```

### 404 Not Found
```json
{
    "error": "Resource not found."
}
```

### 422 Validation Error
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "field": ["Error message"]
    }
}
```

---

## Models

### Transaction
```json
{
    "id": 1,
    "invoice_no": "INV-2026-0001",
    "transaction_date": "2026-06-27 10:30:00",
    "total_before_tax": 100000,
    "tax_amount": 11000,
    "discount_amount": 0,
    "final_total": 111000,
    "payment_status": "paid",
    "status": "final"
}
```

### Payment
```json
{
    "id": 1,
    "transaction_id": 1,
    "amount": 111000,
    "method": "cash",
    "paid_on": "2026-06-27 10:30:00"
}
```

---

*Last updated: Juni 2026*
