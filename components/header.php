<?php
// Header component for InfraLabs Cloud
if (!isset($pageTitle)) {
    $pageTitle = "InfraLabs Cloud - Professional Hosting Solutions";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <meta name="description" content="<?php echo isset($pageDescription) ? htmlspecialchars($pageDescription) : 'InfraLabs Cloud - Professional hosting solutions with 99.99% uptime guarantee, 24/7 support, and cutting-edge technology.'; ?>">
    
    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>☁️</text></svg>">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo isset($cssPath) ? $cssPath : './assets/css/index.css'; ?>">
    <?php if (isset($additionalCSS)): ?>
        <link rel="stylesheet" href="<?php echo $additionalCSS; ?>">
    <?php endif; ?>
</head>

<body>
    <!-- Header Navigation -->
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <div class="navbar-brand">
                    <a href="index.php" style="text-decoration: none; color: inherit;">
                        <div class="logo-box">
                            <i class="fas fa-cloud"></i>
                            <span class="logo-text">InfraLabs Cloud</span>
                        </div>
                    </a>
                </div>

                <button class="mobile-toggle" id="mobileToggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

                <div class="navbar-menu" id="navbarMenu">
                    <a href="index.php" class="nav-link">Home</a>
                    <a href="index.php#features" class="nav-link">Features</a>
                    <a href="hosting-types.php" class="nav-link">Hosting</a>
                    <a href="index.php#pricing" class="nav-link">Pricing</a>
                    <a href="custom-solutions.php" class="nav-link">Custom Solutions</a>
                    <a href="contact.php" class="nav-link">Contact</a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="<?php echo (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') ? 'admin/index.php' : 'user/index.php'; ?>" class="nav-link">
                            <i class="fas fa-user"></i> Dashboard
                        </a>
                        <a href="logout.php" class="btn-secondary">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="nav-link">Login</a>
                        <a href="register.php" class="btn-primary">Get Started</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>
