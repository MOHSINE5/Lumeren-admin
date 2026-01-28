<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: index.php');
    exit;
}

$pdo = getDBConnection();

// Handle operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $stmt = $pdo->prepare("INSERT INTO appointments (customer_id, appointment_date, appointment_time, service_type, status, notes) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_POST['customer_id'], $_POST['appointment_date'], $_POST['appointment_time'], $_POST['service_type'], $_POST['status'], $_POST['notes']]);
            $success = "Appointment added successfully!";
        } elseif ($_POST['action'] === 'edit') {
            $stmt = $pdo->prepare("UPDATE appointments SET customer_id=?, appointment_date=?, appointment_time=?, service_type=?, status=?, notes=? WHERE id=?");
            $stmt->execute([$_POST['customer_id'], $_POST['appointment_date'], $_POST['appointment_time'], $_POST['service_type'], $_POST['status'], $_POST['notes'], $_POST['id']]);
            $success = "Appointment updated successfully!";
        } elseif ($_POST['action'] === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM appointments WHERE id=?");
            $stmt->execute([$_POST['id']]);
            $success = "Appointment deleted successfully!";
        }
    }
}

$appointments = $pdo->query("
    SELECT a.*, c.name as customer_name, c.phone 
    FROM appointments a 
    JOIN customers c ON a.customer_id = c.id 
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
")->fetchAll();

$customers = $pdo->query("SELECT id, name FROM customers ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments - VisionCare Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --primary: #667eea; --secondary: #764ba2; --dark: #2d3748; }
        body { background-color: #f7fafc; font-family: 'Inter', sans-serif; }
        .sidebar { background: linear-gradient(180deg, #1a4d4d 0%, #0f2828 100%); min-height: 100vh; position: fixed; width: 250px; color: white; }
        .sidebar-header { padding: 2rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header h3 { margin: 0; font-size: 1.5rem; font-weight: 700; }
        .nav-link { color: rgba(255,255,255,0.8); padding: 0.75rem 1.5rem; display: flex; align-items: center; gap: 0.75rem; transition: all 0.3s; }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.1); color: white; }
        .main-content { margin-left: 250px; padding: 2rem; }
        .card { border: none; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .card-header { background: white; border-bottom: 1px solid #e2e8f0; padding: 1.25rem; font-weight: 600; }
        /*.btn-primary { background: linear-gradient(135deg, var(--primary), var(--secondary)); border: none; }*/
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Lumeren</h3>
            <small class="text-white-50">Admin Portal</small>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="customers.php"><i class="fas fa-users"></i> Customers</a></li>
            <li class="nav-item"><a class="nav-link active" href="appointments.php"><i class="fas fa-calendar-alt"></i> Appointments</a></li>
            <li class="nav-item"><a class="nav-link" href="prescriptions.php"><i class="fas fa-file-medical"></i> Prescriptions</a></li>
            <li class="nav-item"><a class="nav-link" href="products.php"><i class="fas fa-boxes"></i> Products</a></li>
            <li class="nav-item"><a class="nav-link" href="sales.php"><i class="fas fa-shopping-cart"></i> Sales</a></li>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Appointments</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fas fa-plus"></i> Add Appointment
            </button>
        </div>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show"><?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Service</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $apt): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($apt['customer_name']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($apt['phone']); ?></small></td>
                                <td><?php echo date('M j, Y', strtotime($apt['appointment_date'])); ?></td>
                                <td><?php echo date('g:i A', strtotime($apt['appointment_time'])); ?></td>
                                <td><?php echo htmlspecialchars($apt['service_type']); ?></td>
                                <td><span class="badge bg-<?php echo $apt['status'] === 'completed' ? 'success' : ($apt['status'] === 'cancelled' ? 'danger' : 'primary'); ?>">
                                    <?php echo ucfirst($apt['status']); ?></span></td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick='editItem(<?php echo json_encode($apt); ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteItem(<?php echo $apt['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label class="form-label">Customer</label>
                            <select class="form-select" name="customer_id" required>
                                <option value="">Select Customer</option>
                                <?php foreach ($customers as $c): ?>
                                <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" name="appointment_date" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Time</label>
                            <input type="time" class="form-control" name="appointment_time" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Service Type</label>
                            <select class="form-select" name="service_type" required>
                                <option>Eye Exam</option>
                                <option>Contact Lens Fitting</option>
                                <option>Frame Selection</option>
                                <option>Prescription Glasses</option>
                                <option>Repairs</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="scheduled">Scheduled</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">Customer</label>
                            <select class="form-select" name="customer_id" id="edit_customer_id" required>
                                <?php foreach ($customers as $c): ?>
                                <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" name="appointment_date" id="edit_date" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Time</label>
                            <input type="time" class="form-control" name="appointment_time" id="edit_time" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Service Type</label>
                            <input type="text" class="form-control" name="service_type" id="edit_service" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" id="edit_status">
                                <option value="scheduled">Scheduled</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" id="edit_notes" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" id="delete_id">
    </form>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editItem(item) {
            document.getElementById('edit_id').value = item.id;
            document.getElementById('edit_customer_id').value = item.customer_id;
            document.getElementById('edit_date').value = item.appointment_date;
            document.getElementById('edit_time').value = item.appointment_time;
            document.getElementById('edit_service').value = item.service_type;
            document.getElementById('edit_status').value = item.status;
            document.getElementById('edit_notes').value = item.notes || '';
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }
        
        function deleteItem(id) {
            if (confirm('Delete this appointment?')) {
                document.getElementById('delete_id').value = id;
                document.getElementById('deleteForm').submit();
            }
        }
    </script>
</body>
</html>
