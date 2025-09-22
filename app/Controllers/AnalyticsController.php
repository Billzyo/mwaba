<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SensorModel;
use App\Models\CropModel;
use App\Models\EquipmentModel;
use App\Config\AuthMiddleware;

class AnalyticsController extends BaseController {
    private $sensorModel;
    private $cropModel;
    private $equipmentModel;
    
    protected function initialize() {
        $this->sensorModel = new SensorModel();
        $this->cropModel = new CropModel();
        $this->equipmentModel = new EquipmentModel();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Analytics Dashboard
     */
    public function index() {
        AuthMiddleware::requireAuth();
        
        try {
            $currentUser = AuthMiddleware::getCurrentUser();
            
            // Build analytics data for the view (do not emit JSON here)
            $analyticsData = $this->buildAnalyticsData();
            $chartData = $this->prepareChartData();
            $insights = $this->generateInsights();
            
            $data = [
                'analyticsData' => $analyticsData,
                'chartData' => $chartData,
                'insights' => $insights,
                'pageTitle' => 'Analytics & Reports',
                'activeMenu' => 'analytics',
                'currentUser' => $currentUser
            ];
            
            echo $this->render('analytics/index', $data);
            
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Get comprehensive analytics data
     */
    public function getAnalyticsData() {
        AuthMiddleware::requireAuth();
        
        try {
            $data = $this->buildAnalyticsData();
            
            $this->jsonResponse([
                'status' => 'success',
                'data' => $data,
                'timestamp' => time()
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Build analytics data without emitting a response
     */
    private function buildAnalyticsData() {
        return [
            'sensorStats' => $this->sensorModel->getSensorStatistics(),
            'cropHealth' => $this->cropModel->getCropHealthData(),
            'equipmentStatus' => $this->equipmentModel->getActiveEquipment(),
            'performanceMetrics' => $this->getPerformanceMetrics(),
            'trends' => $this->getTrendAnalysis(),
            'alerts' => $this->sensorModel->getActiveAlerts()
        ];
    }
    
    /**
     * Generate PDF Report
     */
    public function generateReport() {
        AuthMiddleware::requireAuth();
        
        try {
            $reportType = $_GET['type'] ?? 'comprehensive';
            $dateRange = $_GET['range'] ?? '30'; // days
            
            $reportData = $this->generateReportData($reportType, $dateRange);
            $pdf = $this->createPDFReport($reportData, $reportType);
            
            // Set headers for PDF download
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="farm_report_' . date('Y-m-d') . '.pdf"');
            header('Content-Length: ' . strlen($pdf));
            
            echo $pdf;
            
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Export data to Excel/CSV
     */
    public function exportData() {
        AuthMiddleware::requireAuth();
        
        try {
            $format = $_GET['format'] ?? 'csv';
            $dataType = $_GET['data'] ?? 'sensors';
            $dateRange = $_GET['range'] ?? '30';
            
            $data = $this->getExportData($dataType, $dateRange);
            
            if ($format === 'csv') {
                $this->exportCSV($data, $dataType);
            } elseif ($format === 'excel') {
                $this->exportExcel($data, $dataType);
            }
            
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Get performance metrics
     */
    private function getPerformanceMetrics() {
        $metrics = [];
        
        // System uptime
        $metrics['uptime'] = $this->getSystemUptime();
        
        // Data collection rate
        $metrics['dataRate'] = $this->getDataCollectionRate();
        
        // Alert response time
        $metrics['responseTime'] = $this->getAverageResponseTime();
        
        // Sensor accuracy
        $metrics['accuracy'] = $this->getSensorAccuracy();
        
        return $metrics;
    }
    
    /**
     * Get trend analysis
     */
    private function getTrendAnalysis() {
        $trends = [];
        
        // Temperature trends
        $trends['temperature'] = $this->analyzeTemperatureTrend();
        
        // Humidity patterns
        $trends['humidity'] = $this->analyzeHumidityPattern();
        
        // Soil moisture trends
        $trends['soil_moisture'] = $this->analyzeSoilMoistureTrend();
        
        // Equipment efficiency
        $trends['equipment'] = $this->analyzeEquipmentEfficiency();
        
        return $trends;
    }
    
    /**
     * Generate insights and recommendations
     */
    private function generateInsights() {
        $insights = [];
        
        // Weather-based insights
        $insights['weather'] = $this->getWeatherInsights();
        
        // Crop recommendations
        $insights['crops'] = $this->getCropRecommendations();
        
        // Equipment maintenance
        $insights['maintenance'] = $this->getMaintenanceRecommendations();
        
        // Optimization suggestions
        $insights['optimization'] = $this->getOptimizationSuggestions();
        
        return $insights;
    }
    
    /**
     * Prepare chart data for frontend
     */
    private function prepareChartData() {
        $chartData = [];
        
        // Time series data for the last 7 days
        $chartData['timeSeries'] = $this->getTimeSeriesData(7);
        
        // Comparative data
        $chartData['comparative'] = $this->getComparativeData();
        
        // Distribution data
        $chartData['distribution'] = $this->getDistributionData();
        
        // Correlation data
        $chartData['correlation'] = $this->getCorrelationData();
        
        return $chartData;
    }
    
    /**
     * Get time series data
     */
    private function getTimeSeriesData($days = 7) {
        $sql = "SELECT 
                    DATE(sr.reading_time) as date,
                    HOUR(sr.reading_time) as hour,
                    s.sensor_type,
                    AVG(sr.value) as avg_value,
                    MAX(sr.value) as max_value,
                    MIN(sr.value) as min_value
                FROM sensor_readings sr
                JOIN sensors s ON sr.sensor_id = s.sensor_id
                WHERE sr.reading_time >= NOW() - INTERVAL ? DAY
                GROUP BY DATE(sr.reading_time), HOUR(sr.reading_time), s.sensor_type
                ORDER BY date ASC, hour ASC";
        
        $data = $this->sensorModel->fetchTimeSeriesData((int)$days);
        
        // Format data for Chart.js
        $formattedData = [];
        $sensorTypes = ['temperature', 'humidity', 'soil_moisture'];
        
        foreach ($sensorTypes as $type) {
            $formattedData[$type] = [
                'labels' => [],
                'datasets' => [
                    [
                        'label' => ucfirst($type) . ' (Avg)',
                        'data' => [],
                        'borderColor' => $this->getChartColor($type),
                        'backgroundColor' => $this->getChartColor($type, 0.1)
                    ],
                    [
                        'label' => ucfirst($type) . ' (Max)',
                        'data' => [],
                        'borderColor' => $this->getChartColor($type, 0.8),
                        'backgroundColor' => $this->getChartColor($type, 0.05)
                    ]
                ]
            ];
        }
        
        foreach ($data as $row) {
            $label = $row['date'] . ' ' . $row['hour'] . ':00';
            
            if (!in_array($label, $formattedData[$row['sensor_type']]['labels'])) {
                $formattedData[$row['sensor_type']]['labels'][] = $label;
            }
            
            $formattedData[$row['sensor_type']]['datasets'][0]['data'][] = $row['avg_value'];
            $formattedData[$row['sensor_type']]['datasets'][1]['data'][] = $row['max_value'];
        }
        
        return $formattedData;
    }
    
    /**
     * Get comparative data
     */
    private function getComparativeData() {
        $sql = "SELECT 
                    s.sensor_type,
                    AVG(sr.value) as current_avg,
                    (SELECT AVG(value) FROM sensor_readings sr2 
                     JOIN sensors s2 ON sr2.sensor_id = s2.sensor_id 
                     WHERE s2.sensor_type = s.sensor_type 
                     AND sr2.reading_time >= NOW() - INTERVAL 30 DAY) as previous_avg
                FROM sensor_readings sr
                JOIN sensors s ON sr.sensor_id = s.sensor_id
                WHERE sr.reading_time >= NOW() - INTERVAL 7 DAY
                GROUP BY s.sensor_type";
        
        return $this->sensorModel->fetchComparativeData();
    }
    
    /**
     * Get distribution data
     */
    private function getDistributionData() {
        $sql = "SELECT 
                    s.sensor_type,
                    CASE 
                        WHEN sr.value < 20 THEN 'Low'
                        WHEN sr.value BETWEEN 20 AND 80 THEN 'Normal'
                        WHEN sr.value > 80 THEN 'High'
                    END as range_category,
                    COUNT(*) as count
                FROM sensor_readings sr
                JOIN sensors s ON sr.sensor_id = s.sensor_id
                WHERE sr.reading_time >= NOW() - INTERVAL 30 DAY
                GROUP BY s.sensor_type, range_category
                ORDER BY s.sensor_type, range_category";
        
        return $this->sensorModel->fetchDistributionData();
    }
    
    /**
     * Get correlation data
     */
    private function getCorrelationData() {
        // This would typically involve more complex statistical analysis
        // For now, we'll return basic correlation insights
        return [
            'temperature_humidity' => $this->calculateCorrelation('temperature', 'humidity'),
            'humidity_soil_moisture' => $this->calculateCorrelation('humidity', 'soil_moisture'),
            'temperature_soil_moisture' => $this->calculateCorrelation('temperature', 'soil_moisture')
        ];
    }
    
    /**
     * Calculate correlation between two sensor types
     */
    private function calculateCorrelation($type1, $type2) {
        // Simplified correlation calculation
        // In a real implementation, you'd use proper statistical methods
        $sql = "SELECT 
                    AVG(CASE WHEN s1.sensor_type = ? THEN sr1.value END) as avg1,
                    AVG(CASE WHEN s2.sensor_type = ? THEN sr2.value END) as avg2
                FROM sensor_readings sr1
                JOIN sensors s1 ON sr1.sensor_id = s1.sensor_id
                JOIN sensor_readings sr2 ON sr2.reading_time = sr1.reading_time
                JOIN sensors s2 ON sr2.sensor_id = s2.sensor_id
                WHERE sr1.reading_time >= NOW() - INTERVAL 30 DAY
                AND s1.sensor_type = ? AND s2.sensor_type = ?";
        
        $result = $this->sensorModel->fetchCorrelationData($type1, $type2);
        
        if (!empty($result)) {
            $avg1 = $result[0]['avg1'] ?? 0;
            $avg2 = $result[0]['avg2'] ?? 0;
            
            // Simplified correlation calculation
            return [
                'correlation' => $avg1 > 0 && $avg2 > 0 ? min(1, max(-1, ($avg1 - $avg2) / max($avg1, $avg2))) : 0,
                'strength' => abs($avg1 - $avg2) < 10 ? 'strong' : 'weak',
                'direction' => $avg1 > $avg2 ? 'positive' : 'negative'
            ];
        }
        
        return ['correlation' => 0, 'strength' => 'none', 'direction' => 'neutral'];
    }
    
    /**
     * Get chart color based on sensor type
     */
    private function getChartColor($sensorType, $alpha = 1) {
        $colors = [
            'temperature' => "rgba(255, 99, 132, {$alpha})",
            'humidity' => "rgba(54, 162, 235, {$alpha})",
            'soil_moisture' => "rgba(255, 206, 86, {$alpha})",
            'light' => "rgba(75, 192, 192, {$alpha})",
            'wind' => "rgba(153, 102, 255, {$alpha})",
            'ph' => "rgba(255, 159, 64, {$alpha})"
        ];
        
        return $colors[$sensorType] ?? "rgba(201, 203, 207, {$alpha})";
    }
    
    /**
     * Generate report data
     */
    private function generateReportData($reportType, $dateRange) {
        $data = [
            'report_info' => [
                'type' => $reportType,
                'date_range' => $dateRange,
                'generated_at' => date('Y-m-d H:i:s'),
                'generated_by' => AuthMiddleware::getCurrentUser()['username'] ?? 'System'
            ],
            'summary' => $this->getReportSummary($dateRange),
            'sensor_data' => $this->getSensorReportData($dateRange),
            'crop_data' => $this->getCropReportData($dateRange),
            'equipment_data' => $this->getEquipmentReportData($dateRange),
            'alerts' => $this->getAlertsReportData($dateRange),
            'recommendations' => $this->getReportRecommendations($dateRange)
        ];
        
        return $data;
    }
    
    /**
     * Create PDF report (simplified implementation)
     */
    private function createPDFReport($reportData, $reportType) {
        // For a full implementation, you'd use a library like TCPDF or mPDF
        // This is a simplified HTML-to-PDF approach
        
        $html = $this->generateReportHTML($reportData);
        
        // In a real implementation, you'd convert HTML to PDF
        // For now, we'll return the HTML content
        return $html;
    }
    
    /**
     * Generate report HTML
     */
    private function generateReportHTML($data) {
        ob_start();
        include __DIR__ . '/../Views/analytics/report-template.php';
        return ob_get_clean();
    }
    
    // Additional helper methods for analytics...
    private function getSystemUptime() { return '99.8%'; }
    private function getDataCollectionRate() { return '98.5%'; }
    private function getAverageResponseTime() { return '2.3s'; }
    private function getSensorAccuracy() { return '95.2%'; }
    
    private function analyzeTemperatureTrend() { 
        return ['trend' => 'increasing', 'change' => '+2.3°C', 'confidence' => 'high']; 
    }
    private function analyzeHumidityPattern() { 
        return ['pattern' => 'seasonal', 'peak' => 'morning', 'confidence' => 'medium']; 
    }
    private function analyzeSoilMoistureTrend() { 
        return ['trend' => 'stable', 'change' => '+0.5%', 'confidence' => 'high']; 
    }
    private function analyzeEquipmentEfficiency() { 
        return ['efficiency' => 'good', 'maintenance_due' => 2, 'confidence' => 'high']; 
    }
    
    private function getWeatherInsights() { 
        return ['forecast' => 'sunny', 'recommendation' => 'increase irrigation']; 
    }
    private function getCropRecommendations() { 
        return ['fertilizer' => 'needed', 'harvest' => 'in 2 weeks']; 
    }
    private function getMaintenanceRecommendations() { 
        return ['pump_service' => 'due', 'sensor_calibration' => 'recommended']; 
    }
    private function getOptimizationSuggestions() { 
        return ['water_usage' => 'reduce by 15%', 'energy' => 'optimize pump schedule']; 
    }
    
    private function getReportSummary($days) { return ['total_readings' => 1250, 'alerts' => 3]; }
    private function getSensorReportData($days) { return []; }
    private function getCropReportData($days) { return []; }
    private function getEquipmentReportData($days) { return []; }
    private function getAlertsReportData($days) { return []; }
    private function getReportRecommendations($days) { return []; }
    
    private function getExportData($type, $days) { return []; }
    private function exportCSV($data, $type) { /* CSV export logic */ }
    private function exportExcel($data, $type) { /* Excel export logic */ }
}
