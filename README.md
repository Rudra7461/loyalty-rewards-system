# Customer Loyalty Rewards System

A dual-portal web application designed for physical product manufacturers and factory owners. The system enables factory owners to generate secure, cryptographically random reward codes printed inside product boxes. Customers can create accounts, log in, and redeem codes for loyalty points, while built-in rate-limiting and atomic database transactions prevent brute-force attacks and code reuse fraud.

---

## Tech Stack & System Requirements

- **Language & Runtime:** PHP 8.x (Native PDO extension)
- **Database:** MySQL 8.0+ / MariaDB 10.4+
- **Web Server:** Apache 2.4+ (or Nginx)
- **Frontend:** HTML5, CSS3 (Vanilla / Custom Properties)

---

## Key Features

1. **Physical Proof of Purchase Redemption:** Cryptographic 12-character alphanumeric code format (`FACT-XXXX-XXXX`).
2. **Fraud & Double-Spend Prevention:** 
   - MySQL atomic transactions (`PDO::beginTransaction()`) ensure a code cannot be claimed twice concurrently.
   - IP-based rate-limiting locks accounts/IPs after 5 consecutive failed code redemption attempts for 15 minutes.
3. **Factory Owner Admin Dashboard:**
   - Batch code generation (up to 500 codes per batch).
   - Real-time statistics: total codes printed, total codes claimed, redemption percentage, and total registered users.
   - Text export ready for printing and packaging facilities.
4. **Customer Auth Flow:** Separate Login (`login.php`), Registration (`register.php`), and Dashboard (`index.php`) with session-based route protection.

---

## File Structure

```text
loyalty_rewards/
├── .env.example        # Environment variable template
├── AI_LOG.md           # AI prompt log and resolved mistakes
├── ARCHITECTURE.md     # Architectural diagrams and security choices
├── ASSUMPTIONS.md      # Domain and security assumptions
├── README.md           # Project documentation and setup guide
├── Tests.md            # Automated and manual test plan
├── admin.php           # Factory owner dashboard & batch code generator
├── database.sql        # Database schema import script
├── db.php              # Secure PDO database connection
├── index.php           # Customer points dashboard & code redemption portal
├── login.php           # Customer authentication login page
├── register.php        # Customer account creation page
└── style.css           # Unified application stylesheet
```

---

## Setup & Run Instructions

### Option A: Setup on Windows (XAMPP Environment)

#### Prerequisites
- Download and install [XAMPP for Windows](https://www.apachefriends.org/) with PHP 8.x.

#### Step 1: Place Project Files
1. Launch the XAMPP Control Panel.
2. Open your File Explorer and navigate to your XAMPP web root (default: `C:\xampp\htdocs`).
3. Create a folder named `loyalty_rewards` inside `htdocs`:
   ```cmd
   C:\xampp\htdocs\loyalty_rewards
   ```
4. Copy all project files into `C:\xampp\htdocs\loyalty_rewards\`.

#### Step 2: Start Apache and MySQL
1. Open XAMPP Control Panel.
2. Click **Start** next to **Apache**.
3. Click **Start** next to **MySQL**.

#### Step 3: Import Database Schema
1. Open your browser and navigate to `http://localhost/phpmyadmin`.
2. Click on the **SQL** tab in the top navigation bar.
3. Open `database.sql` from your project folder, copy its contents, paste them into the SQL input box, and click **Go**.
4. *(Alternative Method)* Create a new database named `loyalty_rewards`, click **Import**, choose `database.sql`, and click **Import**.

#### Step 4: Access the Application
- **Customer Login:** `http://localhost/loyalty_rewards/login.php`
- **Customer Registration:** `http://localhost/loyalty_rewards/register.php`
- **Admin Dashboard:** `http://localhost/loyalty_rewards/admin.php`
  - **Default Admin Password:** `factory2026`

---

### Option B: Setup on Ubuntu Linux (LAMP Stack)

#### Prerequisites
Open your terminal (`Ctrl+Alt+T`) and update your system packages:
```bash
sudo apt update && sudo apt upgrade -y
```

#### Step 1: Install Apache, MySQL, and PHP
Install Apache, MySQL Server, PHP 8.x, and the required PDO extension:
```bash
sudo apt install -y apache2 mysql-server php libapache2-mod-php php-mysql php-cli php-pdo
```

Verify that Apache and MySQL services are active and set to launch on boot:
```bash
sudo systemctl enable --now apache2
sudo systemctl enable --now mysql
```

#### Step 2: Database Setup & Import Schema
1. Connect to MySQL CLI as root:
   ```bash
   sudo mysql
   ```
2. Create the database and configure user permissions:
   ```sql
   CREATE DATABASE IF NOT EXISTS loyalty_rewards;
   ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '';
   FLUSH PRIVILEGES;
   EXIT;
   ```
3. Import the `database.sql` script into MySQL:
   ```bash
   sudo mysql -u root loyalty_rewards < /path/to/your/database.sql
   ```
   *(Replace `/path/to/your/database.sql` with the absolute path where your SQL file is located).*

#### Step 3: Deploy Project Files to Apache Web Root
1. Copy your project folder to `/var/www/html/`:
   ```bash
   sudo cp -r /path/to/loyalty_rewards /var/www/html/
   ```
2. Assign the correct permissions and web server ownership:
   ```bash
   sudo chown -R www-data:www-data /var/www/html/loyalty_rewards
   sudo chmod -R 755 /var/www/html/loyalty_rewards
   ```

#### Step 4: Access the Application
- **Customer Login:** `http://localhost/loyalty_rewards/login.php` (or `http://<your-ip-address>/loyalty_rewards/login.php`)
- **Customer Registration:** `http://localhost/loyalty_rewards/register.php`
- **Admin Dashboard:** `http://localhost/loyalty_rewards/admin.php`
  - **Default Admin Password:** `factory2026`

---

## Testing & Demonstration Workflow

1. Navigate to `admin.php` and log in with password `factory2026`.
2. Generate a new batch of box codes (e.g., Batch: `Nov-Batch`, Quantity: `5`, Points: `100`).
3. Copy one generated code (e.g., `FACT-3F8E-9C1D`).
4. Go to `register.php` and create a new customer account.
5. Log in at `login.php`, input the reward code on `index.php`, and verify the total points increment by 100.
6. Submit the same code again to test anti-reuse validation.