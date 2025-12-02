CREATE TABLE appraisal_periods (
    period_id INT PRIMARY KEY AUTO_INCREMENT,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('Draft', 'Active', 'Completed') DEFAULT 'Draft',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE appraisal_criteria (
    criteria_id INT PRIMARY KEY AUTO_INCREMENT,
    criteria_name VARCHAR(100) NOT NULL,
    description TEXT,
    weightage DECIMAL(5,2),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE employee_appraisals (
    appraisal_id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT,
    period_id INT,
    status ENUM('Pending', 'Self_Submitted', 'HOD_Reviewed', 'HR_Reviewed', 'Completed') DEFAULT 'Pending',
    final_rating DECIMAL(3,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (period_id) REFERENCES appraisal_periods(period_id)
);

CREATE TABLE appraisal_ratings (
    rating_id INT PRIMARY KEY AUTO_INCREMENT,
    appraisal_id INT,
    criteria_id INT,
    self_rating INT,
    hod_rating INT,
    hr_rating INT,
    comments TEXT,
    FOREIGN KEY (appraisal_id) REFERENCES employee_appraisals(appraisal_id),
    FOREIGN KEY (criteria_id) REFERENCES appraisal_criteria(criteria_id)
);