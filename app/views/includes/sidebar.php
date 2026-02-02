


<link rel="stylesheet" href="../../../public/css/admin-dashboard.css">

<!-- Sidebar -->
<nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="#" class="sidebar-brand">
                <i class="bi bi-shop"></i>
                <span class="brand-text">Empire - Shop</span>
            </a>
        </div>
        
        <ul class="sidebar-nav nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="#dashboard" data-bs-target="dashboard-section">
                    <i class="bi bi-speedometer2"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#products" data-bs-target="products-section">
                    <i class="bi bi-box"></i>
                    <span class="nav-text">Products</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#category" data-bs-target="category-section">
                    <i class="bi bi-tags"></i>
                    <span class="nav-text">Categories</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#orders" data-bs-target="orders-section">
                    <i class="bi bi-receipt"></i>
                    <span class="nav-text">Orders</span>
                </a>
            </li>
                <li class="nav-item">
                    <a class="nav-link" href="#customers" data-bs-target="customers-section">
                        <i class="bi bi-people"></i>
                        <span class="nav-text">Customers</span>
                    </a>
                </li>

                
             <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?> 
            <li class="nav-item">
                <a class="nav-link" href="#analytics" data-bs-target="analytics-section">
                    <i class="bi bi-graph-up"></i>
                    <span class="nav-text">Analytics</span>
                </a>
            </li>
            <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link" href="#inventory" data-bs-target="inventory-section">
                    <i class="bi bi-boxes"></i>
                    <span class="nav-text">Inventory</span>
                </a>
            </li>
           <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?> 
<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#systemSettings">
        <i class="bi bi-gear"></i>
        <span class="nav-text">System Settings</span>
        <i class="bi bi-chevron-down ms-auto ms-2"></i>
    </a>
    <div id="systemSettings" class="collapse" data-bs-parent="#sidebar">
        <ul class="nav flex-column ms-3 mt-1">
            <li class="nav-item">
                <a class="nav-link py-1" href="#maintenance" data-bs-target="maintenance-section">
                    <i class="bi bi-tools me-2"></i> Maintenance
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-1" href="#rbac" data-bs-target="rbac-section">
                    <i class="bi bi-shield-lock me-2"></i> RBAC
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-1" href="#" data-bs-target="backup-section">
                    <i class="bi bi-database me-2"></i> Database Backup
                </a>
            </li>
        </ul>
    </div>
</li>
<?php endif; ?>
        </ul>
    </nav>
    