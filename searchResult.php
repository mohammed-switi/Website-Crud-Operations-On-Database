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

// Function to display the table
function displayTable($result, $tableName, $tableHeaders) {
    echo '<head>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="styles.css">
        <title>' . $tableName . '</title>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
      </head>';

      echo "<h2 style='color: #4a4a4a; border-bottom: 2px solid #009879; padding-bottom: 5px; margin-bottom: 20px;'>Search Result:</h2>";

    // Check if there are results
    if ($result->num_rows > 0) {
        echo "<table border='1'><thead><tr>";

        foreach ($tableHeaders[$tableName] as $header) {
            echo "<th>$header</th>";
        }

        echo "</tr></thead><tbody>";

        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>$value</td>";
            }
            echo "</tr>";
        }

        echo "</tbody></table>";
    } else {
        // Display a message if there are no results
        echo "<div style='margin-top: 20px; padding: 10px; background-color: #ffdddd; border-left: 6px solid #f44336;'>
        <p style='margin: 0; color: #333;'>No results found for your search.</p>
      </div>";
      
    }
}


// Table headers definition
$tableHeaders = [
    "car" => ["Name", "Model", "Year", "Made"],
    "address" => ["ID", "Building", "Street", "City", "Country"],
    "customer" => ["ID", "First Name", "Last Name", "Address", "Job"],
    "device" => ["No", "Name", "Price", "Weight", "Made"],
    "manufacture" => ["Name", "Type", "City", "Country"],
    "orders" => ["ID", "Date", "Customer", "Car"],
    "car_part" => ["car", "part"]
];



$tableName = isset($_POST["table"]) && array_key_exists($_POST["table"], $tableHeaders) ? $_POST["table"] : 'default_table';

$searchInput = isset($_POST['searchInput']) ? $_POST['searchInput'] : '';

$selectedColumn= isset($_POST['column']) ? $_POST['column'] : 'id';


if (!array_key_exists($tableName, $tableHeaders)) {
    echo "Invalid table selection.";
    die();
}

//$query = "SELECT * FROM `$tableName` WHERE `$selectedColumn` LIKE ?";
$query = "SELECT * FROM `$tableName` WHERE 1=1 ";

$columns= $_POST["columns"];
foreach($columns as $column) {
    if (!empty($_POST[$column]))
    $query .= "AND " . $column . " = '" . $conn->real_escape_string($_POST[$column]) . "' ";
}




$stmt = $conn->prepare($query);

$stmt->execute();
$result = $stmt->get_result();

displayTable($result, $tableName, $tableHeaders);

$stmt->close();
$conn->close();
?>