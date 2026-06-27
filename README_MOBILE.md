# KasirPro Mobile App Structure (Flutter)

## API Authentication (Sanctum)
1. POST `/api/v1/login` (email, password) -> `token`
2. Store token in `SharedPreferences` or `flutter_secure_storage`
3. Add `Authorization: Bearer <token>` to all subsequent requests

## Core API Endpoints
- `GET /api/v1/dashboard`
- `GET /api/v1/products`
- `GET /api/v1/pos/recent`
- `POST /api/v1/pos/sale`
- `POST /api/v1/pos/search-barcode`

## Flutter Implementation Checklist
- [ ] Setup `dio` package for API calls
- [ ] Setup `provider` or `riverpod` for state management
- [ ] Setup `sqflite` for offline data storage
- [ ] Create `AuthRepository` for login/token management
- [ ] Create `ProductRepository` for fetching and searching products
- [ ] Create `TransactionRepository` for POS sales sync
- [ ] Implement local synchronization queue (Sync pending sales when online)
- [ ] Add barcode scanner dependency (`mobile_scanner` or `flutter_barcode_scanner`)
