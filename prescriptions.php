<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: index.php');
    exit;
}

$pdo = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $stmt = $pdo->prepare("INSERT INTO prescriptions (customer_id, prescription_date, od_sphere, od_cylinder, od_axis, os_sphere, os_cylinder, os_axis, pd, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_POST['customer_id'], $_POST['prescription_date'], $_POST['od_sphere'], $_POST['od_cylinder'], $_POST['od_axis'], $_POST['os_sphere'], $_POST['os_cylinder'], $_POST['os_axis'], $_POST['pd'], $_POST['notes']]);
            $success = "Prescription added successfully!";
        } elseif ($_POST['action'] === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM prescriptions WHERE id=?");
            $stmt->execute([$_POST['id']]);
            $success = "Prescription deleted successfully!";
        }
    }
}

$prescriptions = $pdo->query("
    SELECT p.*, c.name as customer_name 
    FROM prescriptions p 
    JOIN customers c ON p.customer_id = c.id 
    ORDER BY p.prescription_date DESC
")->fetchAll();

$customers = $pdo->query("SELECT id, name FROM customers ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescriptions - VisionCare Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f7fafc; font-family: 'Inter', sans-serif; }
        .sidebar { background: linear-gradient(180deg, #1a4d4d 0%, #0f2828 100%); min-height: 100vh; position: fixed; width: 250px; color: white; }
        .sidebar-header { padding: 2rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header h3 { margin: 0; font-size: 1.5rem; font-weight: 700; }
        .nav-link { color: rgba(255,255,255,0.8); padding: 0.75rem 1.5rem; display: flex; align-items: center; gap: 0.75rem; }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.1); color: white; }
        .main-content { margin-left: 250px; padding: 2rem; }
        .card { border: none; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        /*.btn-primary { background: linear-gradient(135deg, #667eea, #764ba2); border: none; }*/
        .prescription-card { background: white; padding: 1.5rem; border-radius: 10px; margin-bottom: 1rem; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .rx-data { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; }
        .rx-label { font-weight: 600; color: #666; font-size: 0.85rem; }
        .rx-value { font-size: 1rem; color: #333; }
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
            <li class="nav-item"><a class="nav-link" href="appointments.php"><i class="fas fa-calendar-alt"></i> Appointments</a></li>
            <li class="nav-item"><a class="nav-link active" href="prescriptions.php"><i class="fas fa-file-medical"></i> Prescriptions</a></li>
            <li class="nav-item"><a class="nav-link" href="products.php"><i class="fas fa-boxes"></i> Products</a></li>
            <li class="nav-item"><a class="nav-link" href="sales.php"><i class="fas fa-shopping-cart"></i> Sales</a></li>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Prescriptions</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fas fa-plus"></i> Add Prescription
            </button>
        </div>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show"><?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php foreach ($prescriptions as $rx): ?>
        <div class="prescription-card">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h5 class="mb-1"><?php echo htmlspecialchars($rx['customer_name']); ?></h5>
                    <small class="text-muted">Date: <?php echo date('F j, Y', strtotime($rx['prescription_date'])); ?></small>
                </div>
                <button class="btn btn-sm btn-danger" onclick="deleteItem(<?php echo $rx['id']; ?>)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-primary mb-2">OD (Right Eye)</h6>
                    <div class="rx-data">
                        <div>
                            <div class="rx-label">Sphere</div>
                            <div class="rx-value"><?php echo $rx['od_sphere']; ?></div>
                        </div>
                        <div>
                            <div class="rx-label">Cylinder</div>
                            <div class="rx-value"><?php echo $rx['od_cylinder']; ?></div>
                        </div>
                        <div>
                            <div class="rx-label">Axis</div>
                            <div class="rx-value"><?php echo $rx['od_axis']; ?>°</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <h6 class="text-primary mb-2">OS (Left Eye)</h6>
                    <div class="rx-data">
                        <div>
                            <div class="rx-label">Sphere</div>
                            <div class="rx-value"><?php echo $rx['os_sphere']; ?></div>
                        </div>
                        <div>
                            <div class="rx-label">Cylinder</div>
                            <div class="rx-value"><?php echo $rx['os_cylinder']; ?></div>
                        </div>
                        <div>
                            <div class="rx-label">Axis</div>
                            <div class="rx-value"><?php echo $rx['os_axis']; ?>°</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-3">
                <span class="rx-label">PD:</span> <span class="rx-value"><?php echo $rx['pd']; ?> mm</span>
            </div>
            
            <?php if ($rx['notes']): ?>
            <div class="mt-3">
                <div class="rx-label">Notes:</div>
                <div class="text-muted"><?php echo htmlspecialchars($rx['notes']); ?></div>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Prescription</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Customer</label>
                                <select class="form-select" name="customer_id" required>
                                    <option value="">Select Customer</option>
                                    <?php foreach ($customers as $c): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date</label>
                                <input type="date" class="form-control" name="prescription_date" required>
                            </div>
                        </div>
                        
                        <h6 class="mt-3 mb-2">OD (Right Eye)</h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Sphere</label>
                                <input type="number" step="0.25" class="form-control" name="od_sphere" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Cylinder</label>
                                <input type="number" step="0.25" class="form-control" name="od_cylinder" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Axis</label>
                                <input type="number" min="0" max="180" class="form-control" name="od_axis" required>
                            </div>
                        </div>
                        
                        <h6 class="mt-3 mb-2">OS (Left Eye)</h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Sphere</label>
                                <input type="number" step="0.25" class="form-control" name="os_sphere" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Cylinder</label>
                                <input type="number" step="0.25" class="form-control" name="os_cylinder" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Axis</label>
                                <input type="number" min="0" max="180" class="form-control" name="os_axis" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">PD (Pupillary Distance)</label>
                            <input type="number" min="50" max="80" class="form-control" name="pd" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Prescription</button>
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
        function deleteItem(id) {
            if (confirm('Delete this prescription?')) {
                document.getElementById('delete_id').value = id;
                document.getElementById('deleteForm').submit();
            }
        }
    </script>
</body>
</html>
