CREATE DATABASE IF NOT EXISTS karyashalaNG;

USE karyashalaNG;

CREATE TABLE employees (

    ICNO INT AUTO_INCREMENT PRIMARY KEY,
    ENAME VARCHAR(100) NOT NULL,
    EDESIG VARCHAR(100) NOT NULL,
    EGROUP VARCHAR(100) NOT NULL,
    PASSWORD VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

);

ALTER TABLE employees AUTO_INCREMENT = 1001;

CREATE TABLE roles (
    ICNO INT NOT NULL PRIMARY KEY,
    ENAME VARCHAR(200) NOT NULL,
    ROLE ENUM('admin', 'karyashala_admin') NOT NULL,

    FOREIGN KEY (ICNO)
        REFERENCES employees(ICNO)
        ON DELETE CASCADE
);

-- CREATE TABLE karyashalamgt (

--     ICNO INT NOT NULL,
--     ENAME VARCHAR(100) NOT NULL,
--     karyashala_date DATE NOT NULL,
--     karyashala_remark VARCHAR(255),

--     FOREIGN KEY (ICNO) REFERENCES employees(ICNO)

-- );

-- ALTER TABLE karyashalamgt
-- ADD COLUMN id INT AUTO_INCREMENT PRIMARY KEY FIRST;

CREATE TABLE karyashalamgt (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ICNO INT NOT NULL,
    ENAME VARCHAR(100) NOT NULL,
    karyashala_date DATE NOT NULL,
    karyashala_remark VARCHAR(255),

    FOREIGN KEY (ICNO) REFERENCES employees(ICNO)
);

INSERT INTO employees (ENAME, EDESIG, EGROUP, PASSWORD)
VALUES
('Admin', 'Administrator', 'Administration', 'admin123'),
('Karyashala Admin', 'Karyashala Administrator', 'Karyashala', 'karyashala123');

INSERT INTO roles (ICNO, ENAME, ROLE)
VALUES
(1001, 'Admin', 'admin'),
(1002, 'Karyashala Admin', 'karyashala_admin');

