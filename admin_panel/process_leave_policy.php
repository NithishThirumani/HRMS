<?php
include ('session.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle toggling policy status
    if (isset($_POST['action']) && $_POST['action'] === 'toggle') {
        $policy_id = $_POST['policy_id'];
        // The currentStatus from JS is inverted for the check, so we use it directly
        $newStatus = $_POST['status'];

        $update_query = "UPDATE leave_policies SET is_active = ? WHERE id = ?";
        $stmt = $con->prepare($update_query);
        $stmt->bind_param('ii', $newStatus, $policy_id);

        if ($stmt->execute()) {
            echo 'success';
        } else {
            echo 'Error: ' . $stmt->error;
        }
        $stmt->close();
        exit;
    }

    // Handle adding or editing a policy
    $leave_type = $_POST['leave_type'];
    $max_days = $_POST['max_days'];
    $monthly_accrual = $_POST['monthly_accrual'];
    $min_service_months = $_POST['min_service_months'];
    $requires_certificate = $_POST['requires_certificate'];
    $is_paid = $_POST['is_paid'];
    // Use null coalescing operator (??) to prevent 'Undefined array key' warnings
    $gender_restriction = $_POST['gender_restriction'] ?? 'all';
    $policy_id = !empty($_POST['policy_id']) ? $_POST['policy_id'] : null;

    if ($policy_id) {
        // Update existing policy
        $query = "UPDATE leave_policies SET leave_type = ?, max_days = ?, monthly_accrual = ?, min_service_months = ?, requires_certificate = ?, is_paid = ?, gender_restriction = ? WHERE id = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param('sidiissi', $leave_type, $max_days, $monthly_accrual, $min_service_months, $requires_certificate, $is_paid, $gender_restriction, $policy_id);
    } else {
        // Insert new policy
        $query = "INSERT INTO leave_policies (leave_type, max_days, monthly_accrual, min_service_months, requires_certificate, is_paid, gender_restriction, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)";
        $stmt = $con->prepare($query);
        $stmt->bind_param('sidiiss', $leave_type, $max_days, $monthly_accrual, $min_service_months, $requires_certificate, $is_paid, $gender_restriction);
    }

    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'Error: ' . $stmt->error;
    }

    $stmt->close();
} else {
    // If not a POST request, redirect or show an error
    header("Location: leave_settings.php");
}
?> 