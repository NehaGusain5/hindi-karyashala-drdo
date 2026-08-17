<?php

session_start();

if (!isset($_SESSION['username'])) {

    header("Location: ../login.php");
    exit();

}

if ($_SESSION['role'] != "employee") {

    header("Location: ../login.php");
    exit();

}

?>

<!DOCTYPE html>

<html>

<head>

    <title>Employee Dashboard</title>

</head>

<body>

    <h1>
        Employee Dashboard
    </h1>

    <p>
        Welcome <?php echo $_SESSION['username']; ?>
    </p>

    <hr>

    <h2>My Information</h2>

    <ul>

        <li>
            <a href="my_data.php">
                My Data
            </a>
        </li>

    </ul>

    <br>

    <a href="../logout.php">
        Logout
    </a>

</body>

</html>