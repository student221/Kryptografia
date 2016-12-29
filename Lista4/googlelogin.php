<?php
	session_start();

	require_once 'dbconnect.php';
	require_once 'src/Google/vendor/autoload.php';
	
	$client_id = '980699461957-2uoptmkoghi7736jdeqt7ob9fa3vpks5.apps.googleusercontent.com';
	$client_secret = '6Jbi-vf7cY6fWf7xQuKKfkCD';
	$redirect_uri = 'http://localhost/mybank/googlelogin.php';

	$client = new Google_Client();
	$client->setClientId($client_id);
	$client->setClientSecret($client_secret);
	$client->setRedirectUri($redirect_uri);
	$client->setScopes(array(
		"email",
		"profile",
	));

	$service = new Google_Service_Oauth2($client);

	if (isset($_GET['code'])) {
		$client->authenticate($_GET['code']);
		$_SESSION['access_token'] = $client->getAccessToken();
		header('Location: '.filter_var($redirect_uri, FILTER_SANITIZE_URL));
	}

	if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
		$client->setAccessToken($_SESSION['access_token']);
	} 

	if ($client->getAccessToken()) {
		$user = $service->userinfo->get();
		$gemail = $user['email'];
		$gname = $user['given_name'] . " " . $user['family_name'];
		$gid = $user['id'];

		$query = "SELECT userEmail FROM users WHERE userEmail = '$gemail'";
		$result = mysql_query($query);
		$count = mysql_num_rows($result);
		if ($count == 0) {	
			$query = "INSERT INTO users(userName, userEmail, userPass, userAccount) VALUES('$gname', '$gemail', '', '$gid')";
			$res = mysql_query($query);		
		}

		$res = mysql_query("SELECT userId FROM users WHERE userEmail = '$gemail'");
		$row = mysql_fetch_array($res);

   		$_SESSION['user'] = $row['userId'];
		header("Location: home.php");
	} else {
		$authUrl = $client->createAuthUrl();
		header('Location: '.filter_var($authUrl, FILTER_SANITIZE_URL));
	}
?> 
