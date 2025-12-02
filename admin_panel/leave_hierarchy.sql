CREATE TABLE IF NOT EXISTS leave_hierarchy (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    recommender_id INT DEFAULT NULL,
    approver_id INT DEFAULT NULL,
    department_id INT,
    type ENUM('recommender','approver') NOT NULL,
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (recommender_id) REFERENCES employees(id),
    FOREIGN KEY (approver_id) REFERENCES employees(id),
    FOREIGN KEY (department_id) REFERENCES departments(id)
); 