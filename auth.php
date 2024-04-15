<?php
 session_start();

 $servername = "localhost:3390";
 $username = "root";
 $password = "";
 $dbname = "db_sowaity";
 
 $conn = new mysqli($servername, $username, $password, $dbname);
 
 if ($conn->connect_error) {
     die("Connection failed: " . $conn->connect_error);
 }
 
 if ($_SERVER["REQUEST_METHOD"] == "POST") {
     $uname = $_POST['username'];
     $password = $_POST['password'];
 
     $query = "SELECT * FROM accounts WHERE username = ?";
     $stmt = $conn->prepare($query);
     $stmt->bind_param("s", $uname);
     $stmt->execute();
     $result = $stmt->get_result();
 
     if ($result->num_rows > 0) {
         $row = $result->fetch_assoc();
         if (password_verify($password, $row['password'])) {
             $_SESSION['loggedin'] = true;
             $_SESSION['username'] = $uname;
             echo "<script>window.location = 'landingPage.php';</script>";
             exit;
         } else {
            echo "<script>alert('Password didn\'t match'); window.location = 'index.html';</script>";
           exit();
             
         }
     } else {
        echo "<script>alert('username is not found'); window.location = 'index.html';</script>";
         header("location: index.html");
         exit;
     }
 }
 $conn->close();
 ?>
 
?>
