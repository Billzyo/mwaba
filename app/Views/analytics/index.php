<?php
$activeMenu = 'analytics';
ob_start();
?>

<div class="content-header">
    <h2><i class="fas fa-chart-line"></i> Analytics & Reports</h2>
    <div class="header-controls">
        <button class="btn btn-primary" onclick="generateReport('comprehensive')">
            <i class="fas fa-file-pdf"></i> Generate PDF Report
        </button>
        <button class="btn btn-secondary" onclick="exportData('csv')">
            <i class="fas fa-download"></i> Export Data
        </button>
        <div class="date-range-selector">
            <select id="dateRange" onchange="updateAnalytics()">
                <option value="7">Last 7 Days</option>
                <option value="30" selected>Last 30 Days</option>
                <option value="90">Last 90 Days</option>
                <option value="365">Last Year</option>
            </select>
        </div>
    </div>
</div>

<!-- Key Metrics Cards -->
<div class="metrics-grid">
    <div class="metric-card">
        <div class="metric-icon">
            <i class="fas fa-thermometer-half"></i>
        </div>
        <div class="metric-content">
            <h3>Temperature Trend</h3>
            <div class="metric-value" id="temp-trend">
                <span class="trend-indicator up">↗</span>
                <span>+2.3°C</span>
            </div>
            <div class="metric-subtitle">vs last month</div>
        </div>
    </div>
    
    <div class="metric-card">
        <div class="metric-icon">
            <i class="fas fa-tint"></i>
        </div>
        <div class="metric-content">
            <h3>Humidity Pattern</h3>
            <div class="metric-value" id="humidity-pattern">
                <span class="pattern-indicator stable">≈</span>
                <span>Stable</span>
            </div>
            <div class="metric-subtitle">seasonal variation</div>
        </div>
    </div>
    
    <div class="metric-card">
        <div class="metric-icon">
            <i class="fas fa-seedling"></i>
        </div>
        <div class="metric-content">
            <h3>Crop Health</h3>
            <div class="metric-value" id="crop-health">
                <span class="health-indicator good">●</span>
                <span>Good</span>
            </div>
            <div class="metric-subtitle">95% healthy areas</div>
        </div>
    </div>
    
    <div class="metric-card">
        <div class="metric-icon">
            <i class="fas fa-cogs"></i>
        </div>
        <div class="metric-content">
            <h3>Equipment Efficiency</h3>
            <div class="metric-value" id="equipment-efficiency">
                <span class="efficiency-indicator high">⚡</span>
                <span>92%</span>
            </div>
            <div class="metric-subtitle">operational efficiency</div>
        </div>
    </div>
</div>

<!-- Analytics Tabs -->
<div class="analytics-tabs">
    <div class="tab-nav">
        <button class="tab-btn active" onclick="switchTab('overview')">
            <i class="fas fa-chart-pie"></i> Overview
        </button>
        <button class="tab-btn" onclick="switchTab('trends')">
            <i class="fas fa-chart-line"></i> Trends
        </button>
        <button class="tab-btn" onclick="switchTab('correlations')">
            <i class="fas fa-project-diagram"></i> Correlations
        </button>
        <button class="tab-btn" onclick="switchTab('insights')">
            <i class="fas fa-lightbulb"></i> Insights
        </button>
    </div>
    
    <!-- Overview Tab -->
    <div id="overview-tab" class="tab-content active">
        <div class="charts-grid">
            <div class="chart-container">
                <h3>Sensor Data Overview</h3>
                <canvas id="sensor-overview-chart"></canvas>
            </div>
            
            <div class="chart-container">
                <h3>Data Distribution</h3>
                <canvas id="distribution-chart"></canvas>
            </div>
            
            <div class="chart-container">
                <h3>Performance Metrics</h3>
                <canvas id="performance-chart"></canvas>
            </div>
            
            <div class="chart-container">
                <h3>Alert History</h3>
                <canvas id="alerts-chart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Trends Tab -->
    <div id="trends-tab" class="tab-content">
        <div class="charts-grid">
            <div class="chart-container full-width">
                <h3>Temperature Trends</h3>
                <canvas id="temperature-trend-chart"></canvas>
            </div>
            
            <div class="chart-container full-width">
                <h3>Humidity Patterns</h3>
                <canvas id="humidity-trend-chart"></canvas>
            </div>
            
            <div class="chart-container full-width">
                <h3>Soil Moisture Analysis</h3>
                <canvas id="soil-moisture-trend-chart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Correlations Tab -->
    <div id="correlations-tab" class="tab-content">
        <div class="correlation-matrix">
            <h3>Sensor Correlations</h3>
            <div class="correlation-grid">
                <div class="correlation-item">
                    <div class="correlation-label">Temperature ↔ Humidity</div>
                    <div class="correlation-value" id="temp-humidity-correlation">
                        <div class="correlation-bar">
                            <div class="correlation-fill" style="width: 65%"></div>
                        </div>
                        <span class="correlation-strength">Strong Negative</span>
                    </div>
                </div>
                
                <div class="correlation-item">
                    <div class="correlation-label">Humidity ↔ Soil Moisture</div>
                    <div class="correlation-value" id="humidity-soil-correlation">
                        <div class="correlation-bar">
                            <div class="correlation-fill" style="width: 80%"></div>
                        </div>
                        <span class="correlation-strength">Strong Positive</span>
                    </div>
                </div>
                
                <div class="correlation-item">
                    <div class="correlation-label">Temperature ↔ Soil Moisture</div>
                    <div class="correlation-value" id="temp-soil-correlation">
                        <div class="correlation-bar">
                            <div class="correlation-fill" style="width: 45%"></div>
                        </div>
                        <span class="correlation-strength">Moderate Negative</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="chart-container">
            <h3>Correlation Heatmap</h3>
            <canvas id="correlation-heatmap"></canvas>
        </div>
    </div>
    
    <!-- Insights Tab -->
    <div id="insights-tab" class="tab-content">
        <div class="insights-grid">
            <div class="insight-card weather">
                <div class="insight-header">
                    <i class="fas fa-cloud-sun"></i>
                    <h3>Weather Insights</h3>
                </div>
                <div class="insight-content">
                    <p>Current forecast shows sunny conditions for the next 3 days.</p>
                    <div class="recommendation">
                        <strong>Recommendation:</strong> Increase irrigation frequency by 15%
                    </div>
                </div>
            </div>
            
            <div class="insight-card crops">
                <div class="insight-header">
                    <i class="fas fa-seedling"></i>
                    <h3>Crop Recommendations</h3>
                </div>
                <div class="insight-content">
                    <p>Soil analysis indicates optimal conditions for growth.</p>
                    <div class="recommendation">
                        <strong>Action:</strong> Apply nitrogen fertilizer in Field A
                    </div>
                </div>
            </div>
            
            <div class="insight-card maintenance">
                <div class="insight-header">
                    <i class="fas fa-wrench"></i>
                    <h3>Maintenance Alerts</h3>
                </div>
                <div class="insight-content">
                    <p>Equipment performance is within normal parameters.</p>
                    <div class="recommendation">
                        <strong>Schedule:</strong> Pump service due in 2 weeks
                    </div>
                </div>
            </div>
            
            <div class="insight-card optimization">
                <div class="insight-header">
                    <i class="fas fa-rocket"></i>
                    <h3>Optimization Opportunities</h3>
                </div>
                <div class="insight-content">
                    <p>Energy usage patterns show potential for improvement.</p>
                    <div class="recommendation">
                        <strong>Optimize:</strong> Adjust pump schedule for 10% energy savings
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Advanced Filters -->
<div class="analytics-filters">
    <h3>Advanced Filters</h3>
    <div class="filter-grid">
        <div class="filter-group">
            <label>Sensor Type:</label>
            <select id="sensorFilter" multiple>
                <option value="temperature" selected>Temperature</option>
                <option value="humidity" selected>Humidity</option>
                <option value="soil_moisture" selected>Soil Moisture</option>
                <option value="light">Light</option>
                <option value="wind">Wind</option>
            </select>
        </div>
        
        <div class="filter-group">
            <label>Location:</label>
            <select id="locationFilter" multiple>
                <option value="all" selected>All Locations</option>
                <option value="greenhouse">Greenhouse A</option>
                <option value="field">Field B</option>
                <option value="field_nw">Field NW</option>
            </select>
        </div>
        
        <div class="filter-group">
            <label>Time Period:</label>
            <input type="datetime-local" id="startDate" />
            <input type="datetime-local" id="endDate" />
        </div>
        
        <div class="filter-group">
            <button class="btn btn-primary" onclick="applyFilters()">
                <i class="fas fa-filter"></i> Apply Filters
            </button>
            <button class="btn btn-secondary" onclick="clearFilters()">
                <i class="fas fa-times"></i> Clear
            </button>
        </div>
    </div>
</div>

<style>
/* Analytics Styles */
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
    background: linear-gradient(135deg, #4CAF50, #45a049);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
}

.metric-content h3 {
    margin: 0 0 8px 0;
    color: #333;
    font-size: 14px;
    font-weight: 600;
}

.metric-value {
    font-size: 24px;
    font-weight: bold;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 8px;
}

.metric-subtitle {
    font-size: 12px;
    color: #666;
    margin-top: 4px;
}

.trend-indicator.up { color: #e74c3c; }
.trend-indicator.down { color: #27ae60; }
.pattern-indicator.stable { color: #f39c12; }
.health-indicator.good { color: #27ae60; }
.efficiency-indicator.high { color: #3498db; }

.analytics-tabs {
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

.chart-container.full-width {
    grid-column: 1 / -1;
}

.chart-container h3 {
    margin: 0 0 20px 0;
    color: #333;
    font-size: 18px;
}

.correlation-matrix {
    margin-bottom: 30px;
}

.correlation-grid {
    display: grid;
    gap: 20px;
}

.correlation-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.correlation-label {
    font-weight: 600;
    color: #333;
}

.correlation-value {
    display: flex;
    align-items: center;
    gap: 15px;
}

.correlation-bar {
    width: 150px;
    height: 8px;
    background: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
}

.correlation-fill {
    height: 100%;
    background: linear-gradient(90deg, #e74c3c, #f39c12, #27ae60);
    transition: width 0.3s ease;
}

.correlation-strength {
    font-size: 12px;
    font-weight: 600;
    color: #666;
}

.insights-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.insight-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-left: 4px solid #4CAF50;
}

.insight-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
}

.insight-header i {
    font-size: 20px;
    color: #4CAF50;
}

.insight-header h3 {
    margin: 0;
    color: #333;
    font-size: 16px;
}

.insight-content p {
    color: #666;
    margin-bottom: 15px;
    line-height: 1.5;
}

.recommendation {
    background: #e8f5e8;
    padding: 10px;
    border-radius: 6px;
    font-size: 14px;
}

.analytics-filters {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-top: 30px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.analytics-filters h3 {
    margin: 0 0 20px 0;
    color: #333;
}

.filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.filter-group label {
    font-weight: 600;
    color: #333;
    font-size: 14px;
}

.filter-group select,
.filter-group input {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
}

.filter-group select[multiple] {
    height: 100px;
}

.header-controls {
    display: flex;
    align-items: center;
    gap: 15px;
}

.date-range-selector select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
}

@media (max-width: 768px) {
    .charts-grid {
        grid-template-columns: 1fr;
    }
    
    .metrics-grid {
        grid-template-columns: 1fr;
    }
    
    .insights-grid {
        grid-template-columns: 1fr;
    }
    
    .filter-grid {
        grid-template-columns: 1fr;
    }
    
    .header-controls {
        flex-direction: column;
        gap: 10px;
    }
}
</style>

<script>
// Analytics JavaScript
let analyticsCharts = {};

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
        case 'overview':
            initOverviewCharts();
            break;
        case 'trends':
            initTrendCharts();
            break;
        case 'correlations':
            initCorrelationCharts();
            break;
        case 'insights':
            loadInsights();
            break;
    }
}

function initOverviewCharts() {
    // Initialize overview charts
    if (typeof Chart !== 'undefined') {
        createSensorOverviewChart();
        createDistributionChart();
        createPerformanceChart();
        createAlertsChart();
    }
}

function initTrendCharts() {
    // Initialize trend charts
    if (typeof Chart !== 'undefined') {
        createTemperatureTrendChart();
        createHumidityTrendChart();
        createSoilMoistureTrendChart();
    }
}

function initCorrelationCharts() {
    // Initialize correlation charts
    if (typeof Chart !== 'undefined') {
        createCorrelationHeatmap();
    }
}

function loadInsights() {
    // Load insights data
    fetch(`${window.BASE_PATH}/api/analytics/insights`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                updateInsightsDisplay(data.data);
            }
        })
        .catch(error => {
            console.error('Error loading insights:', error);
        });
}

function updateAnalytics() {
    const dateRange = document.getElementById('dateRange').value;
    
    // Reload analytics data with new date range
    fetch(`${window.BASE_PATH}/api/analytics/data?range=${dateRange}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                updateAnalyticsDisplay(data.data);
            }
        })
        .catch(error => {
            console.error('Error updating analytics:', error);
        });
}

function generateReport(type) {
    const dateRange = document.getElementById('dateRange').value;
    const url = `${window.BASE_PATH}/api/analytics/report?type=${type}&range=${dateRange}`;
    
    // Open report generation in new window
    window.open(url, '_blank');
}

function exportData(format) {
    const dateRange = document.getElementById('dateRange').value;
    const url = `${window.BASE_PATH}/api/analytics/export?format=${format}&range=${dateRange}`;
    
    // Trigger download
    window.location.href = url;
}

function applyFilters() {
    const sensorFilter = Array.from(document.getElementById('sensorFilter').selectedOptions)
        .map(option => option.value);
    const locationFilter = Array.from(document.getElementById('locationFilter').selectedOptions)
        .map(option => option.value);
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    // Apply filters and reload data
    const params = new URLSearchParams({
        sensors: sensorFilter.join(','),
        locations: locationFilter.join(','),
        start_date: startDate,
        end_date: endDate
    });
    
    fetch(`${window.BASE_PATH}/api/analytics/data?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                updateAnalyticsDisplay(data.data);
            }
        })
        .catch(error => {
            console.error('Error applying filters:', error);
        });
}

function clearFilters() {
    document.getElementById('sensorFilter').selectedIndex = -1;
    document.getElementById('locationFilter').selectedIndex = -1;
    document.getElementById('startDate').value = '';
    document.getElementById('endDate').value = '';
    
    // Reload with default data
    updateAnalytics();
}

// Chart creation functions
function createSensorOverviewChart() {
    const ctx = document.getElementById('sensor-overview-chart').getContext('2d');
    
    analyticsCharts.sensorOverview = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Temperature', 'Humidity', 'Soil Moisture', 'Light', 'Wind'],
            datasets: [{
                data: [25, 30, 20, 15, 10],
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF'
                ]
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

function createDistributionChart() {
    const ctx = document.getElementById('distribution-chart').getContext('2d');
    
    analyticsCharts.distribution = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Low', 'Normal', 'High'],
            datasets: [{
                label: 'Temperature',
                data: [5, 80, 15],
                backgroundColor: '#FF6384'
            }, {
                label: 'Humidity',
                data: [10, 70, 20],
                backgroundColor: '#36A2EB'
            }, {
                label: 'Soil Moisture',
                data: [8, 75, 17],
                backgroundColor: '#FFCE56'
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

function createPerformanceChart() {
    const ctx = document.getElementById('performance-chart').getContext('2d');
    
    analyticsCharts.performance = new Chart(ctx, {
        type: 'radar',
        data: {
            labels: ['Uptime', 'Data Rate', 'Response Time', 'Accuracy', 'Efficiency'],
            datasets: [{
                label: 'Performance',
                data: [99.8, 98.5, 95, 95.2, 92],
                backgroundColor: 'rgba(76, 175, 80, 0.2)',
                borderColor: 'rgba(76, 175, 80, 1)',
                pointBackgroundColor: 'rgba(76, 175, 80, 1)'
            }]
        },
        options: {
            responsive: true,
            scales: {
                r: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
}

function createAlertsChart() {
    const ctx = document.getElementById('alerts-chart').getContext('2d');
    
    analyticsCharts.alerts = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Alerts',
                data: [2, 1, 3, 0, 2, 1, 0],
                borderColor: '#e74c3c',
                backgroundColor: 'rgba(231, 76, 60, 0.1)',
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

// Initialize analytics on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize overview charts by default
    initOverviewCharts();
    
    // Load initial analytics data
    updateAnalytics();
});
</script>

<?php
$content = ob_get_clean();
?>

<?php
// Include the main layout with the captured $content
include __DIR__ . '/../layouts/main.php';
?>