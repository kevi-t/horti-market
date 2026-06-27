# Horti-Market

Horti-Market is a PHP and MySQL ecommerce web application for horticultural products. It supports buyer and farmer accounts, product listings, cart and checkout flows, product reviews, profile management, blog posts, an admin dashboard, and M-Pesa payment pages.

## Features

- Buyer and farmer registration and login
- Product browsing, search, upload, review, and purchase pages
- Shopping cart and checkout pages
- M-Pesa payment flow under `mpesa/`
- User profile viewing and editing
- Blog publishing and feedback pages
- Admin dashboard for users, products, payments, farmers, and buyers

## Tech Stack

- PHP
- MySQL or MariaDB
- HTML, CSS, and JavaScript
- Bootstrap-based admin UI

## Project Structure

```text
.
|-- admin/          # Admin dashboard and admin database connection
|-- assets/         # Public CSS, JavaScript, and image assets
|-- authenticate/   # Login, signup, verification, and logout handlers
|-- blog/           # Blog write, submit, and view handlers
|-- database/       # Main database connection and SQL dump
|-- includes/       # Shared menu, footer, and flash message files
|-- mpesa/          # M-Pesa payment pages and callbacks
|-- products/       # Product listing, upload, review, and search pages
|-- profiles/       # Profile edit, password, and picture update pages
`-- Images/         # README screenshots
```

## Requirements

- PHP 7.1 or newer
- MySQL or MariaDB
- A local web server such as Apache, XAMPP, WAMP, MAMP, or PHP's built-in server
- phpMyAdmin or MySQL CLI for importing the database

## Local Setup

1. Clone the repository.

   ```bash
   git clone https://github.com/kevi-t/Horti-Market.git
   cd Horti-Market
   ```

2. Create a MySQL database named `horticulture-market`.

3. Import the database dump.

   Using MySQL CLI:

   ```bash
   mysql -u root -p horticulture-market < database/horticulture-market.sql
   ```

   Or use phpMyAdmin to import [database/horticulture-market.sql](database/horticulture-market.sql).

4. Update the database credentials if your local database user is different.

   Main app config:

   ```php
   // database/db.php
   $serverName = "localhost";
   $userName = "horti";
   $password = "horti1234";
   $dbName = "horticulture-market";
   ```

   Admin config:

   ```php
   // admin/database/db.php
   $serverName = "localhost";
   $userName = "horti";
   $password = "horti1234";
   $dbName = "horticulture-market";
   ```

5. Serve the project from your local web server document root.

   Example with PHP's built-in server:

   ```bash
   php -S localhost:8000
   ```

6. Open the app in your browser.

   ```text
   http://localhost:8000/
   ```

## Useful Pages

- Home: `index.php`
- Market: `market.php`
- Login: `loginpage.php`
- Sign up: `signuppage.php`
- Cart: `myCart.php`
- Checkout: `checkout.php`
- Admin login: `admin/login.php`
- M-Pesa payment page: `mpesa/index.php`

## Default Admin Account

The SQL dump includes this admin user:

```text
Email: kevin@gmail.com
Password: 123
```

Change the default admin password before using the app outside a local development environment.

## Screenshots

### Homepage

![Homepage](Images/Homepage.PNG)

### Registration

![Registration page](Images/registration%20page.PNG)

### Login

![Login page](Images/Login%20page.PNG)

### Admin Dashboard

![Admin page](Images/Admin%20page.PNG)

### Market

![Products display page](Images/Products%20diplay%20page.PNG)

### Profile

![Profile page](Images/Profile%20page.PNG)

### Product Purchase

![Product purchase page](Images/Product%20purchase%20page.PNG)

## Notes

- The project currently stores database credentials directly in PHP files. For production, move credentials into environment variables or an ignored local config file.
- The M-Pesa files use Safaricom sandbox endpoints. Configure sandbox credentials before testing payment requests.
- The database dump contains sample data and default credentials. Replace them before deployment.

## License

This project is licensed under the terms in [LICENSE.txt](LICENSE.txt).
