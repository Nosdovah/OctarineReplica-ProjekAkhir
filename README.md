# Octarine — Premium Perfume E-Commerce Platform ( Replica )

Octarine is a premium local perfume webstore built with a focus on modern aesthetics, security, and high performance. It features a fully dynamic, PHP-driven backend, a session-based shopping cart system, and a robust administrative panel for seamless inventory management.

## 🚀 Features

### Customer Experience (Frontend)
- **Modern Minimalist UI:** Built with raw CSS featuring dynamic micro-animations, glassmorphism hints, and responsive layouts.
- **Dynamic Product Catalog:** Products are rendered server-side dynamically from the database.
- **Seamless Shopping Cart:** Add products, update quantities, and remove items dynamically via AJAX without page reloads.
- **User Authentication:** Secure user registration and login system handling encrypted passwords.
- **Checkout Processing:** Converts session-based carts into database-recorded orders.

### Administrative Control (Backend)
- **Super Admin Dashboard:** A fully isolated and protected CRUD (Create, Read, Update, Delete) interface to manage the product catalog.
- **Secure Image Uploads:** Implements strict validation (MIME-type checking using Object-Oriented `finfo`, size limits, and randomized filenames) to prevent malicious uploads.
- **Dynamic Statistics:** Real-time metrics counting total products, visible catalog items, out-of-stock items, and total variations directly on the dashboard.

## 🛠️ Technology Stack
- **Frontend:** Vanilla HTML5, CSS3, JavaScript
- **Typography:** Plus Jakarta Sans (Google Fonts)
- **Icons:** FontAwesome 6
- **Backend Architecture:** PHP 8.x
- **Database Engine:** MySQL via secure PHP Data Objects (PDO)

## 🔒 Security Measures
- **Principle of Least Privilege (PoLP):** Uses two separate database connection tiers:
  - `db_viewer.php`: Read-only connection for the public storefront to prevent accidental or malicious data modification.
  - `db_modifier.php`: Privileged connection restricted to the Admin Panel for CRUD operations.
- **SQL Injection Prevention:** 100% adherence to Parameterized Queries (Prepared Statements) for all database interactions.
- **Password Hashing:** Implements standard cryptographic hashing (`password_hash`) for user and admin accounts.
- **Authentication Barriers:** All administrative endpoints forcefully reroute unauthenticated users.

## 💻 Local Development Setup

Follow these steps to run the project locally using XAMPP:

### 1. Database Initialization
1. Start the **Apache** and **MySQL** modules inside your XAMPP Control Panel.
2. Open your terminal in the project root directory and execute the table setup script to build the architecture:
   ```bash
   php setup_tables.php
   ```
   *(This automatically generates the `users`, `admins`, `wms_products`, `orders`, and `order_items` tables and seeds the default Super Admin account).*

### 2. File Execution
1. Place the repository inside your XAMPP web directory (`c:\xampp\htdocs\OctarineProjectAkhir`).
2. Navigate to `http://localhost/OctarineProjectAkhir` in your browser to view the storefront.

## 🔑 Default Credentials

**Super Admin Login** (`/admin/login.php`)
- **Username:** `superadmin`
- **Password:** `admin123`

## 📂 Project Structure

```text
/OctarineProjectAkhir
│
├── /admin                  # Secure Administrative Control Panel
│   ├── dashboard.php       # Product Catalog Overview
│   ├── add.php             # Create Product form
│   ├── edit.php            # Update Product form
│   ├── delete.php          # Remove Product endpoint
│   ├── login.php           # Admin Authentication Gate
│   ├── logout.php          # Admin Session Terminator
│   └── auth.php            # Access Control Middleware
│
├── /config                 # Database Connection Architectures
│   ├── db_modifier.php     # High-privilege PDO Connection
│   └── db_viewer.php       # Low-privilege (Select-only) PDO Connection
│
├── /uploads                # User/Admin generated media storage
│
├── style.css               # Global Styling System
├── index.php               # Dynamic Storefront Landing Page
├── cart.php                # Shopping List & Modifications
├── cart_action.php         # AJAX Endpoint for Cart logic
├── checkout.php            # Order creation processor
├── register.php            # Customer Signup
├── login.php               # Customer Login
├── logout.php              # Customer Session Terminator
└── setup_tables.php        # Database Migration/Seed Script
```

## 📝 License
This project is proprietary and intended for educational/demonstration purposes as a final project.
