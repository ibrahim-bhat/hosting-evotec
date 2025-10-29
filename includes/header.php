<?php
// Include settings helper
require_once __DIR__ . '/../components/settings_helper.php';
require_once __DIR__ . '/../config.php';

// Get system settings
$companyName = getCompanyName($conn);
$companyLogo = getCompanyLogo($conn);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . htmlspecialchars($companyName) : htmlspecialchars($companyName); ?></title>

    <!-- Favicon (using company logo) -->
    <?php if (!empty($companyLogo)): ?>
        <link rel="icon" type="image/x-icon" href="<?php echo htmlspecialchars($companyLogo); ?>">
    <?php endif; ?>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/auth.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>