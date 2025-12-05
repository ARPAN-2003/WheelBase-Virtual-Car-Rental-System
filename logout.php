<?php
	// logout.php
	session_start();
	// destroy session completely
	$_SESSION = [];
	if (ini_get('session.use_cookies')) {
	    $params = session_get_cookie_params();
	    setcookie(session_name(), '', time() - 42000,
	        $params['path'], $params['domain'],
	        $params['secure'], $params['httponly']
	    );
	}
	session_destroy();
	// redirect to home (BASE_URL + index.php or index.html)
	require_once __DIR__ . '/config.php';
	$home = rtrim(BASE_URL, '/') . '/index.php';
	header('Location: ' . $home);
	exit;
?>