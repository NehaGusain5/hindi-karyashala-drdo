<?php
session_start();

require_once "config/db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $icno = $_POST["icno"];
    $password = $_POST["password"];

    $sql = "SELECT ICNO, ENAME, PASSWORD FROM employees WHERE ICNO = ?";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $icno);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 0) {

        $error = "Incorrect ICNO";

    } else {

        $employee = mysqli_fetch_assoc($result);

        if ($password != $employee["PASSWORD"]) {

            $error = "Invalid password";

        } else {

            $role_sql = "SELECT ROLE FROM roles WHERE ICNO = ?";

            $role_stmt = mysqli_prepare($conn, $role_sql);
            mysqli_stmt_bind_param($role_stmt, "i", $icno);
            mysqli_stmt_execute($role_stmt);

            $role_result = mysqli_stmt_get_result($role_stmt);

            if (mysqli_num_rows($role_result) > 0) {

                $role_data = mysqli_fetch_assoc($role_result);

                $_SESSION["ICNO"] = $employee["ICNO"];
                $_SESSION["ENAME"] = $employee["ENAME"];
                $_SESSION["ROLE"] = $role_data["ROLE"];

                if ($role_data["ROLE"] == "admin") {

                    header("Location: admin/dashboard.php");
                    exit();

                } elseif ($role_data["ROLE"] == "karyashala_admin") {

                    header("Location: karyashala_dashboard.php");
                    exit();

                } else {

                    $error = "Invalid role";
                }

            } else {

                $error = "Role not assigned";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>DRDO Karyashala Login</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #dff3ff;
            min-height: 100vh;

            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-box {

            width: 400px;
            background-color: white;

            padding: 30px;

            border-radius: 12px;

            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);

            text-align: center;
        }

        .logo {

            width: 150px;
            height: 150px;

            object-fit: contain;

            margin-bottom: 15px;
        }

        .drdo-full-form {

            font-size: 14px;
            font-weight: bold;

            color: #333;

            line-height: 1.5;

            margin-bottom: 20px;
        }

        .login-box h2 {

            margin-bottom: 20px;

            color: #003b6f;
        }

        .input-group {

            text-align: left;

            margin-bottom: 15px;
        }

        .input-group label {

            display: block;

            margin-bottom: 6px;

            font-weight: bold;

            color: #333;
        }

        .input-group input {

            width: 100%;

            padding: 11px;

            border: 1px solid #aaa;

            border-radius: 6px;

            font-size: 16px;
        }

        .input-group input:focus {

            outline: none;

            border-color: #0066a1;
        }

        .error {

            color: red;

            margin-bottom: 15px;

            font-size: 14px;

            font-weight: bold;
        }

        .login-btn {

            width: 100%;

            padding: 12px;

            border: none;

            border-radius: 6px;

            background-color: #0066a1;

            color: white;

            font-size: 16px;

            font-weight: bold;

            cursor: pointer;
        }

        .login-btn:hover {

            background-color: #004f7d;
        }

    </style>

</head>

<body>

    <div class="login-box">

        <img
            src="images/drdo-logo.jpg"
            alt="DRDO Logo"
            class="logo"
        >

        <div class="drdo-full-form">

            Defence Research and Development Organisation
            <br>

            Ministry of Defence, Government of India

        </div>

        <h2>Login</h2>

        <?php if ($error != ""): ?>

            <div class="error">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>

        <form method="POST" action="">

            <div class="input-group">

                <label for="icno">ICNO</label>

                <input
                    type="number"
                    id="icno"
                    name="icno"
                    min="1001"
                    step="1"
                    required
                    placeholder="Enter ICNO"
                >

            </div>

            <div class="input-group">

                <label for="password">Password</label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    placeholder="Enter password"
                >

            </div>

            <button
                type="submit"
                class="login-btn"
            >
                Login
            </button>

        </form>

    </div>

</body>

</html>