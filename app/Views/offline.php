<?php
$pageTitle = 'Offline - Farm Monitoring System';
ob_start();
?>

<div class="offline-container">
    <div class="offline-content">
        <div class="offline-icon">
            <i class="fas fa-wifi-slash"></i>
        </div>
        
        <h1>You're Offline</h1>
        
        <p>It looks like you've lost your internet connection. Don't worry - you can still view cached data and use some features.</p>
        
        <div class="offline-features">
            <div class="feature-item">
                <i class="fas fa-chart-line"></i>
                <span>View cached analytics</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-thermometer-half"></i>
                <span>Check recent sensor data</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-mobile-alt"></i>
                <span>Use offline features</span>
            </div>
        </div>
        
        <div class="offline-actions">
            <button class="btn btn-primary" onclick="window.location.reload()">
                <i class="fas fa-sync-alt"></i> Try Again
            </button>
            <button class="btn btn-secondary" onclick="goToDashboard()">
                <i class="fas fa-home"></i> Go to Dashboard
            </button>
        </div>
        
        <div class="connection-status">
            <div class="status-indicator offline"></div>
            <span>Connection Status: Offline</span>
        </div>
        
        <div class="offline-tips">
            <h3>While you're offline:</h3>
            <ul>
                <li>Check your internet connection</li>
                <li>Try refreshing the page</li>
                <li>View cached sensor data</li>
                <li>Access offline analytics</li>
            </ul>
        </div>
    </div>
</div>

<style>
.offline-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 20px;
}

.offline-content {
    background: white;
    border-radius: 20px;
    padding: 40px;
    text-align: center;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    max-width: 500px;
    width: 100%;
}

.offline-icon {
    font-size: 80px;
    color: #e74c3c;
    margin-bottom: 20px;
}

.offline-content h1 {
    color: #2c3e50;
    margin-bottom: 20px;
    font-size: 32px;
}

.offline-content p {
    color: #666;
    font-size: 16px;
    line-height: 1.6;
    margin-bottom: 30px;
}

.offline-features {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.feature-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 12px;
    transition: transform 0.3s ease;
}

.feature-item:hover {
    transform: translateY(-2px);
}

.feature-item i {
    font-size: 24px;
    color: #4CAF50;
}

.feature-item span {
    font-size: 14px;
    color: #333;
    font-weight: 600;
}

.offline-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-bottom: 30px;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    text-decoration: none;
}

.btn-primary {
    background: #4CAF50;
    color: white;
}

.btn-primary:hover {
    background: #45a049;
    transform: translateY(-2px);
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
    transform: translateY(-2px);
}

.connection-status {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin-bottom: 30px;
    padding: 15px;
    background: #fff3cd;
    border-radius: 8px;
    border-left: 4px solid #ffc107;
}

.status-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

.status-indicator.offline {
    background: #e74c3c;
}

.status-indicator.online {
    background: #27ae60;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.offline-tips {
    text-align: left;
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
}

.offline-tips h3 {
    color: #333;
    margin-bottom: 15px;
    font-size: 18px;
}

.offline-tips ul {
    list-style: none;
    padding: 0;
}

.offline-tips li {
    padding: 8px 0;
    color: #666;
    position: relative;
    padding-left: 25px;
}

.offline-tips li:before {
    content: '✓';
    position: absolute;
    left: 0;
    color: #4CAF50;
    font-weight: bold;
}

@media (max-width: 768px) {
    .offline-content {
        padding: 30px 20px;
    }
    
    .offline-actions {
        flex-direction: column;
    }
    
    .offline-features {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function goToDashboard() {
    // Try to go to dashboard, fallback to home if offline
    if (navigator.onLine) {
        window.location.href = `${window.BASE_PATH}/dashboard`;
    } else {
        window.location.href = `${window.BASE_PATH}/`;
    }
}

// Monitor connection status
function updateConnectionStatus() {
    const statusElement = document.querySelector('.connection-status span');
    const indicator = document.querySelector('.status-indicator');
    
    if (navigator.onLine) {
        statusElement.textContent = 'Connection Status: Online';
        indicator.className = 'status-indicator online';
    } else {
        statusElement.textContent = 'Connection Status: Offline';
        indicator.className = 'status-indicator offline';
    }
}

// Listen for connection changes
window.addEventListener('online', updateConnectionStatus);
window.addEventListener('offline', updateConnectionStatus);

// Initial status check
updateConnectionStatus();

// Auto-retry when connection is restored
window.addEventListener('online', function() {
    setTimeout(() => {
        if (confirm('Connection restored! Would you like to refresh the page?')) {
            window.location.reload();
        }
    }, 1000);
});
</script>

<?php
$content = ob_get_clean();

// Include the main layout
include __DIR__ . '/layouts/main.php';
?>
