# Changelog

All notable changes to POS Universal will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.1.0] - 2025-09-01

### Added
- EAN-13 barcode generation for all products
- Dual-engine barcode scanner (ZXing-js + QuaggaJS)
- Camera-based barcode scanning with real-time detection
- Print-ready barcode labels (8 per A4 page)
- Auto-generated barcodes for new products
- Barcode search functionality in POS interface

### Changed
- Improved scanner success rate to ~85%
- Enhanced visual feedback during scanning
- Updated product forms with barcode fields

### Fixed
- Scanner compatibility issues with various devices
- Barcode label printing alignment

## [2.0.0] - 2025-08-15

### Added
- iPaymu payment gateway integration
- Multiple payment methods support:
  - QRIS (QR code payments)
  - Virtual Accounts (BCA, Mandiri, BNI, BRI, CIMB Niaga)
  - E-wallets (OVO, DANA, LinkAja, ShopeePay)
  - Credit Cards (Visa, Mastercard)
  - Convenience Store (Indomaret, Alfamart)
- Webhook callback handling for payment status
- Online store module with responsive design
- Shopping cart with session persistence
- Customer management module
- Draft transaction save/resume functionality
- Admin notifications system
- Low stock alerts

### Changed
- Upgraded to Laravel 12
- Improved dashboard analytics
- Enhanced receipt printing with thermal printer support
- Updated UI with Tailwind CSS 3.1

### Fixed
- Transaction calculation accuracy
- User role permission issues
- Mobile responsive layout bugs

## [1.0.0] - 2025-08-01

### Added
- Initial release
- Multi-user authentication with role-based access
- Three user roles: Admin, Supervisor, Kasir (Cashier)
- Product management (CRUD operations)
- Category management
- Basic POS transaction interface
- Transaction history
- Sales reports with date filtering
- PDF and Excel export
- Settings management
- Receipt printing
- User management
- Dashboard with key metrics
- SQLite/MySQL/PostgreSQL database support
- Docker containerization support

---

## Version History Summary

| Version | Release Date | Highlights |
|---------|-------------|------------|
| 2.1.0 | 2025-09-01 | Barcode system with scanner |
| 2.0.0 | 2025-08-15 | Payment gateway & online store |
| 1.0.0 | 2025-08-01 | Initial release |
