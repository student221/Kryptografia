<?php
ob_start();
session_start();
require_once 'dbconnect.php';

if (isset($_SESSION['user']) != "") {
	header("Location: home.php");
	exit;
}

$res = mysql_query("SELECT * FROM users WHERE userId = ".$_SESSION['user']);
$userRow = mysql_fetch_array($res);

$error = false;
if (isset($_POST['btn-login'])) {
	$email = trim($_POST['email']);
	$email = strip_tags($email);
	$email = htmlspecialchars($email);

	$pass = trim($_POST['pass']);
	$pass = strip_tags($pass);
	$pass = htmlspecialchars($pass);

	if (empty($email)) {
		$error = true;
		$emailError = "Please enter your email address.";
	} else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$error = true;
		$emailError = "Please enter valid email address.";
	}

	if (empty($pass)) {
		$error = true;
		$passError = "Please enter your password.";
	}

	if (!isset($_POST['g-recaptcha-response']) || empty($_POST['g-recaptcha-response'])) {
		$error = true;
		$captchaError = "Please check captcha.";
	}

	if (!$error) {

		$secretKey = "6LesHxAUAAAAACBQ9RDpklIXRbat_noBi-ZNQVEE";
		$verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secretKey.'&response='.$_POST['g-recaptcha-response']);
		$responseData = json_decode($verifyResponse);

		if ($responseData->success) {
			$password = hash('sha256', $pass);

			$res = mysql_query("SELECT userId, userName, userPass FROM users WHERE userEmail = '$email'");
			$row = mysql_fetch_array($res);
			$count = mysql_num_rows($res);

			if ($count == 1 && $row['userPass'] == $password) {
				$_SESSION['user'] = $row['userId'];
				header("Location: home.php");
			} else {
				$errMSG = "Incorrect Credentials, Try again...";
			}
		} else {
			$error = true;
			$captchaError = "Please check captcha.";
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
	<script src='https://www.google.com/recaptcha/api.js'></script>
</head>

<body>
	<div class "container">
		<div id = "login-form">
			<form method = "post" action = "<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" autocomplete = "off">
				<div class = "col-md-12">
					<div class = "form-group">
						<h2 class = "">Sign In.</h2>
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
             				<input type="email" name="email" class="form-control" placeholder="Your Email" value="<?php echo $email; ?>" maxlength="40" />
                		</div>
                		<span class="text-danger"><?php echo $emailError; ?></span>
            		</div>
            
		            <div class="form-group">
	             		<div class="input-group">
		                	<span class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></span>
		             		<input type="password" name="pass" class="form-control" placeholder="Your Password" maxlength="15" />
		                </div>
		                <span class="text-danger"><?php echo $passError; ?></span>
		            </div>
		            
		            <div class="form-group">
		             	<hr />
		            </div>

		            <div class = "form-group">
		            	<div class="g-recaptcha" data-sitekey="6LesHxAUAAAAAGY1IZaHLPtXDhRyQ-a3QC5nfGkC"></div>
		            	<span class="text-danger"><?php echo $captchaError; ?></span>
		            </div>
		            
		            <div class="form-group">
		             	<button type="submit" class="btn btn-block btn-primary" name="btn-login">Sign In</button>
		            </div>
		            
		            <div class="form-group">
		             	<hr />
		            </div>
		            
		            <div class = "form-group">
						<div>
			               <a href = "fblogin.php">Login with Facebook</a>
			            </div>
					</div>

					<div class = "form-group">
						<div>
			               <a href = "googlelogin.php">Login with Google account</a>
			            </div>
					</div>

		            <div class="form-group">
		             	<a href="register.php">Sign Up Here...</a>
		            </div>
				</div>
			</form>
		</div>
	</div>
</body>
</html>
<?php ob_end_flush(); ?>