<?php

require_once 'dbconnect.php';

if (isset($_POST["ResetPassword"])) {
	$email = trim($_POST['email']);
	$email = strip_tags($email);
	$email = htmlspecialchars($email);

	$newpass = trim($_POST['newpass']);
	$newpass = strip_tags($newpass);
	$newpass = htmlspecialchars($newpass);

	$confpass = trim($_POST['confpass']);
	$confpass = strip_tags($confpass);
	$confpass = htmlspecialchars($confpass);

	$key = $_POST['key'];

	$passwordKey = md5($email);

	if ($passwordKey == $key) {
		if ($passwordKey == $confpass) {
			$res = mysql_query("UPDATE users SET password = '$newpass' WHERE userEmail = '$email'");
			$row = mysql_fetch_array($res);
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
						<h2 class = "">Reset Password.</h2>
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
	             		<div class="input-group">
		                	<span class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></span>
		             		<input type="password" name="newpass" class="form-control" placeholder="New Password" maxlength="200" />
		                </div>
		                <span class="text-danger"><?php echo $passError; ?></span>
		            </div>

		            <div class="form-group">
	             		<div class="input-group">
		                	<span class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></span>
		             		<input type="password" name="confpass" class="form-control" placeholder="Confirm New Password" maxlength="200" />
		                </div>
		                <span class="text-danger"><?php echo $passError; ?></span>
		            </div>
		            
		            <div class="form-group">
		             	<button type="submit" class="btn btn-block btn-primary" name="ResetPassword">Reset Password</button>
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