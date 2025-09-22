<?php
$activeMenu = 'performance';
ob_start();
?>

<div class="content-header">
    <h2><i class="fas fa-tachometer-alt"></i> Performance Monitor</h2>
    <div class="header-controls">
        <button class="btn btn-warning" onclick="clearCache()">
            <i class="fas fa-trash"></i> Clear Cache
        </button>
        <button class="btn btn-secondary" onclick="refreshMetrics()">
            <i class="fas fa-sync-alt"></i> Refresh
        </button>
        <div class="auto-refresh">
            <label>
                <input type="checkbox" id="autoRefresh" onchange="toggleAutoRefresh()">
                Auto Refresh (30s)
            </label>
        </div>
    </div>
</div>

<!-- Performance Overview Cards -->
<div class="metrics-grid">
    <div class="metric-card cpu">
        <div class="metric-icon">
            <i class="fas fa-microchip"></i>
        </div>
        <div class="metric-content">
            <h3>CPU Usage</h3>
            <div class="metric-value" id="cpu-usage">
                <span class="value">0%</span>
                <div class="progress-bar">
                    <div class="progress-fill" id="cpu-progress"></div>
                </div>
            </div>
            <div class="metric-subtitle">System Load</div>
        </div>
    </div>
    
    <div class="metric-card memory">
        <div class="metric-icon">
            <i class="fas fa-memory"></i>
        </div>
        <div class="metric-content">
            <h3>Memory Usage</h3>
            <div class="metric-value" id="memory-usage">
                <span class="value">0 MB</span>
                <div class="progress-bar">
                    <div class="progress-fill" id="memory-progress"></div>
                </div>
            </div>
            <div class="metric-subtitle">PHP Memory</div>
        </div>
    </div>
    
    <div class="metric-card disk">
        <div class="metric-icon">
            <i class="fas fa-hdd"></i>
        </div>
        <div class="metric-content">
            <h3>Disk Usage</h3>
            <div class="metric-value" id="disk-usage">
                <span class="value">0%</span>
                <div class="progress-bar">
                    <div class="progress-fill" id="disk-progress"></div>
                </div>
            </div>
            <div class="metric-subtitle">Storage</div>
        </div>
    </div>
    
    <div class="metric-card cache">
        <div class="metric-icon">
            <i class="fas fa-database"></i>
        </div>
        <div class="metric-content">
            <h3>Cache Hit Rate</h3>
            <div class="metric-value" id="cache-hit-rate">
                <span class="value">0%</span>
                <div class="progress-bar">
                    <div class="progress-fill" id="cache-progress"></div>
                </div>
            </div>
            <div class="metric-subtitle">Performance</div>
        </div>
    </div>
</div>

<!-- Performance Tabs -->
<div class="performance-tabs">
    <div class="tab-nav">
        <button class="tab-btn active" onclick="switchTab('system')">
            <i class="fas fa-server"></i> System
        </button>
        <button class="tab-btn" onclick="switchTab('database')">
            <i class="fas fa-database"></i> Database
        </button>
        <button class="tab-btn" onclick="switchTab('api')">
            <i class="fas fa-code"></i> API
        </button>
        <button class="tab-btn" onclick="switchTab('cache')">
            <i class="fas fa-memory"></i> Cache
        </button>
    </div>
    
    <!-- System Tab -->
    <div id="system-tab" class="tab-content active">
        <div class="charts-grid">
            <div class="chart-container">
                <h3>CPU Usage Over Time</h3>
                <canvas id="cpu-chart"></canvas>
            </div>
            
            <div class="chart-container">
                <h3>Memory Usage Over Time</h3>
                <canvas id="memory-chart"></canvas>
            </div>
            
            <div class="chart-container">
                <h3>Load Average</h3>
                <canvas id="load-chart"></canvas>
            </div>
            
            <div class="chart-container">
                <h3>System Information</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="label">PHP Version:</span>
                        <span class="value" id="php-version"><?= PHP_VERSION ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">Memory Limit:</span>
                        <span class="value" id="memory-limit"><?= ini_get('memory_limit') ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">Max Execution Time:</span>
                        <span class="value" id="execution-time"><?= ini_get('max_execution_time') ?>s</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Server Uptime:</span>
                        <span class="value" id="uptime">Unknown</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Database Tab -->
    <div id="database-tab" class="tab-content">
        <div class="charts-grid">
            <div class="chart-container">
                <h3>Query Performance</h3>
                <canvas id="query-chart"></canvas>
            </div>
            
            <div class="chart-container">
                <h3>Slow Queries</h3>
                <div class="slow-queries" id="slow-queries">
                    <!-- Slow queries will be populated here -->
                </div>
            </div>
            
            <div class="chart-container">
                <h3>Database Statistics</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="label">Active Connections:</span>
                        <span class="value" id="db-connections">1</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Queries/Second:</span>
                        <span class="value" id="queries-per-second">25</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Cache Hit Ratio:</span>
                        <span class="value" id="db-cache-ratio">95%</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Slow Queries:</span>
                        <span class="value" id="slow-query-count">2</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- API Tab -->
    <div id="api-tab" class="tab-content">
        <div class="charts-grid">
            <div class="chart-container">
                <h3>API Response Times</h3>
                <canvas id="api-response-chart"></canvas>
            </div>
            
            <div class="chart-container">
                <h3>Request Volume</h3>
                <canvas id="api-volume-chart"></canvas>
            </div>
            
            <div class="chart-container">
                <h3>Error Rate</h3>
                <canvas id="error-rate-chart"></canvas>
            </div>
            
            <div class="chart-container">
                <h3>Endpoint Performance</h3>
                <div class="endpoint-list" id="endpoint-performance">
                    <!-- Endpoint performance will be populated here -->
                </div>
            </div>
        </div>
    </div>
    
    <!-- Cache Tab -->
    <div id="cache-tab" class="tab-content">
        <div class="charts-grid">
            <div class="chart-container">
                <h3>Cache Hit Ratio</h3>
                <canvas id="cache-hit-chart"></canvas>
            </div>
            
            <div class="chart-container">
                <h3>Cache Size</h3>
                <canvas id="cache-size-chart"></canvas>
            </div>
            
            <div class="chart-container">
                <h3>Cache Statistics</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="label">Driver:</span>
                        <span class="value" id="cache-driver">File</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Total Items:</span>
                        <span class="value" id="cache-items">0</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Total Size:</span>
                        <span class="value" id="cache-size">0 KB</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Expired Items:</span>
                        <span class="value" id="cache-expired">0</span>
                    </div>
                </div>
            </div>
            
            <div class="chart-container">
                <h3>Cache Actions</h3>
                <div class="cache-actions">
                    <button class="btn btn-primary" onclick="clearCache()">
                        <i class="fas fa-trash"></i> Clear All Cache
                    </button>
                    <button class="btn btn-secondary" onclick="warmCache()">
                        <i class="fas fa-fire"></i> Warm Cache
                    </button>
                    <button class="btn btn-info" onclick="exportCacheStats()">
                        <i class="fas fa-download"></i> Export Stats
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Performance Monitor Styles */
.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.metric-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 15px;
    transition: transform 0.3s ease;
}

.metric-card:hover {
    transform: translateY(-2px);
}

.metric-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
}

.metric-card.cpu .metric-icon { background: linear-gradient(135deg, #e74c3c, #c0392b); }
.metric-card.memory .metric-icon { background: linear-gradient(135deg, #3498db, #2980b9); }
.metric-card.disk .metric-icon { background: linear-gradient(135deg, #f39c12, #e67e22); }
.metric-card.cache .metric-icon { background: linear-gradient(135deg, #9b59b6, #8e44ad); }

.metric-content h3 {
    margin: 0 0 8px 0;
    color: #333;
    font-size: 14px;
    font-weight: 600;
}

.metric-value {
    font-size: 18px;
    font-weight: bold;
    color: #2c3e50;
    margin-bottom: 8px;
}

.progress-bar {
    width: 100%;
    height: 6px;
    background: #ecf0f1;
    border-radius: 3px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #27ae60, #2ecc71);
    transition: width 0.3s ease;
    width: 0%;
}

.metric-subtitle {
    font-size: 12px;
    color: #666;
}

.performance-tabs {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
}

.tab-nav {
    display: flex;
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.tab-btn {
    flex: 1;
    padding: 15px 20px;
    border: none;
    background: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    color: #666;
    transition: all 0.3s ease;
}

.tab-btn.active {
    background: white;
    color: #4CAF50;
    border-bottom: 3px solid #4CAF50;
}

.tab-btn:hover {
    background: #e9ecef;
}

.tab-content {
    display: none;
    padding: 30px;
}

.tab-content.active {
    display: block;
}

.charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
}

.chart-container {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
}

.chart-container h3 {
    margin: 0 0 20px 0;
    color: #333;
    font-size: 18px;
}

.info-grid {
    display: grid;
    gap: 15px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    background: white;
    border-radius: 6px;
    border-left: 4px solid #4CAF50;
}

.info-item .label {
    font-weight: 600;
    color: #333;
}

.info-item .value {
    color: #666;
    font-family: monospace;
}

.slow-queries {
    max-height: 300px;
    overflow-y: auto;
}

.slow-query-item {
    padding: 12px;
    margin-bottom: 10px;
    background: white;
    border-radius: 6px;
    border-left: 4px solid #e74c3c;
}

.slow-query-item .query {
    font-family: monospace;
    font-size: 12px;
    color: #333;
    margin-bottom: 5px;
    word-break: break-all;
}

.slow-query-item .stats {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    color: #666;
}

.endpoint-list {
    max-height: 300px;
    overflow-y: auto;
}

.endpoint-item {
    padding: 12px;
    margin-bottom: 10px;
    background: white;
    border-radius: 6px;
    border-left: 4px solid #3498db;
}

.endpoint-item .endpoint {
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
}

.endpoint-item .stats {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    color: #666;
}

.cache-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.cache-actions .btn {
    width: 100%;
    justify-content: center;
}

.header-controls {
    display: flex;
    align-items: center;
    gap: 15px;
}

.auto-refresh {
    display: flex;
    align-items: center;
    gap: 8px;
}

.auto-refresh label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #666;
    cursor: pointer;
}

@media (max-width: 768px) {
    .charts-grid {
        grid-template-columns: 1fr;
    }
    
    .metrics-grid {
        grid-template-columns: 1fr;
    }
    
    .header-controls {
        flex-direction: column;
        gap: 10px;
    }
    
    .tab-nav {
        flex-wrap: wrap;
    }
    
    .tab-btn {
        flex: 1 1 50%;
    }
}
</style>

<script>
// Performance Monitor JavaScript
let performanceCharts = {};
let autoRefreshInterval = null;

function switchTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Remove active class from all buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab
    document.getElementById(tabName + '-tab').classList.add('active');
    
    // Add active class to clicked button
    event.target.classList.add('active');
    
    // Initialize charts for the active tab
    initializeTabCharts(tabName);
}

function initializeTabCharts(tabName) {
    switch(tabName) {
        case 'system':
            initSystemCharts();
            break;
        case 'database':
            initDatabaseCharts();
            break;
        case 'api':
            initApiCharts();
            break;
        case 'cache':
            initCacheCharts();
            break;
    }
}

function initSystemCharts() {
    if (typeof Chart !== 'undefined') {
        createCpuChart();
        createMemoryChart();
        createLoadChart();
    }
}

function initDatabaseCharts() {
    if (typeof Chart !== 'undefined') {
        createQueryChart();
    }
    loadSlowQueries();
}

function initApiCharts() {
    if (typeof Chart !== 'undefined') {
        createApiResponseChart();
        createApiVolumeChart();
        createErrorRateChart();
    }
    loadEndpointPerformance();
}

function initCacheCharts() {
    if (typeof Chart !== 'undefined') {
        createCacheHitChart();
        createCacheSizeChart();
    }
    loadCacheStats();
}

function refreshMetrics() {
    // Reload all performance data
    fetch('/mwaba/api/performance/metrics')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                updateMetricsDisplay(data.data);
            }
        })
        .catch(error => {
            console.error('Error refreshing metrics:', error);
        });
}

function clearCache() {
    if (confirm('Are you sure you want to clear all cache? This action cannot be undone.')) {
        fetch('/mwaba/api/performance/clear-cache', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showNotification('Cache cleared successfully', 'success');
                refreshMetrics();
            } else {
                showNotification('Failed to clear cache', 'error');
            }
        })
        .catch(error => {
            console.error('Error clearing cache:', error);
            showNotification('Error clearing cache', 'error');
        });
    }
}

function toggleAutoRefresh() {
    const checkbox = document.getElementById('autoRefresh');
    
    if (checkbox.checked) {
        autoRefreshInterval = setInterval(refreshMetrics, 30000); // 30 seconds
        showNotification('Auto refresh enabled', 'info');
    } else {
        if (autoRefreshInterval) {
            clearInterval(autoRefreshInterval);
            autoRefreshInterval = null;
        }
        showNotification('Auto refresh disabled', 'info');
    }
}

function warmCache() {
    fetch('/mwaba/api/performance/warm-cache', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showNotification('Cache warmed successfully', 'success');
            refreshMetrics();
        } else {
            showNotification('Failed to warm cache', 'error');
        }
    })
    .catch(error => {
        console.error('Error warming cache:', error);
        showNotification('Error warming cache', 'error');
    });
}

function exportCacheStats() {
    fetch('/mwaba/api/performance/export-stats')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Create and download file
                const blob = new Blob([JSON.stringify(data.data, null, 2)], {
                    type: 'application/json'
                });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'performance-stats.json';
                a.click();
                URL.revokeObjectURL(url);
            }
        })
        .catch(error => {
            console.error('Error exporting stats:', error);
            showNotification('Error exporting stats', 'error');
        });
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 12px 20px;
        border-radius: 6px;
        color: white;
        font-weight: 600;
        z-index: 1000;
        animation: slideInDown 0.3s ease-out;
    `;
    
    switch (type) {
        case 'success':
            notification.style.background = '#27ae60';
            break;
        case 'error':
            notification.style.background = '#e74c3c';
            break;
        case 'info':
            notification.style.background = '#3498db';
            break;
    }
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Chart creation functions
function createCpuChart() {
    const ctx = document.getElementById('cpu-chart').getContext('2d');
    
    performanceCharts.cpu = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'CPU Usage %',
                data: [],
                borderColor: '#e74c3c',
                backgroundColor: 'rgba(231, 76, 60, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
}

function createMemoryChart() {
    const ctx = document.getElementById('memory-chart').getContext('2d');
    
    performanceCharts.memory = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Memory Usage (MB)',
                data: [],
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function createLoadChart() {
    const ctx = document.getElementById('load-chart').getContext('2d');
    
    performanceCharts.load = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['1min', '5min', '15min'],
            datasets: [{
                label: 'Load Average',
                data: [0, 0, 0],
                backgroundColor: ['#f39c12', '#e67e22', '#d35400']
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function createQueryChart() {
    const ctx = document.getElementById('query-chart').getContext('2d');
    
    performanceCharts.query = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Avg Execution Time (ms)',
                data: [],
                borderColor: '#9b59b6',
                backgroundColor: 'rgba(155, 89, 182, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function createApiResponseChart() {
    const ctx = document.getElementById('api-response-chart').getContext('2d');
    
    performanceCharts.apiResponse = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Response Time (ms)',
                data: [],
                borderColor: '#2ecc71',
                backgroundColor: 'rgba(46, 204, 113, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function createApiVolumeChart() {
    const ctx = document.getElementById('api-volume-chart').getContext('2d');
    
    performanceCharts.apiVolume = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Requests/Minute',
                data: [],
                backgroundColor: '#3498db'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function createErrorRateChart() {
    const ctx = document.getElementById('error-rate-chart').getContext('2d');
    
    performanceCharts.errorRate = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Success', 'Errors'],
            datasets: [{
                data: [98, 2],
                backgroundColor: ['#27ae60', '#e74c3c']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

function createCacheHitChart() {
    const ctx = document.getElementById('cache-hit-chart').getContext('2d');
    
    performanceCharts.cacheHit = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Hit Rate %',
                data: [],
                borderColor: '#9b59b6',
                backgroundColor: 'rgba(155, 89, 182, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
}

function createCacheSizeChart() {
    const ctx = document.getElementById('cache-size-chart').getContext('2d');
    
    performanceCharts.cacheSize = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Cache Size (KB)',
                data: [],
                borderColor: '#f39c12',
                backgroundColor: 'rgba(243, 156, 18, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function loadSlowQueries() {
    // This would typically fetch from API
    const slowQueriesContainer = document.getElementById('slow-queries');
    slowQueriesContainer.innerHTML = `
        <div class="slow-query-item">
            <div class="query">SELECT * FROM sensor_readings WHERE reading_time > NOW() - INTERVAL 1 HOUR</div>
            <div class="stats">
                <span>Execution Time: 250ms</span>
                <span>Count: 5</span>
            </div>
        </div>
        <div class="slow-query-item">
            <div class="query">SELECT AVG(value) FROM sensor_readings GROUP BY sensor_type</div>
            <div class="stats">
                <span>Execution Time: 180ms</span>
                <span>Count: 12</span>
            </div>
        </div>
    `;
}

function loadEndpointPerformance() {
    // This would typically fetch from API
    const endpointContainer = document.getElementById('endpoint-performance');
    endpointContainer.innerHTML = `
        <div class="endpoint-item">
            <div class="endpoint">/mwaba/dashboard</div>
            <div class="stats">
                <span>Avg Time: 120ms</span>
                <span>Requests: 45</span>
            </div>
        </div>
        <div class="endpoint-item">
            <div class="endpoint">/mwaba/api/realtime/data</div>
            <div class="stats">
                <span>Avg Time: 80ms</span>
                <span>Requests: 60</span>
            </div>
        </div>
        <div class="endpoint-item">
            <div class="endpoint">/mwaba/analytics</div>
            <div class="stats">
                <span>Avg Time: 250ms</span>
                <span>Requests: 15</span>
            </div>
        </div>
    `;
}

function loadCacheStats() {
    // This would typically fetch from API
    document.getElementById('cache-driver').textContent = 'File';
    document.getElementById('cache-items').textContent = '125';
    document.getElementById('cache-size').textContent = '2.5 MB';
    document.getElementById('cache-expired').textContent = '3';
}

// Initialize performance monitor on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize system charts by default
    initSystemCharts();
    
    // Load initial metrics
    refreshMetrics();
    
    // Set up periodic refresh
    setInterval(refreshMetrics, 30000); // 30 seconds
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
