<?php
/**
 * Settings Page - System Configuration and User Preferences
 */
?>

<div class="settings-container">
    <div class="settings-header">
        <h1><i class="fas fa-cog"></i> Settings</h1>
        <p>Configure system settings and manage your preferences</p>
    </div>

    <div class="settings-tabs">
        <button class="tab-btn active" data-tab="system">
            <i class="fas fa-server"></i> System Settings
        </button>
        <button class="tab-btn" data-tab="user">
            <i class="fas fa-user"></i> User Preferences
        </button>
        <?php if (isset($currentUser) && $currentUser['role'] === 'admin'): ?>
        <button class="tab-btn" data-tab="maintenance">
            <i class="fas fa-tools"></i> Maintenance
        </button>
        <?php endif; ?>
    </div>

    <!-- System Settings Tab -->
    <div class="settings-content" id="system-settings">
        <div class="settings-card">
            <h3><i class="fas fa-farm"></i> Farm Configuration</h3>
            <form id="system-settings-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="farm_name">Farm Name</label>
                        <input type="text" id="farm_name" name="farm_name" 
                               value="<?= htmlspecialchars($settings['farm_name'] ?? '') ?>" 
                               placeholder="Enter farm name">
                    </div>
                    <div class="form-group">
                        <label for="timezone">Timezone</label>
                        <select id="timezone" name="timezone">
                            <option value="UTC" <?= ($settings['timezone'] ?? '') === 'UTC' ? 'selected' : '' ?>>UTC</option>
                            <option value="America/New_York" <?= ($settings['timezone'] ?? '') === 'America/New_York' ? 'selected' : '' ?>>Eastern Time</option>
                            <option value="America/Chicago" <?= ($settings['timezone'] ?? '') === 'America/Chicago' ? 'selected' : '' ?>>Central Time</option>
                            <option value="America/Denver" <?= ($settings['timezone'] ?? '') === 'America/Denver' ? 'selected' : '' ?>>Mountain Time</option>
                            <option value="America/Los_Angeles" <?= ($settings['timezone'] ?? '') === 'America/Los_Angeles' ? 'selected' : '' ?>>Pacific Time</option>
                            <option value="Europe/London" <?= ($settings['timezone'] ?? '') === 'Europe/London' ? 'selected' : '' ?>>London</option>
                            <option value="Europe/Paris" <?= ($settings['timezone'] ?? '') === 'Europe/Paris' ? 'selected' : '' ?>>Paris</option>
                            <option value="Asia/Tokyo" <?= ($settings['timezone'] ?? '') === 'Asia/Tokyo' ? 'selected' : '' ?>>Tokyo</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="temperature_unit">Temperature Unit</label>
                        <select id="temperature_unit" name="temperature_unit">
                            <option value="celsius" <?= ($settings['temperature_unit'] ?? '') === 'celsius' ? 'selected' : '' ?>>Celsius (°C)</option>
                            <option value="fahrenheit" <?= ($settings['temperature_unit'] ?? '') === 'fahrenheit' ? 'selected' : '' ?>>Fahrenheit (°F)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="notification_email">Notification Email</label>
                        <input type="email" id="notification_email" name="notification_email" 
                               value="<?= htmlspecialchars($settings['notification_email'] ?? '') ?>" 
                               placeholder="admin@farm.com">
                    </div>
                </div>
            </form>
        </div>

        <div class="settings-card">
            <h3><i class="fas fa-thermometer-half"></i> Sensor Thresholds</h3>
            <form id="sensor-settings-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="humidity_threshold">Humidity Threshold (%)</label>
                        <input type="number" id="humidity_threshold" name="humidity_threshold" 
                               value="<?= htmlspecialchars($settings['humidity_threshold'] ?? '') ?>" 
                               min="0" max="100" step="1">
                        <small>Alert when humidity exceeds this value</small>
                    </div>
                    <div class="form-group">
                        <label for="soil_moisture_threshold">Soil Moisture Threshold (%)</label>
                        <input type="number" id="soil_moisture_threshold" name="soil_moisture_threshold" 
                               value="<?= htmlspecialchars($settings['soil_moisture_threshold'] ?? '') ?>" 
                               min="0" max="100" step="1">
                        <small>Alert when soil moisture falls below this value</small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="sensor_update_interval">Sensor Update Interval (seconds)</label>
                        <input type="number" id="sensor_update_interval" name="sensor_update_interval" 
                               value="<?= htmlspecialchars($settings['sensor_update_interval'] ?? '') ?>" 
                               min="5" max="300" step="5">
                        <small>How often sensors send data (5-300 seconds)</small>
                    </div>
                    <div class="form-group">
                        <label for="data_retention_days">Data Retention (days)</label>
                        <input type="number" id="data_retention_days" name="data_retention_days" 
                               value="<?= htmlspecialchars($settings['data_retention_days'] ?? '') ?>" 
                               min="30" max="3650" step="1">
                        <small>How long to keep historical data</small>
                    </div>
                </div>
            </form>
        </div>

        <div class="settings-card">
            <h3><i class="fas fa-shield-alt"></i> System Options</h3>
            <form id="system-options-form">
                <div class="form-row">
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" id="backup_enabled" name="backup_enabled" 
                                   <?= ($settings['backup_enabled'] ?? false) ? 'checked' : '' ?>>
                            <span class="checkmark"></span>
                            Enable Automatic Backups
                        </label>
                        <small>Automatically backup system data daily</small>
                    </div>
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" id="maintenance_mode" name="maintenance_mode" 
                                   <?= ($settings['maintenance_mode'] ?? false) ? 'checked' : '' ?>>
                            <span class="checkmark"></span>
                            Maintenance Mode
                        </label>
                        <small>Put system in maintenance mode (admin access only)</small>
                    </div>
                </div>
            </form>
        </div>

        <div class="settings-actions">
            <button type="button" class="btn btn-primary" onclick="saveSystemSettings()">
                <i class="fas fa-save"></i> Save System Settings
            </button>
        </div>
    </div>

    <!-- User Preferences Tab -->
    <div class="settings-content" id="user-settings" style="display: none;">
        <div class="settings-card">
            <h3><i class="fas fa-palette"></i> Appearance</h3>
            <form id="appearance-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="theme">Theme</label>
                        <select id="theme" name="theme">
                            <option value="light" <?= ($userPreferences['theme'] ?? '') === 'light' ? 'selected' : '' ?>>Light</option>
                            <option value="dark" <?= ($userPreferences['theme'] ?? '') === 'dark' ? 'selected' : '' ?>>Dark</option>
                            <option value="auto" <?= ($userPreferences['theme'] ?? '') === 'auto' ? 'selected' : '' ?>>Auto</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="language">Language</label>
                        <select id="language" name="language">
                            <option value="en" <?= ($userPreferences['language'] ?? '') === 'en' ? 'selected' : '' ?>>English</option>
                            <option value="es" <?= ($userPreferences['language'] ?? '') === 'es' ? 'selected' : '' ?>>Español</option>
                            <option value="fr" <?= ($userPreferences['language'] ?? '') === 'fr' ? 'selected' : '' ?>>Français</option>
                            <option value="de" <?= ($userPreferences['language'] ?? '') === 'de' ? 'selected' : '' ?>>Deutsch</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="dashboard_layout">Dashboard Layout</label>
                        <select id="dashboard_layout" name="dashboard_layout">
                            <option value="default" <?= ($userPreferences['dashboard_layout'] ?? '') === 'default' ? 'selected' : '' ?>>Default</option>
                            <option value="compact" <?= ($userPreferences['dashboard_layout'] ?? '') === 'compact' ? 'selected' : '' ?>>Compact</option>
                            <option value="detailed" <?= ($userPreferences['dashboard_layout'] ?? '') === 'detailed' ? 'selected' : '' ?>>Detailed</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>

        <div class="settings-card">
            <h3><i class="fas fa-bell"></i> Notifications</h3>
            <form id="notifications-form">
                <div class="form-row">
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" id="notifications" name="notifications" 
                                   <?= ($userPreferences['notifications'] ?? true) ? 'checked' : '' ?>>
                            <span class="checkmark"></span>
                            Enable Notifications
                        </label>
                        <small>Receive browser notifications for alerts</small>
                    </div>
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" id="email_alerts" name="email_alerts" 
                                   <?= ($userPreferences['email_alerts'] ?? true) ? 'checked' : '' ?>>
                            <span class="checkmark"></span>
                            Email Alerts
                        </label>
                        <small>Receive email notifications for critical alerts</small>
                    </div>
                </div>
            </form>
        </div>

        <div class="settings-actions">
            <button type="button" class="btn btn-primary" onclick="saveUserPreferences()">
                <i class="fas fa-save"></i> Save Preferences
            </button>
        </div>
    </div>

    <!-- Maintenance Tab (Admin Only) -->
    <?php if (isset($currentUser) && $currentUser['role'] === 'admin'): ?>
    <div class="settings-content" id="maintenance-settings" style="display: none;">
        <div class="settings-card">
            <h3><i class="fas fa-database"></i> System Maintenance</h3>
            <div class="maintenance-actions">
                <div class="action-item">
                    <h4>Clear Cache</h4>
                    <p>Remove all cached data to free up space and ensure fresh data loading.</p>
                    <button type="button" class="btn btn-warning" onclick="performMaintenance('cache')">
                        <i class="fas fa-trash"></i> Clear Cache
                    </button>
                </div>

                <div class="action-item">
                    <h4>Clear Logs</h4>
                    <p>Remove old log files to free up disk space.</p>
                    <button type="button" class="btn btn-warning" onclick="performMaintenance('logs')">
                        <i class="fas fa-file-alt"></i> Clear Logs
                    </button>
                </div>

                <div class="action-item">
                    <h4>Reset Settings</h4>
                    <p>Reset all system settings to their default values.</p>
                    <button type="button" class="btn btn-danger" onclick="performMaintenance('settings')">
                        <i class="fas fa-undo"></i> Reset to Defaults
                    </button>
                </div>
            </div>
        </div>

        <div class="settings-card">
            <h3><i class="fas fa-info-circle"></i> System Information</h3>
            <div class="system-info">
                <div class="info-item">
                    <span class="label">PHP Version:</span>
                    <span class="value"><?= PHP_VERSION ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Server Software:</span>
                    <span class="value"><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Database:</span>
                    <span class="value">MySQL/MariaDB</span>
                </div>
                <div class="info-item">
                    <span class="label">Application Version:</span>
                    <span class="value">1.0.0</span>
                </div>
                <div class="info-item">
                    <span class="label">Last Updated:</span>
                    <span class="value"><?= date('Y-m-d H:i:s') ?></span>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.settings-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.settings-header {
    text-align: center;
    margin-bottom: 30px;
}

.settings-header h1 {
    color: #2c3e50;
    margin-bottom: 10px;
    font-size: 2.5rem;
}

.settings-header p {
    color: #7f8c8d;
    font-size: 1.1rem;
}

.settings-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 30px;
    border-bottom: 2px solid #ecf0f1;
    padding-bottom: 0;
}

.tab-btn {
    background: none;
    border: none;
    padding: 15px 25px;
    cursor: pointer;
    font-size: 1rem;
    font-weight: 600;
    color: #7f8c8d;
    border-bottom: 3px solid transparent;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.tab-btn:hover {
    color: #3498db;
    background: #f8f9fa;
}

.tab-btn.active {
    color: #3498db;
    border-bottom-color: #3498db;
    background: #f8f9fa;
}

.settings-content {
    animation: fadeIn 0.3s ease-in;
}

.settings-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-left: 4px solid #3498db;
}

.settings-card h3 {
    color: #2c3e50;
    margin-bottom: 20px;
    font-size: 1.3rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 8px;
    font-size: 0.95rem;
}

.form-group input,
.form-group select {
    padding: 12px;
    border: 2px solid #ecf0f1;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.form-group small {
    color: #7f8c8d;
    font-size: 0.85rem;
    margin-top: 5px;
}

.checkbox-label {
    display: flex !important;
    flex-direction: row !important;
    align-items: center;
    cursor: pointer;
    font-weight: 600;
    color: #2c3e50;
}

.checkbox-label input[type="checkbox"] {
    margin-right: 10px;
    transform: scale(1.2);
}

.settings-actions {
    text-align: center;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #ecf0f1;
}

.btn {
    padding: 12px 25px;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
}

.btn-primary {
    background: #3498db;
    color: white;
}

.btn-primary:hover {
    background: #2980b9;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
}

.btn-warning {
    background: #f39c12;
    color: white;
}

.btn-warning:hover {
    background: #e67e22;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(243, 156, 18, 0.3);
}

.btn-danger {
    background: #e74c3c;
    color: white;
}

.btn-danger:hover {
    background: #c0392b;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
}

.maintenance-actions {
    display: grid;
    gap: 20px;
}

.action-item {
    padding: 20px;
    border: 1px solid #ecf0f1;
    border-radius: 8px;
    background: #f8f9fa;
}

.action-item h4 {
    color: #2c3e50;
    margin-bottom: 8px;
    font-size: 1.1rem;
}

.action-item p {
    color: #7f8c8d;
    margin-bottom: 15px;
    font-size: 0.95rem;
}

.system-info {
    display: grid;
    gap: 15px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #ecf0f1;
}

.info-item:last-child {
    border-bottom: none;
}

.info-item .label {
    font-weight: 600;
    color: #2c3e50;
}

.info-item .value {
    color: #7f8c8d;
    font-family: monospace;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .settings-tabs {
        flex-direction: column;
    }
    
    .tab-btn {
        text-align: left;
    }
}
</style>

<script>
// Tab switching functionality
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.settings-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            // Remove active class from all tabs
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.style.display = 'none');
            
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Show corresponding content
            const targetContent = document.getElementById(targetTab + '-settings');
            if (targetContent) {
                targetContent.style.display = 'block';
            }
        });
    });
});

// Save system settings
function saveSystemSettings() {
    const forms = ['system-settings-form', 'sensor-settings-form', 'system-options-form'];
    const formData = new FormData();
    formData.append('settings_type', 'system');
    
    forms.forEach(formId => {
        const form = document.getElementById(formId);
        if (form) {
            const formDataObj = new FormData(form);
            for (let [key, value] of formDataObj.entries()) {
                formData.append(key, value);
            }
        }
    });
    
    fetch(`${window.BASE_PATH}/settings/update`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Settings saved successfully!', 'success');
        } else {
            showNotification(data.error || 'Failed to save settings', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while saving settings', 'error');
    });
}

// Save user preferences
function saveUserPreferences() {
    const forms = ['appearance-form', 'notifications-form'];
    const formData = new FormData();
    formData.append('settings_type', 'user');
    
    forms.forEach(formId => {
        const form = document.getElementById(formId);
        if (form) {
            const formDataObj = new FormData(form);
            for (let [key, value] of formDataObj.entries()) {
                formData.append(key, value);
            }
        }
    });
    
    fetch(`${window.BASE_PATH}/settings/update`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Preferences saved successfully!', 'success');
        } else {
            showNotification(data.error || 'Failed to save preferences', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while saving preferences', 'error');
    });
}

// Perform maintenance actions
function performMaintenance(action) {
    if (confirm(`Are you sure you want to ${action}? This action cannot be undone.`)) {
        const formData = new FormData();
        formData.append('reset_type', action);
        
        fetch(`${window.BASE_PATH}/settings/reset`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
            } else {
                showNotification(data.error || 'Failed to perform maintenance action', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while performing maintenance', 'error');
        });
    }
}

// Notification system
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'}`;
    notification.innerHTML = `
        ${message}
        <button type="button" class="close" onclick="this.parentElement.remove()">&times;</button>
    `;
    
    // Add to alert container or create one
    let container = document.querySelector('.alert-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'alert-container';
        document.body.appendChild(container);
    }
    
    container.appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}
</script>
