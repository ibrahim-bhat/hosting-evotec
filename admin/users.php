<?php
require_once '../config.php';
require_once '../components/auth_helper.php';
require_once '../components/admin_helper.php';
require_once '../components/flash_message.php';

// Require admin access
requireAdmin();

// Handle AJAX requests for user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $action = $_POST['action'];
    $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    
    // Prevent self-modification for certain actions
    if (in_array($action, ['update_user', 'update_status', 'update_role', 'delete']) && $userId == $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'You cannot modify your own account']);
        exit;
    }
    
    switch ($action) {
        case 'add_user':
            $name = sanitizeInput($_POST['name']);
            $email = sanitizeInput($_POST['email']);
            $phone = sanitizeInput($_POST['phone']);
            $password = $_POST['password'];
            $role = $_POST['role'];
            $status = $_POST['status'];
            
            // Validate inputs
            if (empty($name) || empty($email) || empty($phone) || empty($password)) {
                echo json_encode(['success' => false, 'message' => 'All fields are required']);
                break;
            }
            
            if (!validateEmail($email)) {
                echo json_encode(['success' => false, 'message' => 'Invalid email format']);
                break;
            }
            
            if (!validatePhone($phone)) {
                echo json_encode(['success' => false, 'message' => 'Invalid phone number']);
                break;
            }
            
            if (!validatePassword($password)) {
                echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long']);
                break;
            }
            
            if (!in_array($role, ['admin', 'user', 'moderator'])) {
                echo json_encode(['success' => false, 'message' => 'Invalid role']);
                break;
            }
            
            if (!in_array($status, ['active', 'inactive', 'blocked'])) {
                echo json_encode(['success' => false, 'message' => 'Invalid status']);
                break;
            }
            
            // Check if email already exists
            if (emailExists($conn, $email)) {
                echo json_encode(['success' => false, 'message' => 'Email already exists']);
                break;
            }
            
            // Hash password and insert user
            $hashedPassword = hashPassword($password);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, role, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $name, $email, $hashedPassword, $phone, $role, $status);
            $success = $stmt->execute();
            $stmt->close();
            
            echo json_encode(['success' => $success, 'message' => $success ? 'User added successfully' : 'Failed to add user']);
            break;
            
        case 'get_user':
            $user = getUserById($conn, $userId);
            if ($user) {
                // Don't send password
                unset($user['password']);
                echo json_encode(['success' => true, 'user' => $user]);
            } else {
                echo json_encode(['success' => false, 'message' => 'User not found']);
            }
            break;
            
        case 'update_user':
            $name = sanitizeInput($_POST['name']);
            $email = sanitizeInput($_POST['email']);
            $phone = sanitizeInput($_POST['phone']);
            $role = $_POST['role'];
            $status = $_POST['status'];
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            
            // Validate inputs
            if (empty($name) || empty($email) || empty($phone)) {
                echo json_encode(['success' => false, 'message' => 'All fields are required']);
                break;
            }
            
            if (!validateEmail($email)) {
                echo json_encode(['success' => false, 'message' => 'Invalid email format']);
                break;
            }
            
            if (!validatePhone($phone)) {
                echo json_encode(['success' => false, 'message' => 'Invalid phone number']);
                break;
            }
            
            // Validate password if provided
            if (!empty($password) && !validatePassword($password)) {
                echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long']);
                break;
            }
            
            if (!in_array($role, ['admin', 'user', 'moderator'])) {
                echo json_encode(['success' => false, 'message' => 'Invalid role']);
                break;
            }
            
            if (!in_array($status, ['active', 'inactive', 'blocked'])) {
                echo json_encode(['success' => false, 'message' => 'Invalid status']);
                break;
            }
            
            // Check if email is taken by another user
            $existingUser = getUserByEmail($conn, $email);
            if ($existingUser && $existingUser['id'] != $userId) {
                echo json_encode(['success' => false, 'message' => 'Email already exists']);
                break;
            }
            
            // Update user with or without password
            if (!empty($password)) {
                $hashedPassword = hashPassword($password);
                $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, password = ?, role = ?, status = ? WHERE id = ?");
                $stmt->bind_param("ssssssi", $name, $email, $phone, $hashedPassword, $role, $status, $userId);
            } else {
                $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, role = ?, status = ? WHERE id = ?");
                $stmt->bind_param("sssssi", $name, $email, $phone, $role, $status, $userId);
            }
            
            $success = $stmt->execute();
            $stmt->close();
            
            echo json_encode(['success' => $success, 'message' => $success ? 'User updated successfully' : 'Failed to update user']);
            break;
            
        case 'update_status':
            $status = $_POST['status'];
            if (in_array($status, ['active', 'inactive', 'blocked'])) {
                $success = updateUserStatus($conn, $userId, $status);
                echo json_encode(['success' => $success, 'message' => $success ? 'Status updated successfully' : 'Failed to update status']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid status']);
            }
            break;
            
        case 'update_role':
            $role = $_POST['role'];
            if (in_array($role, ['admin', 'user', 'moderator'])) {
                $success = updateUserRole($conn, $userId, $role);
                echo json_encode(['success' => $success, 'message' => $success ? 'Role updated successfully' : 'Failed to update role']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid role']);
            }
            break;
            
        case 'delete':
            $success = deleteUser($conn, $userId);
            echo json_encode(['success' => $success, 'message' => $success ? 'User deleted successfully' : 'Failed to delete user']);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit;
}

// Get search parameter
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Get all users
$users = getAllUsers($conn, $search);

$pageTitle = "User Management";
include 'includes/header.php';
?>
            <!-- Page Header -->
            <div class="page-header-row">
                <div>
                    <h1 class="page-title">User Management</h1>
                    <p class="page-subtitle">Manage your users and their roles</p>
                </div>
                <button class="btn-add-user" onclick="openAddUserModal()">
                    <i class="bi bi-person-plus-fill"></i>
                    Add User
                </button>
            </div>
            
            <!-- Flash Message -->
            <div id="flashMessage"></div>
            
            <!-- User Management Card -->
            <div class="content-card">
                <!-- Search Bar -->
                <form method="GET" class="search-container" id="searchForm">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" 
                           class="search-input" 
                           name="search" 
                           placeholder="Search users..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </form>
                
                <!-- Users Table -->
                <div class="table-responsive">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $user): ?>
                                    <tr data-user-id="<?php echo $user['id']; ?>">
                                        <td>
                                            <div class="user-name"><?php echo htmlspecialchars($user['name']); ?></div>
                                        </td>
                                        <td>
                                            <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                                        </td>
                                        <td>
                                            <span class="role-badge <?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span>
                                        </td>
                                        <td>
                                            <span class="status-badge-table <?php echo $user['status']; ?>"><?php echo ucfirst($user['status']); ?></span>
                                        </td>
                                        <td>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <div class="dropdown">
                                                    <button class="btn-action" type="button" id="dropdownMenuButton<?php echo $user['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="bi bi-three-dots-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton<?php echo $user['id']; ?>">
                                                        <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); editUser(<?php echo $user['id']; ?>)">
                                                            <i class="bi bi-pencil-fill"></i> Edit User
                                                        </a></li>
                                                        <li><a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); deleteUser(<?php echo $user['id']; ?>)">
                                                            <i class="bi bi-trash-fill"></i> Delete User
                                                        </a></li>
                                                    </ul>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">You</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <?php echo $search ? 'No users found matching your search' : 'No users found'; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        
<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addUserForm">
                    <div class="mb-3">
                        <label for="add_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="add_name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="add_email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add_phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="add_phone" name="phone" placeholder="+1 (555) 000-0000" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add_password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="add_password" name="password" placeholder="Min. 8 characters" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add_role" class="form-label">Role</label>
                        <select class="form-select" id="add_role" name="role" required>
                            <option value="user">User</option>
                            <option value="moderator">Moderator</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add_status" class="form-label">Status</label>
                        <select class="form-select" id="add_status" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="blocked">Blocked</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addUser()">Add User</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <input type="hidden" id="edit_user_id" name="user_id">
                    
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="edit_phone" name="phone" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_password" class="form-label">New Password <small class="text-muted">(Leave blank to keep current)</small></label>
                        <input type="password" class="form-control" id="edit_password" name="password" placeholder="Enter new password (optional)">
                        <small class="form-text text-muted">Min. 8 characters</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_role" class="form-label">Role</label>
                        <select class="form-select" id="edit_role" name="role" required>
                            <option value="user">User</option>
                            <option value="moderator">Moderator</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Status</label>
                        <select class="form-select" id="edit_status" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="blocked">Blocked</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveUser()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-submit search form
document.querySelector('.search-input').addEventListener('input', function() {
    clearTimeout(this.searchTimer);
    this.searchTimer = setTimeout(function() {
        document.getElementById('searchForm').submit();
    }, 500);
});

// Show flash message
function showFlash(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill';
    
    document.getElementById('flashMessage').innerHTML = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="bi ${icon}"></i> <span>${message}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    setTimeout(() => {
        const alert = document.querySelector('.alert');
        if (alert) alert.remove();
    }, 5000);
}

// Open add user modal
function openAddUserModal() {
    document.getElementById('addUserForm').reset();
    const modal = new bootstrap.Modal(document.getElementById('addUserModal'));
    modal.show();
}

// Add new user
function addUser() {
    const formData = new FormData(document.getElementById('addUserForm'));
    formData.append('action', 'add_user');
    
    fetch('users.php', {
        method: 'POST',
        body: new URLSearchParams(formData)
    })
    .then(res => res.json())
    .then(data => {
        showFlash(data.success ? 'success' : 'error', data.message);
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('addUserModal')).hide();
            setTimeout(() => location.reload(), 1500);
        }
    });
}

// Edit user
function editUser(userId) {
    event.preventDefault();
    
    fetch('users.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=get_user&user_id=${userId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('edit_user_id').value = data.user.id;
            document.getElementById('edit_name').value = data.user.name;
            document.getElementById('edit_email').value = data.user.email;
            document.getElementById('edit_phone').value = data.user.phone || '';
            document.getElementById('edit_role').value = data.user.role;
            document.getElementById('edit_status').value = data.user.status;
            
            const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
            modal.show();
        } else {
            showFlash('error', data.message);
        }
    });
}

// Save user changes
function saveUser() {
    const formData = new FormData(document.getElementById('editUserForm'));
    formData.append('action', 'update_user');
    
    fetch('users.php', {
        method: 'POST',
        body: new URLSearchParams(formData)
    })
    .then(res => res.json())
    .then(data => {
        showFlash(data.success ? 'success' : 'error', data.message);
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('editUserModal')).hide();
            setTimeout(() => location.reload(), 1500);
        }
    });
}

// Update user status
function updateStatus(userId, status) {
    event.preventDefault();
    if (!confirm('Are you sure you want to change this user\'s status?')) return;
    
    fetch('users.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=update_status&user_id=${userId}&status=${status}`
    })
    .then(res => res.json())
    .then(data => {
        showFlash(data.success ? 'success' : 'error', data.message);
        if (data.success) setTimeout(() => location.reload(), 1500);
    });
}

// Update user role
function updateRole(userId, role) {
    event.preventDefault();
    if (!confirm('Are you sure you want to change this user\'s role?')) return;
    
    fetch('users.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=update_role&user_id=${userId}&role=${role}`
    })
    .then(res => res.json())
    .then(data => {
        showFlash(data.success ? 'success' : 'error', data.message);
        if (data.success) setTimeout(() => location.reload(), 1500);
    });
}

// Delete user
function deleteUser(userId) {
    event.preventDefault();
    if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) return;
    
    fetch('users.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=delete&user_id=${userId}`
    })
    .then(res => res.json())
    .then(data => {
        showFlash(data.success ? 'success' : 'error', data.message);
        if (data.success) setTimeout(() => location.reload(), 1500);
    });
}
</script>

<?php include 'includes/footer.php'; ?>
