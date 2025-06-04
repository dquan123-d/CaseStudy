<?php
// auth_handler.php
function handle_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start([
            'cookie_lifetime' => 86400,
            'cookie_secure' => true,
            'cookie_httponly' => true
        ]);
    }
}

function redirect_with_js($url) {
    echo "<script>window.location.href='$url';</script>";
    exit();
}

function reload_page() {
    echo "<script>window.location.reload(true);</script>";
    exit();
}