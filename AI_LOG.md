# AI Collaboration Log

This log documents key interactions with the LLM while building the application, including architectural prompts and critical security corrections.

1.  **Prompt:** "Design the SQL schema for a physical box loyalty system with an owner dashboard."
    *   *Result:* The AI generated the core relational structure, including the `users` and `reward_codes` tables.
2.  **Prompt (Security Correction 1):** "Write the redemption logic in PHP."
    *   *Where the AI was wrong:* The AI wrote a standard `UPDATE` query to mark a code as redeemed.
    *   *How I noticed:* I recognized that this allows for a "double-spend" race condition. If a user runs a script to submit the exact same code 10 times in one millisecond, multiple PHP threads could read `is_redeemed = 0` simultaneously, awarding the points 10 times before the database updates.
    *   *How I fixed it:* I instructed the AI to wrap the entire redemption process in a MySQL `PDO::beginTransaction()` and `$pdo->commit()` block, ensuring atomic execution.
3.  **Prompt (Logic Correction 2):** "How do we prevent hackers from running a script to guess random codes?"
    *   *Where the AI was wrong:* The AI suggested making the codes 50 characters long (e.g., SHA-256 hashes) to make them mathematically impossible to guess.
    *   *How I noticed:* Customers will not type 50 random characters from a physical box into a website without extreme frustration.
    *   *How I fixed it:* I directed the AI to keep the codes short and user-friendly (12 characters), but add a `rate_limits` table to the database. This blocks IPs after 5 failed guesses, neutralizing brute-force scripts while keeping the UX clean.
4.  **Prompt:** "Separate the customer portal into index.php, login.php, and register.php."
    *   *Result:* The AI successfully decoupled the logic, creating a circular routing loop where unauthenticated users are redirected from index -> login -> register -> login -> index.