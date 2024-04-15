<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<?php
    session_unset();
    session_destroy();

?>

<script>
    window.location.href="index.html"
</script>
    
</body>
</html>