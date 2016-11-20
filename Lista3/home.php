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
		            <li class="active"><a href="home.php">Home</a></li>
		            <li><a href="transfer.php">Transfer</a></li>
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
     		<div class="page-header">
     			<h3>Welcome in MyBank</h3>
     		</div>
        
        	<div class="row">
        		<div class="col-lg-12">
        			<h1>You can send transfers here and nothing more!</h1>
        		</div>
    		</div>
    
    	</div>
    </div>
    <script src="js/jquery-3.1.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
</body>
</html>
<?php ob_end_flush(); ?>