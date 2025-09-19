<?php
$activeMenu = 'equipment';
ob_start();
?>

<div class="content-header">
    <h2>EQUIPMENT MANAGEMENT</h2>
    <div class="last-updated">
        <i class="fas fa-cogs"></i> Total Equipment: <span id="equipment-count"><?= count($equipment) ?></span>
    </div>
</div>

<div class="equipment-grid">
    <?php foreach ($equipment as $item): ?>
    <div class="equipment-card status-<?= $item['status'] ?>">
        <div class="equipment-header">
            <h3><?= htmlspecialchars($item['name']) ?></h3>
            <span class="equipment-type"><?= htmlspecialchars($item['type']) ?></span>
        </div>
        
        <div class="equipment-details">
            <div class="detail-item">
                <i class="fas fa-map-marker-alt"></i>
                <span><?= htmlspecialchars($item['location']) ?></span>
            </div>
            
            <div class="detail-item">
                <i class="fas fa-calendar"></i>
                <span>Last Maintenance: <?= $item['last_maintenance'] ? date('M d, Y', strtotime($item['last_maintenance'])) : 'Never' ?></span>
            </div>
        </div>
        
        <div class="equipment-status">
            <div class="status-indicator status-<?= $item['status'] ?>">
                <i class="fas fa-circle"></i>
                <span><?= ucfirst($item['status']) ?></span>
            </div>
        </div>
        
        <div class="equipment-actions">
            <button class="btn btn-primary" onclick="updateEquipmentStatus(<?= $item['equipment_id'] ?>, '<?= $item['status'] ?>')">
                <i class="fas fa-edit"></i> Update Status
            </button>
            <button class="btn btn-secondary" onclick="updateMaintenance(<?= $item['equipment_id'] ?>)">
                <i class="fas fa-wrench"></i> Maintenance
            </button>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="equipment-summary">
    <div class="summary-card">
        <h3>Equipment Summary</h3>
        <div class="summary-stats">
            <div class="stat">
                <span class="stat-value"><?= count(array_filter($equipment, fn($e) => $e['status'] === 'online' || $e['status'] === 'active')) ?></span>
                <span class="stat-label">Active</span>
            </div>
            <div class="stat">
                <span class="stat-value"><?= count(array_filter($equipment, fn($e) => $e['status'] === 'maintenance')) ?></span>
                <span class="stat-label">Maintenance</span>
            </div>
            <div class="stat">
                <span class="stat-value"><?= count(array_filter($equipment, fn($e) => $e['status'] === 'offline')) ?></span>
                <span class="stat-label">Offline</span>
            </div>
        </div>
    </div>
</div>


<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
