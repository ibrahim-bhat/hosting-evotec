<?php
require_once 'includes/header.php';
require_once '../components/user_helper.php';

$userId = $_SESSION['user_id'];

// Get search parameter
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Get user's websites
$websites = getUserWebsites($conn, $userId, $search);

$pageTitle = "My Websites";
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">My Websites</h1>
            <p class="page-subtitle">Manage your hosted websites and domains</p>
        </div>
    </div>
</div>

<!-- Search -->
<?php if (!empty($websites)): ?>
    <div class="search-container">
        <form method="GET" class="d-flex">
            <div class="position-relative flex-fill">
                <i class="bi bi-search search-icon"></i>
                <input type="text" 
                       class="form-control search-input" 
                       name="search" 
                       placeholder="Search websites by name or domain..."
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <button type="submit" class="btn btn-primary ms-2">
                <i class="bi bi-search"></i>
            </button>
            <?php if (!empty($search)): ?>
                <a href="websites.php" class="btn btn-secondary ms-2">
                    <i class="bi bi-x"></i>
                </a>
            <?php endif; ?>
        </form>
    </div>
<?php endif; ?>

<!-- Websites Grid -->
<?php if (!empty($websites)): ?>
    <div class="row g-4">
        <?php foreach ($websites as $website): ?>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="content-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h5 class="card-title mb-0"><?php echo htmlspecialchars($website['website_name']); ?></h5>
                        <span class="website-status <?php echo $website['status']; ?>">
                            <?php echo ucfirst($website['status']); ?>
                        </span>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-globe me-2 text-muted"></i>
                            <span class="text-muted">Domain:</span>
                        </div>
                        <div class="fw-bold"><?php echo htmlspecialchars($website['domain_name']); ?></div>
                    </div>
                    
                    <?php if ($website['package_name']): ?>
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-server me-2 text-muted"></i>
                                <span class="text-muted">Package:</span>
                            </div>
                            <div class="fw-bold"><?php echo htmlspecialchars($website['package_name']); ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($website['website_url']): ?>
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-link-45deg me-2 text-muted"></i>
                                <span class="text-muted">URL:</span>
                            </div>
                            <a href="<?php echo htmlspecialchars($website['website_url']); ?>" 
                               target="_blank" 
                               class="text-decoration-none">
                                <?php echo htmlspecialchars($website['website_url']); ?>
                                <i class="bi bi-box-arrow-up-right ms-1"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-calendar me-2 text-muted"></i>
                            <span class="text-muted">Created:</span>
                        </div>
                        <div class="fw-bold"><?php echo formatDate($website['created_at']); ?></div>
                    </div>
                    
                    <!-- Access Information -->
                    <?php if ($website['status'] === 'active'): ?>
                        <div class="border-top pt-3">
                            <h6 class="fw-bold mb-2">Access Information</h6>
                            
                            <?php if ($website['cpanel_url'] && $website['cpanel_username']): ?>
                                <div class="mb-2">
                                    <small class="text-muted">Control Panel:</small>
                                    <div class="d-flex align-items-center">
                                        <a href="<?php echo htmlspecialchars($website['cpanel_url']); ?>" 
                                           target="_blank" 
                                           class="btn btn-sm btn-outline-primary me-2">
                                            <i class="bi bi-gear me-1"></i>
                                            cPanel
                                        </a>
                                        <small class="text-muted">
                                            User: <?php echo htmlspecialchars($website['cpanel_username']); ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($website['ssh_host'] && $website['ssh_username']): ?>
                                <div class="mb-2">
                                    <small class="text-muted">SSH Access:</small>
                                    <div class="d-flex align-items-center">
                                        <code class="bg-light px-2 py-1 rounded me-2">
                                            <?php echo htmlspecialchars($website['ssh_username']); ?>@<?php echo htmlspecialchars($website['ssh_host']); ?>
                                        </code>
                                        <small class="text-muted">Port: <?php echo $website['ssh_port'] ?? 22; ?></small>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($website['db_name'] && $website['db_username']): ?>
                                <div class="mb-2">
                                    <small class="text-muted">Database:</small>
                                    <div class="d-flex align-items-center">
                                        <code class="bg-light px-2 py-1 rounded me-2">
                                            <?php echo htmlspecialchars($website['db_name']); ?>
                                        </code>
                                        <small class="text-muted">
                                            User: <?php echo htmlspecialchars($website['db_username']); ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="d-flex gap-2 mt-3">
                        <a href="websites.php?id=<?php echo $website['id']; ?>" class="btn btn-primary flex-fill">
                            <i class="bi bi-eye me-1"></i>
                            View Details
                        </a>
                        <?php if ($website['website_url']): ?>
                            <a href="<?php echo htmlspecialchars($website['website_url']); ?>" 
                               target="_blank" 
                               class="btn btn-secondary">
                                <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <!-- No Websites -->
    <div class="content-card text-center">
        <div class="py-5">
            <i class="bi bi-globe" style="font-size: 64px; color: #9ca3af; margin-bottom: 16px;"></i>
            <h3 class="card-title">No Websites Found</h3>
            <p class="card-subtitle">
                <?php if (!empty($search)): ?>
                    No websites match your search criteria.
                <?php else: ?>
                    You don't have any websites yet.
                <?php endif; ?>
            </p>
            <a href="../select-package.php" class="btn btn-primary mt-3">
                <i class="bi bi-plus-circle me-2"></i>
                Order Hosting
            </a>
        </div>
    </div>
<?php endif; ?>

<!-- Website Details Modal (if viewing specific website) -->
<?php if (isset($_GET['id'])): ?>
    <?php
    $websiteId = (int)$_GET['id'];
    if (canUserAccessWebsite($conn, $userId, $websiteId)) {
        $stmt = $conn->prepare("
            SELECT hw.*, hp.name as package_name, hp.description as package_description
            FROM hosting_websites hw 
            LEFT JOIN hosting_packages hp ON hw.package_id = hp.id 
            WHERE hw.id = ?
        ");
        $stmt->bind_param("i", $websiteId);
        $stmt->execute();
        $result = $stmt->get_result();
        $websiteDetails = $result->fetch_assoc();
        $stmt->close();
    } else {
        $websiteDetails = null;
    }
    ?>
    
    <?php if ($websiteDetails): ?>
        <div class="modal fade show" style="display: block;">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Website Details - <?php echo htmlspecialchars($websiteDetails['website_name']); ?></h5>
                        <a href="websites.php" class="btn-close"></a>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Website Name</label>
                                <div class="form-control-plaintext"><?php echo htmlspecialchars($websiteDetails['website_name']); ?></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Domain Name</label>
                                <div class="form-control-plaintext"><?php echo htmlspecialchars($websiteDetails['domain_name']); ?></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <div class="form-control-plaintext">
                                    <span class="website-status <?php echo $websiteDetails['status']; ?>">
                                        <?php echo ucfirst($websiteDetails['status']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Package</label>
                                <div class="form-control-plaintext"><?php echo htmlspecialchars($websiteDetails['package_name'] ?? 'N/A'); ?></div>
                            </div>
                            <?php if ($websiteDetails['website_url']): ?>
                                <div class="col-md-6">
                                    <label class="form-label">Website URL</label>
                                    <div class="form-control-plaintext">
                                        <a href="<?php echo htmlspecialchars($websiteDetails['website_url']); ?>" 
                                           target="_blank" 
                                           class="text-decoration-none">
                                            <?php echo htmlspecialchars($websiteDetails['website_url']); ?>
                                            <i class="bi bi-box-arrow-up-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="col-md-6">
                                <label class="form-label">Created Date</label>
                                <div class="form-control-plaintext"><?php echo formatDate($websiteDetails['created_at']); ?></div>
                            </div>
                            
                            <!-- Access Information -->
                            <?php if ($websiteDetails['cpanel_url'] || $websiteDetails['ssh_host'] || $websiteDetails['db_name']): ?>
                                <div class="col-12">
                                    <h6 class="fw-bold mb-3">Access Information</h6>
                                </div>
                                
                                <?php if ($websiteDetails['cpanel_url']): ?>
                                    <div class="col-md-6">
                                        <label class="form-label">Control Panel URL</label>
                                        <div class="form-control-plaintext">
                                            <a href="<?php echo htmlspecialchars($websiteDetails['cpanel_url']); ?>" 
                                               target="_blank" 
                                               class="text-decoration-none">
                                                <?php echo htmlspecialchars($websiteDetails['cpanel_url']); ?>
                                                <i class="bi bi-box-arrow-up-right ms-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">cPanel Username</label>
                                        <div class="form-control-plaintext"><?php echo htmlspecialchars($websiteDetails['cpanel_username']); ?></div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($websiteDetails['ssh_host']): ?>
                                    <div class="col-md-6">
                                        <label class="form-label">SSH Host</label>
                                        <div class="form-control-plaintext"><?php echo htmlspecialchars($websiteDetails['ssh_host']); ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">SSH Username</label>
                                        <div class="form-control-plaintext"><?php echo htmlspecialchars($websiteDetails['ssh_username']); ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">SSH Port</label>
                                        <div class="form-control-plaintext"><?php echo $websiteDetails['ssh_port'] ?? 22; ?></div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($websiteDetails['db_name']): ?>
                                    <div class="col-md-6">
                                        <label class="form-label">Database Name</label>
                                        <div class="form-control-plaintext"><?php echo htmlspecialchars($websiteDetails['db_name']); ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Database Username</label>
                                        <div class="form-control-plaintext"><?php echo htmlspecialchars($websiteDetails['db_username']); ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Database Host</label>
                                        <div class="form-control-plaintext"><?php echo htmlspecialchars($websiteDetails['db_host']); ?></div>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php if ($websiteDetails['notes']): ?>
                                <div class="col-12">
                                    <label class="form-label">Notes</label>
                                    <div class="form-control-plaintext"><?php echo htmlspecialchars($websiteDetails['notes']); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <?php if ($websiteDetails['website_url']): ?>
                            <a href="<?php echo htmlspecialchars($websiteDetails['website_url']); ?>" 
                               target="_blank" 
                               class="btn btn-primary">
                                <i class="bi bi-box-arrow-up-right me-1"></i>
                                Visit Website
                            </a>
                        <?php endif; ?>
                        <a href="websites.php" class="btn btn-secondary">Close</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
