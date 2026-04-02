# JSLearn — JavaScript Learning Platform
## Full setup guide for InfinityFree (or any PHP + MySQL host)

---

## What's included

| File | Purpose |
|------|---------|
| `schema.sql` | Database tables + 60 seeded exercises |
| `config.php` | DB credentials, rank thresholds, helpers |
| `index.php` | Landing page |
| `register.php` | User registration |
| `login.php` | Login |
| `logout.php` | Session destroy |
| `exercises.php` | Exercise list with filters |
| `exercise.php` | Code editor + sandbox runner |
| `api_submit.php` | AJAX endpoint — saves progress, awards XP |
| `dashboard.php` | User progress, rank, stats |
| `leaderboard.php` | Top users by XP |
| `css/style.css` | All styles |
| `includes/header.php` | Shared nav header |
| `includes/footer.php` | Shared footer |

---

## Step 1 — Create your InfinityFree account

1. Go to **https://infinityfree.com** and sign up (free).
2. Create a **hosting account** — you'll get a subdomain like `yourname.infinityfreeapp.com`.
3. Note your **MySQL hostname, database name, username and password** from the control panel under *MySQL Databases*.

---

## Step 2 — Set up the database

1. In the InfinityFree control panel, open **phpMyAdmin**.
2. Select your database from the left sidebar.
3. Click the **SQL** tab.
4. Open `schema.sql` from this folder, copy the entire contents, paste it into phpMyAdmin, and click **Go**.
5. You should see the tables `users`, `exercises`, and `user_progress` created, plus 60 exercises inserted.

---

## Step 3 — Configure the application

Open `config.php` and fill in your real values:

```php
define('DB_HOST', 'sql200.infinityfree.com');   // from your control panel
define('DB_NAME', 'epiz_12345678_jslearn');      // your actual DB name
define('DB_USER', 'epiz_12345678');              // your actual DB user
define('DB_PASS', 'your_password_here');

define('SITE_URL', 'https://yourname.infinityfreeapp.com'); // no trailing slash
```

---

## Step 4 — Upload files

1. In the InfinityFree control panel, open **File Manager** (or use an FTP client like FileZilla).
2. Navigate to the `htdocs` folder.
3. Upload **all files and folders** from this project, preserving the directory structure:
   ```
   htdocs/
   ├── index.php
   ├── register.php
   ├── login.php
   ├── logout.php
   ├── exercises.php
   ├── exercise.php
   ├── api_submit.php
   ├── dashboard.php
   ├── leaderboard.php
   ├── config.php
   ├── schema.sql          ← optional, safe to delete after import
   ├── css/
   │   └── style.css
   └── includes/
       ├── header.php
       └── footer.php
   ```

---

## Step 5 — Test

Visit `https://yourname.infinityfreeapp.com` and:

1. Click **Register** — create your first account.
2. Go to **Exercises** — pick any Easy exercise.
3. Write your solution in the editor, click **Run** to test it, then **Submit** to record progress.
4. Visit **Progress** to see your XP, rank, and stats.
5. Visit **Leaderboard** to see the rankings.

---

## Rank system

| Rank | XP required |
|------|------------|
| Beginner | 0 |
| Apprentice | 50 |
| Developer | 150 |
| Engineer | 300 |
| Senior | 500 |
| Architect | 800 |
| Expert | 1,200 |
| Master | 1,800 |
| Legend | 2,500 |

XP is awarded the first time you submit each exercise. Re-submitting a solved exercise does not award XP again.

---

## Exercise XP values

| Difficulty | XP reward |
|-----------|-----------|
| Easy (1) | 5 XP |
| Medium (2) | 10 XP |
| Hard (3) | 20 XP |
| Expert (4) | 40 XP |

Solving all 60 exercises awards **730 XP total** — enough to reach Master rank.

---

## Security notes

- Passwords are hashed with **bcrypt** (`password_hash` / `password_verify`).
- All SQL queries use **prepared statements** (PDO).
- CSRF tokens protect the register and login forms.
- The code editor runs JavaScript in a **sandboxed iframe** — user code never touches the server.
- `config.php` contains credentials — do not expose it publicly (it's not web-accessible by default as PHP, but consider adding a `.htaccess` deny rule if needed).

---

## Troubleshooting

| Problem | Fix |
|---------|-----|
| Blank white page | Check PHP error logs in InfinityFree control panel |
| "Database error" | Verify DB_HOST, DB_NAME, DB_USER, DB_PASS in config.php |
| Styles not loading | Make sure SITE_URL is correct — no trailing slash |
| Can't log in | Confirm you ran schema.sql and the `users` table exists |
| Exercises not showing | Confirm the INSERT block in schema.sql ran successfully |

---

## Adding more exercises

Insert a new row into the `exercises` table via phpMyAdmin:

```sql
INSERT INTO exercises (slug, title, description, difficulty, topic, xp_reward, sort_order, hint, expected_output)
VALUES (
  'my-new-exercise',
  'My New Exercise',
  'Write a function <code>foo()</code> that returns 42.',
  2,                          -- 1=Easy 2=Medium 3=Hard 4=Expert
  'Functions & Closures',
  10,                         -- XP reward
  200,                        -- sort order (higher = later in list)
  'Think about what 6×7 equals.',
  '42'
);
```
