<?php
/**
 * Display Flash Message Component
 */
function displayFlashMessage() {
    $flash = getFlashMessage();
    if ($flash) {
        $alertClass = '';
        $icon = '';
        
        switch($flash['type']) {
            case 'success':
                $alertClass = 'alert-success';
                $icon = '<i class="bi bi-check-circle-fill"></i>';
                break;
            case 'error':
                $alertClass = 'alert-danger';
                $icon = '<i class="bi bi-exclamation-circle-fill"></i>';
                break;
            case 'warning':
                $alertClass = 'alert-warning';
                $icon = '<i class="bi bi-exclamation-triangle-fill"></i>';
                break;
            case 'info':
                $alertClass = 'alert-info';
                $icon = '<i class="bi bi-info-circle-fill"></i>';
                break;
        }
        
        echo '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">';
        echo $icon . ' <span>' . htmlspecialchars($flash['message']) . '</span>';
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
    }
}
?>
