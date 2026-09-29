# StudentHub Portal

**Subject:** Web Development Frameworks (ITUE203)  
**Semester:** 3rd Semester, A.Y. 2026-27 (ODD)  
**Institution:** Devang Patel Institute of Advance Technology and Research (DEPSTAR)  
**University:** Charotar University of Science and Technology (CHARUSAT)  

**Author / Student Information:**  
- **Name:** Krupanshu Patel  
- **Roll Number:** 25DCS088  
- **GitHub Username:** krupanshupatel2347ok-cmd  
- **GitHub Repository:** [https://github.com/krupanshupatel2347/StudentHub-Portal](https://github.com/krupanshupatel2347/StudentHub-Portal)  

---

## 📌 Project Overview
**StudentHub** is an all-in-one full-stack student academic portal developed across the semester for **ITUE203 (Web Development Frameworks)**. It encompasses all 15 laboratory practicals into a production-grade, coherent web application featuring modern semantic HTML5, responsive CSS Grid and Flexbox layouts, interactive client-side JavaScript, server-side PHP processing, MySQL database persistence, session security with Role-Based Access Control (RBAC), and RESTful AJAX/Fetch CRUD APIs.

---

## 🚀 Practical Index & Syllabus Coverage

| Practical | Title | Core Technology / Concepts |
| :---: | :--- | :--- |
| **Prac 1** | Project Initiation, Requirement Analysis, Sitemap, Wireframe & GitHub Setup | Requirements, Wireframes, Git/GitHub |
| **Prac 2** | Semantic HTML5 Pages with Accessibility-Ready Structure | Semantic HTML5, ARIA, Content Hierarchy |
| **Prac 3** | Responsive UI Design using CSS Grid and Flexbox | CSS3, Flexbox, Grid, Media Queries |
| **Prac 4** | JavaScript DOM Manipulation, Event Handling, and UI Interactivity | DOM, LocalStorage, Themes, Sliders, Modals |
| **Prac 5** | Registration Form with Frontend Validation & User-Friendly Errors | HTML Forms, Regex Validation |
| **Prac 6** | Rendering External JSON Data using Fetch API, Search & Filter | JSON, Fetch API, Filter, Sort, Pagination |
| **Prac 7** | PHP Form Processing with Server-Side Validation and CSV Storage | PHP POST, Input Sanitization, `users.csv` |
| **Prac 8** | MySQL Schema Design, ER Model, PDO Connectivity & Prepared Statements | MySQL Schema, PDO Database Handler |
| **Prac 9** | Secure User Registration with Database Insert, Duplicate Email & Hashing | MySQLi, `password_hash()`, Prepared Statements |
| **Prac 10** | Secure Login/Logout with Sessions, Role-Based Access, Timeout & Remember-Me | Sessions, RBAC, Timeout, Dual-Token Cookies |
| **Prac 11** | Student Management CRUD with Search and Filter | PHP-MySQL CRUD, Search, Filter UI |
| **Prac 12** | Event Management CRUD with Poster Upload and File Validation | File Uploads, MIME Checking, Image Replacement |
| **Prac 13** | PHP JSON API and AJAX/Fetch-Based CRUD without Page Reload | RESTful JSON API, Asynchronous Fetch UI |
| **Prac 14** | Admin Dashboard with Role-Based Access Control, Dynamic Menu & Audit Log | RBAC Dashboard, SQL Aggregation, Audit Logs |
| **Prac 15** | Final Integration, Testing, Documentation, GitHub Submission & Local Deployment | Full-Stack Integration, SQL Dump, Deployment |

---

## 🛠️ Technology Stack
- **Frontend:** HTML5, CSS3 (CSS Grid & Flexbox), Vanilla JavaScript (ES6+), Fetch API
- **Backend:** PHP 8.x (Session Handling, MySQLi & PDO Prepared Statements, MIME Validation)
- **Database:** MySQL 8.x / MariaDB
- **Web Server:** Apache (via XAMPP / LAMP)
- **Version Control:** Git & GitHub

---

## 📁 Repository Structure
```text
StudentHub-Portal/
├── Home.html                  # Main portal landing page (Practical 2, 4)
├── About.html                 # Mission, vision, university details (Practical 2)
├── Courses.html               # Academic departments and degree programs (Practical 2)
├── Admission.html             # Admission process and required documents (Practical 2)
├── StudyMaterial.html         # Downloadable course resources and notes (Practical 2)
├── Timetable.html             # Weekly lecture and laboratory schedule (Practical 2)
├── Signup.html                # Registration form with Regex validation (Practical 2, 5)
├── Login.html                 # Secure authentication page with role selector (Practical 2, 10)
├── FAQ.html                   # Frequently asked questions with dynamic search (Practical 2, 6)
├── Help.html                  # Student support desk and contacts (Practical 2)
├── event.html / events.html   # JSON-driven events directory (Practical 6)
├── style.css                  # Core design system & responsive styling (Practical 3)
├── script.js                  # Client-side DOM & Fetch API interactivity (Practical 4, 5, 6)
├── events.json                # Structured dataset for university events (Practical 6)
├── faqs.json                  # Structured dataset for student queries (Practical 6)
├── students.json              # Student profiles dataset (Practical 6)
├── signup.php                 # PHP form handler (CSV & MySQL registration) (Practical 7, 9)
├── db.php                     # Centralized MySQL connection (PDO & MySQLi) (Practical 8)
├── fetch.php                  # Database connection verification test (Practical 8)
├── process.php                # Authentication processor & session setup (Practical 10)
├── auth.php                   # Session timeout (900s) & RBAC guard (Practical 10)
├── remember.php               # Secure dual-token remember-me mechanism (Practical 10)
├── logout.php                 # Secure session destruction and cookie purge (Practical 10)
├── students.php               # Complete Student CRUD module (Practical 11)
├── events.php                 # Complete Event CRUD with poster upload (Practical 12)
├── api.php                    # RESTful JSON API endpoint (GET, POST, PUT, DELETE) (Practical 13)
├── index.html                 # AJAX Single-Page Application for events (Practical 13)
├── admin_dashboard.php        # Admin metrics, audit logging & dynamic menu (Practical 14)
├── studenthub_db.sql          # Complete MySQL database export (Practical 15)
├── README.md                  # Comprehensive project documentation
├── data/
│   └── users.csv              # CSV file-based user records (Practical 7)
├── database/
│   └── schema.sql             # SQL database creation script (Practical 8)
├── docs/
│   └── sitemap.txt            # Navigation flow & portal architecture (Practical 1)
└── uploads/                   # Uploaded event posters directory (Practical 12)
```

---

## 💻 Local Deployment Instructions (XAMPP)

1. **Install XAMPP:**
   Ensure XAMPP is installed on your machine with **Apache** and **MySQL**.

2. **Clone / Place the Project in `htdocs`:**
   Copy the `StudentHub-Portal` folder into your local XAMPP `htdocs` directory:
   ```bash
   # Windows
   C:\xampp\htdocs\StudentHub-Portal
   
   # macOS
   /Applications/XAMPP/htdocs/StudentHub-Portal
   ```

3. **Start Servers:**
   Launch the XAMPP Control Panel and start both **Apache** and **MySQL**.

4. **Import Database:**
   - Open your browser and navigate to `http://localhost/phpmyadmin/`.
   - Create a new database named `studenthub_db`.
   - Click on the **Import** tab, choose `studenthub_db.sql` from the repository root, and click **Import**.

5. **Verify Database Credentials:**
   Check `db.php` to verify host, username (`root`), and password (default is empty `""`).

6. **Access the Portal in Browser:**
   - **Student Portal:** `http://localhost/StudentHub-Portal/Home.html`
   - **Account Login:** `http://localhost/StudentHub-Portal/Login.html`
   - **Student Management:** `http://localhost/StudentHub-Portal/students.php`
   - **Event Management:** `http://localhost/StudentHub-Portal/events.php`
   - **AJAX CRUD Interface:** `http://localhost/StudentHub-Portal/index.html`
   - **Admin Dashboard:** `http://localhost/StudentHub-Portal/admin_dashboard.php`

---

## 🔐 Default Login Credentials

| Role | Email | Password | Destination Dashboard |
| :--- | :--- | :--- | :--- |
| **Admin** | `krupanshupatel2347@gmail.com` | `Admin@123` | `admin_dashboard.php` |
| **Student** | `rahul@example.com` | `Student@123` | `Home.html` |

---

## 🧪 Testing Checklist
- [x] Responsive layout tested on Desktop (1920x1080), Tablet (768px), and Mobile (375px).
- [x] Client-side regex validation prevents invalid name, mobile, and weak password submissions.
- [x] Server-side duplicate email verification in `signup.php` properly blocks duplicate entries.
- [x] Passwords safely hashed using Bcrypt (`password_hash`).
- [x] Session fixation prevented using `session_regenerate_id(true)`.
- [x] 15-minute inactivity session timeout verified.
- [x] Access-denied triggers when a student account attempts admin login.
- [x] Dual-token remember-me cookie functions across browser sessions.
- [x] Student CRUD (Create, Read, Update, Delete) operates smoothly with MySQL statements.
- [x] Event poster file upload enforces 2MB limit and MIME type restriction (JPEG/PNG/WEBP).
- [x] RESTful JSON API (`api.php`) responds with standard HTTP status codes (`200`, `201`, `400`, `404`, `405`, `500`).
- [x] AJAX Single-Page Application performs add, update, and delete without full page reload.
- [x] Admin dashboard records all administrative actions into `audit_logs` table.

---

## 📜 Conclusion
The StudentHub Portal successfully fulfills all requirements laid out in the **ITUE203 (Web Development Frameworks)** syllabus. It represents a clean, fully documented, accessible, and secure multi-tier web application adhering to academic guidelines and best development practices.
