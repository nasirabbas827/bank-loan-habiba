# bank-loan-newhabiba  

A web‑based loan management system built with PHP that allows administrators to manage loan officers, loan types, and applications, while loan officers can process disbursements and communicate with applicants.

---

## Overview  

The **bank-loan-newhabiba** application provides a simple yet functional interface for a bank’s loan department:

* **Admin portal** – Create and edit loan officers, define loan types, view applications, and assign officers.  
* **Officer portal** – Review assigned applications, send messages to applicants, and process loan disbursements.  
* **Secure authentication** – Separate login pages for admins and officers with session handling.  

All pages are styled with a minimal CSS stylesheet and organized under clear MVC‑like directories.

---

## Features  

| Area | Capability |
|------|------------|
| **Authentication** | `admin_login.php`, `login.php` (officer) with logout handling. |
| **Admin Dashboard** | `admin_home.php` – overview, navigation (`admin_navbar.php`). |
| **Loan Officer Management** | Add, edit, view, and assign officers (`add_loan_officer.php`, `edit_loan_officer.php`, `view_loan_officers.php`, `assign_loan_officer.php`). |
| **Loan Type Management** | Add, edit, view loan products (`add_loantype.php`, `edit_loan_type.php`, `view_loan_types.php`). |
| **Application Review** | View full application details (`view_application_details.php`). |
| **Officer Dashboard** | `officer_home.php` with navigation (`officer/navbar.php`). |
| **Disbursement & Messaging** | Process disbursements (`loan_disbursement.php`) and send/receive messages (`loan_messages.php`). |
| **Database** | MySQL schema in `Database/bankloan_db.sql`. |
| **Styling** | Central CSS (`css/style.css`). |
| **Configuration** | Centralized DB connection (`config.php` & `admin/config.php`, `officer/config.php`). |

---

## Tech Stack  

| Component | Technology |
|-----------|------------|
| **Backend** | PHP 7.4+ |
| **Database** | MySQL |
| **Frontend** | HTML5, CSS3 (custom stylesheet) |
| **Server** | Apache / Nginx (any LAMP/LEMP stack) |
| **Version Control** | Git (GitHub) |

---

## Installation  

1. **Clone the repository**  

   ```bash
   git clone https://github.com/yourusername/bank-loan-newhabiba.git
   cd bank-loan-newhabiba
   ```

2. **Create the database**  

   ```sql
   -- In MySQL client or phpMyAdmin
   SOURCE Database/bankloan_db.sql;
   ```

3. **Configure database credentials**  

   Edit the following files and replace placeholder values with your own:

   * `config.php`
   * `admin/config.php`
   * `officer/config.php`

   ```php
   // Example snippet
   $db_host = 'localhost';
   $db_name = 'bankloan_db';
   $db_user = 'YOUR_DB_USERNAME';
   $db_pass = 'YOUR_DB_PASSWORD';
   ```

4. **Set up a virtual host (optional)**  

   ```apacheconf
   # Apache example
   DocumentRoot "/path/to/bank-loan-newhabiba"
   <Directory "/path/to/bank-loan-newhabiba">
       AllowOverride All
       Require all granted
   </Directory>
   ```

5. **Ensure the `sessions` directory is writable** (if you use a custom session store).  

6. **Open the application**  

   * Admin login: `http://your-domain