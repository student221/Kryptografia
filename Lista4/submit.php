<?php
ob_start();
session_start();
require_once 'dbconnect.php';

if (!isset($_SESSION['user'])) {
	header("Location: index.php");
	exit;
}

if (!isset($_SESSION['currTitle']) || !isset($_SESSION['currAccount']) || !isset($_SESSION['currAmount'])) {
	header("Location: transfer.php");
	exit;
}

$res = mysql_query("SELECT * FROM users WHERE userId = ".$_SESSION['user']);
$userRow = mysql_fetch_array($res);

$title = $_SESSION['currTitle'];
$account = $_SESSION['currAccount'];
$amount = $_SESSION['currAmount'];

$error = false;

if (isset($_POST['btn-send'])) {

	$query = "SELECT userAccount FROM users WHERE userId=".$_SESSION['user'];
	$result = mysql_query($query);
	$count = mysql_num_rows($result);
	if ($count == 0) {
		$error = true;
	}

	if (!$error) {

		$row = mysql_fetch_array($result);
		$myAccount = $row["userAccount"];

		$query = "UPDATE transfers SET transfers.transferSubmit = 1 WHERE transfers.transferAmount = '$amount' AND transfers.transferOwner = '$myAccount' AND transfers.transferTarget = '$account' AND transfers.transferTitle = '$title'";
		$res = mysql_query($query);

		if ($res) {
			$errTyp = "success";
			$errMSG = "Successfully send transfer, you may now send next transfer.";
			unset($_SESSION['currTitle']);
			unset($_SESSION['currAccount']);
			unset($_SESSION['currAmount']);
			header("Location: transfer.php");
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
     			<h3>Submit transfer</h3>
     		</div>
     		
			<form id = "form1" method = "post" action = "<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" autocomplete = "off">
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
					<div id = "title">
						Title:
						<div id = "titleName">
							<?php echo $title; ?>
						</div>
					</div>
				</div>
				<div class = "form-group">
					<div id = "account">
						Account: 
						<div id = "accountNumber">
							<?php echo $account; ?>
						</div>
					</div>
				</div>
				<div class = "form-group">
					<div id = "amount">
						Amount: 
						<div id = "amountNumber">
							<?php echo $amount; ?>
						</div>
					</div>
				</div>
				<div class = "form-group">
					<hr />
				</div>
				<div class = "form-group">
					<button type = "submit" class = "btn btn-block btn-primary" name = "btn-send">Submit Transfer</button>
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
	<script src = "js/script.js"></script>
</body>
</html>
<?php ob_end_flush(); ?>