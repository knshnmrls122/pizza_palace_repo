<?php
// Logout script: destroy session and redirect to login page.
session_start();

// Unset all session variables
$_SESSION = [];

// If there's a session cookie, delete it
if (ini_get("session.use_cookies")) {
	$params = session_get_cookie_params();
	setcookie(session_name(), '', time() - 42000,
		$params['path'], $params['domain'], $params['secure'], $params['httponly']
	);
}

// Destroy the session
session_destroy();

// Decide redirect target: prefer ../includes/Login_Page.php if it exists, otherwise ../pages/Login_Page.php
$preferred = __DIR__ . DIRECTORY_SEPARATOR . 'Login_Page.php';
if (file_exists($preferred)) {
	$target = '../includes/Login_Page.php';
} else {
	$target = '../pages/Login_Page.php';
}

// Redirect with header and provide a fallback link
header('Location: ' . $target);
exit();

?>
