<?php
ob_start();
session_start();

if (isset($_SESSION['user']) != "") {
	header("Location: home.php");
	exit;
}

if (empty($_SESSION['token'])) {
	if (function_exists('mcrypt_create_iv')) {
		$_SESSION['token'] = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
	} else {
		$_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes(32));
	}
}

$token = $_SESSION['token'];

#header("X-XSS-Protection: 0");

$error = false;

if (isset($_POST['btn-login'])) {
	if (!empty($_POST['token'])) {
		if (hash_equals($_SESSION['token'], $_POST['token'])) {
			#$email = $_POST['email'];
			$email = trim($_POST['email']);
			$email = strip_tags($email);
			$email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');

			#$pass = $_POST['pass'];
			$pass = trim($_POST['pass']);
			$pass = strip_tags($pass);
			$pass = htmlspecialchars($pass, ENT_QUOTES, 'UTF-8');


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
			echo 'a';
			if (!$error) {

				$secretKey = "6LesHxAUAAAAACBQ9RDpklIXRbat_noBi-ZNQVEE";
				$verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secretKey.'&response='.$_POST['g-recaptcha-response']);
				$responseData = json_decode($verifyResponse);

				if ($responseData->success) {
					$password = hash('sha256', $pass);

					define ('DBHOST', 'localhost');
					define ('DBUSER', 'root');
					define ('DBPASS', 'admin');
					define ('DBNAME', 'krypto');
					$mysqli = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);

					if (!$mysqli) {
						echo "Error: Unable to connect to MySQL." . PHP_EOL;
						exit;
					}
					echo 'a';
					$stmt = $mysqli->stmt_init();
					if (!$stmt->prepare("SELECT userId, userName FROM users WHERE userEmail = ? AND userPass = ?"))
					{
						echo "Failed to prepare statement\n";
					}
					$stmt->bind_param("ss", $email, $password);
					$stmt->execute();

					$result = $stmt->get_result();

					if ($result->num_rows == 1) {
						$row = $result->fetch_array();
						$_SESSION['user'] = $row['userId'];
						header("Location: home.php");
						$stmt->close();
					} else {
						$errMSG = "Incorrect Credentials, Try again...";
					}

					$stmt->close();
					$mysqli->close();
				} else {
					$error = true;
					$captchaError = "Please check captcha.";
				}
			}
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
             				<input type="email" name="email" class="form-control" placeholder="Your Email" value="<?php echo $email; ?>" maxlength="200" />
                		</div>
                		<span class="text-danger"><?php echo $emailError; ?></span>
            		</div>
            
		            <div class="form-group">
	             		<div class="input-group">
		                	<span class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></span>
		             		<input type="password" name="pass" class="form-control" placeholder="Your Password" maxlength="600"/>
		                </div>
		                <span class="text-danger"><?php echo $passError; ?></span>
		            </div>

		            <input type = "hidden" name = "token" value = "<?php echo $token; ?>" />
		            
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

					<div class = "form-group">
						<div>
							<a href = "changepassword.php">Click here if you forget your password...</a>
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