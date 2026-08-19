# Hindi Karyashala Management System

A web-based Hindi Karyashala Management System developed to manage employees, user roles, and Karyashala-related employee records.

## Features Implemented

### 1. Database Setup

Database name:

`karyashalaNG`

The project currently uses the following main tables:

- `employees`
- `roles`
- `karyashalamgt`

---

## 2. Employee Management

The `employees` table stores employee details.

### Attributes

| Column | Description |
|---|---|
| `ICNO` | Unique employee IC number |
| `ENAME` | Employee name |
| `EDESIG` | Employee designation |
| `EGROUP` | Employee group |
| `PASSWORD` | Employee password |
| `created_at` | Employee record creation time |

The IC number is automatically generated using `AUTO_INCREMENT` and starts from:

```text
1001
```

### Supported Designations

The admin can select the following designations while adding or editing an employee:

- Scientist B
- Scientist C
- Scientist D
- Scientist E
- Scientist F
- Scientist G
- Scientist H
- TO A
- TO B
- TO C
- TO D
- HRD
- Director
- Employee

---

## 3. Role Management

The `roles` table is used to manage user access.

### Attributes

| Column | Description |
|---|---|
| `ICNO` | Employee IC number |
| `ENAME` | Employee name |
| `ROLE` | Assigned role |

Currently implemented roles:

- `admin`
- `karyashala_admin`
- `employee`

### User Access

| Role | Access |
|---|---|
| Admin | Full employee management |
| Karyashala Admin | Karyashala management dashboard |
| Employee | Employee-level access for future implementation |

---

## 4. Login System

The login page validates users using:

- ICNO
- Password

### Login Validation

The system displays different error messages:

- `Incorrect ICNO` if the ICNO does not exist.
- `Invalid password` if the password is incorrect.

The ICNO field only accepts numeric values.

### Role-Based Redirection

After successful login, users are redirected according to their role:

```text
Login
  |
  v
Validate ICNO
  |
  +---- Incorrect --> Display "Incorrect ICNO"
  |
  v
Validate Password
  |
  +---- Incorrect --> Display "Invalid password"
  |
  v
Check Role
  |
  +---- admin ------------> Admin Dashboard
  |
  +---- karyashala_admin -> Karyashala Dashboard
```

---

## 5. Admin Dashboard

The Admin Dashboard allows the administrator to manage employee records.

### Implemented Features

- DRDO logo in the dashboard
- Welcome message for the logged-in admin
- Display all employee records from the database
- Add new employees
- View employee details
- Edit employee details
- Delete employees
- Role-based dashboard access
- Session validation

### Add Employee

The admin can add a new employee with:

- Employee Name
- Designation
- Group
- Password

A new ICNO is automatically generated.

New employees are assigned the role:

```text
employee
```

---

## 6. View Employee Details

When the admin clicks the `View` button, employee details are displayed in a modal.

The displayed information is read-only:

- ICNO
- Employee Name
- Designation
- Group

---

## 7. Edit Employee Details

When the admin clicks the `Edit` button, an editable modal form appears.

The admin can update:

- Employee Name
- Designation
- Group

The ICNO remains read-only.

Both frontend and backend validation are implemented to ensure required information is provided.

After submission, changes are saved to the database.

---

## 8. Delete Employee

The delete process includes multiple confirmation steps.

1. The admin receives a confirmation alert.
2. A prompt asks the admin to type:

```text
DELETE
```

3. Only after successful confirmation is the deletion submitted.

The system also prevents the currently logged-in admin from deleting their own account.

Before deleting an employee, the associated role record is deleted first to avoid foreign key constraint issues.

```text
Delete Role Record
        |
        v
Delete Employee Record
```

---

## 9. Karyashala Management Table

The project includes a `karyashalamgt` table to store employee Karyashala attendance information.

### Required Attributes

| Column | Description |
|---|---|
| `ICNO` | Employee IC number |
| `ENAME` | Employee name |
| `karyashala_date` | Date of Karyashala attendance |
| `karyashala_remark` | Remarks related to attendance |

An employee can have multiple Karyashala attendance records.

Example:

| ICNO | ENAME | Date | Remark |
|---|---|---|---|
| 1003 | Employee 1 | 2025-01-15 | Attended |
| 1003 | Employee 1 | 2025-08-20 | Completed workshop |

---

## 10. Two-Year Karyashala Validation

The Karyashala management functionality is planned to allow the user to select a two-year reporting period.

For example:

```text
01-01-2023 to 31-12-2024
```

The Karyashala date entered for an employee must fall within the selected period.

The date will be validated in both:

- Frontend
- PHP backend

Example:

```text
Selected Period:
01-01-2023 to 31-12-2024

Entered Date:
15-08-2024

Result:
Valid
```

If the entered date is outside the selected two-year period, the system will reject it.

---

## 11. Database Connection

A central database connection file is used so database connection code does not need to be repeated in every PHP file.

### File

```text
config/db.php
```

Other PHP files include the connection using:

```php
require_once "../config/db.php";
```

For files located in the project root:

```php
require_once "config/db.php";
```

---

## 12. Project Structure

```text
Hindi-Karyashala-Management/
│
├── admin/
│   └── dashboard.php
│
├── config/
│   └── db.php
│
├── css/
│   └── admin.css
│
├── images/
│   └── drdo_logo.png
│
├── login.php
│
├── karyashala_dashboard.php
│
├── logout.php
│
└── README.md
```

---

## Technologies Used

- PHP
- MySQL
- HTML
- CSS
- JavaScript
- MySQLi

---

## Current Project Status

### Completed

- [x] Database creation
- [x] Employee table
- [x] Role table
- [x] ICNO generation starting from 1001
- [x] Initial Admin and Karyashala Admin accounts
- [x] Central database connection file
- [x] Login page
- [x] ICNO validation
- [x] Password validation
- [x] Role-based login redirection
- [x] Admin dashboard
- [x] Employee list
- [x] Add employee
- [x] View employee details
- [x] Edit employee details using modal
- [x] Delete employee with confirmation
- [x] DRDO logo integration
- [x] External CSS structure
- [x] Karyashala attendance table design

### In Progress / Planned

- [ ] Karyashala Admin dashboard
- [ ] Karyashala attendance marking
- [ ] Two-year period selection
- [ ] Karyashala date validation
- [ ] Karyashala remarks management
- [ ] Employee dashboard
- [ ] Two-year attendance report
- [ ] Identify employees who have not attended a Karyashala
- [ ] Logout functionality improvements
- [ ] Password hashing and improved security

---

## Future Improvements

- Use `password_hash()` and `password_verify()` for secure password management.
- Add CSRF protection to forms.
- Add search and filtering for employees.
- Add pagination for large employee records.
- Generate Karyashala attendance reports.
- Allow filtering reports by a selected two-year period.
- Automatically identify employees who have not attended any Karyashala within the required period.
- Improve role-based access control.

---

## Author

**Neha Gusain**

B.Tech Computer Science Engineering
