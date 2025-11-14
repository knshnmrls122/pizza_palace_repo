# Pizza Palace
# Open Xampp
# Wesite Url : http://localhost/Pizza_Palace/pages/Home_Page.php#home
# Tailwind ang ginamit na CSS Framework search nyo lang sa google kung pano gamitin

# Project Directory
Pizza_Palace/
 ├── includes/
 │    ├── db_connection.php ( connection para sa database)
 │    ├── signup_handler.php ( naghandle kapag nagsign up)
 │    ├── login_handler.php ( naghandle kapag nagsign up)
 │    ├── logout.php ( para sa logout)
 ├── admin/
 │    ├── dashboard.php 
 │    ├── employees.php
 │    ├── attendance.php
 │    ├── reports.php
 │    ├── notifications.php
 ├── employee/
 │    ├── dashboard.php
 │    ├── profile.php
 │    ├── attendance.php
 │    ├── reports.php
 ├── pages/
 │    ├── Login_Page.php // uncomment yung line 51 para ma enable yung signpage / homepage <!-- --> (yun eto)
 │    ├── Signup_Page.php
 │    ├── Home_Page.php
 └── assets/
      ├── Bg.jpg
      ├── logo.jpg


# Database Name : db_pizzapalace
# User SQL
CREATE TABLE Users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    contact_number VARCHAR(20),
    address VARCHAR(255),
    role ENUM('Admin', 'Employee') NOT NULL DEFAULT 'Employee',
    employee_id VARCHAR(20) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE Attendance (
    attendance_id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(20),
    date DATE NOT NULL,
    status ENUM('Present', 'Absent', 'Late') DEFAULT 'Present',
    time_in TIME,
    time_out TIME,
    FOREIGN KEY (employee_id) REFERENCES Users(employee_id)
);

CREATE TABLE Notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    date_sent TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE Performance (
    performance_id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(20),
    month VARCHAR(20),
    performance_score INT,
    remarks TEXT,
    FOREIGN KEY (employee_id) REFERENCES Users(employee_id)
);
CREATE TABLE Payroll (
  payroll_id INT AUTO_INCREMENT PRIMARY KEY,
  employee_id VARCHAR(100) NOT NULL,
  month VARCHAR(7) NOT NULL,
  gross DECIMAL(10,2) NOT NULL DEFAULT 0,
  deductions DECIMAL(10,2) NOT NULL DEFAULT 0,
  net DECIMAL(10,2) NOT NULL DEFAULT 0,
  remarks TEXT,
  date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
ALTER TABLE Notifications ADD COLUMN employee_id VARCHAR(100) NULL AFTER notification_id;