<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: index.php');
    exit;
}

// Initialize database on first load
initializeDatabase();

$pdo = getDBConnection();

// Get statistics
$totalCustomers = $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
$todayAppointments = $pdo->query("SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE()")->fetchColumn();
$upcomingAppointments = $pdo->query("SELECT COUNT(*) FROM appointments WHERE appointment_date > CURDATE() AND status = 'scheduled'")->fetchColumn();
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$lowStock = $pdo->query("SELECT COUNT(*) FROM products WHERE stock < 10")->fetchColumn();

// Get recent appointments
$recentAppointments = $pdo->query("
    SELECT a.*, c.name as customer_name, c.phone 
    FROM appointments a 
    JOIN customers c ON a.customer_id = c.id 
    WHERE a.appointment_date >= CURDATE() 
    ORDER BY a.appointment_date, a.appointment_time 
    LIMIT 5
")->fetchAll();

// Get low stock products
$lowStockProducts = $pdo->query("
    SELECT * FROM products 
    WHERE stock < 10 
    ORDER BY stock ASC 
    LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - VisionCare Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --success: #48bb78;
            --warning: #f6ad55;
            --danger: #fc8181;
            --dark: #2d3748;
        }
        
        body {
            background-color: #f7fafc;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        
        .sidebar {
            background: linear-gradient(180deg, #1a4d4d 0%, #0f2828 100%);
            min-height: 100vh;
            padding: 0;
            position: fixed;
            width: 250px;
            color: white;
        }
        
        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header h3 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .nav-item {
            margin: 0.25rem 0;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1.5rem;
            border-radius: 0;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .nav-link:hover, .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .nav-link i {
            width: 20px;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 2rem;
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            border-left: 4px solid;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .stats-card.primary { border-color: var(--primary); }
        .stats-card.success { border-color: var(--success); }
        .stats-card.warning { border-color: var(--warning); }
        .stats-card.danger { border-color: var(--danger); }
        
        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .stats-icon.primary { background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; }
        .stats-icon.success { background: var(--success); color: white; }
        .stats-icon.warning { background: var(--warning); color: white; }
        .stats-icon.danger { background: var(--danger); color: white; }
        
        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.25rem;
        }
        
        .stats-label {
            color: #718096;
            font-size: 0.9rem;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 1.25rem;
            font-weight: 600;
            color: var(--dark);
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .badge {
            padding: 0.35rem 0.75rem;
            font-weight: 500;
        }
        
        .btn-logout {
            background: rgba(252, 129, 129, 0.2);
            color: #fc8181;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .btn-logout:hover {
            background: #fc8181;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Lumeren</h3>
            <small class="text-white-50">Admin Portal</small>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="dashboard.php">
                    <i class="fas fa-chart-line"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="customers.php">
                    <i class="fas fa-users"></i> Customers
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="appointments.php">
                    <i class="fas fa-calendar-alt"></i> Appointments
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="prescriptions.php">
                    <i class="fas fa-file-medical"></i> Prescriptions
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="products.php">
                    <i class="fas fa-boxes"></i> Products
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="sales.php">
                    <i class="fas fa-shopping-cart"></i> Sales
                </a>
            </li>
        </ul>
        <div class="p-3 mt-auto" style="position: absolute; bottom: 20px; width: 100%;">
            <a href="logout.php" class="btn btn-logout w-100">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>Dashboard</h2>
                <p class="text-muted mb-0">Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
            </div>
            <div class="text-muted">
                <i class="far fa-calendar-alt"></i> <?php echo date('l, F j, Y'); ?>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stats-card primary">
                    <div class="stats-icon primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stats-number"><?php echo $totalCustomers; ?></div>
                    <div class="stats-label">Total Customers</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card success">
                    <div class="stats-icon success">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stats-number"><?php echo $todayAppointments; ?></div>
                    <div class="stats-label">Today's Appointments</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card warning">
                    <div class="stats-icon warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stats-number"><?php echo $upcomingAppointments; ?></div>
                    <div class="stats-label">Upcoming Appointments</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card danger">
                    <div class="stats-icon danger">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stats-number"><?php echo $lowStock; ?></div>
                    <div class="stats-label">Low Stock Alerts</div>
                </div>
            </div>
        </div>
        
        <div class="row g-4">
            <!-- Recent Appointments -->
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Upcoming Appointments</span>
                        <a href="appointments.php" class="btn btn-sm btn-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Customer</th>
                                        <th>Date & Time</th>
                                        <th>Service</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentAppointments as $apt): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($apt['customer_name']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($apt['phone']); ?></small>
                                        </td>
                                        <td>
                                            <?php echo date('M j, Y', strtotime($apt['appointment_date'])); ?><br>
                                            <small class="text-muted"><?php echo date('g:i A', strtotime($apt['appointment_time'])); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($apt['service_type']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $apt['status'] === 'completed' ? 'success' : 'primary'; ?>">
                                                <?php echo ucfirst($apt['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Low Stock Products -->
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Low Stock Alert</span>
                        <a href="products.php" class="btn btn-sm btn-danger">Restock</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>Stock</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($lowStockProducts as $product): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($product['name']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($product['brand']); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $product['stock'] < 5 ? 'danger' : 'warning'; ?>">
                                                <?php echo $product['stock']; ?> units
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
