<?php
$activeMenu = 'dashboard';
ob_start();
?>

<div class="content-header">
    <h2>FARM DASHBOARD</h2>
    <div class="last-updated">
        <i class="fas fa-sync"></i> Updated: <span id="last-updated">Just now</span>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card temperature">
        <div class="card-header">
            <i class="fas fa-thermometer-half"></i>
            <h3>Temperature</h3>
        </div>
        <div class="card-value">
            <span id="temperature-value"><?= isset($sensorData['temperature']['value']) ? htmlspecialchars($sensorData['temperature']['value']) : 'N/A' ?></span>
            <span class="card-unit">°C</span>
        </div>
        <div class="card-footer">
            <div class="status good" id="temperature-status">
                <i class="fas fa-check-circle"></i>
                Optimal
            </div>
            <div class="trend down" id="temperature-trend">
                <i class="fas fa-arrow-down"></i>
                <span id="temperature-change">0.5</span>°C
            </div>
        </div>
    </div>

    <div class="stat-card humidity">
        <div class="card-header">
            <i class="fas fa-tint"></i>
            <h3>Humidity</h3>
        </div>
        <div class="card-value">
            <span id="humidity-value"><?= isset($sensorData['humidity']['value']) ? htmlspecialchars($sensorData['humidity']['value']) : 'N/A' ?></span>
            <span class="card-unit">%</span>
        </div>
        <div class="card-footer">
            <div class="status good" id="humidity-status">
                <i class="fas fa-check-circle"></i>
                Normal
            </div>
            <div class="trend up" id="humidity-trend">
                <i class="fas fa-arrow-up"></i>
                <span id="humidity-change">1.2</span>%
            </div>
        </div>
    </div>

    <div class="stat-card moisture">
        <div class="card-header">
            <i class="fas fa-water"></i>
            <h3>Soil Moisture</h3>
        </div>
        <div class="card-value">
            <span id="moisture-value"><?= isset($sensorData['soil_moisture']['value']) ? htmlspecialchars($sensorData['soil_moisture']['value']) : 'N/A' ?></span>
            <span class="card-unit">%</span>
        </div>
        <div class="card-footer">
            <div class="status warning" id="moisture-status">
                <i class="fas fa-exclamation-triangle"></i>
                Needs attention
            </div>
            <div class="trend down" id="moisture-trend">
                <i class="fas fa-arrow-down"></i>
                <span id="moisture-change">3.8</span>%
            </div>
        </div>
    </div>
</div>

<div class="charts-container">
    <div class="chart-card">
        <div class="chart-header">
            <h3>Environmental Trends</h3>
            <i class="fas fa-expand"></i>
        </div>
        <div class="chart-container">
            <canvas id="environmentChart"></canvas>
        </div>
    </div>

    <div class="chart-card">
        <div class="chart-header">
            <h3>Crop Health</h3>
            <i class="fas fa-expand"></i>
        </div>
        <div class="chart-container">
            <canvas id="cropHealthChart"></canvas>
        </div>
    </div>
</div>

<div class="farm-map">
    <div class="map-overlay">
        <h3>Field 4 - Wheat</h3>
        <div class="sensor-grid">
            <div class="sensor-item">
                <i class="fas fa-sun sensor-icon-light"></i>
                <span>Light: <span id="light-value"><?= isset($sensorData['light']['value']) ? htmlspecialchars($sensorData['light']['value']) : 'N/A' ?></span>%</span>
            </div>
            <div class="sensor-item">
                <i class="fas fa-wind sensor-icon-wind"></i>
                <span>Wind: <span id="wind-value"><?= isset($sensorData['wind']['value']) ? htmlspecialchars($sensorData['wind']['value']) : 'N/A' ?></span> km/h</span>
            </div>
            <div class="sensor-item">
                <i class="fas fa-flask sensor-icon-ph"></i>
                <span>pH: <span id="ph-value"><?= isset($sensorData['ph']['value']) ? htmlspecialchars($sensorData['ph']['value']) : 'N/A' ?></span></span>
            </div>
        </div>
    </div>
</div>

<script>
// Prepare data from PHP for JavaScript
const chartData = <?= json_encode($chartData); ?>;
const cropData = <?= json_encode($cropData); ?>;
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
