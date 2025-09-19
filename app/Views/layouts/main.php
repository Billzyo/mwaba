<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Farm Monitoring System' ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="http://localhost:8000/public/assets/css/styles.css">
</head>
<body>
    <div class="container">
        <div class="dashboard-header">
            <div class="logo">
                <i class="fas fa-tractor"></i>
                <h1>SHANTUKA FARM MONITOR</h1>
            </div>
            <div class="header-controls">
                <i class="fas fa-search"></i>
                <i class="fas fa-bell"></i>
                <i class="fas fa-envelope"></i>
                <div class="user-profile">
                    <img src="http://localhost:8000/public/assets/images/poslogo.png" alt="User">
                    <div class="user-info">
                        <span><?= htmlspecialchars($currentUser['full_name'] ?? 'Farmer Mwaba') ?></span>
                        <small><?= htmlspecialchars($currentUser['role'] ?? 'user') ?></small>
                    </div>
                    <div class="user-menu">
                        <a href="/mwaba/logout" class="logout-btn" title="Logout">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    </div>
                </div>
                <div class="mobile-menu-btn">
                    <i class="fas fa-bars"></i>
                </div>
            </div>
        </div>

        <div class="dashboard-content">
            <aside class="sidebar">
                <div class="sidebar-title">
                    <h2><i class="fas fa-leaf"></i> Farm Controls</h2>
                </div>
                <ul class="sidebar-menu">
                    <li class="menu-item <?= $activeMenu === 'dashboard' ? 'active' : '' ?>">
                        <a href="/mwaba/dashboard">
                            <i class="fas fa-chart-line"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="menu-item <?= $activeMenu === 'crops' ? 'active' : '' ?>">
                        <a href="/mwaba/crops">
                            <i class="fas fa-seedling"></i>
                            <span>Crop Analytics</span>
                        </a>
                    </li>
                    <li class="menu-item <?= $activeMenu === 'irrigation' ? 'active' : '' ?>">
                        <a href="/mwaba/irrigation">
                            <i class="fas fa-tint"></i>
                            <span>Irrigation</span>
                        </a>
                    </li>
                    <li class="menu-item <?= $activeMenu === 'climate' ? 'active' : '' ?>">
                        <a href="/mwaba/climate">
                            <i class="fas fa-thermometer-half"></i>
                            <span>Climate Control</span>
                        </a>
                    </li>
                    <li class="menu-item <?= $activeMenu === 'equipment' ? 'active' : '' ?>">
                        <a href="/mwaba/equipment">
                            <i class="fas fa-cogs"></i>
                            <span>Equipment</span>
                        </a>
                    </li>
                    <?php if (isset($currentUser) && $currentUser['role'] === 'admin'): ?>
                    <li class="menu-item <?= $activeMenu === 'users' ? 'active' : '' ?>">
                        <a href="/mwaba/users">
                            <i class="fas fa-users"></i>
                            <span>User Management</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="menu-item <?= $activeMenu === 'alerts' ? 'active' : '' ?>">
                        <a href="/mwaba/alerts">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Alerts</span>
                        </a>
                    </li>
                    <li class="menu-item <?= $activeMenu === 'settings' ? 'active' : '' ?>">
                        <a href="/mwaba/settings">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                    </li>
                </ul>
            </aside>

            <main class="main-content">
                <?= $content ?>
            </main>
        </div>
    </div>

    <script src="http://localhost:8000/public/assets/js/script.js"></script>
</body>
</html>
