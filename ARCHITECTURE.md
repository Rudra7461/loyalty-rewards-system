# Architecture & Data Model

## Technology Stack Justification
1.  **PHP 8.x:** Chosen for the backend due to its native handling of secure sessions (`session_start()`) and password hashing (`password_hash()`, `password_verify()`), which are critical for the login and registration portals.
2.  **MySQL (PDO):** A relational database is required to strictly link `reward_codes` to `users` via Foreign Keys, ensuring we can track exactly who redeemed which physical box code.
3.  **HTML/CSS (Vanilla):** A unified `style.css` ensures the Admin Dashboard, Login, Registration, and Customer Dashboards maintain a cohesive, professional aesthetic without requiring heavy frontend frameworks.

## Security Choices
- **Transactional Integrity:** During code redemption, `PDO::beginTransaction()` prevents race conditions (two requests trying to redeem the same code at the exact millisecond).
- **Cryptographic Codes:** Codes are generated using `bin2hex(random_bytes(2))`. This uses a cryptographically secure pseudo-random number generator (CSPRNG), ensuring codes cannot be guessed sequentially.

## Data Model

The application relies on three normalized MySQL tables:

### Table: `users`
*   `id` (INT, Primary Key)
*   `username` (VARCHAR) - Unique identifier for login.
*   `password_hash` (VARCHAR) - Securely hashed via BCRYPT.
*   `total_points` (INT) - The customer's loyalty balance.

### Table: `reward_codes`
*   `id` (INT, Primary Key)
*   `code` (VARCHAR) - The unique 12-character physical box code.
*   `points_value` (INT) - How many points the code is worth.
*   `batch_name` (VARCHAR) - For factory tracking (e.g., "Batch A").
*   `is_redeemed` (TINYINT) - Boolean flag preventing reuse.
*   `redeemed_by` (INT, Foreign Key -> users.id) - Tracks the claimant.

### Table: `rate_limits`
*   `ip_address` (VARCHAR, Primary Key) - Tracks user location.
*   `failed_attempts` (INT) - Number of invalid code guesses.
*   `last_attempt` (TIMESTAMP) - Used to calculate the 15-minute lockout timer.