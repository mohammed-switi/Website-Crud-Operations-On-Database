<?php
$servername = "localhost:3390";
$username = "root";
$password = "";
$dbname = "dbassignment";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Assuming oldData and newData are passed as associative arrays
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['oldData']) && isset($_POST['newData'])) {
        $oldData = $_POST['oldData'];
        $newData = $_POST['newData'];

        // Example: Accessing a specific value
        // Assuming 'someColumn' is a key in your data arrays
        $oldValueTableName = $oldData['table'];
        $oldValuecolumns = $oldData['columns'];
        $newValuecolumns =  $newData['columns'];


        // Your update logic here...
        
        
        
        
        // Checking if both oldData and newData are received
        if ($oldData === null || $newData === null) {
            echo "Missing data for update operation.";
            exit;
        }
        
        $updates = [];
        $whereConditions = [];
        // Building the SET part of the query
        foreach ($oldValuecolumns as $key => $value) {
            if (!empty($newData[$value])) {
                $updates[] = $value . " = '" . $conn->real_escape_string($newData[$value]) . "'";
            }
        }
        foreach ($oldValuecolumns as $key => $value) {
            if ($oldData[$value]) {
                $whereConditions[] = $value . " = '" . $conn->real_escape_string($oldData[$value]) . "'";
            }
        }


// Building the WHERE part of the query

// Check if there are updates to make and conditions to match
if (count($updates) > 0 && count($whereConditions) > 0) {
    // Assuming tableName is part of newData
    $tableName = isset($oldValueTableName) ? $oldValueTableName : 'default_table';
    $query = "UPDATE `$oldValueTableName` SET " . implode(", ", $updates) . " WHERE " . implode(" AND ", $whereConditions);

    echo $query;

    $stmt = $conn->prepare($query);

    if ($stmt === false) {
        die("Error preparing the statement: " . $conn->error);
    }

    if ($stmt->execute()) {
        echo "Record updated successfully.";
    } else {
        echo "Error updating record: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "No data to update or missing conditions.";
}
    }

}

$conn->close();
?>
