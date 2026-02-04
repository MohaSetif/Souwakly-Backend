# agents.md
## Backend Agents – Laravel (Filament + Spatie Permissions)

This document defines task-based agents responsible for building and maintaining
the backend of the Affiliate Marketing Platform.

---

## 1. Authentication & Authorization Agent

**Goal:** Manage users, roles, and permissions securely.

### Tasks
- Configure Laravel authentication
- Integrate Spatie Permissions
- Define roles:
  - Admin
  - Merchant
  - Affiliate
- Define permissions:
  - manage products
  - view orders
  - view analytics
  - manage sellers
- Assign roles on user registration
- Enforce role-based access control (RBAC)
- Protect API routes using middleware
- Integrate roles with Filament panels

---

## 2. User Management Agent

**Goal:** Handle merchants and affiliates lifecycle.

### Tasks
- Create User model extensions:
  - role
  - status (active/inactive)
- Implement CRUD for:
  - Merchants
  - Affiliates
- Manage account activation/deactivation
- Expose user lists to Filament dashboard
- Provide user statistics (sales count, product count)
- Prevent unauthorized role escalation

---

## 3. Product Management Agent

**Goal:** Manage merchant products.

### Tasks
- Design Product database schema
- Implement Product CRUD:
  - name
  - description
  - brand
  - price (informational only)
  - images
  - merchant_id
- Enforce ownership (merchant can manage only own products)
- Expose product management in Filament
- Enable product visibility for affiliates
- Implement product status (active/inactive)

---

## 4. Affiliate Linking & Referral Agent

**Goal:** Track affiliate promotions and referrals.

### Tasks
- Generate unique referral links per affiliate/product
- Store affiliate-product relationship
- Track referral clicks (optional)
- Attribute orders to affiliates
- Prevent duplicate or invalid referrals
- Expose referral data via API
- Provide affiliate performance metrics

---

## 5. Orders Management Agent

**Goal:** Record and manage sales/orders.

### Tasks
- Design Order schema:
  - product_id
  - affiliate_id
  - merchant_id
  - status
  - created_at
- Implement order creation endpoint
- Support manual order validation
- Expose orders to:
  - Admin (all)
  - Merchant (own)
  - Affiliate (own)
- Add filters (date, product, affiliate)
- Display orders in Filament dashboard

---

## 6. Sales & Analytics Agent

**Goal:** Provide insights into platform performance.

### Tasks
- Aggregate sales data
- Calculate:
  - sales per merchant
  - sales per affiliate
  - sales per product
- Implement time-based analytics (daily, weekly, monthly)
- Expose analytics to Filament charts
- Optimize queries for performance
- Cache heavy analytics queries

---

## 7. Admin Dashboard Agent (Filament)

**Goal:** Build a powerful admin interface.

### Tasks
- Configure Filament panels
- Build resources for:
  - Users
  - Products
  - Orders
  - Sales
- Implement filters & search
- Add role-based visibility
- Add stats widgets
- Ensure clean UX and permissions enforcement

---

## 8. WhatsApp Integration Agent

**Goal:** Enable off-platform communication.

### Tasks
- Generate WhatsApp deep links
- Pre-fill messages with product & affiliate info
- Ensure merchant–affiliate contact privacy
- Expose WhatsApp links via API
- Log interaction attempts (optional)

---

## 9. API Layer Agent

**Goal:** Serve mobile applications securely.

### Tasks
- Design RESTful API endpoints
- Implement authentication via tokens (the used auth in this prohect is Sanctum)
- Validate request payloads
- Normalize API responses
- Handle errors consistently
- Version API endpoints
- Document API routes

---

## 10. Security & Compliance Agent

**Goal:** Protect data and system integrity.

### Tasks
- Enforce input validation
- Prevent unauthorized data access
- Protect against IDOR vulnerabilities
- Apply rate limiting
- Secure file uploads
- Ensure secure password handling
- Audit permissions regularly

---

## 11. Database & Migration Agent

**Goal:** Maintain data consistency.

### Tasks
- Design normalized schemas
- Write migrations & seeders
- Seed roles & permissions
- Maintain indexes for performance
- Handle schema evolution safely
- Back up critical data

---

## 12. Testing & Quality Agent

**Goal:** Ensure backend reliability.

### Tasks
- Write unit tests for models
- Write feature tests for APIs
- Test role & permission enforcement
- Test referral attribution logic
- Validate Filament access rules
- Perform regression testing

---

## 13. Deployment & Maintenance Agent

**Goal:** Ensure stable production environment.

### Tasks
- Configure environment variables
- Prepare production database
- Set up queues & schedulers
- Monitor logs & errors
- Optimize performance
- Maintain backups

---

## Notes
- No payment processing is implemented
- No commission payout logic included
- System is tracking & reporting only
