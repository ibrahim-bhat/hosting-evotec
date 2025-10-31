<?php
/**
 * Hosting Helper Functions
 * Functions for managing hosting packages and orders
 */

/**
 * Get all hosting packages
 */
function getAllPackages($conn, $status = null, $search = '') {
    $sql = "SELECT * FROM hosting_packages WHERE 1=1";
    $params = [];
    $types = '';
    
    if ($status) {
        $sql .= " AND status = ?";
        $params[] = $status;
        $types .= 's';
    }
    
    if (!empty($search)) {
        $sql .= " AND (name LIKE ? OR description LIKE ? OR short_description LIKE ?)";
        $searchTerm = "%{$search}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'sss';
    }
    
    $sql .= " ORDER BY sort_order ASC, id ASC";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $packages = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $packages;
}

/**
 * Get package by ID
 */
function getPackageById($conn, $packageId) {
    $stmt = $conn->prepare("SELECT * FROM hosting_packages WHERE id = ?");
    $stmt->bind_param("i", $packageId);
    $stmt->execute();
    $result = $stmt->get_result();
    $package = $result->fetch_assoc();
    $stmt->close();
    return $package;
}

/**
 * Get package by slug
 */
function getPackageBySlug($conn, $slug) {
    $stmt = $conn->prepare("SELECT * FROM hosting_packages WHERE slug = ? AND status = 'active'");
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $result = $stmt->get_result();
    $package = $result->fetch_assoc();
    $stmt->close();
    return $package;
}

/**
 * Get active packages only
 */
function getActivePackages($conn, $limit = null) {
    $sql = "SELECT * FROM hosting_packages WHERE status = 'active' ORDER BY sort_order ASC, id ASC";
    if ($limit) {
        $sql .= " LIMIT ?";
    }
    
    $stmt = $conn->prepare($sql);
    if ($limit) {
        $stmt->bind_param("i", $limit);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $packages = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $packages;
}

/**
 * Create package
 */
function createPackage($conn, $data) {
    $stmt = $conn->prepare("INSERT INTO hosting_packages (
        name, slug, description, short_description,
        price_monthly, price_yearly, price_2years, price_4years,
        storage_gb, bandwidth_gb, allowed_websites, database_limit,
        ftp_accounts, email_accounts, ssh_access, ssl_free,
        daily_backups, dedicated_ip, setup_fee, gst_percentage,
        processing_fee, status, is_popular, sort_order
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("ssssddddddiiiiiidddddssi",
        $data['name'], $data['slug'], $data['description'], $data['short_description'],
        $data['price_monthly'], $data['price_yearly'], $data['price_2years'], $data['price_4years'],
        $data['storage_gb'], $data['bandwidth_gb'], $data['allowed_websites'], $data['database_limit'],
        $data['ftp_accounts'], $data['email_accounts'], $data['ssh_access'], $data['ssl_free'],
        $data['daily_backups'], $data['dedicated_ip'], $data['setup_fee'], $data['gst_percentage'],
        $data['processing_fee'], $data['status'], $data['is_popular'], $data['sort_order']
    );
    
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Update package
 */
function updatePackage($conn, $packageId, $data) {
    $stmt = $conn->prepare("UPDATE hosting_packages SET 
        name = ?, slug = ?, description = ?, short_description = ?,
        price_monthly = ?, price_yearly = ?, price_2years = ?, price_4years = ?,
        storage_gb = ?, bandwidth_gb = ?, allowed_websites = ?, database_limit = ?,
        ftp_accounts = ?, email_accounts = ?, ssh_access = ?, ssl_free = ?,
        daily_backups = ?, dedicated_ip = ?, setup_fee = ?, gst_percentage = ?,
        processing_fee = ?, status = ?, is_popular = ?, sort_order = ?
        WHERE id = ?");
    
    $stmt->bind_param("ssssddddddiiiiiidddddssii",
        $data['name'], $data['slug'], $data['description'], $data['short_description'],
        $data['price_monthly'], $data['price_yearly'], $data['price_2years'], $data['price_4years'],
        $data['storage_gb'], $data['bandwidth_gb'], $data['allowed_websites'], $data['database_limit'],
        $data['ftp_accounts'], $data['email_accounts'], $data['ssh_access'], $data['ssl_free'],
        $data['daily_backups'], $data['dedicated_ip'], $data['setup_fee'], $data['gst_percentage'],
        $data['processing_fee'], $data['status'], $data['is_popular'], $data['sort_order'],
        $packageId
    );
    
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Delete package (soft delete)
 */
function deletePackage($conn, $packageId) {
    $stmt = $conn->prepare("UPDATE hosting_packages SET status = 'deleted' WHERE id = ?");
    $stmt->bind_param("i", $packageId);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Toggle package status
 */
function togglePackageStatus($conn, $packageId, $status) {
    $stmt = $conn->prepare("UPDATE hosting_packages SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $packageId);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Generate slug from name
 */
function generateSlug($name) {
    $slug = strtolower($name);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug;
}

/**
 * Calculate package price for billing cycle
 */
function getPackagePrice($package, $billingCycle) {
    switch ($billingCycle) {
        case 'monthly':
            return $package['price_monthly'];
        case 'yearly':
            return $package['price_yearly'];
        case '2years':
            return $package['price_2years'];
        case '4years':
            return $package['price_4years'];
        default:
            return $package['price_monthly'];
    }
}

/**
 * Calculate order total with taxes and fees (using global settings or legacy parameters)
 */
function calculateOrderTotal(...$args) {
    require_once __DIR__ . '/payment_settings_helper.php';
    
    // Handle both old (4 params) and new (2 params) signatures for backward compatibility
    if (count($args) === 4) {
        // Legacy signature: calculateOrderTotal($basePrice, $setupFee, $gstPercentage, $processingFee)
        return calculateOrderTotalLegacy($args[0], $args[1], $args[2], $args[3]);
    } else if (count($args) === 2) {
        // New signature: calculateOrderTotal($conn, $basePrice)
        return calculateOrderTotalWithGlobalSettings($args[0], $args[1]);
    }
    
    // Default to legacy if called with wrong number of params
    return ['total_amount' => 0, 'base_price' => 0, 'setup_fee' => 0, 'gst_amount' => 0, 'processing_fee' => 0, 'subtotal' => 0];
}

/**
 * Calculate order total with taxes and fees (legacy function for backward compatibility)
 */
function calculateOrderTotalLegacy($basePrice, $setupFee, $gstPercentage, $processingFee) {
    $subtotal = $basePrice + $setupFee;
    $gstAmount = ($subtotal * $gstPercentage) / 100;
    $total = $subtotal + $gstAmount + $processingFee;
    
    return [
        'base_price' => $basePrice,
        'setup_fee' => $setupFee,
        'subtotal' => $subtotal,
        'gst_amount' => $gstAmount,
        'processing_fee' => $processingFee,
        'total_amount' => $total
    ];
}

// ========== ORDERS MANAGEMENT ==========

/**
 * Get all orders
 */
function getAllOrders($conn, $search = '', $status = null, $paymentStatus = null) {
    $sql = "SELECT o.*, u.name as user_name, u.email as user_email, p.name as package_name 
            FROM hosting_orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            LEFT JOIN hosting_packages p ON o.package_id = p.id 
            WHERE 1=1";
    $params = [];
    $types = '';
    
    if (!empty($search)) {
        $sql .= " AND (o.order_number LIKE ? OR u.name LIKE ? OR u.email LIKE ? OR p.name LIKE ?)";
        $searchTerm = "%{$search}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'ssss';
    }
    
    if ($status) {
        $sql .= " AND o.order_status = ?";
        $params[] = $status;
        $types .= 's';
    }
    
    if ($paymentStatus) {
        $sql .= " AND o.payment_status = ?";
        $params[] = $paymentStatus;
        $types .= 's';
    }
    
    $sql .= " ORDER BY o.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $orders;
}

/**
 * Get order by ID
 */
function getOrderById($conn, $orderId) {
    $stmt = $conn->prepare("SELECT o.*, u.name as user_name, u.email as user_email, 
                           u.phone as user_phone, p.name as package_name, p.description as package_description
                           FROM hosting_orders o 
                           LEFT JOIN users u ON o.user_id = u.id 
                           LEFT JOIN hosting_packages p ON o.package_id = p.id 
                           WHERE o.id = ?");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();
    return $order;
}

/**
 * Get orders by user ID
 */
function getOrdersByUserId($conn, $userId) {
    $stmt = $conn->prepare("SELECT o.*, p.name as package_name, p.slug as package_slug
                           FROM hosting_orders o 
                           LEFT JOIN hosting_packages p ON o.package_id = p.id 
                           WHERE o.user_id = ? 
                           ORDER BY o.created_at DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $orders;
}

/**
 * Get active orders by user ID
 */
function getActiveOrdersByUserId($conn, $userId) {
    $stmt = $conn->prepare("SELECT o.*, p.name as package_name
                           FROM hosting_orders o 
                           LEFT JOIN hosting_packages p ON o.package_id = p.id 
                           WHERE o.user_id = ? AND o.order_status = 'active' 
                           ORDER BY o.created_at DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $orders;
}

/**
 * Create new order
 */
function createOrder($conn, $userId, $packageId, $billingCycle) {
    // Get package details
    $package = getPackageById($conn, $packageId);
    if (!$package || $package['status'] !== 'active') {
        return false;
    }
    
    // Get package price
    $basePrice = getPackagePrice($package, $billingCycle);
    
    // Calculate totals using global settings
    $calculations = calculateOrderTotal($conn, $basePrice);
    
    // Generate unique order number
    $orderNumber = 'ORD' . date('Ymd') . strtoupper(substr(uniqid(), -6));
    
    // Calculate dates
    $startDate = date('Y-m-d');
    $expiryDate = calculateExpiryDate($startDate, $billingCycle);
    $renewalDate = $expiryDate;
    
    $stmt = $conn->prepare("INSERT INTO hosting_orders (
        order_number, user_id, package_id, billing_cycle, base_price, setup_fee,
        gst_amount, processing_fee, subtotal, total_amount,
        start_date, expiry_date, renewal_date
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("siisddddddsss",
        $orderNumber, $userId, $packageId, $billingCycle,
        $calculations['base_price'], $calculations['setup_fee'],
        $calculations['gst_amount'], $calculations['processing_fee'],
        $calculations['subtotal'], $calculations['total_amount'],
        $startDate, $expiryDate, $renewalDate
    );
    
    $success = $stmt->execute();
    $orderId = $conn->insert_id;
    $stmt->close();
    
    return $success ? $orderId : false;
}

/**
 * Calculate expiry date based on billing cycle
 */
function calculateExpiryDate($startDate, $billingCycle) {
    $date = new DateTime($startDate);
    
    switch ($billingCycle) {
        case 'monthly':
            $date->modify('+1 month');
            break;
        case 'yearly':
            $date->modify('+1 year');
            break;
        case '2years':
            $date->modify('+2 years');
            break;
        case '4years':
            $date->modify('+4 years');
            break;
        default:
            $date->modify('+1 month');
    }
    
    return $date->format('Y-m-d');
}

/**
 * Update order status
 */
function updateOrderStatus($conn, $orderId, $status) {
    $stmt = $conn->prepare("UPDATE hosting_orders SET order_status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $orderId);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Update payment status
 */
function updatePaymentStatus($conn, $orderId, $paymentStatus, $paymentId = null, $razorpayOrderId = null, $razorpayPaymentId = null) {
    $stmt = $conn->prepare("UPDATE hosting_orders SET 
        payment_status = ?, payment_id = ?, razorpay_order_id = ?, 
        razorpay_payment_id = ?, payment_date = NOW()
        WHERE id = ?");
    $stmt->bind_param("ssssi", $paymentStatus, $paymentId, $razorpayOrderId, $razorpayPaymentId, $orderId);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Approve order and activate
 */
function approveOrder($conn, $orderId, $adminNotes = null) {
    $stmt = $conn->prepare("UPDATE hosting_orders SET 
        order_status = 'active', admin_notes = ?, updated_at = NOW()
        WHERE id = ?");
    $stmt->bind_param("si", $adminNotes, $orderId);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Reject order
 */
function rejectOrder($conn, $orderId, $adminNotes = null) {
    $stmt = $conn->prepare("UPDATE hosting_orders SET 
        order_status = 'cancelled', admin_notes = ?, updated_at = NOW()
        WHERE id = ?");
    $stmt->bind_param("si", $adminNotes, $orderId);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Count orders by status
 */
function countOrdersByStatus($conn, $status) {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM hosting_orders WHERE order_status = ?");
    $stmt->bind_param("s", $status);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['total'];
}

/**
 * Count orders by payment status
 */
function countOrdersByPaymentStatus($conn, $paymentStatus) {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM hosting_orders WHERE payment_status = ?");
    $stmt->bind_param("s", $paymentStatus);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['total'];
}

/**
 * Get order statistics
 */
function getOrderStatistics($conn) {
    // Get all orders count
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM hosting_orders");
    $stmt->execute();
    $result = $stmt->get_result();
    $totalOrders = $result->fetch_assoc()['total'];
    $stmt->close();
    
    return [
        'total_orders' => $totalOrders,
        'pending_orders' => countOrdersByStatus($conn, 'pending'),
        'active_orders' => countOrdersByStatus($conn, 'active'),
        'suspended_orders' => countOrdersByStatus($conn, 'suspended'),
        'expired_orders' => countOrdersByStatus($conn, 'expired'),
        'paid_orders' => countOrdersByPaymentStatus($conn, 'paid'),
        'pending_payments' => countOrdersByPaymentStatus($conn, 'pending')
    ];
}

/**
 * Check if order has expired
 */
function isOrderExpired($order) {
    if ($order['order_status'] !== 'active') {
        return false;
    }
    
    if (empty($order['expiry_date'])) {
        return false;
    }
    
    $expiryDate = new DateTime($order['expiry_date']);
    $today = new DateTime();
    
    return $today > $expiryDate;
}

/**
 * Get expiring orders (expires within 7 days)
 */
function getExpiringOrders($conn, $days = 7) {
    $stmt = $conn->prepare("SELECT o.*, u.name as user_name, u.email as user_email, 
                           p.name as package_name
                           FROM hosting_orders o 
                           LEFT JOIN users u ON o.user_id = u.id 
                           LEFT JOIN hosting_packages p ON o.package_id = p.id 
                           WHERE o.order_status = 'active' 
                           AND o.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
                           ORDER BY o.expiry_date ASC");
    $stmt->bind_param("i", $days);
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $orders;
}

/**
 * Renew order
 */
function renewOrder($conn, $orderId) {
    $order = getOrderById($conn, $orderId);
    if (!$order) {
        return false;
    }
    
    // Calculate new expiry date
    $newExpiryDate = calculateExpiryDate($order['expiry_date'], $order['billing_cycle']);
    
    $stmt = $conn->prepare("UPDATE hosting_orders SET 
        expiry_date = ?, renewal_date = ?, updated_at = NOW()
        WHERE id = ?");
    $stmt->bind_param("ssi", $newExpiryDate, $newExpiryDate, $orderId);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

// ========== WEBSITE MANAGEMENT ==========

/**
 * Get all websites
 */
function getAllWebsites($conn, $search = '', $status = null, $userId = null) {
    $sql = "SELECT w.*, u.name as user_name, u.email as user_email, p.name as package_name, o.order_number
            FROM hosting_websites w
            LEFT JOIN users u ON w.user_id = u.id
            LEFT JOIN hosting_packages p ON w.package_id = p.id
            LEFT JOIN hosting_orders o ON w.order_id = o.id
            WHERE 1=1";
    $params = [];
    $types = '';
    
    if (!empty($search)) {
        $sql .= " AND (w.website_name LIKE ? OR w.domain_name LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
        $searchTerm = "%{$search}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'ssss';
    }
    
    if ($status) {
        $sql .= " AND w.status = ?";
        $params[] = $status;
        $types .= 's';
    }
    
    if ($userId) {
        $sql .= " AND w.user_id = ?";
        $params[] = $userId;
        $types .= 'i';
    }
    
    $sql .= " ORDER BY w.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $websites = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $websites;
}

/**
 * Get website by ID
 */
function getWebsiteById($conn, $websiteId) {
    $stmt = $conn->prepare("SELECT w.*, u.name as user_name, u.email as user_email, p.name as package_name 
                           FROM hosting_websites w 
                           LEFT JOIN users u ON w.user_id = u.id 
                           LEFT JOIN hosting_packages p ON w.package_id = p.id 
                           WHERE w.id = ?");
    $stmt->bind_param("i", $websiteId);
    $stmt->execute();
    $result = $stmt->get_result();
    $website = $result->fetch_assoc();
    $stmt->close();
    return $website;
}

/**
 * Create website
 */
function createWebsite($conn, $data) {
    // Handle NULL values for optional fields
    $orderId = $data['order_id'] ?? null;
    $packageId = $data['package_id'] ?? null;
    
    $stmt = $conn->prepare("INSERT INTO hosting_websites (
        order_id, user_id, package_id, website_name, domain_name, website_url,
        ssh_username, ssh_password, ssh_host, ssh_port,
        db_name, db_username, db_password, db_host,
        ftp_username, ftp_password, ftp_host, ftp_port,
        cpanel_url, cpanel_username, cpanel_password,
        status, payment_status, server_ip, nameservers
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("iiissssssississississssss",
        $orderId, $data['user_id'], $packageId,
        $data['website_name'], $data['domain_name'], $data['website_url'],
        $data['ssh_username'], $data['ssh_password'], $data['ssh_host'], $data['ssh_port'],
        $data['db_name'], $data['db_username'], $data['db_password'], $data['db_host'],
        $data['ftp_username'], $data['ftp_password'], $data['ftp_host'], $data['ftp_port'],
        $data['cpanel_url'], $data['cpanel_username'], $data['cpanel_password'],
        $data['status'], $data['payment_status'], $data['server_ip'], $data['nameservers']
    );
    
    $success = $stmt->execute();
    $websiteId = $conn->insert_id;
    $stmt->close();
    return $success ? $websiteId : false;
}

/**
 * Update website
 */
function updateWebsite($conn, $websiteId, $data) {
    $stmt = $conn->prepare("UPDATE hosting_websites SET 
        website_name = ?, domain_name = ?, website_url = ?,
        ssh_username = ?, ssh_password = ?, ssh_host = ?, ssh_port = ?,
        db_name = ?, db_username = ?, db_password = ?, db_host = ?,
        ftp_username = ?, ftp_password = ?, ftp_host = ?, ftp_port = ?,
        cpanel_url = ?, cpanel_username = ?, cpanel_password = ?,
        status = ?, payment_status = ?, server_ip = ?, nameservers = ?, 
        notes = ?, admin_notes = ?
        WHERE id = ?");
    
    $stmt->bind_param("sssssssississississsssssi",
        $data['website_name'], $data['domain_name'], $data['website_url'],
        $data['ssh_username'], $data['ssh_password'], $data['ssh_host'], $data['ssh_port'],
        $data['db_name'], $data['db_username'], $data['db_password'], $data['db_host'],
        $data['ftp_username'], $data['ftp_password'], $data['ftp_host'], $data['ftp_port'],
        $data['cpanel_url'], $data['cpanel_username'], $data['cpanel_password'],
        $data['status'], $data['payment_status'], $data['server_ip'], $data['nameservers'], 
        $data['notes'], $data['admin_notes'],
        $websiteId
    );
    
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Toggle website status
 */
function toggleWebsiteStatus($conn, $websiteId, $status) {
    $stmt = $conn->prepare("UPDATE hosting_websites SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $websiteId);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Delete website (soft delete)
 */
function deleteWebsite($conn, $websiteId) {
    $stmt = $conn->prepare("UPDATE hosting_websites SET status = 'deleted' WHERE id = ?");
    $stmt->bind_param("i", $websiteId);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

/**
 * Get websites by user
 */
function getWebsitesByUserId($conn, $userId) {
    $stmt = $conn->prepare("SELECT w.*, p.name as package_name, o.order_number 
                           FROM hosting_websites w 
                           LEFT JOIN hosting_packages p ON w.package_id = p.id 
                           LEFT JOIN hosting_orders o ON w.order_id = o.id 
                           WHERE w.user_id = ? AND w.status != 'deleted'
                           ORDER BY w.created_at DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $websites = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $websites;
}

/**
 * Manually assign package to user (create order directly)
 */
function manuallyAssignPackage($conn, $userId, $packageId, $billingCycle = 'monthly') {
    // Check if package exists and is active
    $package = getPackageById($conn, $packageId);
    if (!$package || $package['status'] !== 'active') {
        return ['success' => false, 'message' => 'Package not found or inactive'];
    }
    
    // Create order
    $orderId = createOrder($conn, $userId, $packageId, $billingCycle);
    
    if (!$orderId) {
        return ['success' => false, 'message' => 'Failed to create order'];
    }
    
    // Get the created order
    $order = getOrderById($conn, $orderId);
    
    // Approve the order immediately
    $approved = approveOrder($conn, $orderId, 'Manually assigned by admin');
    
    return [
        'success' => $approved,
        'order_id' => $orderId,
        'order' => $order,
        'message' => $approved ? 'Package assigned successfully' : 'Package assigned but failed to activate'
    ];
}

// ========== PAYMENT HISTORY ==========

/**
 * Create payment record
 */
function createPaymentHistory($conn, $data) {
    $stmt = $conn->prepare("INSERT INTO payment_history (
        order_id, user_id, payment_amount, currency, payment_method, payment_status,
        razorpay_order_id, razorpay_payment_id, razorpay_signature, transaction_id,
        transaction_date, payment_description, failure_reason
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("iidssssssssss",
        $data['order_id'], $data['user_id'], $data['payment_amount'], $data['currency'],
        $data['payment_method'], $data['payment_status'], $data['razorpay_order_id'],
        $data['razorpay_payment_id'], $data['razorpay_signature'], $data['transaction_id'],
        $data['transaction_date'], $data['payment_description'], $data['failure_reason']
    );
    
    $success = $stmt->execute();
    $paymentId = $conn->insert_id;
    $stmt->close();
    return $success ? $paymentId : false;
}

/**
 * Get payment history by order
 */
function getPaymentHistoryByOrder($conn, $orderId) {
    $stmt = $conn->prepare("SELECT * FROM payment_history WHERE order_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    $payments = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $payments;
}

/**
 * Get payment history by user
 */
function getPaymentHistoryByUser($conn, $userId) {
    $stmt = $conn->prepare("SELECT ph.*, o.order_number, o.total_amount 
                            FROM payment_history ph
                            LEFT JOIN hosting_orders o ON ph.order_id = o.id
                            WHERE ph.user_id = ? ORDER BY ph.created_at DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $payments = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $payments;
}

/**
 * Update payment status
 */
function updatePaymentHistoryStatus($conn, $paymentId, $status, $razorpayPaymentId = null, $razorpaySignature = null, $transactionDate = null) {
    $stmt = $conn->prepare("UPDATE payment_history SET 
        payment_status = ?, razorpay_payment_id = ?, razorpay_signature = ?, 
        transaction_id = ?, transaction_date = ?
        WHERE id = ?");
    $stmt->bind_param("sssssi", $status, $razorpayPaymentId, $razorpaySignature, 
                     $razorpayPaymentId, $transactionDate, $paymentId);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

// ========== RENEWAL HISTORY ==========

/**
 * Create renewal record
 */
function createRenewalHistory($conn, $orderId, $userId, $billingCycle, $amount, $expiryDate) {
    $stmt = $conn->prepare("INSERT INTO renewal_history (
        order_id, user_id, previous_order_id, renewal_amount, billing_cycle,
        payment_status, renewal_date, expiry_date, auto_renewal
    ) VALUES (?, ?, ?, ?, ?, 'pending', CURDATE(), ?, 0)");
    
    $stmt->bind_param("iiids", $orderId, $userId, $previousOrderId, $amount, $billingCycle, $expiryDate);
    $success = $stmt->execute();
    $renewalId = $conn->insert_id;
    $stmt->close();
    return $success ? $renewalId : false;
}

/**
 * Get renewal history by user
 */
function getRenewalHistoryByUser($conn, $userId) {
    $stmt = $conn->prepare("SELECT rh.*, o.order_number 
                           FROM renewal_history rh
                           LEFT JOIN hosting_orders o ON rh.order_id = o.id
                           WHERE rh.user_id = ? ORDER BY rh.renewal_date DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $renewals = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $renewals;
}

/**
 * Clean up old pending orders (older than specified hours)
 */
function cleanupPendingOrders($conn, $hours = 0) {
    if ($hours > 0) {
        $stmt = $conn->prepare("
            DELETE FROM hosting_orders 
            WHERE payment_status = 'pending' 
            AND order_status = 'pending' 
            AND created_at < DATE_SUB(NOW(), INTERVAL ? HOUR)
        ");
        $stmt->bind_param("i", $hours);
    } else {
        $stmt = $conn->prepare("
            DELETE FROM hosting_orders 
            WHERE payment_status = 'pending' 
            AND order_status = 'pending'
        ");
    }
    $success = $stmt->execute();
    $affectedRows = $stmt->affected_rows;
    $stmt->close();
    
    return ['success' => $success, 'deleted_count' => $affectedRows];
}

/**
 * Clean up old pending payments (older than specified hours)
 */
function cleanupPendingPayments($conn, $hours = 0) {
    if ($hours > 0) {
        $stmt = $conn->prepare("
            DELETE FROM payment_history 
            WHERE payment_status = 'pending' 
            AND created_at < DATE_SUB(NOW(), INTERVAL ? HOUR)
        ");
        $stmt->bind_param("i", $hours);
    } else {
        $stmt = $conn->prepare("
            DELETE FROM payment_history 
            WHERE payment_status = 'pending'
        ");
    }
    $success = $stmt->execute();
    $affectedRows = $stmt->affected_rows;
    $stmt->close();
    
    return ['success' => $success, 'deleted_count' => $affectedRows];
}

/**
 * Run automatic cleanup of pending orders and payments
 */
function runAutomaticCleanup($conn, $removeAll = false) {
    if ($removeAll) {
        $ordersResult = cleanupPendingOrders($conn, 0); // Remove all pending
        $paymentsResult = cleanupPendingPayments($conn, 0); // Remove all pending
    } else {
        $ordersResult = cleanupPendingOrders($conn, 24); // 24 hours
        $paymentsResult = cleanupPendingPayments($conn, 24); // 24 hours
    }
    
    return [
        'orders' => $ordersResult,
        'payments' => $paymentsResult
    ];
}

?>

