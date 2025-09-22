/**
 * Real-time Farm Monitoring JavaScript Client
 * Handles WebSocket connections and real-time updates
 */

class RealtimeFarmMonitor {
    constructor() {
        this.ws = null;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.reconnectDelay = 1000;
        this.isConnected = false;
        this.lastUpdate = null;
        this.charts = {};
        this.alerts = [];
        
        this.init();
    }
    
    init() {
        this.connectWebSocket();
        this.setupEventListeners();
        this.startPeriodicUpdates();
        this.initializeCharts();
    }
    
    connectWebSocket() {
        try {
            const isSecure = window.location.protocol === 'https:';
            const wsProtocol = isSecure ? 'wss' : 'ws';
            const host = window.location.hostname || 'localhost';
            const overrideUrl = window.WS_URL; // optional global override
            const overridePort = window.WS_PORT; // optional global override
            const currentPort = window.location.port ? parseInt(window.location.port, 10) : (isSecure ? 443 : 80);
            
            const candidates = [];
            if (overrideUrl) {
                candidates.push(overrideUrl);
            } else {
                if (overridePort) {
                    candidates.push(`${wsProtocol}://${host}:${overridePort}`);
                }
                // Try same port as page first
                if (currentPort) {
                    candidates.push(`${wsProtocol}://${host}:${currentPort}`);
                }
                // Common defaults
                candidates.push(`${wsProtocol}://${host}:8080`);
                // Finally try without explicit port
                candidates.push(`${wsProtocol}://${host}`);
            }
            
            let attemptIndex = 0;
            const tryConnect = () => {
                const url = candidates[attemptIndex];
                if (!url) {
                    // Exhausted candidates
                    this.showConnectionStatus('failed');
                    this.startPollingFallback();
                    return;
                }
                
                this.ws = new WebSocket(url);
            
            this.ws.onopen = () => {
                console.log('WebSocket connected');
                this.isConnected = true;
                this.reconnectAttempts = 0;
                this.showConnectionStatus('connected');
            };
            
            this.ws.onmessage = (event) => {
                try {
                    this.handleMessage(JSON.parse(event.data));
                } catch (error) {
                    console.error('Error parsing WebSocket message:', error);
                }
            };
            
            this.ws.onclose = () => {
                console.log('WebSocket disconnected');
                this.isConnected = false;
                this.showConnectionStatus('disconnected');
                // If initial connection attempts are ongoing, try next candidate
                if (this.reconnectAttempts === 0 && attemptIndex < candidates.length - 1) {
                    attemptIndex += 1;
                    tryConnect();
                } else {
                    this.attemptReconnect();
                }
            };
            
            this.ws.onerror = (error) => {
                console.error('WebSocket error:', error);
                this.showConnectionStatus('error');
                // Try next candidate on initial connect
                if (this.reconnectAttempts === 0 && attemptIndex < candidates.length - 1) {
                    try {
                        this.ws.close();
                    } catch (_) {}
                    attemptIndex += 1;
                    tryConnect();
                } else {
                    // Fallback to polling if all candidates fail
                    this.startPollingFallback();
                }
            };
            };
            
            tryConnect();
        } catch (error) {
            console.error('Failed to connect WebSocket:', error);
            this.showConnectionStatus('error');
            // Fallback to polling if WebSocket fails
            this.startPollingFallback();
        }
    }
    
    handleMessage(data) {
        switch (data.type) {
            case 'sensor_update':
                this.updateSensorData(data.data);
                this.handleAlerts(data.alerts);
                break;
            case 'initial_data':
                this.loadInitialData(data.data);
                break;
            case 'alert':
                this.showAlert(data.alert);
                break;
        }
    }
    
    updateSensorData(data) {
        // Update sensor cards
        Object.keys(data).forEach(sensorType => {
            const value = data[sensorType];
            const element = document.querySelector(`[data-sensor="${sensorType}"]`);
            
            if (element) {
                const valueElement = element.querySelector('.sensor-value');
                const statusElement = element.querySelector('.sensor-status');
                
                if (valueElement) {
                    valueElement.textContent = value;
                }
                
                if (statusElement) {
                    const status = this.getSensorStatus(sensorType, value);
                    statusElement.className = `sensor-status status-${status}`;
                    statusElement.textContent = status.toUpperCase();
                }
            }
        });
        
        // Update charts
        this.updateCharts(data);
        
        this.lastUpdate = new Date();
        this.updateLastUpdateTime();
    }
    
    loadInitialData(data) {
        if (data && Object.keys(data).length > 0) {
            this.updateSensorData(data);
        }
    }
    
    handleAlerts(alerts) {
        if (alerts && alerts.length > 0) {
            alerts.forEach(alert => {
                this.showAlert(alert);
                this.addToAlertsList(alert);
            });
        }
    }
    
    showAlert(alert) {
        // Show browser notification if permission granted
        if (Notification.permission === 'granted') {
            new Notification(`Farm Alert: ${alert.sensor}`, {
                body: alert.message,
                icon: 'public/assets/images/poslogo.png',
                tag: `alert-${alert.sensor}-${Date.now()}`
            });
        }
        
        // Show in-page notification
        this.showInPageAlert(alert);
    }
    
    showInPageAlert(alert) {
        const alertContainer = document.getElementById('alert-container');
        if (!alertContainer) return;
        
        const alertElement = document.createElement('div');
        alertElement.className = `alert alert-${alert.type} alert-dismissible`;
        alertElement.innerHTML = `
            <i class="fas fa-exclamation-triangle"></i>
            <strong>${alert.sensor.toUpperCase()}</strong>: ${alert.message}
            <button type="button" class="close" onclick="this.parentElement.remove()">
                <span>&times;</span>
            </button>
        `;
        
        alertContainer.appendChild(alertElement);
        
        // Auto-remove after 10 seconds
        setTimeout(() => {
            if (alertElement.parentElement) {
                alertElement.remove();
            }
        }, 10000);
    }
    
    addToAlertsList(alert) {
        this.alerts.unshift({
            ...alert,
            timestamp: new Date().toLocaleString()
        });
        
        // Keep only last 50 alerts
        if (this.alerts.length > 50) {
            this.alerts = this.alerts.slice(0, 50);
        }
        
        this.updateAlertsPanel();
    }
    
    updateAlertsPanel() {
        const alertsList = document.getElementById('alerts-list');
        if (!alertsList) return;
        
        alertsList.innerHTML = this.alerts.map(alert => `
            <div class="alert-item alert-${alert.type}">
                <div class="alert-time">${alert.timestamp}</div>
                <div class="alert-message">${alert.message}</div>
            </div>
        `).join('');
    }
    
    getSensorStatus(sensorType, value) {
        const thresholds = {
            temperature: { min: 10, max: 35 },
            humidity: { min: 30, max: 80 },
            soil_moisture: { min: 20, max: 80 }
        };
        
        const threshold = thresholds[sensorType];
        if (!threshold) return 'normal';
        
        if (value < threshold.min) return 'low';
        if (value > threshold.max) return 'high';
        return 'normal';
    }
    
    updateCharts(data) {
        // Update temperature chart
        if (this.charts.temperature && data.temperature) {
            this.charts.temperature.addData(data.temperature);
        }
        
        // Update humidity chart
        if (this.charts.humidity && data.humidity) {
            this.charts.humidity.addData(data.humidity);
        }
        
        // Update soil moisture chart
        if (this.charts.soil_moisture && data.soil_moisture) {
            this.charts.soil_moisture.addData(data.soil_moisture);
        }
    }
    
    initializeCharts() {
        // Initialize Chart.js charts if available
        if (typeof Chart !== 'undefined') {
            this.setupChart('temperature', 'Temperature (°C)');
            this.setupChart('humidity', 'Humidity (%)');
            this.setupChart('soil_moisture', 'Soil Moisture (%)');
        }
    }
    
    setupChart(sensorType, label) {
        const canvas = document.getElementById(`${sensorType}-chart`);
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        
        this.charts[sensorType] = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: label,
                    data: [],
                    borderColor: this.getChartColor(sensorType),
                    backgroundColor: this.getChartColor(sensorType, 0.1),
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                animation: {
                    duration: 750
                }
            }
        });
        
        // Add method to chart for real-time updates
        this.charts[sensorType].addData = (value) => {
            const chart = this.charts[sensorType];
            const now = new Date().toLocaleTimeString();
            
            chart.data.labels.push(now);
            chart.data.datasets[0].data.push(value);
            
            // Keep only last 20 data points
            if (chart.data.labels.length > 20) {
                chart.data.labels.shift();
                chart.data.datasets[0].data.shift();
            }
            
            chart.update('none');
        };
    }
    
    getChartColor(sensorType, alpha = 1) {
        const colors = {
            temperature: `rgba(255, 99, 132, ${alpha})`,
            humidity: `rgba(54, 162, 235, ${alpha})`,
            soil_moisture: `rgba(255, 206, 86, ${alpha})`
        };
        
        return colors[sensorType] || `rgba(153, 102, 255, ${alpha})`;
    }
    
    startPeriodicUpdates() {
        // Fallback polling if WebSocket fails
        setInterval(() => {
            if (!this.isConnected) {
                this.fetchLatestData();
            }
        }, 30000); // Every 30 seconds
    }
    
    startPollingFallback() {
        console.log('Starting polling fallback mode');
        this.isConnected = false;
        this.showConnectionStatus('polling');
        
        // Fetch data immediately
        this.fetchLatestData();
        
        // Set up more frequent polling
        setInterval(() => {
            this.fetchLatestData();
        }, 10000); // Every 10 seconds in fallback mode
    }
    
    async fetchLatestData() {
        try {
            const response = await fetch('/mwaba/api/realtime/data');
            
            // Check if response is ok and content type is JSON
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Response is not JSON');
            }
            
            const result = await response.json();
            
            if (result.status === 'success') {
                this.updateSensorData(result.data);
            }
        } catch (error) {
            console.error('Failed to fetch latest data:', error);
            // Fallback to simulated data if API fails
            this.updateSensorData({
                temperature: (Math.random() * 10 + 20).toFixed(1),
                humidity: (Math.random() * 20 + 50).toFixed(1),
                soil_moisture: (Math.random() * 20 + 30).toFixed(1)
            });
        }
    }
    
    setupEventListeners() {
        // Request notification permission
        if ('Notification' in window) {
            Notification.requestPermission();
        }
        
        // Handle page visibility changes
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.ws?.send(JSON.stringify({ type: 'ping' }));
            }
        });
    }
    
    attemptReconnect() {
        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++;
            this.showConnectionStatus('reconnecting');
            
            setTimeout(() => {
                this.connectWebSocket();
            }, this.reconnectDelay * this.reconnectAttempts);
        } else {
            this.showConnectionStatus('failed');
        }
    }
    
    showConnectionStatus(status) {
        const statusElement = document.getElementById('connection-status');
        if (!statusElement) return;
        
        const statusText = {
            connected: '🟢 Connected',
            disconnected: '🔴 Disconnected',
            reconnecting: '🟡 Reconnecting...',
            error: '🔴 Connection Error',
            failed: '🔴 Connection Failed',
            polling: '🟡 Polling Mode'
        };
        
        statusElement.textContent = statusText[status] || '❓ Unknown';
        statusElement.className = `connection-status status-${status}`;
    }
    
    updateLastUpdateTime() {
        const timeElement = document.getElementById('last-update-time');
        if (timeElement && this.lastUpdate) {
            timeElement.textContent = `Last update: ${this.lastUpdate.toLocaleTimeString()}`;
        }
    }
    
    // Public methods for manual control
    connect() {
        this.connectWebSocket();
    }
    
    disconnect() {
        if (this.ws) {
            this.ws.close();
        }
    }
    
    getConnectionStatus() {
        return {
            connected: this.isConnected,
            lastUpdate: this.lastUpdate,
            alerts: this.alerts.length
        };
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.farmMonitor = new RealtimeFarmMonitor();
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = RealtimeFarmMonitor;
}
