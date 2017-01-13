<?php
	session_start();

	require_once 'dbconnect.php';
	require_once 'autoload.php';
	use Facebook\FacebookSession;
	use Facebook\FacebookRedirectLoginHelper;
	use Facebook\FacebookRequest;
	use Facebook\FacebookResponse;
	use Facebook\FacebookSDKException;
	use Facebook\FacebookRequestException;
	use Facebook\FacebookAuthorizationException;
	use Facebook\GraphObject;
	use Facebook\Entities\AccessToken;
	use Facebook\HttpClients\FacebookCurlHttpClient;
	use Facebook\HttpClients\FacebookHttpable;

	FacebookSession::setDefaultApplication('194368367695296', 'c1a53597ae5b2ddd6ef61716e8a0e5a1');
	$helper = new FacebookRedirectLoginHelper('http://localhost/mybank/fblogin.php');
	try {
		$session = $helper->getSessionFromRedirect();
	} catch (Exception $ex) {
		echo $e->getMessage();
		exit;
	}

	if (isset($session)) {
		$request = new FacebookRequest($session, 'GET', '/me?fields=id,name,email');
		$response = $request->execute();
		$graphObject = $response->getGraphObject();
		$fbid = $graphObject->getProperty('id');
		$fbfullname = $graphObject->getProperty('name');
		$fbemail = $graphObject->getProperty('email');

		$query = "SELECT userEmail FROM users WHERE userEmail = '$fbemail'";
   		$result = mysql_query($query);
   		$count = mysql_num_rows($result);
   		if ($count == 0) {	
   			$query = "INSERT INTO users(userName, userEmail, userPass, userAccount) VALUES('$fbfullname', '$fbemail', '', '$fbid')";
   			$res = mysql_query($query);		
   		}

   		$res = mysql_query("SELECT userId FROM users WHERE userEmail = '$fbemail'");
   		$row = mysql_fetch_array($res);

   		$_SESSION['user'] = $row['userId'];
   		header("Location: home.php");
   } else {
		$loginUrl = $helper->getLoginUrl(
			array(
				'scope' => 'email'
				)
			);
		header("Location: ".$loginUrl);
	}
?>