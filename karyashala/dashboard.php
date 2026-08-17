<?php

session_start();

if (!isset($_SESSION['username'])) {

    header("Location: ../login.php");
    exit();

}

if ($_SESSION['role'] != "karyashala") {

    header("Location: ../login.php");
    exit();

}

?>

<!DOCTYPE html>

<html>

<head>

    <title>Hindi Karyashala Dashboard</title>

</head>

<body>

    <h1>
        Hindi Karyashala Dashboard
    </h1>

    <p>
        Welcome <?php echo $_SESSION['username']; ?>
    </p>

    <hr>

    <h2>Dashboard</h2>

    <ul>

        <li>
            <a href="karyashala_data.php">
                Hindi Karyashala Data
            </a>
        </li>

        <li>
            <a href="employees.php">
                Employee Data
            </a>
        </li>

    </ul>

    <br>

    <a href="../logout.php">
        Logout
    </a>

</body>

</html>