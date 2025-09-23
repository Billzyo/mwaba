<?php
$error = $_GET['error'] ?? '';
$errorMessage = '';

switch ($error) {
    case 'missing_fields':
        $errorMessage = 'Please fill in all required fields.';
        break;
    case 'username_exists':
        $errorMessage = 'Username already exists. Please choose a different username.';
        break;
    case 'email_exists':
        $errorMessage = 'Email already exists. Please use a different email address.';
        break;
    case 'creation_failed':
        $errorMessage = 'Failed to create user. Please try again.';
        break;
    case 'server_error':
        $errorMessage = 'Server error occurred. Please try again.';
        break;
}
?>

<div class="dashboard-content">
    <div class="content-header">
        <div class="header-left">
            <a href="<?= $BASE_PATH ?>/dashboard" class="btn btn-secondary">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="<?= $BASE_PATH ?>/users" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Users
            </a>
            <h2><i class="fas fa-user-plus"></i> Create New User</h2>
        </div>
    </div>
    
    <?php if ($errorMessage): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($errorMessage) ?>
        </div>
    <?php endif; ?>
    
    <div class="form-container">
        <form method="POST" action="<?= $BASE_PATH ?>/users/store" class="user-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" required 
                           placeholder="Enter username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                    <small class="form-help">Username must be unique and cannot be changed later.</small>
                </div>
                
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Enter password" minlength="6">
                    <small class="form-help">Password must be at least 6 characters long.</small>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" required 
                           placeholder="Enter full name" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required 
                           placeholder="Enter email address" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="role">Role *</label>
                <select id="role" name="role" required>
                    <option value="">Select a role</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= $role ?>" <?= ($_POST['role'] ?? '') === $role ? 'selected' : '' ?>>
                            <?= ucfirst($role) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-help">
                    <strong>Admin:</strong> Full access to all features including user management<br>
                    <strong>Farmer:</strong> Access to farm monitoring and data<br>
                    <strong>Viewer:</strong> Read-only access to dashboard
                </small>
            </div>
            
            <div class="form-actions">
                <button type="button" onclick="history.back()" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create User
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.form-container {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 30px;
    margin-top: 20px;
}

.user-form {
    max-width: 800px;
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
    margin-bottom: 8px;
    color: #333;
}

.form-group input,
.form-group select {
    padding: 12px 15px;
    border: 2px solid #e1e5e9;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #4CAF50;
    box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
}

.form-help {
    margin-top: 5px;
    color: #6c757d;
    font-size: 0.875rem;
    line-height: 1.4;
}

.form-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-primary {
    background: linear-gradient(135deg, #4CAF50, #45a049);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(76, 175, 80, 0.4);
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
}

.alert {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.content-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.content-header h2 {
    margin: 0;
    color: #333;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .content-header {
        flex-direction: column;
        gap: 15px;
        align-items: stretch;
    }
}
</style>

<script>
// Password strength indicator
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strength = getPasswordStrength(password);
    updatePasswordStrength(strength);
});

function getPasswordStrength(password) {
    let strength = 0;
    if (password.length >= 6) strength++;
    if (password.length >= 8) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    return strength;
}

function updatePasswordStrength(strength) {
    const help = document.querySelector('#password + .form-help');
    const colors = ['#dc3545', '#fd7e14', '#ffc107', '#28a745', '#20c997'];
    const messages = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
    
    if (strength > 0) {
        help.innerHTML = `Password strength: <span style="color: ${colors[strength - 1]}">${messages[strength - 1]}</span>`;
    } else {
        help.innerHTML = 'Password must be at least 6 characters long.';
    }
}

// Form validation
document.querySelector('.user-form').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password')?.value;
    
    if (password.length < 6) {
        e.preventDefault();
        alert('Password must be at least 6 characters long.');
        return;
    }
    
    if (confirmPassword && password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match.');
        return;
    }
});
</script>
