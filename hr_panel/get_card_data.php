<?php
include('session.php');
include('connection.php');

$type = $_POST['type'];
error_log("Received card type: " . $type);
$response = [];

switch (trim($type)) {
    case 'Total Employees':
        // Get overall employee statistics
        $queries = [
            "SELECT COUNT(*) as count FROM employees WHERE status = 'active'" => 'Active',
            "SELECT COUNT(*) as count FROM employees WHERE YEAR(doj) = YEAR(CURRENT_DATE)" => 'New This Year',
            "SELECT COUNT(*) as count FROM employees WHERE DATEDIFF(CURRENT_DATE, doj) > 365" => 'Over 1 Year',
            "SELECT COUNT(*) as count FROM employees WHERE status = 'probation'" => 'On Probation'
        ];

        $labels = [];
        $data = [];
        $colors = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e'];
        $i = 0;

        foreach ($queries as $query => $label) {
            $result = mysqli_query($con, $query);
            $row = mysqli_fetch_assoc($result);
            $labels[] = $label;
            $data[] = $row['count'];
        }

        $response = [
            'chartType' => 'doughnut',
            'title' => 'Employee Distribution',
            'chartData' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'data' => $data,
                        'backgroundColor' => $colors,
                        'hoverOffset' => 4
                    ]
                ]
            ]
        ];
        break;

    case 'On Leave Today':
        // Get leave data for last 7 days
        $query = "SELECT DATE(start_date) as date, COUNT(*) as count 
                 FROM leaves 
                 WHERE start_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                 GROUP BY DATE(start_date)
                 ORDER BY date";
        $result = mysqli_query($con, $query);

        $labels = [];
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $labels[] = date('M d', strtotime($row['date']));
            $data[] = $row['count'];
        }

        $response = [
            'chartType' => 'line',
            'title' => 'Leave Trends (Last 7 Days)',
            'chartData' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Employees on Leave',
                        'data' => $data,
                        'borderColor' => '#1cc88a',
                        'tension' => 0.3,
                        'fill' => false
                    ]
                ]
            ]
        ];
        break;

    case 'Absent Today':
        // Get absence data for last 7 days
        $query = "SELECT attendance_date, COUNT(*) as count 
                 FROM attendance 
                 WHERE status = 'absent' 
                 AND attendance_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                 GROUP BY attendance_date
                 ORDER BY attendance_date";
        $result = mysqli_query($con, $query);

        $labels = [];
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $labels[] = date('M d', strtotime($row['attendance_date']));
            $data[] = $row['count'];
        }

        $response = [
            'chartType' => 'bar',
            'title' => 'Absence Trends (Last 7 Days)',
            'chartData' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Absent Employees',
                        'data' => $data,
                        'backgroundColor' => '#e74a3b',
                        'borderColor' => '#e74a3b'
                    ]
                ]
            ]
        ];
        break;

    case 'Departments':
        // Get employee count per department
        $query = "SELECT department, COUNT(*) as count 
                 FROM employees 
                 GROUP BY department";
        $result = mysqli_query($con, $query);

        $labels = [];
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $labels[] = $row['department'];
            $data[] = $row['count'];
        }

        $response = [
            'chartType' => 'doughnut',
            'title' => 'Department Distribution',
            'chartData' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'data' => $data,
                        'backgroundColor' => [
                            '#4e73df',
                            '#1cc88a',
                            '#36b9cc',
                            '#f6c23e',
                            '#e74a3b',
                            '#858796'
                        ]
                    ]
                ]
            ]
        ];
        break;

    case 'Birthdays This Month':
        // Get birthday distribution by week
        $query = "SELECT 
                        CONCAT('Week ', WEEK(birthday, 1) - WEEK(DATE_FORMAT(birthday, '%Y-%m-01'), 1) + 1) as week_label,
                        COUNT(*) as count 
                     FROM employees 
                     WHERE MONTH(birthday) = MONTH(CURRENT_DATE())
                     GROUP BY WEEK(birthday, 1)
                     ORDER BY WEEK(birthday, 1)";
        $result = mysqli_query($con, $query);

        $labels = [];
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $labels[] = $row['week_label'];
            $data[] = $row['count'];
        }

        $response = [
            'chartType' => 'bar',
            'title' => 'Birthday Distribution This Month',
            'chartData' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Number of Birthdays',
                        'data' => $data,
                        'backgroundColor' => '#f6c23e'
                    ]
                ]
            ]
        ];
        break;

    case 'Work Anniversaries':
        // Get work anniversary distribution by week for current month
        $query = "SELECT 
                        CONCAT('Week ', WEEK(doj, 1) - WEEK(DATE_FORMAT(doj, '%Y-%m-01'), 1) + 1) as week_label,
                        COUNT(*) as count 
                     FROM employees 
                     WHERE MONTH(doj) = MONTH(CURRENT_DATE())
                     GROUP BY WEEK(doj, 1)
                     ORDER BY WEEK(doj, 1)";
        $result = mysqli_query($con, $query);

        $labels = [];
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $labels[] = $row['week_label'];
            $data[] = $row['count'];
        }

        $response = [
            'chartType' => 'bar',
            'title' => 'Work Anniversary Distribution This Month',
            'chartData' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Number of Work Anniversaries',
                        'data' => $data,
                        'backgroundColor' => '#4e73df'
                    ]
                ]
            ]
        ];
        break;



    case 'Visa Expiring Soon':
        // Get visa expiry distribution for next 20 days
        $query = "SELECT DATE(visa_expiry_date) as date, COUNT(*) as count 
                 FROM employees 
                 WHERE visa_expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 20 DAY)
                 GROUP BY date
                 ORDER BY date";
        $result = mysqli_query($con, $query);

        $labels = [];
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $labels[] = date('M d', strtotime($row['date']));
            $data[] = $row['count'];
        }

        $response = [
            'chartType' => 'line',
            'title' => 'Upcoming Visa Expirations',
            'chartData' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Expiring Visas',
                        'data' => $data,
                        'borderColor' => '#e74a3b',
                        'tension' => 0.3,
                        'fill' => false
                    ]
                ]
            ]
        ];
        break;

    case 'Labor Cards Expiring':
        // Get labor card expiry distribution
        $query = "SELECT DATE(labour_card_end_date) as date, COUNT(*) as count 
                 FROM employees 
                 WHERE labour_card_end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 20 DAY)
                 GROUP BY date
                 ORDER BY date";
        $result = mysqli_query($con, $query);

        $labels = [];
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $labels[] = date('M d', strtotime($row['date']));
            $data[] = $row['count'];
        }

        $response = [
            'chartType' => 'line',
            'title' => 'Upcoming Labor Card Expirations',
            'chartData' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Expiring Labor Cards',
                        'data' => $data,
                        'borderColor' => '#f6c23e',
                        'tension' => 0.3,
                        'fill' => false
                    ]
                ]
            ]
        ];
        break;

    // Get monthly salary

    case 'Monthly Salaries':
        $current_month = date('F Y');
        $query = "SELECT 
                DAY(salary_date) as day,
                SUM(total_salary) as daily_total
                FROM sal
                WHERE MONTH(salary_date) = MONTH(CURRENT_DATE())
                AND YEAR(salary_date) = YEAR(CURRENT_DATE())
                GROUP BY DAY(salary_date)
                ORDER BY day";

        $result = mysqli_query($con, $query);

        // Create specific day points for X-axis
        $key_days = [1, 15, 30];
        $labels = array_map(function ($day) {
            return "Day " . $day;
        }, $key_days);

        $data = array_fill(0, 3, 0);

        while ($row = mysqli_fetch_assoc($result)) {
            $day = (int) $row['day'];
            if ($day == 1)
                $data[0] = (float) $row['daily_total'];
            if ($day == 15)
                $data[1] = (float) $row['daily_total'];
            if ($day == 30)
                $data[2] = (float) $row['daily_total'];
        }

        $response = [
            'chartType' => 'line',
            'title' => 'Salary Distribution - ' . $current_month,
            'chartData' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => $current_month . ' Salary (AED)',
                        'data' => $data,
                        'borderColor' => '#1cc88a',
                        'backgroundColor' => 'rgba(28, 200, 138, 0.1)',
                        'tension' => 0.4,
                        'fill' => true
                    ]
                ]
            ],
            'options' => [
                'scales' => [
                    'y' => [
                        'beginAtZero' => true,
                        'ticks' => [
                            'stepSize' => 5000,
                            'max' => 50000
                        ]
                    ]
                ]
            ]
        ];
        break;


    // Get Annual salary

    case 'Annual Salaries':
        $query = "SELECT 
                MONTH(salary_date) as month,
                SUM(total_salary) as monthly_total
                FROM sal
                WHERE YEAR(salary_date) = YEAR(CURRENT_DATE())
                GROUP BY MONTH(salary_date)
                ORDER BY month";

        $result = mysqli_query($con, $query);

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $data = array_fill(0, 12, 0);

        while ($row = mysqli_fetch_assoc($result)) {
            $monthIndex = (int) $row['month'] - 1;
            $data[$monthIndex] = (float) $row['monthly_total'];
        }

        $response = [
            'chartType' => 'bar',
            'title' => 'Annual Salary Distribution ' . date('Y'),
            'chartData' => [
                'labels' => $months,
                'datasets' => [
                    [
                        'label' => 'Monthly Total (AED)',
                        'data' => $data,
                        'backgroundColor' => '#4e73df'
                    ]
                ]
            ],
            'options' => [
                'scales' => [
                    'y' => [
                        'beginAtZero' => true,
                        'ticks' => [
                            'stepSize' => 10000,
                            'max' => 100000
                        ]
                    ]
                ]
            ]
        ];
        break;

}

echo json_encode($response);
?>