<?php
$servername = "localhost:3390";
$username = "root";
$password = "";
$dbname = "dbassignment";
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$tableHeaders = [
    "car" => ["Name", "Model", "Year", "Made"],
    "address" => ["ID", "Building", "Street", "City", "Country"],
    "customer" => ["ID", "First Name", "Last Name", "Address", "Job"],
    "device" => ["No", "Name", "Price", "Weight", "Made"],
    "manufacture" => ["Name", "Type", "City", "Country"],
    "orders" => ["ID", "Date", "Customer", "Car"]
];

$tableName = isset($_POST["table"]) && array_key_exists($_POST["table"], $tableHeaders) ? $_POST["table"] : 'default_table';


$selectedColumn= isset($_POST['column']) ? $_POST['column'] : 'id';



if (!array_key_exists($tableName, $tableHeaders)) {
    echo "Invalid table selection.";
    die();
}

$query = "INSERT INTO `$tableName` (";
$values = "VALUES (";
$allValuesPresent = true;

foreach ($_POST['columns'] as $column) {
    // Check if a value for the column exists and is not empty
    if (!isset($_POST[$column]) || $_POST[$column] === '') {
        $allValuesPresent = false;
        break; 
    } else {
        $query .= $column . ", ";
        $values .= "'" . $conn->real_escape_string($_POST[$column]) . "', ";
    }
}


// Remove the last comma and space
$query = rtrim($query, ", ") . ") ";
$values = rtrim($values, ", ") . ");";

// Check if all values were present
if ($allValuesPresent) {
    $query .= $values; // Final query
  
} else {
    echo "Error: Not all values are provided for the insert operation.";
    $query = ""; 
}




$stmt = $conn->prepare($query);

if ($stmt === false) {
    die("Error preparing the statement: " . $conn->error);
}

try{
    $executeSuccess = $stmt->execute();
}catch(Exception $e){
    $errorMessage = addslashes($e->getMessage());

    // Display the error message in a JavaScript alert
    echo "<script>alert('Error: $errorMessage' );</script>";
    exit();
}

if ($executeSuccess) {
    echo "<div style='margin-top: 20px; padding: 10px; background-color: #A1EEBD; border-left: 6px solid #65B741;'>
    <p style='margin: 0; color: #000;'>Record inserted successfully.</p>
  </div>";
} else {
    $errorMessage =$stmt->error;
    echo "<script>alert('Error inserting record: $errorMessage' );</script>";

}

$stmt->close();
$conn->close();

?>