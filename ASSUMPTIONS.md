# Project Assumptions & Gap Resolutions

While the project requirements outlined the core features, several gaps were left open for interpretation. Here is how they were addressed:

### 1. Proof of Purchase Mechanism
*   **The Gap:** The prompt asks how a customer proves they bought a physical product.
*   **The Solution:** I assumed that printing a unique, 12-character alphanumeric code (e.g., FACT-A1B2-C3D4) inside the physical, sealed packaging is sufficient proof of purchase. A customer must physically buy and open the box to see and redeem the code.

### 2. Fraud & Brute-Force Prevention
*   **The Gap:** Attackers could write a script to randomly guess 12-character codes until they hit a valid one.
*   **The Solution:** I assumed a strict rate-limiting protocol was required. I implemented a `rate_limits` database table. If an IP address submits 5 invalid or already-redeemed codes, they are locked out of the redemption system entirely for 15 minutes.

### 3. Factory Owner Authentication
*   **The Gap:** The prompt requires the factory owner to view and manage rewards, but does not specify how the owner authenticates.
*   **The Solution:** Rather than building a complex, multi-tiered Role-Based Access Control (RBAC) system with database-driven admin accounts, I assumed a single factory owner operates this system. Therefore, a hardcoded, environmental password (`factory2026`) acts as a secure gate to `admin.php`.

### 4. Code Generation
*   **The Gap:** How do the codes get generated for the boxes?
*   **The Solution:** I assumed the factory owner needs to generate codes in "batches" (e.g., "November 2026 Production"). The admin dashboard allows generating up to 500 codes at a time, assigning them a batch name and a specific points value, and outputting them in a format ready to be sent to the packaging printer.