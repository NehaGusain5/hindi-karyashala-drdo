CREATE DATABASE IF NOT EXISTS karyashala;

USE karyashala;

CREATE TABLE employees (

    ic_number INT(4) PRIMARY KEY,

    name VARCHAR(100) NOT NULL,

    phone VARCHAR(15) NOT NULL,

    designation VARCHAR(100) NOT NULL,

    email VARCHAR(100) NOT NULL UNIQUE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

);



CREATE TABLE roles (

    ic_number INT(4) PRIMARY KEY,

    password VARCHAR(255) NOT NULL,

    role ENUM(
        'Admin',
        'Karyashala Admin',
        'Employee'
    ) NOT NULL,

    FOREIGN KEY (ic_number)
        REFERENCES employees(ic_number)

        ON DELETE CASCADE

        ON UPDATE CASCADE

);
ALTER TABLE roles
MODIFY role ENUM('Admin','Karyashala Admin','Employee') NOT NULL;



CREATE TABLE workshops (

    workshop_id INT AUTO_INCREMENT PRIMARY KEY,

    employee_ic INT(4) NOT NULL,

    workshop_name VARCHAR(150) NOT NULL,

    workshop_year YEAR NOT NULL,

    attendance_date DATE,

    attendance_status ENUM(
        'Pending',
        'Attended',
        'Absent'
    ) DEFAULT 'Pending',

    remarks VARCHAR(255),

    updated_by INT(4),

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (employee_ic)
        REFERENCES employees(ic_number)

        ON DELETE CASCADE

        ON UPDATE CASCADE,

    FOREIGN KEY (updated_by)
        REFERENCES employees(ic_number)

        ON DELETE SET NULL

        ON UPDATE CASCADE

);



CREATE TABLE reports (

    report_id INT AUTO_INCREMENT PRIMARY KEY,

    report_name VARCHAR(150) NOT NULL,

    employee_ic INT(4) NOT NULL,

    workshop_year YEAR NOT NULL,

    generated_by INT(4) NOT NULL,

    generated_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    status ENUM(
        'Pending',
        'Verified'
    ) DEFAULT 'Pending',

    verified_by INT(4),

    verified_date DATETIME,

    FOREIGN KEY (employee_ic)
        REFERENCES employees(ic_number)

        ON DELETE CASCADE

        ON UPDATE CASCADE,

    FOREIGN KEY (generated_by)
        REFERENCES employees(ic_number)

        ON DELETE CASCADE

        ON UPDATE CASCADE,

    FOREIGN KEY (verified_by)
        REFERENCES employees(ic_number)

        ON DELETE SET NULL

        ON UPDATE CASCADE

);


CREATE TABLE activity_log (

    log_id INT AUTO_INCREMENT PRIMARY KEY,

    activity VARCHAR(255) NOT NULL,

    activity_by INT(4) NOT NULL,

    activity_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (activity_by)
        REFERENCES employees(ic_number)

        ON DELETE CASCADE

        ON UPDATE CASCADE

);


INSERT INTO employees
(
    ic_number,
    name,
    phone,
    designation,
    email
)
VALUES

(1001,
 'Admin',
 '9876543210',
 'Administrator',
 'admin@karyashala.com'),

(1002,
 'Hindi Cell',
 '9876543211',
 'Karyashala Admin',
 'hindicell@karyashala.com'),

(1003,
 'Amit Kumar',
 '9876500001',
 'Assistant',
 'amit@gmail.com'),

(1004,
 'Neha Sharma',
 '9876500002',
 'Officer',
 'neha@gmail.com'),

(1005,
 'Rahul Singh',
 '9876500003',
 'Clerk',
 'rahul@gmail.com'),

(1006,
 'Pooja Gupta',
 '9876500004',
 'Assistant',
 'pooja@gmail.com'),

(1007,
 'Rohan Mehta',
 '9876500005',
 'Manager',
 'rohan@gmail.com'),

(1008,
 'Anjali Verma',
 '9876500006',
 'Officer',
 'anjali@gmail.com'),

(1009,
 'Suresh Kumar',
 '9876500007',
 'Clerk',
 'suresh@gmail.com'),

(1010,
 'Deepak Singh',
 '9876500008',
 'Assistant',
 'deepak@gmail.com');



INSERT INTO roles
(
    ic_number,
    password,
    role
)
VALUES

(1001,
 'admin123',
 'Admin'),

(1002,
 'admin123',
 'Karyashala Admin'),

(1003,
 'emp123',
 'Employee'),

(1004,
 'emp123',
 'Employee'),

(1005,
 'emp123',
 'Employee'),

(1006,
 'emp123',
 'Employee'),

(1007,
 'emp123',
 'Employee'),

(1008,
 'emp123',
 'Employee'),

(1009,
 'emp123',
 'Employee'),

(1010,
 'emp123',
 'Employee');



INSERT INTO workshops
(
    employee_ic,
    workshop_name,
    workshop_year,
    attendance_date,
    attendance_status,
    remarks,
    updated_by
)
VALUES

(1003,
 'Hindi Workshop 2025',
 2025,
 '2025-01-15',
 'Attended',
 'Completed successfully',
 1002),

(1004,
 'Hindi Workshop 2025',
 2025,
 '2025-01-15',
 'Pending',
 'Awaiting attendance',
 1002),

(1005,
 'Hindi Workshop 2025',
 2025,
 '2025-01-15',
 'Absent',
 'Medical leave',
 1002),

(1006,
 'Hindi Workshop 2025',
 2025,
 '2025-01-16',
 'Attended',
 'Good participation',
 1002),

(1007,
 'Hindi Workshop 2025',
 2025,
 '2025-01-16',
 'Pending',
 'Not updated',
 1002),

(1008,
 'Hindi Workshop 2025',
 2025,
 '2025-01-16',
 'Attended',
 'Completed',
 1002),

(1009,
 'Hindi Workshop 2025',
 2025,
 '2025-01-17',
 'Absent',
 'Official duty',
 1002),

(1010,
 'Hindi Workshop 2025',
 2025,
 '2025-01-17',
 'Pending',
 'Waiting',
 1002);



INSERT INTO reports
(
    report_name,
    employee_ic,
    workshop_year,
    generated_by,
    status
)
VALUES

(
    'Hindi Workshop 2025 - Amit Kumar',
    1003,
    2025,
    1002,
    'Verified'
),

(
    'Hindi Workshop 2025 - Neha Sharma',
    1004,
    2025,
    1002,
    'Pending'
);


INSERT INTO activity_log
(
    activity,
    activity_by
)
VALUES

(
    'Karyashala Admin logged into the portal',
    1002
),

(
    'Workshop attendance updated',
    1002
),

(
    'Report generated',
    1002
);