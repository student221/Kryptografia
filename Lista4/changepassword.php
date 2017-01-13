<?php

require_once 'dbconnect.php';

if (isset($_POST['ForgotPassword'])) {
	$email = trim($_POST['email']);
	$email = strip_tags($email);
	$email = htmlspecialchars($email);
	if (empty($email)) {
		$error = true;
		$emailError = "Please enter your email address.";
	} else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$error = true;
		$emailError = "Please enter valid email address.";
	}

	if (!$error) {
		$res = mysql_query("SELECT userId FROM users WHERE userEmail = '$email'");
		$row = mysql_fetch_array($res);
		$count = mysql_num_rows($res);

		if ($count == 0) {
			$errMSG = "No username with this email address.";
		} else {
			$passwordKey = md5($email);
			$url = "http://localhost/mybank/resetpassword.php?key=".$passwordKey;
			$mail = "Dear user,\n\nWe received a request to change your password on MyBank.\n\nClick the link below to set a new password:\n\n".$url."\n\nIf you don't want to change your password, you can ignore this email.\n\nThanks,Administrator";
			mail($email, "MyBank - Password Reset", $mail);
		}
	}
}
?>

<!DOCTYPE html>
<html>
<head>
	<meta http-equiv = "Content-Type" content = "text/html; charset = utf-8" />
	<title>MyBank</title>
	<link rel = "stylesheet" href = "css/bootstrap.min.css" type = "text/css" />
</head>

<body>
	<div class "container">
		<div id = "login-form">
			<form method = "post" action = "<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" autocomplete = "off">
				<div class = "col-md-12">
					<div class = "form-group">
						<h2 class = "">Forgot Password.</h2>
					</div>
					<div class = "form-group">
						<hr />
					</div>
					<?php
					if (isset($errMSG)) {
						?>
						<div class = "form-group">
							<div class = "alert alert-danger">
								<span class = "glyphicon glyphicon-info-sign"></span> <?php echo $errMSG; ?>
							</div>
						</div>
						<?php
					}
					?>
					<div class="form-group">
             			<div class="input-group">
                			<span class="input-group-addon"><span class="glyphicon glyphicon-envelope"></span></span>
             				<input type="email" name="email" class="form-control" placeholder="Your Email" value="<?php echo $email; ?>" maxlength="200" />
                		</div>
                		<span class="text-danger"><?php echo $emailError; ?></span>
            		</div>          
		            
		            <div class="form-group">
		             	<button type="submit" class="btn btn-block btn-primary" name="ForgotPassword">Forgot Password</button>
		            </div>
		            
		            <div class="form-group">
		             	<hr />
		            </div>		          
				</div>
			</form>
		</div>
	</div>
</body>
</html>
<?php ob_end_flush(); ?>