# Manual Test Plan and Results

Because this application handles points, balances, and brute-force defenses, a structured manual test plan was executed.

## Environment Setup
*   OS: Windows 11
*   Server: XAMPP (Apache + MySQL)
*   Browsers Tested: Chrome Desktop

## Test Cases Executed

### Test 1: Customer Authentication Flow
*   **Action:** Attempted to access `index.php` while logged out.
*   **Expected Result:** System redirects to `login.php`.
*   **Actual Result:** Successfully redirected. Registered a new account, was redirected back to login with a success message, and successfully logged in to reach the dashboard.
*   **Status:** PASSED ✅

### Test 2: Valid Code Generation & Redemption
*   **Action:** Logged into `admin.php`, generated 5 codes worth 100 points each. Logged into the customer portal and entered one of the generated codes.
*   **Expected Result:** Points balance increases by 100, success message displays, and the code is marked `is_redeemed = 1` in the database.
*   **Actual Result:** Points increased to 100. Admin dashboard correctly reflected 1 redeemed code.
*   **Status:** PASSED ✅

### Test 3: Fraud Prevention - Code Reuse
*   **Action:** Attempted to submit the exact same valid code a second time on the customer portal.
*   **Expected Result:** The system must reject the code and not increase the points balance.
*   **Actual Result:** Received the error "Invalid or already redeemed code." Points balance remained at 100.
*   **Status:** PASSED ✅

### Test 4: Fraud Prevention - Brute Force Defense
*   **Action:** Rapidly submitted 5 completely random, fake codes into the redemption form.
*   **Expected Result:** On the 5th failed attempt, the system must lock the user out for 15 minutes.
*   **Actual Result:** After 5 failures, the system displayed "Too many failed attempts. Locked out for 15 minutes." Attempting to enter a valid code during the lockout period was also successfully rejected.
*   **Status:** PASSED ✅