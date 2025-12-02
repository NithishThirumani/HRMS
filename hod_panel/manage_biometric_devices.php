<?php
require_once '../includes/header.php';
require_once '../includes/navbar.php';
require_once '../includes/sidebar.php';
require_once '../biometric_config/config.php';
require_once '../biometric_config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$db = new Database();

// Handle form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_device'])) {
        // Add new device
        $device_name = $_POST['device_name'];
        $ip_address = $_POST['ip_address'];
        $port = $_POST['port'];
        $device_id = $_POST['device_id'];
        $model = $_POST['model'];
        $comm_key = $_POST['comm_key'];
        
        // Validate inputs
        if (empty($device_name) || empty($ip_address) || empty($port) || empty($device_id)) {
            $message = 'All required fields must be filled out';
            $messageType = 'danger';
        } else {
            // Insert into database
            $query = "INSERT INTO biometric_devices (name, ip_address, port, device_id, model, comm_key) 
                      VALUES (?, ?, ?, ?, ?, ?)";
            $result = $db->query($query, [$device_name, $ip_address, $port, $device_id, $model, $comm_key]);
            
            if ($result) {
                $message = 'Device added successfully';
                $messageType = 'success';
            } else {
                $message = 'Failed to add device';
                $messageType = 'danger';
            }
        }
    } elseif (isset($_POST['update_device'])) {
        // Update existing device
        $id = $_POST['id'];
        $device_name = $_POST['device_name'];
        $ip_address = $_POST['ip_address'];
        $port = $_POST['port'];
        $device_id = $_POST['device_id'];
        $model = $_POST['model'];
        $comm_key = $_POST['comm_key'];
        
        // Update in database
        $query = "UPDATE biometric_devices 
                  SET name = ?, ip_address = ?, port = ?, device_id = ?, model = ?, comm_key = ? 
                  WHERE id = ?";
        $result = $db->query($query, [$device_name, $ip_address, $port, $device_id, $model, $comm_key, $id]);
        
        if ($result) {
            $message = 'Device updated successfully';
            $messageType = 'success';
        } else {
            $message = 'Failed to update device';
            $messageType = 'danger';
        }
    } elseif (isset($_POST['delete_device'])) {
        // Delete device
        $id = $_POST['id'];
        
        $query = "DELETE FROM biometric_devices WHERE id = ?";
        $result = $db->query($query, [$id]);
        
        if ($result) {
            $message = 'Device deleted successfully';
            $messageType = 'success';
        } else {
            $message = 'Failed to delete device';
            $messageType = 'danger';
        }
    } elseif (isset($_POST['test_connection'])) {
        // Test connection to device
        $id = $_POST['id'];
        
        // Get device details
        $query = "SELECT * FROM biometric_devices WHERE id = ?";
        $result = $db->query($query, [$id]);
        
        if ($result && $result->num_rows > 0) {
            $device = $result->fetch_assoc();
            
            // Test connection
            require_once '../biometric_config/device_manager.php';
            
            try {
                $deviceManager = new BiometricDeviceManager($device['name']);
                $connected = $deviceManager->connect();
                
                if ($connected) {
                    $deviceManager->disconnect();
                    $message = 'Successfully connected to device';
                    $messageType = 'success';
                } else {
                    $message = 'Failed to connect to device';
                    $messageType = 'danger';
                }
            } catch (Exception $e) {
                $message = 'Error: ' . $e->getMessage();
                $messageType = 'danger';
            }
        } else {
            $message = 'Device not found';
            $messageType = 'danger';
        }
    }
}

// Get all devices
$query = "SELECT * FROM biometric_devices ORDER BY name";
$result = $db->query($query);
$devices = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $devices[] = $row;
    }
}
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Manage Biometric Devices</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item active">Biometric Devices</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                    <?php echo $message; ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Add New Biometric Device</h3>
                </div>
                <div class="card-body">
                    <form method="post" action="">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="device_name">Device Name*</label>
                                    <input type="text" class="form-control" id="device_name" name="device_name" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="ip_address">IP Address*</label>
                                    <input type="text" class="form-control" id="ip_address" name="ip_address" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="port">Port*</label>
                                    <input type="number" class="form-control" id="port" name="port" value="4370" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="device_id">Device ID*</label>
                                    <input type="number" class="form-control" id="device_id" name="device_id" value="1" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="model">Model</label>
                                    <input type="text" class="form-control" id="model" name="model" placeholder="e.g. ZKTeco F18">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="comm_key">Communication Key</label>
                                    <input type="text" class="form-control" id="comm_key" name="comm_key" value="0" placeholder="0 means no password">
                                </div>
                            </div>
                        </div>
                        <button type="submit" name="add_device" class="btn btn-primary">Add Device</button>
                    </form>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">Configured Devices</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>IP Address</th>
                                <th>Port</th>
                                <th>Device ID</th>
                                <th>Model</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($devices)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No devices configured</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($devices as $device): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($device['name']); ?></td>
                                        <td><?php echo htmlspecialchars($device['ip_address']); ?></td>
                                        <td><?php echo htmlspecialchars($device['port']); ?></td>
                                        <td><?php echo htmlspecialchars($device['device_id']); ?></td>
                                        <td><?php echo htmlspecialchars($device['model']); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info edit-device" 
                                                    data-id="<?php echo $device['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($device['name']); ?>"
                                                    data-ip="<?php echo htmlspecialchars($device['ip_address']); ?>"
                                                    data-port="<?php echo htmlspecialchars($device['port']); ?>"
                                                    data-device-id="<?php echo htmlspecialchars($device['device_id']); ?>"
                                                    data-model="<?php echo htmlspecialchars($device['model']); ?>"
                                                    data-comm-key="<?php echo htmlspecialchars($device['comm_key']); ?>">
                                                Edit
                                            </button>
                                            <form method="post" action="" class="d-inline">
                                                <input type="hidden" name="id" value="<?php echo $device['id']; ?>">
                                                <button type="submit" name="test_connection" class="btn btn-sm btn-success">Test Connection</button>
                                            </form>
                                            <form method="post" action="" class="d-inline delete-form">
                                                <input type="hidden" name="id" value="<?php echo $device['id']; ?>">
                                                <button type="submit" name="delete_device" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Edit Device Modal -->
<div class="modal fade" id="editDeviceModal" tabindex="-1" role="dialog" aria-labelledby="editDeviceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editDeviceModalLabel">Edit Device</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_device_name">Device Name*</label>
                                <input type="text" class="form-control" id="edit_device_name" name="device_name" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_ip_address">IP Address*</label>
                                <input type="text" class="form-control" id="edit_ip_address" name="ip_address" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_port">Port*</label>
                                <input type="number" class="form-control" id="edit_port" name="port" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_device_id">Device ID*</label>
                                <input type="number" class="form-control" id="edit_device_id" name="device_id" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_model">Model</label>
                                <input type="text" class="form-control" id="edit_model" name="model">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_comm_key">Communication Key</label>
                                <input type="text" class="form-control" id="edit_comm_key" name="comm_key">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" name="update_device" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Edit device button click
    $('.edit-device').click(function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var ip = $(this).data('ip');
        var port = $(this).data('port');
        var deviceId = $(this).data('device-id');
        var model = $(this).data('model');
        var commKey = $(this).data('comm-key');
        $('#edit_device_id').val(deviceId);
        $('#edit_model').val(model);
        $('#edit_comm_key').val(commKey);
        
        $('#editDeviceModal').modal('show');
    });
    
    // Confirm delete
    $('.delete-form').submit(function(e) {
        if (!confirm('Are you sure you want to delete this device?')) {
            e.preventDefault();
        }
    });
});
</script>

<?php
require_once '../includes/footer.php';
?>