<?php

session_start();
//echo  "<h1> ".$_SESSION["username"]." are you crazy </h1>";
if (!isset($_SESSION["username"])){
  header("location: index.html");
}


$servername = "localhost:3390";
$username = "root";
$password = "";
$dbname = "dbassignment";
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function getForeignKeyReferences($conn, $tableName) {
    $foreignKeyReferences = [];
    $dbName = 'dbassignment'; 

    $query = "SELECT COLUMN_NAME, REFERENCED_TABLE_NAME
              FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
              WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $dbName, $tableName);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $foreignKeyReferences[$row['COLUMN_NAME']] = $row['REFERENCED_TABLE_NAME'];
    }

    $stmt->close();
    return $foreignKeyReferences;
}

function getPrimaryKey($conn, $tableName) {
    $primaryKey = null;
    $query = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
              WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_KEY = 'PRI'";

    $stmt = $conn->prepare($query);
    $dbName = 'dbassignment'; 
    $stmt->bind_param("ss", $dbName, $tableName);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $primaryKey = $row['COLUMN_NAME'];
    }

    $stmt->close();
    return $primaryKey;
}

function renderComboBox($conn, $columnName, $tableName) {
    $primaryKey = getPrimaryKey($conn, $tableName);
    if (!$primaryKey) {
        return ''; 
    }

    $html = "<label for='$columnName' style='margin-right:5px'>" . ucfirst($columnName) . "</label>";
    $html .= "<select id='$columnName'>";
    $html .= "<option value='' selected disabled>Select $columnName</option>"; // Added line for default unselected option

    $query = "SELECT * FROM `$tableName`";
    $result = $conn->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $value = $row[$primaryKey];
            $html .= "<option value='$value' style='background-color: #f0f0f0; color: #333; width:30px'>$value</option>";
        }
    }

    $html .= "</select>";
    return $html;
}


function renderTable($table, $conn) {
    $foreignKeyReferences = getForeignKeyReferences($conn, $table);
    $tableHeaders = [
        "car" => ["Name", "Model", "Year", "Made"],
        "address" => ["ID", "Building", "Street", "City", "Country"],
        "customer" => ["ID", "First Name", "Last Name", "Address", "Job"],
        "device" => ["No", "Name", "Price", "Weight", "Made"],
        "manufacture" => ["Name", "Type", "City", "Country"],
        "orders" => ["ID", "Date", "Customer", "Car"],
        "car_part" => ["car", "part"]
    ];

        


    $tableName = ucfirst($table);

  
    echo '<head>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="styles.css">
        <title>' . $tableName . '</title>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
      </head>';

      echo "<h1 style='color: #139FE8; margin-bottom: 10px;'>$tableName Table from Cars Database developed by Mohammed</h1>";
      
  

    $query2 = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$table'";
    $stmt2 = $conn->prepare($query2);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $columns = [];
    $category_textfield_html = "<form>";
    while ($row2 = $result2->fetch_assoc()) {
        $column = $row2['COLUMN_NAME'];
        $columns[] = $row2['COLUMN_NAME'];
        if (array_key_exists($column, $foreignKeyReferences)) {
            // It's a foreign key, render a combo box
            $refTable = $foreignKeyReferences[$column];
            $category_textfield_html .= renderComboBox($conn, $column, $refTable);
        } else {
        
            $category_textfield_html .= "<label for='$column' style='margin-right:5px'>" . ucfirst($column) . "</label>";
            $category_textfield_html .= "<input style='margin-right:10px' type='text' id='$column'>";
        }
    }

    $category_textfield_html .= "</form>"; 
    echo $category_textfield_html;

    $stmt2->close();

    echo "<br><br>";

    echo "
    <button id='search' style='padding: 5px 10px; margin-left: 10px;'>Search</button>
    <button id='insert' style='padding: 5px 10px; margin-left: 10px;'>Insert</button>
    <button id='update' style='padding: 5px 10px; margin-left: 10px;'>Update</button>
    <button id='clear' style='padding: 5px 10px; margin-left: 10px;'>Clear</button>
    <div id='result' style='margin-top: 20px;'></div>";
    
    
    
        
    
    // JavaScript for handling search and clear actions
    $selectedTable=$table;
    
    
    $javascriptObject = "{\n";
    
        // Dynamically create JavaScript object properties for each column
        foreach ($columns as $column) {
            $javascriptObject .= "    '$column': $('#$column').val(),\n";
        }
        
        // Convert PHP array $columns to a JSON string for JavaScript
        $columnsJson = json_encode($columns);
        $javascriptObject .= "    'columns': $columnsJson,\n";
        $javascriptObject .= "    'table': '$selectedTable'\n"; // Using PHP variable directly
        $javascriptObject .= "}";
        
        // Now $javascriptObject contains the JavaScript object as a string
        
        echo "<script>
        $(document).ready(function () {
            var isUpdateStage = false;
            var oldData = {}; // Object to store old data
            
            $('#update').click(function() {
                if (!isUpdateStage) {
                    var formData = " . $javascriptObject . ";
                    oldData = formData; // Store the old data
                    $('#result').html('<p>Loading...</p>');
                    $('#data-table').hide();
                    $.post('searchResult.php', formData, function(data, status) {
                        var proceed = showUpdatePopup(data);
                        if (proceed) {
                            isUpdateStage = true;
                            $('#update').text('Confirm Update');
                            enterNewValuesForUpdate(data); // Pass the old data for reference
                        } else {
                            console.log('Update cancelled');
                            $('#result').html('');
                        }
                    });
                } else {
                    // Collect new data from user input
                    var newFormData = " . $javascriptObject . ";
                    // Combine old and new data if necessary or use separately
                    $.post('update.php', {oldData: oldData, newData: newFormData}, function(data, status) {
                        $('#result').html(data);
                        isUpdateStage = false;
                        $('#update').text('Update');
                        setTimeout(function() { location.reload(); }, 50);
                    });
                }
            });
        });
        
        function showUpdatePopup(affectedRows) {
            var popupContent = 'The rows will be displayed now will be affected\\n';
            popupContent += '\\nDo you want to proceed with updating these rows?';
            return confirm(popupContent);
        }
        
        function enterNewValuesForUpdate(searchCriteria) {
            // Display the searchCriteria and provide input fields for new values
            $('#result').html(searchCriteria);
            // Logic for entering new values...
            // You can use the old data (searchCriteria) to pre-fill or display alongside new input fields
        }
        
        function collectNewFormData() {
            // Logic to collect new data from the input fields
            // This might involve reading values from a form or individual input elements
            var newFormData = {}; // Replace with actual logic to collect new data
            return newFormData;
        }
        </script>";
        
        
        
    
    
//         echo "<script>
//         $(document).ready(function () {
    
//             $('#insert').click(function(){
//                 var proceed = showUpdatePopup();
//                 if (proceed) {
//                     $('#result').html('<p>Loading...</p>');
//                     $.post('insert.php',";
//     echo  $javascriptObject;
//     echo ", function(data, status){
//                         $('#result').html(data);
//                         setTimeout(function() {
//                             location.reload();
//                         }, 250); 
//                     });
//                 } else {
//                     console.log('Insert cancelled');
//                     $('#result').html('');
//                 }
//             });
    
//             function showUpdatePopup() {
//                 var popupContent = 'This action will update rows.\\n';
//                 popupContent += '\\nDo you want to proceed with inserting the values you entered?';
//                 return confirm(popupContent);
//             }
    
//         });";

//     echo"
//     $('#search').click(function () {
//             $('#data-table').hide();
//             $('#result').html('<p>Loading...</p>');
//             $.post('searchResult.php',";
//         echo  $javascriptObject;
//     echo ", function (data, status) {
//                 $('#result').html(data);
//             });
//     });

//     $('#clear').click(function () {
//         $('#data-table').show(); // Show the table again
//         $('#result').html(''); // Clear the search results
//         $('#searchInput').val(''); // Clear the search input field
//     });
// });
// </script>";
    
    
    
    echo "

<script>
$(document).ready(function () {


    $('#insert').click(function(){
        var proceed = showUpdatePopup();
        if (proceed) {
            $('#result').html('<p>Loading...</p>');
            $.post('insert.php', ".$javascriptObject.", function(data, status){
                $('#result').html(data);
                setTimeout(function() {
                    location.reload();
                }, 250); 
            });
        } else {
            console.log('Insert cancelled');
            $('#result').html('');
        }
    });

    function showUpdatePopup() {
        var popupContent = 'This action will update rows.\\n';
        popupContent += '\\nDo you want to proceed with inserting the values you entered?';
        return confirm(popupContent);
    }

    // Search click event
    $('#search').click(function () {
        $('#data-table').hide();
        $('#result').html('<p>Loading...</p>');
        $.post('searchResult.php', ".$javascriptObject.", function (data, status) {
            $('#result').html(data);
        });
    });

    // Clear click event
    $('#clear').click(function () {
        $('#data-table').show();
        $('#result').html('');
        $('#searchInput').val('');
    });
});
</script>
";
    
    
    
    
    
    
    
    
    
    // ... [Rest of your PHP code including table rendering]
    
    
        echo "<style>
    
    
        
            table {
                width: 100%;
                border-collapse: collapse;
                margin: 25px 0;
                font-size: 0.9em;
                font-family: sans-serif;
                min-width: 400px;
                box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
            }
            thead tr {
                background-color: #009879;
                color: #ffffff;
                text-align: left;
            }
            th, td {
                padding: 12px 15px;
            }
            tbody tr {
                border-bottom: 1px solid #dddddd;
            }
            tbody tr:nth-of-type(even) {
                background-color: #f3f3f3;
            }
            tbody tr:last-of-type {
                border-bottom: 2px solid #009879;
            }
        </style>";
    
        echo "<table id='data-table'>";
        echo "<thead><tr>";
        foreach ($tableHeaders[$table] as $header) {
            echo "<th>$header</th>";
        }
        echo "</tr></thead>";
        echo "<tbody>";
    
        $sql = "SELECT * FROM $table";
        $result = $conn->query($sql);
    
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>$value</td>";
                }
                echo "</tr>";
            }
            echo "</tbody></table>";
        } else {
            echo "0 results";
        }
}

// ... [rest of your PHP code including handling POST requests, if any]
if (isset($_POST["table"])) {
    $selectedTable = $_POST["table"];
    if (in_array($selectedTable, ["car", "address", "customer", "device", "manufacture", "orders","car_part"])) {
        renderTable($selectedTable, $conn);
    } else {
        echo "Invalid table selection.";
    }
}
$conn->close();


?>




