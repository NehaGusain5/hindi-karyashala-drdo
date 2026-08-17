<?php

session_start();

$db_connection = mysqli_connect(
    "localhost",
    "root",
    "",
    "karyashala"
);

if (!$db_connection) {
    die("Database connection failed");
}

$error = "";

if (isset($_POST['login'])) {

    $ic_number = $_POST['ic_number'];
    $password = $_POST['password'];

    $result = mysqli_query(
        $db_connection,
        "SELECT employees.*, roles.password, roles.role
         FROM employees
         INNER JOIN roles
         ON employees.ic_number = roles.ic_number
         WHERE employees.ic_number='$ic_number'
         AND roles.password='$password'"
    );

    if (mysqli_num_rows($result) == 1) {

        $row = mysqli_fetch_assoc($result);

        $_SESSION['ic_number'] = $row['ic_number'];
        $_SESSION['name'] = $row['name'];
        $_SESSION['email'] = $row['email'];
        $_SESSION['role'] = $row['role'];

        setcookie(
            "ic_number",
            $row['ic_number'],
            time() + (10 * 365 * 24 * 60 * 60),
            "/"
        );


        if ($row['role'] == "Admin") {

            header("Location: admin/dashboard.php");
            exit();

        }

        if ($row['role'] == "Karyashala Admin") {

            header("Location: karyashala/dashboard.php");
            exit();

        }

        if ($row['role'] == "Employee") {

            header("Location: employee/dashboard.php");
            exit();

        }

    } else {

        $error = "Invalid IC Number or Password";

    }
}

mysqli_close($db_connection);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        DRDO Hindi Karyashala Portal
    </title>

    <link
        rel="stylesheet"
        href="css/login.css"
    >

</head>

<body>

<div class="login-page">

    <div class="logo-section">

        <img
            src="images/drdo-logo.jpg"
            alt="DRDO Logo"
            class="drdo-logo"
        >

        <h1>
            Hindi Karyashala Portal
        </h1>

        <p>
            Defence Research and Development Organisation
        </p>

    </div>


    <div class="login-box">


        <h2>
            Login
        </h2>

        <p class="login-subtitle">
            Enter your credentials to continue
        </p>


        <!-- ERROR -->

        <?php

        if ($error != "") {

            echo "
            <div class='error'>
                $error
            </div>
            ";

        }

        ?>


        <form method="POST">

            <div class="input-group">

                <label for="ic_number">
                    IC Number
                </label>

                <input
                    type="text"
                    id="ic_number"
                    name="ic_number"
                    placeholder="Enter your IC Number"
                    required
                >

            </div>

            <div class="input-group">

                <label for="password">
                    Password
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter your Password"
                    required
                >

            </div>

            <button
                type="submit"
                name="login"
            >
                Login
            </button>

        </form>

    </div>

    <div class="footer">

        <p>
            © 2026 Defence Research and Development Organisation
        </p>

        <p>
            Hindi Karyashala Portal
        </p>

    </div>


</div>

</body>

</html>