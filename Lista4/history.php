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
		            <li><a href="transfer.php">Transfer</a></li>
		            <li class="active"><a href="history.php">History</a></li>
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
     			<h3>Transfers History</h3>
     		</div>
        
        	<div class="row">
        		<div class="col-lg-12">
        			<?php 
						$query = "SELECT userAccount FROM users WHERE userId=".$_SESSION['user'];
						$result = mysql_query($query);
						$count = mysql_num_rows($result);
						if ($count == 0) {
							$error = true;
						}

						if (!$error) {
							$myAccount = mysql_fetch_array($result);
							$myAccount = $myAccount["userAccount"];
							$query = "SELECT transferTitle, transferTarget, transferAmount, transferDate, transferSubmit, transferAdminSubmit FROM transfers WHERE transferOwner='$myAccount'";
							$result = mysql_query($query);
							$count = mysql_num_rows($result);
							if ($count == 0) {
								$error = true;
							}
							if (!$error) {
								$counter = 0;
								while ($row = mysql_fetch_array($result)) {
									echo "<div id = 'transfer'>";
									echo "<div id = 'title".$counter."' style = 'float: left; margin-right: 10px;'>".$row['transferTitle']."</div>";
									echo "<div id = 'target".$counter."' style = 'float: left; margin-right: 10px;'>".$row['transferTarget']."</div>";
									echo "<div id = 'amount".$counter."' style = 'float: left; margin-right: 10px;'>".$row['transferAmount']."</div>";
									echo "<div id = 'date' style = 'float: left; margin-right: 10px;'>".$row['transferDate']."</div>";
									if ($row['transferSubmit'] == 1) {
										echo "<div id = 'submit' style = 'float: left; margin-right: 10px;'>Submitted by user</div>";
									}
									else {
										echo "<div id = 'submit' style = 'float: left; margin-right: 10px;'>Not submitted by user</div>";
									} 
									if ($row['transferAdminSubmit'] == 1) {
										echo "<div id = 'submit' style = 'float: left; margin-right: 10px;'>Submitted by admin</div>";
									}
									else {
										echo "<div id = 'submit' style = 'float: left; margin-right: 10px;'>Not submitted by admin</div>";
									} 
									echo "</div>";
									echo "<span style = 'clear: both; display: table;'></span>";
									$counter += 1;
								}
							} else {
								echo "No transfers found.<br>";
							}
						} 
    				?>
        		</div>
    		</div>
    
    	</div>
    </div>
    <script src="js/jquery-3.1.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
</body>
</html>
<?php ob_end_flush(); ?>