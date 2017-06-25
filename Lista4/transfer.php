<?php
ob_start();
session_start();
require_once 'dbconnect.php';

if (!isset($_SESSION['user'])) {
	header("Location: index.php");
	exit;
}

$res = mysql_query("SELECT * FROM users WHERE userId = ".$_SESSION['user']);
$userRow = mysql_fetch_array($res);

$error = false;

if (isset($_POST['btn-send'])) {
	$title = $_POST['title'];
	$account = $_POST['account'];
	$amount = $_POST['amount'];
/*	$title = trim($_POST['title']);
	$title = strip_tags($title);
	$title = htmlspecialchars($title);

	$account = trim($_POST['account']);
	$account = strip_tags($account);
	$account = htmlspecialchars($account);

	$amount = trim($_POST['amount']);
	$amount = strip_tags($amount);
	$amount = htmlspecialchars($amount);*/

	if (empty($title)) {
		$error = true;
		$titleError = "Please enter title";
	} else if (strlen($title) < 3) {
		$error = true;
		$titleError = "Title must have atleast 3 characters.";
	}
	# else if (!preg_match("/^[a-zA-Z ]+$/", $title)) {
	#	$error = true;
	#	$titleError = "Title must contain alphabets and space.";
	#}

	if (empty($account)) {
		$error = true;
		$accountError = "Please enter account number.";
	} else if (strlen($account) != 32) {
		$error = true;
		$accountError = "Account number must have 32 characters.";
	} else if (!preg_match("/^[A-Z0-9-]+$/", $account)) {
		$error = true;
		$accountError = "Account number must contain alphabets and digits.";
	}

	if (empty($amount)) {
		$error = true;
		$amountError = "Please enter amount to transfer.";
	} else if (!preg_match("/^[0-9]+$/", $amount)) {
		$error = true;
		$amountError = "Amount must contain numbers.";
	}

	$query = "SELECT userAccount FROM users WHERE userId=".$_SESSION['user'];
	$result = mysql_query($query);
	$count = mysql_num_rows($result);
	if ($count == 0) {
		$error = true;
	}

	if (!$error) {

		$row = mysql_fetch_array($result);
		$myAccount = $row["userAccount"];

		$query = "INSERT INTO transfers(transferAmount, transferDate, transferOwner, transferTarget, transferSubmit, transferTitle) VALUES('$amount', CURDATE(), '$myAccount', '$account', '0', '$title')";
		$res = mysql_query($query);

		if ($res) {
			$_SESSION['currTitle'] = $title;
			$_SESSION['currAccount'] = $account;
			$_SESSION['currAmount'] = $amount;
			header("Location: submit.php");
		} else {
			$errTyp = "danger";
			$errMSG = "Something went wrong, try again later...";
		}
	}
}

?>

<!DOCTYPE html>
<html>
<head>
	<meta http-equiv = "Content-Type" content = "text/html; charset = utf-8" />
	<title>Welcome <?php echo $userRow['userEmail']; ?></title>
	<link rel = "stylesheet" href = "css/bootstrap.min.css" type = "text/css" />
	<script src = "js/script.js"></script>
</head>

<body>
	<nav class="navbar navbar-default navbar-fixed-top">
      	<div class="container">
        	<div class="navbar-header">
	          	<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
		            <span class="sr-only">Toggle navigation</span>
		            <span class="icon-bar"></span>
		            <span class="icon-bar"></span>
		            <span class="icon-bar"></span>
	          	</button>
	          	<span class="navbar-brand">MyBank</a>
        	</div>
    		<div id="navbar" class="navbar-collapse collapse">
	          	<ul class="nav navbar-nav">
		            <li><a href="home.php">Home</a></li>
		            <li class="active"><a href="transfer.php">Transfer</a></li>
		            <li><a href="history.php">History</a></li>
		            <?php
		            	if ($userRow['userName'] == 'root')
		            		echo "<li><a href = 'adminsubmit.php'>Submit</a></li>";
		            ?>
	          	</ul>
      			<ul class="nav navbar-nav navbar-right">
        			<li class="dropdown">
	              		<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
	     				<span class="glyphicon glyphicon-user"></span>&nbsp;Hi' <?php echo $userRow['userEmail']; ?>&nbsp;<span class="caret"></span></a>
	              		<ul class="dropdown-menu">
	                		<li><a href="logout.php?logout"><span class="glyphicon glyphicon-log-out"></span>&nbsp;Sign Out</a></li>
	              		</ul>
            		</li>
          		</ul>
        	</div>
  		</div>
    </nav> 

 	<div id="wrapper">
 		<div class="container">
			<div class = "form-group">
				<hr />
			</div>

     		<div class="page-header">
     			<h3>Transfer Data</h3>
     		</div>
     		
			<form id = "form" method = "post" action = "<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" autocomplete = "off">
			<div class = "col-md-12">
				<?php 
				if (isset($errMSG)) {
					?>
					<div class = "form-group">
						<div class = "alert alert-<?php echo ($errTyp == "success") ? "success" : $errTyp; ?>">
							<span class = "glyphicon glyphicon-info-sign"></span><?php echo $errMSG; ?>
						</div>
					</div>
					<?php
				}
				?>
				<div class = "form-group">
					<div class = "input-group">
						<span class = "input-group-addon"><span class = "glyphicon glyphicon-user"></span></span>
						<input type = "text" name = "title" class = "form-control" placeholder = "Enter Title" maxlength = "600" value = "<?php echo $title ?>" />			
					</div>
					<span class = "text-danger"><?php echo $titleError; ?></span>
				</div>
				<div class = "form-group">
					<div class = "input-group">
						<span class = "input-group-addon"><span class = "glyphicon glyphicon-envelope"></span></span>
						<input type = "text" id = "account" name = "account" class = "form-control" placeholder = "Enter Account Number" maxlength = "600" value = "<?php echo $account ?>" />
					</div>
					<span class = "text-danger"><?php echo $accountError; ?></span>
				</div>
				<div class = "form-group">
					<div class = "input-group">
						<span class = "input-group-addon"><span class = "glyphicon glyphicon-lock"></span></span>
						<input type = "text" name = "amount" class = "form_control" placeholder = "Enter Cash Amount" maxlength = "15" />				
					</div>
					<span class = "text-danger"><?php echo $amountError; ?></span>
				</div>
				<div class = "form-group">
					<hr />
				</div>
				<div class = "form-group">
					<button type = "submit" class = "btn btn-block btn-primary" name = "btn-send">Send Transfer</button>
				</div>
				<div class = "form-group">
					<hr />
				</div>
			</div>
			</form>
		</div>
    
    </div>
    <script src="js/jquery-3.1.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
</body>
</html>
<?php ob_end_flush(); ?>