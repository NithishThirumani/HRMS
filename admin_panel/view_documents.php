<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('session.php');
include('connection.php');

// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Function to format document data consistently
function formatDocumentData($row, $source = 'new') {
    if ($source === 'new') {
        // From document_uploads table
        return array(
            'id' => $row['id'],
            'eid' => $row['eid'],
            'document_type' => $row['document_type'],
            'document_category' => $row['document_category'],
            'file_path' => $row['file_path'],
            'upload_date' => $row['upload_date'],
            'expiry_date' => $row['expiry_date'],
            'status' => $row['status'],
            'remarks' => $row['remarks'],
            'source' => 'document_uploads'
        );
    } else {
        // From employees table
        $documents = array();
        
        // Add passport document if exists
        if (!empty($row['passport_doc'])) {
            $documents[] = array(
                'id' => $row['id'],
                'eid' => $row['eid'],
                'document_type' => 'Passport',
                'document_category' => 'Identity',
                'file_path' => $row['passport_doc'],
                'upload_date' => $row['passport_issue_date'],
                'expiry_date' => $row['passport_expiry_date'],
                'status' => 'active',
                'remarks' => "Passport Number: {$row['passport_number']}, Type: {$row['passport_type']}, Country: {$row['country_of_issue']}",
                'source' => 'employees'
            );
        }
        
        // Add visa document if exists
        if (!empty($row['visa_doc'])) {
            $documents[] = array(
                'id' => $row['id'],
                'eid' => $row['eid'],
                'document_type' => 'Visa',
                'document_category' => 'Immigration',
                'file_path' => $row['visa_doc'],
                'upload_date' => $row['visa_issue_date'],
                'expiry_date' => $row['visa_expiry_date'],
                'status' => 'active',
                'remarks' => "Visa Number: {$row['visa_number']}, Type: {$row['visa_type']}",
                'source' => 'employees'
            );
        }
        
        return $documents;
    }
}

// Get documents from document_uploads table
$new_docs_query = "SELECT d.*, e.full_name, e.department_id, dept.name as department_name 
                   FROM document_uploads d 
                   LEFT JOIN employees e ON d.eid = e.eid 
                   LEFT JOIN departments dept ON e.department_id = dept.id
                   WHERE e.emp_left_org = 0 AND e.status = 'active'
                   ORDER BY dept.name, e.full_name, d.upload_date DESC";
$new_docs_result = mysqli_query($con, $new_docs_query);

// Get documents from employees table
$old_docs_query = "SELECT e.*, e.id as emp_id, dept.name as department_name 
                   FROM employees e 
                   LEFT JOIN departments dept ON e.department_id = dept.id
                   WHERE (e.passport_doc IS NOT NULL OR e.visa_doc IS NOT NULL)
                   AND e.emp_left_org = 0 AND e.status = 'active'";
$old_docs_result = mysqli_query($con, $old_docs_query);

if (!$new_docs_result || !$old_docs_result) {
    die("Query failed: " . mysqli_error($con));
}

// Combine and group all documents by department and then by employee
$grouped_documents = array();

// Add documents from document_uploads table
if (mysqli_num_rows($new_docs_result) > 0) {
    while ($row = mysqli_fetch_assoc($new_docs_result)) {
        $department_name = $row['department_name'] ?? 'No Department';
        $employee_name = $row['full_name'] ?? 'Unassigned';
        
        if (!isset($grouped_documents[$department_name])) {
            $grouped_documents[$department_name] = array();
        }
        if (!isset($grouped_documents[$department_name][$employee_name])) {
            $grouped_documents[$department_name][$employee_name] = array();
        }
        
        $grouped_documents[$department_name][$employee_name][] = formatDocumentData($row, 'new');
    }
}

// Add documents from employees table
if (mysqli_num_rows($old_docs_result) > 0) {
    while ($row = mysqli_fetch_assoc($old_docs_result)) {
        $department_name = $row['department_name'] ?? 'No Department';
        $employee_name = $row['full_name'];
        
        if (!isset($grouped_documents[$department_name])) {
            $grouped_documents[$department_name] = array();
        }
        if (!isset($grouped_documents[$department_name][$employee_name])) {
            $grouped_documents[$department_name][$employee_name] = array();
        }
        
        $documents = formatDocumentData($row, 'old');
        foreach ($documents as $doc) {
            $grouped_documents[$department_name][$employee_name][] = $doc;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Documents</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    
    <style>
        .document-card {
            transition: transform 0.2s;
            margin-bottom: 1rem;
        }
        .document-card:hover {
            transform: translateY(-5px);
        }
        .category-badge {
            font-size: 0.8rem;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
        }
        .actions-column {
            min-width: 120px;
        }
        .table-container {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .source-badge {
            font-size: 0.7rem;
            padding: 0.2rem 0.5rem;
            border-radius: 12px;
            margin-left: 0.5rem;
        }
        .expiry-warning {
            color: #dc3545;
            font-weight: bold;
        }
        .expiry-alert {
            color: #ffc107;
            font-weight: bold;
        }
        .department-section {
            margin-bottom: 2rem;
            border: 1px solid #e3e6f0;
            border-radius: 8px;
            overflow: hidden;
        }
        .department-header {
            background: #4e73df;
            color: white;
            padding: 1rem;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .department-header:hover {
            background: #2e59d9;
        }
        .department-header h4 {
            margin: 0;
            color: white;
        }
        .department-content {
            padding: 1rem;
            display: none;
        }
        .department-content.show {
            display: block;
        }
        .employee-section {
            margin-bottom: 1rem;
            border: 1px solid #e3e6f0;
            border-radius: 8px;
            overflow: hidden;
        }
        .employee-header {
            background: #f8f9fc;
            padding: 0.75rem;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .employee-header:hover {
            background: #eaecf4;
        }
        .employee-header h5 {
            margin: 0;
            color: #4e73df;
        }
        .employee-content {
            padding: 0.75rem;
            display: none;
        }
        .employee-content.show {
            display: block;
        }
        .document-count {
            background: #4e73df;
            color: white;
            padding: 0.2rem 0.6rem;
            border-radius: 12px;
            font-size: 0.8rem;
            margin-left: 0.5rem;
        }
        .department-count {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 0.2rem 0.6rem;
            border-radius: 12px;
            font-size: 0.8rem;
            margin-left: 0.5rem;
        }
        .compact-table {
            font-size: 0.9rem;
        }
        .compact-table th,
        .compact-table td {
            padding: 0.5rem;
        }
        .status-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 0.5rem;
        }
        .status-active {
            background: #28a745;
        }
        .status-expired {
            background: #dc3545;
        }
        .status-warning {
            background: #ffc107;
        }
        .quick-stats {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }
        .stat-card {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            flex: 1;
            min-width: 200px;
        }
        .stat-number {
            font-size: 1.5rem;
            font-weight: bold;
            color: #4e73df;
        }
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include('sidebar.php'); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('header.php'); ?>
                
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Document Management</h1>
                        <div>
                            <button class="btn btn-outline-primary mr-2" onclick="toggleAllSections()">
                                <i class="fas fa-expand-arrows-alt"></i> Toggle All
                            </button>
                            <a href="document_upload.php" class="btn btn-primary">
                                <i class="fas fa-upload fa-sm text-white-50"></i> Upload New Document
                            </a>
                        </div>
                    </div>

                    <!-- Quick Statistics -->
                    <div class="quick-stats">
                        <?php
                        $total_docs = 0;
                        $expired_docs = 0;
                        $expiring_soon = 0;
                        $total_departments = count($grouped_documents);
                        $total_employees = 0;
                        
                        foreach ($grouped_documents as $department => $employees) {
                            foreach ($employees as $employee_docs) {
                                $total_employees++;
                                $total_docs += count($employee_docs);
                                foreach ($employee_docs as $doc) {
                                    if (!empty($doc['expiry_date'])) {
                                        $expiry_date = new DateTime($doc['expiry_date']);
                                        $today = new DateTime();
                                        if ($expiry_date < $today) {
                                            $expired_docs++;
                                        } elseif ($today->diff($expiry_date)->days <= 30) {
                                            $expiring_soon++;
                                        }
                                    }
                                }
                            }
                        }
                        ?>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $total_departments; ?></div>
                            <div class="stat-label">Departments</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $total_employees; ?></div>
                            <div class="stat-label">Active Employees</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $total_docs; ?></div>
                            <div class="stat-label">Total Documents</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number text-warning"><?php echo $expiring_soon; ?></div>
                            <div class="stat-label">Expiring Soon (≤30 days)</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number text-danger"><?php echo $expired_docs; ?></div>
                            <div class="stat-label">Expired Documents</div>
                        </div>
                    </div>

                    <div class="table-container">
                        <?php if (empty($grouped_documents)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-folder-open fa-3x text-gray-400 mb-3"></i>
                                <h5>No documents found</h5>
                                <p>Start by uploading a new document.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($grouped_documents as $department_name => $employees): ?>
                                <div class="department-section">
                                    <div class="department-header" onclick="toggleDepartment(this)">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h4>
                                                <i class="fas fa-building mr-2"></i>
                                                <?php echo htmlspecialchars($department_name); ?>
                                                <span class="department-count"><?php echo count($employees); ?> employees</span>
                                            </h4>
                                            <i class="fas fa-chevron-down toggle-icon"></i>
                                        </div>
                                    </div>
                                    <div class="department-content">
                                        <?php foreach ($employees as $employee_name => $documents): ?>
                                            <div class="employee-section">
                                                <div class="employee-header" onclick="toggleEmployee(this)">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <h5>
                                                            <i class="fas fa-user mr-2"></i>
                                                            <?php echo htmlspecialchars($employee_name); ?>
                                                            <span class="document-count"><?php echo count($documents); ?> docs</span>
                                                        </h5>
                                                        <i class="fas fa-chevron-down toggle-icon"></i>
                                                    </div>
                                                </div>
                                                <div class="employee-content">
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered compact-table documentsTable">
                                                            <thead>
                                                                <tr>
                                                                    <th>Type</th>
                                                                    <th>Category</th>
                                                                    <th>Expiry</th>
                                                                    <th>Status</th>
                                                                    <th>Actions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($documents as $doc): ?>
                                                                    <tr>
                                                                        <td>
                                                                            <?php echo htmlspecialchars($doc['document_type']); ?>
                                                                            <span class="badge <?php echo $doc['source'] === 'employees' ? 'badge-info' : 'badge-success'; ?> source-badge">
                                                                                <?php echo $doc['source'] === 'employees' ? 'Legacy' : 'New'; ?>
                                                                            </span>
                                                                        </td>
                                                                        <td>
                                                                            <span class="badge badge-info category-badge">
                                                                                <?php echo ucfirst(htmlspecialchars($doc['document_category'])); ?>
                                                                            </span>
                                                                        </td>
                                                                        <td>
                                                                            <?php 
                                                                            if (!empty($doc['expiry_date'])) {
                                                                                $expiry_date = new DateTime($doc['expiry_date']);
                                                                                $today = new DateTime();
                                                                                $interval = $today->diff($expiry_date);
                                                                                $days_remaining = $interval->days;
                                                                                
                                                                                if ($expiry_date < $today) {
                                                                                    echo '<span class="expiry-warning">Expired</span>';
                                                                                } elseif ($days_remaining <= 30) {
                                                                                    echo '<span class="expiry-alert">' . $days_remaining . ' days</span>';
                                                                                } else {
                                                                                    echo date('d M Y', strtotime($doc['expiry_date']));
                                                                                }
                                                                            } else {
                                                                                echo '-';
                                                                            }
                                                                            ?>
                                                                        </td>
                                                                        <td>
                                                                            <?php 
                                                                            if (!empty($doc['expiry_date'])) {
                                                                                $expiry_date = new DateTime($doc['expiry_date']);
                                                                                $today = new DateTime();
                                                                                if ($expiry_date < $today) {
                                                                                    echo '<span class="status-indicator status-expired"></span>Expired';
                                                                                } elseif ($today->diff($expiry_date)->days <= 30) {
                                                                                    echo '<span class="status-indicator status-warning"></span>Warning';
                                                                                } else {
                                                                                    echo '<span class="status-indicator status-active"></span>Active';
                                                                                }
                                                                            } else {
                                                                                echo '<span class="status-indicator status-active"></span>Active';
                                                                            }
                                                                            ?>
                                                                        </td>
                                                                        <td class="actions-column">
                                                                            <div class="btn-group">
                                                                                <a href="<?php echo $doc['file_path']; ?>" 
                                                                                   class="btn btn-info btn-sm" 
                                                                                   target="_blank"
                                                                                   title="View Document">
                                                                                    <i class="fas fa-eye"></i>
                                                                                </a>
                                                                                <?php if ($doc['source'] === 'document_uploads'): ?>
                                                                                    <a href="edit_document.php?id=<?php echo $doc['id']; ?>" 
                                                                                       class="btn btn-primary btn-sm"
                                                                                       title="Edit Document">
                                                                                        <i class="fas fa-edit"></i>
                                                                                    </a>
                                                                                    <button class="btn btn-danger btn-sm delete-doc" 
                                                                                            data-id="<?php echo $doc['id']; ?>"
                                                                                            title="Delete Document">
                                                                                        <i class="fas fa-trash"></i>
                                                                                    </button>
                                                                                <?php endif; ?>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php include('footer.php'); ?>
        </div>
    </div>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('.documentsTable').DataTable({
                order: [[2, 'asc']], // Sort by expiry date by default
                pageLength: 5,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search documents..."
                },
                responsive: true,
                dom: 't', // Only show table, no pagination/search
                info: false,
                paging: false
            });

            // Delete document confirmation
            $('.delete-doc').click(function() {
                if (confirm('Are you sure you want to delete this document?')) {
                    const docId = $(this).data('id');
                    window.location.href = `delete_document.php?id=${docId}`;
                }
            });
        });

        function toggleDepartment(header) {
            const content = header.nextElementSibling;
            const icon = header.querySelector('.toggle-icon');
            
            if (content.classList.contains('show')) {
                content.classList.remove('show');
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            } else {
                content.classList.add('show');
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            }
        }

        function toggleEmployee(header) {
            const content = header.nextElementSibling;
            const icon = header.querySelector('.toggle-icon');
            
            if (content.classList.contains('show')) {
                content.classList.remove('show');
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            } else {
                content.classList.add('show');
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            }
        }

        function toggleAllSections() {
            const departments = document.querySelectorAll('.department-content');
            const employees = document.querySelectorAll('.employee-content');
            const allIcons = document.querySelectorAll('.toggle-icon');
            
            const allExpanded = Array.from(departments).every(dept => dept.classList.contains('show')) &&
                               Array.from(employees).every(emp => emp.classList.contains('show'));
            
            departments.forEach((dept, index) => {
                if (allExpanded) {
                    dept.classList.remove('show');
                    allIcons[index].classList.remove('fa-chevron-up');
                    allIcons[index].classList.add('fa-chevron-down');
                } else {
                    dept.classList.add('show');
                    allIcons[index].classList.remove('fa-chevron-down');
                    allIcons[index].classList.add('fa-chevron-up');
                }
            });
            
            employees.forEach((emp, index) => {
                if (allExpanded) {
                    emp.classList.remove('show');
                    allIcons[index + departments.length].classList.remove('fa-chevron-up');
                    allIcons[index + departments.length].classList.add('fa-chevron-down');
                } else {
                    emp.classList.add('show');
                    allIcons[index + departments.length].classList.remove('fa-chevron-down');
                    allIcons[index + departments.length].classList.add('fa-chevron-up');
                }
            });
        }
    </script>
</body>
</html>