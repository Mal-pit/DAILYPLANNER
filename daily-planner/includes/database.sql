CREATE DATABASE daily_planner;
USE daily_planner;

CREATE TABLE habits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    name VARCHAR(100),
    icon VARCHAR(10),
    target INT,
    category VARCHAR(50)
);
