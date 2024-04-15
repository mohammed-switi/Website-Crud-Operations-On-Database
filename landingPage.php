<?php
session_start();
//echo  "<h1> ".$_SESSION["username"]." are you crazy </h1>";
if (!isset($_SESSION["username"])){
  header("location: index.html");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="landingPage.css">
    <title>Tables</title>
</head>
<body>
  <div id="navbar">
     <div id="logout">
        <a href="logout.php">logout</a>
      </div>
  </div>
  

  <div id="body">
<form action="homepage.php" method="post">
  <label for="tables">Choose a table to display</label>
  <select name="table" id="table">\
    <option value="car">Cars</option>
    <option value="address">Address</option>
    <option value="device">device</option>
    <option value="manufacture">Manufacture</option>
    <option value="orders">Orders</option>
    <option value="customer">Customer</option>
    <option value="car_part">Car Part</option>
    
  </select>
  <br><br>
  <input type="submit" value="Submit">
</form>

  </div>
    
</body>
</html>