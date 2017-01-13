<?php
define ('DBHOST', 'localhost');
define ('DBUSER', 'root');
define ('DBPASS', 'admin');
define ('DBNAME', 'krypto');

$conn = mysql_connect(DBHOST, DBUSER, DBPASS);
$dbcon = mysql_select_db(DBNAME);

if (!$conn) {
	die("Connection failed: " . mysql_error());
} 

if (!$dbcon) {
	die("Database Connection failed: " . mysql_error());
}

$username = $_POST['username'];
$password = $_POST['password'];

$sql = "INSERT INTO userData (username, password) VALUES ('$username', '$password')";

$retval = mysql_query($sql, $conn);

if (!$retval)
{
    die('Could not enter data: ' . mysql_error());
}

echo "Entered data successfully\n";
mysql_close($conn);
?>