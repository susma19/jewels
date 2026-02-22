# Admin Panel Setup Guide

## Features Implemented

### 1. Admin Panel with CRUD Operations
- **Admin Login**: `admin_login.php` - Login page for admin users
- **Admin Dashboard**: `admin_dashboard.php` - Overview with stats (products, orders, users)
- **Manage Products**: `admin_products.php` - List all products with edit/delete
- **Add/Edit Product**: `admin_product_form.php` - Form to create or update products
- **Manage Orders**: `admin_orders.php` - View all orders
- **Manage Users**: `admin_users.php` - View all users

### 2. Image Upload Feature
- Products can have images uploaded when creating/editing
- Images are stored in `uploads/` directory
- Supported formats: JPG, JPEG, PNG, GIF
- Max file size: 2MB
- Old images are deleted when updating/deleting products

### 3. Currency Changed to Rs (Nepalese Rupees)
- All prices display as "Rs" instead of "$"
- Updated in: shop.php, collection.php, index.php, cart, payment modal
- Format: `Rs 1,234.56` (with 2 decimal places)

### 4. Billing System in Payment
- **Billing Fields**:
  - Ordered By (required) - Name of person placing order
  - Sent By (required) - Name of sender/company
  - Shipping Address (required) - Full delivery address
- **Order Processing**: `process_order.php` saves orders with billing details
- Orders table includes: `ordered_by`, `sent_by`, `shipping_address`

## Database Schema

The `users` table has a `role` field:
- `role ENUM('user', 'admin') DEFAULT 'user'`

The `orders` table includes:
- `ordered_by VARCHAR(120)` - Who placed the order
- `sent_by VARCHAR(120)` - Who is sending/shipping
- `shipping_address TEXT` - Delivery address

## Creating an Admin User

### Option 1: Via SQL
```sql
UPDATE users SET role = 'admin' WHERE email = 'admin@example.com';
```

### Option 2: Via phpMyAdmin
1. Go to `users` table
2. Find your user
3. Change `role` field to `admin`
4. Save

### Option 3: Create new admin user
```sql
INSERT INTO users (name, email, password_hash, role) 
VALUES ('Admin User', 'admin@example.com', '$2y$10$...', 'admin');
```

## Accessing Admin Panel

1. **Via Admin Login**: Go to `admin_login.php` and login with admin credentials
2. **Via Regular Login**: If logged in as admin, click username dropdown → "Admin Panel"
3. **Direct URL**: `admin_dashboard.php` (requires admin session)

## Admin Features

### Products Management
- ✅ View all products
- ✅ Add new product (with image upload)
- ✅ Edit existing product (update image)
- ✅ Delete product (removes image file too)

### Orders Management
- ✅ View all orders
- ✅ See order details (items, billing info, status)

### Users Management
- ✅ View all registered users

## File Structure

```
/admin_login.php          - Admin login page
/admin_dashboard.php       - Admin dashboard
/admin_products.php       - Product list (CRUD)
/admin_product_form.php   - Add/Edit product form
/admin_orders.php         - Order list
/admin_users.php          - User list
/admin_logout.php         - Admin logout
/process_order.php        - Process payment and create order
/uploads/                 - Product images directory (auto-created)
```

## Notes

- Admin session uses `$_SESSION['admin_id']` and `$_SESSION['admin_name']`
- Regular user session uses `$_SESSION['user_id']` and `$_SESSION['user_name']`
- Admin can access both regular site and admin panel
- Image uploads are validated (type and size)
- Orders are saved with billing details when payment is confirmed
