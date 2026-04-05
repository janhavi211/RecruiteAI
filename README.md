# RecruitAI – Resume Shortlisting System
## Complete Setup Guide (XAMPP + Windows)

---

## 📁 Folder Structure
```
resume_shortlisting/
├── index.php                  ← Landing page
├── candidate/
│   ├── register.php           ← Registration + OTP
│   ├── login.php              ← Login
│   ├── dashboard.php          ← Candidate dashboard
│   ├── jobs.php               ← Browse & apply to jobs
│   ├── applications.php       ← My applications
│   ├── saved_jobs.php         ← Bookmarked jobs
│   ├── resume.php             ← Upload resume
│   └── interviews.php         ← My interviews
├── hr/
│   ├── login.php              ← HR login
│   ├── dashboard.php          ← HR dashboard
│   ├── post_job.php           ← Post new job
│   ├── jobs.php               ← My job postings
│   ├── applicants.php         ← View applicants
│   ├── interviews.php         ← Manage interviews
│   └── shortlist.php          ← Auto-shortlist tool
├── admin/
│   └── dashboard.php          ← Admin panel
├── api/
│   ├── config.php             ← DB config + helpers
│   ├── auth.php               ← Register/Login/OTP/Logout
│   ├── jobs.php               ← Jobs CRUD
│   ├── resume.php             ← Resume upload + parser
│   ├── applications.php       ← Apply, list, status, shortlist
│   ├── interviews.php         ← Schedule + list interviews
│   ├── notifications.php      ← Notification bell
│   └── admin.php              ← Admin operations
├── assets/
│   ├── css/style.css          ← Global stylesheet
│   └── js/main.js             ← Shared JS utilities
├── python/
│   ├── resume_parser.py       ← AI skill extractor
│   └── requirements.txt       ← Python deps
├── uploads/
│   └── resumes/               ← Uploaded resumes (auto-created)
└── database/
    └── schema.sql             ← Full database schema + seed data
```

---

## ⚙️ STEP-BY-STEP SETUP

### Step 1 – Install XAMPP
1. Download XAMPP from https://www.apachefriends.org
2. Install and start **Apache** and **MySQL** from XAMPP Control Panel

### Step 2 – Copy Project Files
1. Copy the `resume_shortlisting/` folder to:
   ```
   C:\xampp\htdocs\resume_shortlisting\
   ```

### Step 3 – Create Database
1. Open browser → go to `http://localhost/phpmyadmin`
2. Click **"New"** → create database named `resume_shortlisting`
3. Select the database → click **"Import"** tab
4. Choose `database/schema.sql` → click **Go**
5. ✅ Tables and seed data will be imported

### Step 4 – Configure Database Connection
Open `api/config.php` and verify:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');          // Leave empty for default XAMPP
define('DB_NAME', 'resume_shortlisting');
define('BASE_URL', 'http://localhost/resume_shortlisting/');
```

### Step 5 – Install Python & Dependencies
1. Download Python from https://python.org (3.8+)
2. During install, check **"Add Python to PATH"**
3. Open Command Prompt and run:
   ```bash
   pip install pdfplumber PyPDF2 python-docx
   ```
4. Test Python works:
   ```bash
   python --version
   ```
5. If command is `python3` on your system, update `config.php`:
   ```php
   define('PYTHON_PATH', 'python3');
   ```

### Step 6 – Set File Permissions (Windows)
- The `uploads/resumes/` folder must be writable
- On Windows with XAMPP this works by default
- On Linux run: `chmod 755 uploads/resumes/`

### Step 7 – Enable PHP Extensions
Open `C:\xampp\php\php.ini` and make sure these are uncommented:
```ini
extension=pdo_mysql
extension=fileinfo
extension=openssl
```
Restart Apache after changes.

### Step 8 – (Optional) Configure Email
For real email notifications, update `api/config.php`:
```php
define('SMTP_USER', 'your_gmail@gmail.com');
define('SMTP_PASS', 'your_app_password');  // Gmail App Password
```
For local testing, the OTP will still be displayed in DB / you can check `otp` column.

**To use Gmail:** Go to Google Account → Security → App Passwords → Generate one.
Then install PHPMailer: `composer require phpmailer/phpmailer`

---

## 🚀 ACCESS THE APP

| Page | URL |
|------|-----|
| Home | http://localhost/resume_shortlisting/ |
| Candidate Register | http://localhost/resume_shortlisting/candidate/register.php |
| Candidate Login | http://localhost/resume_shortlisting/candidate/login.php |
| HR Login | http://localhost/resume_shortlisting/hr/login.php |
| Admin Login | Use candidate login with admin credentials |

### Demo Credentials
After running schema.sql, the following accounts are created:

| Role | Email | Note |
|------|-------|------|
| Admin | admin@rss.com | Password hash in DB is for "password" |
| HR | hr@techcorp.com | Password hash in DB is for "password" |

> **⚠️ Note:** The seed SQL uses Laravel's default hash `$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi` which is for the string **"password"**.
> After setup, login with `password` as the password for demo accounts.

---

## 🧪 TESTING THE SYSTEM

### Test as Candidate:
1. Register at `/candidate/register.php`
2. Check `users` table in phpMyAdmin for OTP (or check email if configured)
3. Verify OTP → Login
4. Upload a PDF resume
5. Browse jobs and apply

### Test as HR:
1. Login with `hr@techcorp.com` / `password`
2. Post a new job with specific skills
3. View applicants → see match percentages
4. Use Auto-Shortlist with 60% threshold
5. Schedule an interview for a shortlisted candidate

### Test Python Parser:
```bash
cd C:\xampp\htdocs\resume_shortlisting\python
python resume_parser.py "C:\path\to\test_resume.pdf"
```
Expected output:
```json
{
  "success": true,
  "skills": "[\"Python\",\"JavaScript\",\"React\"]",
  "skills_array": ["Python","JavaScript","React"],
  "text": "...",
  "total_skills": 3
}
```

---

## ❓ TROUBLESHOOTING

| Problem | Solution |
|---------|----------|
| Blank page | Enable PHP errors: add `ini_set('display_errors',1)` at top of config.php |
| DB connection failed | Check DB_USER and DB_PASS in config.php |
| Resume upload fails | Check `uploads/resumes/` folder exists and is writable |
| Python not found | Update `PYTHON_PATH` in config.php to full path e.g. `C:\Python311\python.exe` |
| OTP email not sending | Check spam folder; for local testing read OTP from `users` table in phpMyAdmin |
| 500 error | Check Apache error log: `C:\xampp\apache\logs\error.log` |

---

## 🔒 SECURITY NOTES (For Production)
- Change `DB_PASS` to a strong password
- Set `SESSION_LIFETIME` appropriately
- Enable HTTPS and set `'secure' => true` in session config
- Add CSRF protection to all forms
- Rate-limit login and OTP endpoints
- Store `SMTP_PASS` in environment variables, not in code

---

## 📚 TECHNOLOGIES USED
| Tech | Purpose |
|------|---------|
| PHP 8.x | Backend APIs, session management |
| MySQL 8 | Database storage |
| Python 3 | Resume text extraction & skill parsing |
| pdfplumber | PDF text extraction |
| python-docx | DOCX text extraction |
| HTML5/CSS3 | Frontend structure and styling |
| Vanilla JS + Fetch API | AJAX calls, real-time UI updates |
| XAMPP | Local development server |
| PHPMailer (optional) | Email notifications |

---

*Built as a Final Year Project – Resume Shortlisting System with AI-powered skill matching*
