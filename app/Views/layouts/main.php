<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Farm Monitoring System' ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="public/assets/css/styles.css">
    
    <!-- PWA Meta Tags -->
    <link rel="manifest" href="public/manifest.json">
    <meta name="theme-color" content="#4CAF50">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Farm Monitor">
    <link rel="apple-touch-icon" href="public/assets/images/icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="public/assets/images/icon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="public/assets/images/icon-16x16.png">
    <style>
        /* Real-time Features CSS */
        .header-controls {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .connection-status {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .connection-status.status-connected {
            background: #d4edda;
            color: #155724;
        }
        
        .connection-status.status-disconnected,
        .connection-status.status-error,
        .connection-status.status-failed {
            background: #f8d7da;
            color: #721c24;
        }
        
        .connection-status.status-reconnecting {
            background: #fff3cd;
            color: #856404;
        }
        
        .alert-container {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 1000;
            max-width: 400px;
        }
        
        .alert {
            margin-bottom: 10px;
            padding: 12px 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            animation: slideIn 0.3s ease-out;
        }
        
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-dismissible {
            position: relative;
            padding-right: 40px;
        }
        
        .alert .close {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            opacity: 0.7;
        }
        
        .alert .close:hover {
            opacity: 1;
        }
        
        .sensor-status {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.8rem;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 12px;
        }
        
        .sensor-status.status-normal {
            background: #d4edda;
            color: #155724;
        }
        
        .sensor-status.status-low {
            background: #fff3cd;
            color: #856404;
        }
        
        .sensor-status.status-high {
            background: #f8d7da;
            color: #721c24;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8rem;
        }
        
        .realtime-charts {
            margin-top: 30px;
        }
        
        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
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
                    <img src="public/assets/images/poslogo.png" alt="User">
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
            <li class="menu-item <?= $activeMenu === 'analytics' ? 'active' : '' ?>">
                <a href="/mwaba/analytics">
                    <i class="fas fa-chart-line"></i>
                    <span>Analytics</span>
                </a>
            </li>
                    <?php if (isset($currentUser) && $currentUser['role'] === 'admin'): ?>
                    <li class="menu-item <?= $activeMenu === 'performance' ? 'active' : '' ?>">
                        <a href="/mwaba/performance">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Performance</span>
                        </a>
                    </li>
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

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="public/assets/js/script.js"></script>
    <script src="public/assets/js/realtime.js"></script>
    
    <!-- PWA Service Worker Registration -->
    <script>
        // Register Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('public/sw.js')
                    .then(function(registration) {
                        console.log('Service Worker registered successfully:', registration.scope);
                        
                        // Handle updates
                        registration.addEventListener('updatefound', function() {
                            const newWorker = registration.installing;
                            newWorker.addEventListener('statechange', function() {
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    // New update available
                                    showUpdateNotification();
                                }
                            });
                        });
                    })
                    .catch(function(error) {
                        console.log('Service Worker registration failed:', error);
                    });
            });
        }
        
        // PWA Install Prompt
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', function(e) {
            e.preventDefault();
            deferredPrompt = e;
            showInstallButton();
        });
        
        // Show install button
        function showInstallButton() {
            const installButton = document.createElement('button');
            installButton.innerHTML = '<i class="fas fa-download"></i> Install App';
            installButton.className = 'btn btn-primary install-btn';
            installButton.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                z-index: 1000;
                background: #4CAF50;
                color: white;
                border: none;
                padding: 12px 20px;
                border-radius: 25px;
                font-weight: 600;
                box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
                cursor: pointer;
                animation: slideInUp 0.3s ease-out;
            `;
            
            installButton.addEventListener('click', installApp);
            document.body.appendChild(installButton);
            
            // Auto-hide after 10 seconds
            setTimeout(() => {
                if (installButton.parentNode) {
                    installButton.remove();
                }
            }, 10000);
        }
        
        // Install app
        function installApp() {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then(function(choiceResult) {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('User accepted the install prompt');
                    } else {
                        console.log('User dismissed the install prompt');
                    }
                    deferredPrompt = null;
                });
            }
        }
        
        // Show update notification
        function showUpdateNotification() {
            const updateNotification = document.createElement('div');
            updateNotification.innerHTML = `
                <div style="
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background: white;
                    padding: 20px;
                    border-radius: 12px;
                    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
                    z-index: 1000;
                    max-width: 300px;
                    animation: slideInDown 0.3s ease-out;
                ">
                    <h4 style="margin: 0 0 10px 0; color: #333;">Update Available</h4>
                    <p style="margin: 0 0 15px 0; color: #666; font-size: 14px;">
                        A new version of the app is available.
                    </p>
                    <div style="display: flex; gap: 10px;">
                        <button onclick="updateApp()" style="
                            background: #4CAF50;
                            color: white;
                            border: none;
                            padding: 8px 16px;
                            border-radius: 6px;
                            font-size: 14px;
                            cursor: pointer;
                        ">Update</button>
                        <button onclick="this.parentElement.parentElement.parentElement.remove()" style="
                            background: #6c757d;
                            color: white;
                            border: none;
                            padding: 8px 16px;
                            border-radius: 6px;
                            font-size: 14px;
                            cursor: pointer;
                        ">Later</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(updateNotification);
        }
        
        // Update app
        function updateApp() {
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.getRegistration().then(function(registration) {
                    if (registration && registration.waiting) {
                        registration.waiting.postMessage({ type: 'SKIP_WAITING' });
                        window.location.reload();
                    }
                });
            }
        }
        
        // Add CSS animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInUp {
                from {
                    transform: translateY(100px);
                    opacity: 0;
                }
                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideInDown {
                from {
                    transform: translateY(-100px);
                    opacity: 0;
                }
                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }
            
            .install-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 16px rgba(76, 175, 80, 0.4);
            }
        `;
        document.head.appendChild(style);
        
        // Handle app installed event
        window.addEventListener('appinstalled', function(event) {
            console.log('PWA was installed');
            // Hide install button if visible
            const installBtn = document.querySelector('.install-btn');
            if (installBtn) {
                installBtn.remove();
            }
        });
        
        // Connection status monitoring
        function updateConnectionStatus() {
            const isOnline = navigator.onLine;
            const statusIndicator = document.querySelector('.connection-status');
            
            if (statusIndicator) {
                if (isOnline) {
                    statusIndicator.className = 'connection-status status-online';
                    statusIndicator.innerHTML = '🟢 Online';
                } else {
                    statusIndicator.className = 'connection-status status-offline';
                    statusIndicator.innerHTML = '🔴 Offline';
                }
            }
        }
        
        // Listen for connection changes
        window.addEventListener('online', updateConnectionStatus);
        window.addEventListener('offline', updateConnectionStatus);
        
        // Initial status check
        updateConnectionStatus();
    </script>
</body>
</html>
