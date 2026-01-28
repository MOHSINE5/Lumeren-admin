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
            $stmt = $pdo->prepare("INSERT INTO products (name, category, brand, price, stock, description) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_POST['name'], $_POST['category'], $_POST['brand'], $_POST['price'], $_POST['stock'], $_POST['description']]);
            $success = "Product added successfully!";
        } elseif ($_POST['action'] === 'edit') {
            $stmt = $pdo->prepare("UPDATE products SET name=?, category=?, brand=?, price=?, stock=?, description=? WHERE id=?");
            $stmt->execute([$_POST['name'], $_POST['category'], $_POST['brand'], $_POST['price'], $_POST['stock'], $_POST['description'], $_POST['id']]);
            $success = "Product updated successfully!";
        } elseif ($_POST['action'] === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM products WHERE id=?");
            $stmt->execute([$_POST['id']]);
            $success = "Product deleted successfully!";
        }
    }
}

$products = $pdo->query("SELECT * FROM products ORDER BY category, name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - VisionCare Admin</title>
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
            <li class="nav-item"><a class="nav-link active" href="products.php"><i class="fas fa-boxes"></i> Products</a></li>
            <li class="nav-item"><a class="nav-link" href="sales.php"><i class="fas fa-shopping-cart"></i> Sales</a></li>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Products Inventory</h2>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fas fa-plus"></i> Add Product
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
                                <th>Name</th>
                                <th>Category</th>
                                <th>Brand</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $p): ?>
                            <tr class="<?php echo $p['stock'] < 10 ? 'table-warning' : ''; ?>">
                                <td><strong><?php echo htmlspecialchars($p['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($p['category']); ?></td>
                                <td><?php echo htmlspecialchars($p['brand']); ?></td>
                                <td>$<?php echo number_format($p['price'], 2); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $p['stock'] < 5 ? 'danger' : ($p['stock'] < 10 ? 'warning' : 'success'); ?>">
                                        <?php echo $p['stock']; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick='editItem(<?php echo json_encode($p); ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteItem(<?php echo $p['id']; ?>)">
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
                    <h5 class="modal-title">Add Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category" required>
                                <option value="">Select Category</option>
                                <option>Frames</option>
                                <option>Lenses</option>
                                <option>Contact Lenses</option>
                                <option>Sunglasses</option>
                                <option>Accessories</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Brand</label>
                            <input type="text" class="form-control" name="brand" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price</label>
                            <input type="number" step="0.01" class="form-control" name="price" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Stock</label>
                            <input type="number" class="form-control" name="stock" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Add Product</button>
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
                    <h5 class="modal-title">Edit Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" id="edit_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <input type="text" class="form-control" name="category" id="edit_category" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Brand</label>
                            <input type="text" class="form-control" name="brand" id="edit_brand" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price</label>
                            <input type="number" step="0.01" class="form-control" name="price" id="edit_price" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Stock</label>
                            <input type="number" class="form-control" name="stock" id="edit_stock" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondar" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Update Product</button>
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
            document.getElementById('edit_name').value = item.name;
            document.getElementById('edit_category').value = item.category;
            document.getElementById('edit_brand').value = item.brand;
            document.getElementById('edit_price').value = item.price;
            document.getElementById('edit_stock').value = item.stock;
            document.getElementById('edit_description').value = item.description || '';
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }
        
        function deleteItem(id) {
            if (confirm('Delete this product?')) {
                document.getElementById('delete_id').value = id;
                document.getElementById('deleteForm').submit();
            }
        }
    </script>
</body>
</html>
