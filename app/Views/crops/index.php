<?php
$activeMenu = 'crops';
ob_start();
?>

<div class="content-header">
    <h2>CROP MANAGEMENT</h2>
    <div class="last-updated">
        <i class="fas fa-seedling"></i> Total Crops: <span id="crop-count"><?= count($crops) ?></span>
    </div>
</div>

<div class="crops-grid">
    <?php foreach ($crops as $crop): ?>
    <div class="crop-card">
        <div class="crop-header">
            <h3><?= htmlspecialchars($crop['name']) ?></h3>
            <span class="crop-type"><?= htmlspecialchars($crop['crop_type']) ?></span>
        </div>
        
        <div class="crop-details">
            <div class="detail-item">
                <i class="fas fa-ruler"></i>
                <span>Size: <?= htmlspecialchars($crop['size_hectares']) ?> hectares</span>
            </div>
            
            <div class="detail-item">
                <i class="fas fa-calendar-alt"></i>
                <span>Planted: <?= date('M d, Y', strtotime($crop['planting_date'])) ?></span>
            </div>
            
            <div class="detail-item">
                <i class="fas fa-clock"></i>
                <span>Harvest: <?= date('M d, Y', strtotime($crop['estimated_harvest_date'])) ?></span>
            </div>
        </div>
        
        <div class="crop-status">
            <div class="health-status status-<?= $crop['health_status'] ?>">
                <i class="fas fa-heart"></i>
                <span><?= ucfirst($crop['health_status']) ?></span>
            </div>
            
            <div class="harvest-countdown">
                <?php 
                $daysToHarvest = $crop['days_to_harvest'];
                $countdownClass = $daysToHarvest <= 15 ? 'urgent' : ($daysToHarvest <= 30 ? 'warning' : 'normal');
                ?>
                <span class="countdown <?= $countdownClass ?>">
                    <?= $daysToHarvest > 0 ? $daysToHarvest . ' days' : 'Ready for harvest' ?>
                </span>
            </div>
        </div>
        
        <div class="crop-actions">
            <button class="btn btn-primary" onclick="updateCropHealth(<?= $crop['area_id'] ?>)">
                <i class="fas fa-edit"></i> Update Health
            </button>
            <button class="btn btn-secondary" onclick="viewCropDetails(<?= $crop['area_id'] ?>)">
                <i class="fas fa-eye"></i> View Details
            </button>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="crop-summary">
    <div class="summary-card">
        <h3>Crop Summary</h3>
        <div class="summary-stats">
            <div class="stat">
                <span class="stat-value"><?= count($crops) ?></span>
                <span class="stat-label">Total Crops</span>
            </div>
            <div class="stat">
                <span class="stat-value"><?= count(array_filter($crops, fn($c) => $c['health_status'] === 'excellent')) ?></span>
                <span class="stat-label">Excellent Health</span>
            </div>
            <div class="stat">
                <span class="stat-value"><?= count(array_filter($crops, fn($c) => $c['days_to_harvest'] <= 30)) ?></span>
                <span class="stat-label">Near Harvest</span>
            </div>
        </div>
    </div>
</div>


<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
