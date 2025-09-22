# 🚀 Real-time Farm Monitoring Features

This document describes the real-time features implemented in the Farm Monitoring System.

## 📡 Real-time Architecture

### WebSocket Server
- **Port**: 8080
- **Protocol**: WebSocket (ws://)
- **Purpose**: Real-time bidirectional communication between dashboard and IoT devices

### API Endpoints
- **Real-time Data**: `GET /mwaba/api/realtime/data`
- **Chart Data**: `GET /mwaba/api/realtime/charts`
- **Alerts**: `GET /mwaba/api/realtime/alerts`
- **Broadcast**: `POST /mwaba/api/realtime/broadcast`

## 🎯 Features Implemented

### 1. **Real-time Sensor Data Updates**
- Live sensor readings displayed on dashboard
- Automatic status indicators (Normal/Low/High)
- Color-coded status badges
- Real-time value updates without page refresh

### 2. **WebSocket Communication**
- Persistent connection for instant updates
- Automatic reconnection on connection loss
- Connection status indicator in dashboard header
- Fallback polling mechanism if WebSocket fails

### 3. **Browser Push Notifications**
- Real-time alerts for sensor threshold violations
- Browser notification permission handling
- In-page alert notifications with auto-dismiss
- Alert history tracking

### 4. **Live Charts and Visualization**
- Chart.js integration for real-time graphs
- Temperature, humidity, and soil moisture charts
- Automatic chart updates with new data points
- Smooth animations and transitions

### 5. **Alert System**
- Configurable sensor thresholds
- Automatic alert generation
- Alert categorization (warning, error, info)
- Alert persistence and history

## 🛠️ Technical Implementation

### Backend Components

#### WebSocket Server (`websocket-server.php`)
```php
class FarmMonitoringWebSocket implements MessageComponentInterface {
    // Handles WebSocket connections
    // Manages client connections
    // Broadcasts sensor data
    // Processes alerts
}
```

#### Real-time Controller (`app/Controllers/RealtimeController.php`)
```php
class RealtimeController extends BaseController {
    // getLatestData() - Fetch current sensor readings
    // getChartData() - Get historical data for charts
    // getAlerts() - Retrieve active alerts
    // broadcastSensorData() - Broadcast new sensor data
}
```

#### Enhanced Sensor Model (`app/Models/SensorModel.php`)
```php
class SensorModel extends BaseModel {
    // insertSensorReading() - Store sensor data
    // getRealtimeData() - Get formatted real-time data
    // getActiveAlerts() - Retrieve threshold violations
    // getOfflineSensors() - Detect offline sensors
}
```

### Frontend Components

#### Real-time Client (`public/assets/js/realtime.js`)
```javascript
class RealtimeFarmMonitor {
    // WebSocket connection management
    // Real-time data processing
    // Chart updates
    // Alert handling
    // Notification management
}
```

## 🚀 Getting Started

### 1. Install Dependencies
```bash
composer install
```

### 2. Start the Servers

#### Option A: Using the Batch Script (Windows)
```bash
start-realtime.bat
```

#### Option B: Manual Start
```bash
# Terminal 1: Web Server
php -S localhost:8000

# Terminal 2: WebSocket Server
php websocket-server.php
```

### 3. Access the Dashboard
- **Web Dashboard**: http://localhost:8000/mwaba/
- **WebSocket Server**: ws://localhost:8080

### 4. Test Real-time Features
```bash
# Simulate sensor data
php test-sensor-data.php
```

## 📊 Real-time Data Flow

```
IoT Device → API Endpoint → Database → WebSocket Server → Dashboard
     ↓              ↓           ↓            ↓              ↓
Sensor Data → Validation → Storage → Broadcast → Real-time Update
```

### Data Flow Steps:
1. **IoT Device** sends sensor data via HTTP POST
2. **API Endpoint** validates and stores data in database
3. **WebSocket Server** broadcasts data to connected clients
4. **Dashboard** receives real-time updates via WebSocket
5. **Charts and UI** update automatically with new data

## 🎨 User Interface Features

### Dashboard Header
- **Connection Status**: Real-time WebSocket connection indicator
- **Last Update**: Timestamp of most recent data update
- **Reconnect Button**: Manual WebSocket reconnection

### Sensor Cards
- **Live Values**: Real-time sensor readings
- **Status Indicators**: Color-coded status badges
- **Trend Indicators**: Direction arrows for value changes

### Alert System
- **Toast Notifications**: Slide-in alerts from top-right
- **Browser Notifications**: System-level notifications
- **Alert History**: Persistent alert log

### Real-time Charts
- **Temperature Chart**: Live temperature trends
- **Humidity Chart**: Real-time humidity monitoring
- **Soil Moisture Chart**: Moisture level tracking

## 🔧 Configuration

### Sensor Thresholds
```php
$alertThresholds = [
    'temperature' => ['min' => 10, 'max' => 35],
    'humidity' => ['min' => 30, 'max' => 80],
    'soil_moisture' => ['min' => 20, 'max' => 80]
];
```

### Device Mapping
```php
$deviceMapping = [
    'ESP32_ABC123' => [
        'temperature' => 1,
        'humidity' => 2,
        'soil_moisture' => 3
    ]
];
```

## 📱 Browser Compatibility

### WebSocket Support
- ✅ Chrome 16+
- ✅ Firefox 11+
- ✅ Safari 6+
- ✅ Edge 12+

### Notification API
- ✅ Chrome 22+
- ✅ Firefox 22+
- ✅ Safari 6+
- ✅ Edge 14+

## 🔒 Security Features

- **Authentication Required**: All real-time endpoints require login
- **Input Validation**: Sensor data validation and sanitization
- **SQL Injection Prevention**: Prepared statements used
- **XSS Protection**: HTML output escaping

## 📈 Performance Features

- **Connection Pooling**: Efficient WebSocket connections
- **Data Caching**: Reduced database queries
- **Fallback Polling**: HTTP fallback if WebSocket fails
- **Auto-reconnection**: Automatic connection recovery

## 🐛 Troubleshooting

### Common Issues

#### WebSocket Connection Failed
- Check if WebSocket server is running on port 8080
- Verify firewall settings
- Ensure browser supports WebSocket

#### No Real-time Updates
- Check browser console for JavaScript errors
- Verify WebSocket connection status
- Test API endpoints manually

#### Alerts Not Showing
- Check browser notification permissions
- Verify sensor threshold configuration
- Check alert container in DOM

### Debug Commands
```bash
# Test WebSocket connection
curl -i -N -H "Connection: Upgrade" -H "Upgrade: websocket" -H "Sec-WebSocket-Version: 13" -H "Sec-WebSocket-Key: x3JJHMbDL1EzLkh9GBhXDw==" http://localhost:8080/

# Test API endpoints
curl -X GET http://localhost:8000/mwaba/api/realtime/data
curl -X POST -H "Content-Type: application/json" -d '{"device_id":"ESP32_ABC123","temperature":25,"humidity":60,"soil_moisture":40}' http://localhost:8000/mwaba/api/realtime/broadcast
```

## 🔮 Future Enhancements

### Planned Features
- [ ] **Multi-farm Support**: Multiple farm locations
- [ ] **Advanced Analytics**: Machine learning predictions
- [ ] **Mobile App**: React Native mobile application
- [ ] **Voice Alerts**: Text-to-speech notifications
- [ ] **Video Streaming**: Live camera feeds
- [ ] **Weather Integration**: External weather data
- [ ] **Predictive Maintenance**: Equipment failure prediction

### Performance Improvements
- [ ] **Redis Caching**: High-performance data caching
- [ ] **Load Balancing**: Multiple WebSocket servers
- [ ] **CDN Integration**: Static asset optimization
- [ ] **Database Optimization**: Query performance tuning

## 📞 Support

For technical support or feature requests, please contact:
- **Email**: admin@mwabafarm.com
- **Documentation**: [Project README](README.md)
- **Issues**: GitHub Issues (if applicable)

---

**Real-time Farm Monitoring System** - Bringing IoT to Agriculture 🌾📡✨
