<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Farm Monitoring System' ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="/public/assets/css/styles.css">
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
                    <img src="/public/assets/images/poslogo.png" alt="User">
                    <span>Farmer Mwaba</span>
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
                        <a href="/dashboard">
                            <i class="fas fa-chart-line"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="menu-item <?= $activeMenu === 'crops' ? 'active' : '' ?>">
                        <a href="/crops">
                            <i class="fas fa-seedling"></i>
                            <span>Crop Analytics</span>
                        </a>
                    </li>
                    <li class="menu-item <?= $activeMenu === 'irrigation' ? 'active' : '' ?>">
                        <a href="/irrigation">
                            <i class="fas fa-tint"></i>
                            <span>Irrigation</span>
                        </a>
                    </li>
                    <li class="menu-item <?= $activeMenu === 'climate' ? 'active' : '' ?>">
                        <a href="/climate">
                            <i class="fas fa-thermometer-half"></i>
                            <span>Climate Control</span>
                        </a>
                    </li>
                    <li class="menu-item <?= $activeMenu === 'equipment' ? 'active' : '' ?>">
                        <a href="/equipment">
                            <i class="fas fa-cogs"></i>
                            <span>Equipment</span>
                        </a>
                    </li>
                    <li class="menu-item <?= $activeMenu === 'alerts' ? 'active' : '' ?>">
                        <a href="/alerts">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Alerts</span>
                        </a>
                    </li>
                    <li class="menu-item <?= $activeMenu === 'settings' ? 'active' : '' ?>">
                        <a href="/settings">
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

    <script src="/public/assets/js/script.js"></script>
</body>
</html>
