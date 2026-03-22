<?php
require_once __DIR__ . '/auth.php';
requireLogin();
$user        = currentUser();
$currentPath = str_replace('\\', '/', $_SERVER['PHP_SELF']);

function inModule(string $folder, string $current): bool {
    return strpos($current, '/inventory_system/modules/' . $folder . '/') !== false;
}
function isActive(string $href, string $current): bool {
    return rtrim($current, '/') === rtrim('/inventory_system/' . ltrim($href, '/'), '/');
}

$roleLabel = ['admin' => 'Admin', 'manager' => 'Manager', 'inventory_officer' => 'Inventory Officer', 'staff' => 'Staff'][$user['role']] ?? 'User';
$initial   = strtoupper(substr($user['username'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/inventory_system/assets/style/style.css">
    <?= $extraHead ?? '' ?>
</head>
<body>
<div class="wrapper">

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">

        <!-- Brand -->
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <img src="/inventory_system/assets/image/logo.jpg" alt="Logo" style="width:38px;height:38px;object-fit:contain;border-radius:6px;">
            </div>
            <div class="sidebar-brand">
                <h3><?= APP_NAME ?></h3>
                <small><?= $roleLabel ?></small>
            </div>
        </div>

        <!-- Scrollable nav -->
        <div class="sidebar-scroll">
            <ul class="sidebar-nav">

                <!-- ── MAIN ── -->
                <li class="nav-section-label">Main</li>

                <li>
                    <a href="/inventory_system/dashboard.php"
                       class="nav-link <?= isActive('dashboard.php', $currentPath) ? 'active' : '' ?>">
                        <span class="nav-icon"><i class="fas fa-home"></i></span>
                        <span class="nav-label">Dashboard</span>
                    </a>
                </li>

                <!-- ── CATALOG ── -->
                <?php if (in_array($user['role'], ['admin','manager','inventory_officer','staff'])): ?>
                <li class="nav-section-label">Catalog</li>

                <?php $prodOpen = inModule('products', $currentPath) || inModule('categories', $currentPath); ?>
                <li class="has-submenu <?= $prodOpen ? 'open' : '' ?>">
                    <a href="#" class="nav-link nav-parent <?= $prodOpen ? 'active' : '' ?>">
                        <span class="nav-icon"><i class="fas fa-box"></i></span>
                        <span class="nav-label">Products</span>
                        <i class="fas fa-chevron-right nav-arrow"></i>
                    </a>
                    <ul class="submenu">
                        <li>
                            <a href="/inventory_system/modules/products/index.php"
                               class="<?= isActive('modules/products/index.php', $currentPath) ? 'active' : '' ?>">
                                All Products
                            </a>
                        </li>
                        <?php if (in_array($user['role'], ['admin','manager','inventory_officer'])): ?>
                        <li>
                            <a href="/inventory_system/modules/categories/index.php"
                               class="<?= isActive('modules/categories/index.php', $currentPath) ? 'active' : '' ?>">
                                Categories
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- ── STOCK ── -->
                <?php if (in_array($user['role'], ['admin','manager','inventory_officer'])): ?>
                <li class="nav-section-label">Stock</li>

                <?php $invOpen = inModule('inventory', $currentPath); ?>
                <li class="has-submenu <?= $invOpen ? 'open' : '' ?>">
                    <a href="#" class="nav-link nav-parent <?= $invOpen ? 'active' : '' ?>">
                        <span class="nav-icon"><i class="fas fa-warehouse"></i></span>
                        <span class="nav-label">Inventory</span>
                        <i class="fas fa-chevron-right nav-arrow"></i>
                    </a>
                    <ul class="submenu">
                        <li>
                            <a href="/inventory_system/modules/inventory/index.php"
                               class="<?= isActive('modules/inventory/index.php', $currentPath) ? 'active' : '' ?>">
                                Stock Levels
                            </a>
                        </li>
                        <?php if (in_array($user['role'], ['admin','manager','inventory_officer'])): ?>
                        <li>
                            <a href="/inventory_system/modules/inventory/adjust.php"
                               class="<?= isActive('modules/inventory/adjust.php', $currentPath) ? 'active' : '' ?>">
                                Adjustments
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>

                <?php endif; ?>
                <?php if (in_array($user['role'], ['admin','manager','inventory_officer','staff'])): ?>
                <?php $txOpen = inModule('transactions', $currentPath); ?>
                <li class="has-submenu <?= $txOpen ? 'open' : '' ?>">
                    <a href="#" class="nav-link nav-parent <?= $txOpen ? 'active' : '' ?>">
                        <span class="nav-icon"><i class="fas fa-exchange-alt"></i></span>
                        <span class="nav-label">Transactions</span>
                        <i class="fas fa-chevron-right nav-arrow"></i>
                    </a>
                    <ul class="submenu">
                        <li>
                            <a href="/inventory_system/modules/transactions/index.php"
                               class="<?= isActive('modules/transactions/index.php', $currentPath) ? 'active' : '' ?>">
                                All Transactions
                            </a>
                        </li>
                        <li>
                            <a href="/inventory_system/modules/transactions/index.php"
                               class="<?= isActive('modules/transactions/view.php', $currentPath) ? 'active' : '' ?>">
                                View Receipt
                            </a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- ── ANALYTICS ── -->
                <?php if (in_array($user['role'], ['admin','manager','inventory_officer'])): ?>
                <li class="nav-section-label">Analytics</li>

                <?php $repOpen = inModule('reports', $currentPath); ?>
                <li class="has-submenu <?= $repOpen ? 'open' : '' ?>">
                    <a href="#" class="nav-link nav-parent <?= $repOpen ? 'active' : '' ?>">
                        <span class="nav-icon"><i class="fas fa-chart-bar"></i></span>
                        <span class="nav-label">Reports</span>
                        <i class="fas fa-chevron-right nav-arrow"></i>
                    </a>
                    <ul class="submenu">
                        <li>
                            <a href="/inventory_system/modules/reports/index.php"
                               class="<?= isActive('modules/reports/index.php', $currentPath) ? 'active' : '' ?>">
                                Overview
                            </a>
                        </li>
                        <li>
                            <a href="/inventory_system/modules/reports/stock_report.php"
                               class="<?= isActive('modules/reports/stock_report.php', $currentPath) ? 'active' : '' ?>">
                                Stock Report
                            </a>
                        </li>
                        <li>
                            <a href="/inventory_system/modules/reports/transaction_report.php"
                               class="<?= isActive('modules/reports/transaction_report.php', $currentPath) ? 'active' : '' ?>">
                                Transaction Report
                            </a>
                        </li>
                    </ul>
                </li>

                <?php endif; ?>

                <!-- ── ADMIN ── -->
                <?php if ($user['role'] === 'admin'): ?>
                <li class="nav-section-label">Admin</li>

                <?php $usrOpen = inModule('users', $currentPath); ?>
                <li class="has-submenu <?= $usrOpen ? 'open' : '' ?>">
                    <a href="#" class="nav-link nav-parent <?= $usrOpen ? 'active' : '' ?>">
                        <span class="nav-icon"><i class="fas fa-users"></i></span>
                        <span class="nav-label">Users</span>
                        <i class="fas fa-chevron-right nav-arrow"></i>
                    </a>
                    <ul class="submenu">
                        <li>
                            <a href="/inventory_system/modules/users/index.php"
                               class="<?= isActive('modules/users/index.php', $currentPath) ? 'active' : '' ?>">
                                All Users
                            </a>
                        </li>
                        <li>
                            <a href="/inventory_system/modules/users/profile.php"
                               class="<?= isActive('modules/users/profile.php', $currentPath) ? 'active' : '' ?>">
                                My Profile
                            </a>
                        </li>
                    </ul>
                </li>

                <li>
                    <a href="/inventory_system/modules/audit/index.php"
                       class="nav-link <?= isActive('modules/audit/index.php', $currentPath) ? 'active' : '' ?>">
                        <span class="nav-icon"><i class="fas fa-shield-alt"></i></span>
                        <span class="nav-label">Audit Logs</span>
                    </a>
                </li>
                <?php endif; ?>

            </ul>
        </div><!-- /.sidebar-scroll -->

        <!-- Footer -->
        <div class="sidebar-footer">
            <?php if ($user['role'] !== 'admin'): ?>
            <a href="/inventory_system/modules/users/profile.php">
                <span class="nav-icon"><i class="fas fa-user-circle"></i></span>
                My Profile
            </a>
            <?php endif; ?>
            <a href="/inventory_system/logout.php">
                <span class="nav-icon"><i class="fas fa-sign-out-alt"></i></span>
                Logout
            </a>
        </div>

    </aside><!-- /.sidebar -->

    <!-- Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- ══════════════ MAIN ══════════════ -->
    <div class="main-content">

        <!-- Topbar -->
        <header class="topbar">
            <div class="topbar-left">
                <button class="hamburger" id="hamburgerBtn" aria-label="Toggle menu">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="topbar-title">
                    <i class="fas fa-<?= $pageIcon ?? 'home' ?>"></i>
                    <?= htmlspecialchars($pageTitle ?? 'Dashboard') ?>
                </div>
            </div>
            <div class="topbar-right">
                <div class="topbar-user-info">
                    <div class="t-name"><?= htmlspecialchars($user['username']) ?></div>
                    <div class="t-role"><?= $roleLabel ?></div>
                </div>
                <div class="user-avatar"><?= $initial ?></div>
            </div>
        </header>

        <div class="content">
