<?php
$servername = "localhost:3390";
$username = "root";
$password = "";
$dbname = "db_sowaity";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $firstname=$_POST["fname"];
    $lastname=$_POST["lname"];
    $uname=$_POST["username"];
    $password=$_POST["pwrd"];
    $conpassword=$_POST["conpassword"];


    $query = "SELECT * FROM accounts WHERE username=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $uname);
    
    $stmt->execute();
    $result = $stmt->get_result();
    $dublicate = $result->num_rows;
    if ($dublicate > 0) {
        echo "<script>
                alert('The username $uname is not available');
                window.location.href='signup.html';
              </script>";
        exit();
    }else{
        if($password==$conpassword){
            $errors=isPasswordValid($password);
            echo count($errors);
            if(!count($errors) == 0){
                $errorString = implode("\\n", $errors); 
                echo "<script>
                alert('Password is not valid:\\n$errorString');
                window.location.href='signup.html';
                </script>";
                exit();
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO accounts (firstname, username, password, lastname) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssss", $firstname, $uname, $hashedPassword, $lastname);
            echo"hello bitch corr";
          
            if ($stmt === false) {
                die("Error preparing the statement: " . $conn->error);
            }
            
            
            $executeSuccess = $stmt->execute();
            
            if ($executeSuccess) {
                echo "Record inserted successfully.";
            } else {
                echo "Error inserting record: " . $stmt->error;
            }
            header("location:index.html");
           
           
        }else{
            echo "<script> 
            alert('the passwords didn\'t match');
             window.location.href='signup.html';
             </script>";
               exit();
        

        }
    }



    $stmt->close();
    $conn->close();
}


function isPasswordValid($password) {
    $errors = [];

    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }

    if (!preg_match("#[0-9]+#", $password)) {
        $errors[] = "Password must include at least one number.";
    }

    if (!preg_match("#[a-zA-Z]+#", $password)) {
        $errors[] = "Password must include at least one letter.";
    }

    if (!preg_match("#[a-z]+#", $password)) {
        $errors[] = "Password must include at least one lowercase letter.";
    }

    if (!preg_match("#[A-Z]+#", $password)) {
        $errors[] = "Password must include at least one uppercase letter.";
    }

    if (!preg_match("#\W+#", $password) && !preg_match("#_#", $password)) {
        $errors[] = "Password must include at least one special character.";
    }

    return $errors;
}

?>