/**
 * Farm Monitoring System - Main JavaScript
 * Handles real-time updates, charts, and user interactions
 */

// Global variables
let envChart = null;
let cropChart = null;
let updateInterval = null;

// Initialize the application when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

/**
 * Initialize the application
 */
function initializeApp() {
    setupEventListeners();
    initializeCharts();
    startRealTimeUpdates();
    updateLastUpdated();
}

/**
 * Setup event listeners for interactive elements
 */
function setupEventListeners() {
    // Mobile menu toggle
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', toggleMobileMenu);
    }
    
    // Menu item clicks
    const menuItems = document.querySelectorAll('.menu-item');
    menuItems.forEach(item => {
        item.addEventListener('click', function() {
            menuItems.forEach(i => i.classList.remove('active'));
            this.classList.add('active');
        });
    });
    
    // Chart expand buttons
    const expandButtons = document.querySelectorAll('.chart-header i');
    expandButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            // Toggle fullscreen for chart
            toggleChartFullscreen(this.closest('.chart-card'));
        });
    });
}

/**
 * Toggle mobile menu
 */
function toggleMobileMenu() {
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        sidebar.classList.toggle('active');
    }
}

/**
 * Initialize charts with data
 */
function initializeCharts() {
    // Get chart data from global variables (set by PHP)
    if (typeof chartData !== 'undefined' && typeof cropData !== 'undefined') {
        initializeEnvironmentalChart();
        initializeCropHealthChart();
    }
}

/**
 * Initialize environmental trends chart
 */
function initializeEnvironmentalChart() {
    const envCtx = document.getElementById('environmentChart');
    if (!envCtx) return;
    
    envChart = new Chart(envCtx, {
        type: 'line',
        data: {
            labels: chartData.temperature ? chartData.temperature.times : [],
            datasets: [
                {
                    label: 'Temperature (°C)',
                    data: chartData.temperature ? chartData.temperature.values : [],
                    borderColor: '#d32f2f',
                    backgroundColor: 'rgba(211, 47, 47, 0.1)',
                    tension: 0.3,
                    fill: true
                },
                {
                    label: 'Humidity (%)',
                    data: chartData.humidity ? chartData.humidity.values : [],
                    borderColor: '#0288d1',
                    backgroundColor: 'rgba(2, 136, 209, 0.1)',
                    tension: 0.3,
                    fill: true
                },
                {
                    label: 'Soil Moisture (%)',
                    data: chartData.soil_moisture ? chartData.soil_moisture.values : [],
                    borderColor: '#388e3c',
                    backgroundColor: 'rgba(56, 142, 60, 0.1)',
                    tension: 0.3,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: false
                }
            }
        }
    });
}

/**
 * Initialize crop health chart
 */
function initializeCropHealthChart() {
    const cropCtx = document.getElementById('cropHealthChart');
    if (!cropCtx) return;
    
    cropChart = new Chart(cropCtx, {
        type: 'bar',
        data: {
            labels: cropData.map(item => item.name),
            datasets: [
                {
                    label: 'Days to Harvest',
                    data: cropData.map(item => item.days_to_harvest),
                    backgroundColor: cropData.map(item => {
                        if(item.days_to_harvest < 15) return '#d32f2f';
                        if(item.days_to_harvest < 30) return '#ffa000';
                        return '#388e3c';
                    }),
                    borderColor: 'rgba(0,0,0,0.1)',
                    borderWidth: 1
                },
                {
                    label: 'Health Status',
                    data: cropData.map(item => {
                        switch(item.health) {
                            case 'excellent': return 100;
                            case 'good': return 75;
                            case 'fair': return 50;
                            case 'poor': return 25;
                            default: return 0;
                        }
                    }),
                    backgroundColor: cropData.map(item => {
                        switch(item.health) {
                            case 'excellent': return 'rgba(56, 142, 60, 0.5)';
                            case 'good': return 'rgba(56, 142, 60, 0.3)';
                            case 'fair': return 'rgba(255, 160, 0, 0.3)';
                            case 'poor': return 'rgba(211, 47, 47, 0.3)';
                            default: return '#ccc';
                        }
                    }),
                    borderColor: 'rgba(0,0,0,0.1)',
                    borderWidth: 1,
                    type: 'line',
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Days to Harvest'
                    }
                },
                y1: {
                    position: 'right',
                    min: 0,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Health Status (%)'
                    },
                    grid: {
                        drawOnChartArea: false
                    }
                }
            }
        }
    });
}

/**
 * Start real-time updates
 */
function startRealTimeUpdates() {
    // Update every 10 seconds
    updateInterval = setInterval(updateSensorReadings, 10000);
    
    // Update time every minute
    setInterval(updateLastUpdated, 60000);
}

/**
 * Update sensor readings with simulated data
 */
function updateSensorReadings() {
    // Generate random sensor values (simulating data from ESP32)
    const temperature = (Math.random() * 10 + 20).toFixed(1);
    const humidity = (Math.random() * 20 + 50).toFixed(1);
    const moisture = (Math.random() * 20 + 30).toFixed(1);
    const light = Math.floor(Math.random() * 20 + 75);
    const wind = (Math.random() * 5 + 10).toFixed(1);
    const ph = (Math.random() * 0.5 + 6.5).toFixed(1);
    
    // Calculate changes
    const tempChange = (Math.random() * 1).toFixed(1);
    const humidChange = (Math.random() * 1).toFixed(1);
    const moistChange = (Math.random() * 1).toFixed(1);
    
    // Update temperature
    updateSensorValue('temperature-value', temperature);
    updateSensorValue('temperature-change', tempChange);
    
    // Update humidity
    updateSensorValue('humidity-value', humidity);
    updateSensorValue('humidity-change', humidChange);
    
    // Update soil moisture
    updateSensorValue('moisture-value', moisture);
    updateSensorValue('moisture-change', moistChange);
    
    // Update other sensors
    updateSensorValue('light-value', light);
    updateSensorValue('wind-value', wind);
    updateSensorValue('ph-value', ph);
    
    // Update status indicators based on values
    updateStatusIndicators(temperature, humidity, moisture);
    
    // Add animation to cards
    animateSensorCards();
}

/**
 * Update sensor value with animation
 */
function updateSensorValue(elementId, value) {
    const element = document.getElementById(elementId);
    if (element) {
        element.textContent = value;
    }
}

/**
 * Update status indicators based on sensor values
 */
function updateStatusIndicators(temp, humid, moist) {
    // Temperature status
    updateStatusIndicator('temperature-status', temp, {
        critical: 28,
        warning: 25,
        good: '< 25'
    }, {
        critical: 'Critical',
        warning: 'High',
        good: 'Optimal'
    });
    
    // Humidity status
    updateStatusIndicator('humidity-status', humid, {
        critical: 80,
        warning: 70,
        low: 40,
        good: '40-70'
    }, {
        critical: 'Critical',
        warning: 'High',
        low: 'Low',
        good: 'Normal'
    });
    
    // Soil moisture status
    updateStatusIndicator('moisture-status', moist, {
        critical: 30,
        warning: 40,
        high: 70,
        good: '40-70'
    }, {
        critical: 'Critical',
        warning: 'Low',
        high: 'High',
        good: 'Optimal'
    });
}

/**
 * Update individual status indicator
 */
function updateStatusIndicator(elementId, value, thresholds, labels) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const numValue = parseFloat(value);
    let status = 'good';
    let label = labels.good;
    let icon = 'fas fa-check-circle';
    
    if (thresholds.critical && numValue > thresholds.critical) {
        status = 'critical';
        label = labels.critical;
        icon = 'fas fa-exclamation-circle';
    } else if (thresholds.warning && numValue > thresholds.warning) {
        status = 'warning';
        label = labels.warning;
        icon = 'fas fa-exclamation-triangle';
    } else if (thresholds.low && numValue < thresholds.low) {
        status = 'warning';
        label = labels.low;
        icon = 'fas fa-exclamation-triangle';
    } else if (thresholds.high && numValue > thresholds.high) {
        status = 'warning';
        label = labels.high;
        icon = 'fas fa-exclamation-triangle';
    }
    
    element.className = `status ${status}`;
    element.innerHTML = `<i class="${icon}"></i> ${label}`;
}

/**
 * Animate sensor cards
 */
function animateSensorCards() {
    const cards = ['.temperature', '.humidity', '.moisture'];
    
    cards.forEach(selector => {
        const card = document.querySelector(selector);
        if (card) {
            card.classList.add('sensor-animation');
            setTimeout(() => {
                card.classList.remove('sensor-animation');
            }, 1000);
        }
    });
}

/**
 * Update last updated time
 */
function updateLastUpdated() {
    const now = new Date();
    const timeString = now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    const element = document.getElementById('last-updated');
    if (element) {
        element.textContent = timeString;
    }
}

/**
 * Toggle chart fullscreen
 */
function toggleChartFullscreen(chartCard) {
    if (chartCard.classList.contains('fullscreen')) {
        chartCard.classList.remove('fullscreen');
        document.body.style.overflow = '';
    } else {
        chartCard.classList.add('fullscreen');
        document.body.style.overflow = 'hidden';
    }
}

/**
 * Fetch latest data from API
 */
async function fetchLatestData() {
    try {
        const response = await fetch(`${window.BASE_PATH}/api/dashboard/latest`);
        const data = await response.json();
        
        if (data.status === 'success') {
            updateDashboardWithData(data.data);
        }
    } catch (error) {
        console.error('Error fetching latest data:', error);
    }
}

/**
 * Update dashboard with fetched data
 */
function updateDashboardWithData(sensorData) {
    // Update sensor values
    Object.keys(sensorData).forEach(sensorType => {
        const sensor = sensorData[sensorType];
        updateSensorValue(`${sensorType}-value`, sensor.value);
    });
    
    // Update status indicators
    if (sensorData.temperature && sensorData.humidity && sensorData.soil_moisture) {
        updateStatusIndicators(
            sensorData.temperature.value,
            sensorData.humidity.value,
            sensorData.soil_moisture.value
        );
    }
}

/**
 * Handle crop health update
 */
function updateCropHealth(areaId) {
    const healthStatus = prompt('Enter new health status (excellent, good, fair, poor):');
    if (healthStatus && ['excellent', 'good', 'fair', 'poor'].includes(healthStatus)) {
        fetch(`${window.BASE_PATH}/api/crops/update-health`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                area_id: areaId,
                health_status: healthStatus
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating crop health');
        });
    }
}

/**
 * Handle equipment status update
 */
function updateEquipmentStatus(equipmentId, currentStatus) {
    const statuses = ['online', 'offline', 'active', 'idle', 'maintenance'];
    const newStatus = prompt(`Enter new status (${statuses.join(', ')}):`, currentStatus);
    
    if (newStatus && statuses.includes(newStatus)) {
        fetch(`${window.BASE_PATH}/api/equipment/update-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                equipment_id: equipmentId,
                status: newStatus
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating equipment status');
        });
    }
}

/**
 * Handle equipment maintenance update
 */
function updateMaintenance(equipmentId) {
    if (confirm('Mark this equipment as maintained today?')) {
        fetch(`${window.BASE_PATH}/api/equipment/update-maintenance`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                equipment_id: equipmentId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating maintenance record');
        });
    }
}

/**
 * View crop details
 */
function viewCropDetails(areaId) {
    // Implement crop details view
    alert('Crop details view not implemented yet');
}

// Add CSS for fullscreen charts
const fullscreenCSS = `
.chart-card.fullscreen {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    z-index: 9999;
    background: white;
    padding: 20px;
    border-radius: 0;
}

.chart-card.fullscreen .chart-container {
    height: calc(100vh - 100px);
}
`;

// Inject fullscreen CSS
const fullscreenStyle = document.createElement('style');
fullscreenStyle.textContent = fullscreenCSS;
document.head.appendChild(fullscreenStyle);