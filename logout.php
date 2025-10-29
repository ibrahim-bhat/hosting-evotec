<?php
require_once 'config.php';
require_once 'components/auth_helper.php';

// Destroy session
destroyUserSession();

// Set flash message
setFlashMessage('success', 'You have been logged out successfully');

// Redirect to login page
redirect('login.php');
?>
