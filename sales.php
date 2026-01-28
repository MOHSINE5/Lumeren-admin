<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: index.php');
    exit;
}

$pdo = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        // Reduce stock when making a sale
        $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $stmt->execute([$_POST['quantity'], $_POST['product_id']]);
        
        $stmt = $pdo->prepare("INSERT INTO sales (customer_id, product_id, quantity, total_price, sale_date, payment_method) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['customer_id'], $_POST['product_id'], $_POST['quantity'], $_POST['total_price'], $_POST['sale_date'], $_POST['payment_method']]);
        $success = "Sale recorded successfully!";
    }
}

$sales = $pdo->query("
    SELECT s.*, c.name as customer_name, p.name as product_name 
    FROM sales s 
    JOIN customers c ON s.customer_id = c.id 
    JOIN products p ON s.product_id = p.id 
    ORDER BY s.sale_date DESC, s.created_at DESC
    LIMIT 50
")->fetchAll();

$customers = $pdo->query("SELECT id, name FROM customers ORDER BY name")->fetchAll();
$products = $pdo->query("SELECT id, name, price, stock FROM products WHERE stock > 0 ORDER BY name")->fetchAll();

$totalSales = $pdo->query("SELECT COALESCE(SUM(total_price), 0) FROM sales")->fetchColumn();
$todaySales = $pdo->query("SELECT COALESCE(SUM(total_price), 0) FROM sales WHERE sale_date = CURDATE()")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales - VisionCare Admin</title>
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
        .stats-card { background: white; border-radius: 15px; padding: 1.5rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .stats-number { font-size: 2rem; font-weight: 700; color: #48bb78; }
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
            <li class="nav-item"><a class="nav-link" href="prescriptions.php"><i class="fas fa-file-medical"></i> Prescriptions</a></li>
            <li class="nav-item"><a class="nav-link" href="products.php"><i class="fas fa-boxes"></i> Products</a></li>
            <li class="nav-item"><a class="nav-link active" href="sales.php"><i class="fas fa-shopping-cart"></i> Sales</a></li>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Sales</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fas fa-plus"></i> New Sale
            </button>
        </div>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show"><?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="stats-card">
                    <h6 class="text-muted mb-2">Today's Sales</h6>
                    <div class="stats-number">$<?php echo number_format($todaySales, 2); ?></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stats-card">
                    <h6 class="text-muted mb-2">Total Sales</h6>
                    <div class="stats-number">$<?php echo number_format($totalSales, 2); ?></div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Total</th>
                                <th>Payment</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sales as $sale): ?>
                            <tr>
                                <td><?php echo date('M j, Y', strtotime($sale['sale_date'])); ?></td>
                                <td><strong><?php echo htmlspecialchars($sale['customer_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($sale['product_name']); ?></td>
                                <td><?php echo $sale['quantity']; ?></td>
                                <td><strong>$<?php echo number_format($sale['total_price'], 2); ?></strong></td>
                                <td><?php echo htmlspecialchars($sale['payment_method']); ?></td>
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
                    <h5 class="modal-title">New Sale</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="saleForm">
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
                            <label class="form-label">Product</label>
                            <select class="form-select" name="product_id" id="product_select" required onchange="updatePrice()">
                                <option value="">Select Product</option>
                                <?php foreach ($products as $p): ?>
                                <option value="<?php echo $p['id']; ?>" data-price="<?php echo $p['price']; ?>" data-stock="<?php echo $p['stock']; ?>">
                                    <?php echo htmlspecialchars($p['name']); ?> - $<?php echo number_format($p['price'], 2); ?> (Stock: <?php echo $p['stock']; ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Quantity</label>
                            <input type="number" class="form-control" name="quantity" id="quantity" min="1" value="1" required onchange="updatePrice()">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Total Price</label>
                            <input type="number" step="0.01" class="form-control" name="total_price" id="total_price" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <select class="form-select" name="payment_method" required>
                                <option value="Cash">Cash</option>
                                <option value="Credit Card">Credit Card</option>
                                <option value="Debit Card">Debit Card</option>
                                <option value="Insurance">Insurance</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sale Date</label>
                            <input type="date" class="form-control" name="sale_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Record Sale</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updatePrice() {
            const select = document.getElementById('product_select');
            const quantity = document.getElementById('quantity').value;
            const option = select.options[select.selectedIndex];
            
            if (option.value) {
                const price = parseFloat(option.dataset.price);
                const stock = parseInt(option.dataset.stock);
                const total = price * quantity;
                
                if (quantity > stock) {
                    alert('Quantity exceeds available stock!');
                    document.getElementById('quantity').value = stock;
                    document.getElementById('total_price').value = (price * stock).toFixed(2);
                } else {
                    document.getElementById('total_price').value = total.toFixed(2);
                }
            }
        }
    </script>
</body>
</html>
