<?php

session_start();

require_once "../config/db.php";

if (!isset($_SESSION["ICNO"]) || !isset($_SESSION["ROLE"])) {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION["ROLE"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

$selected_period = $_GET["period"] ?? "2023-2025";

$status = $_GET["status"] ?? "attended";

$period_parts = explode("-", $selected_period);

if (count($period_parts) != 2) {
    $period_parts = [2023, 2025];
}

$start_year = intval($period_parts[0]);
$end_year = intval($period_parts[1]);

$start_date = $start_year . "-01-01";
$end_date = ($end_year - 1) . "-12-31";

if ($status == "attended") {

    $sql = "
        SELECT
            e.ICNO,
            e.ENAME,
            'Attended' AS STATUS
        FROM employees e

        INNER JOIN karyashalamgt k
            ON e.ICNO = k.ICNO

        WHERE k.karyashala_date
              BETWEEN ? AND ?

        GROUP BY
            e.ICNO,
            e.ENAME

        ORDER BY e.ICNO ASC
    ";

} else {

    $sql = "
        SELECT
            e.ICNO,
            e.ENAME,
            'Not Attended' AS STATUS
        FROM employees e

        WHERE NOT EXISTS (

            SELECT 1

            FROM karyashalamgt k

            WHERE k.ICNO = e.ICNO

            AND k.karyashala_date
                BETWEEN ? AND ?

        )

        ORDER BY e.ICNO ASC
    ";
}


$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ss",
    $start_date,
    $end_date
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Generate Karyashala Report</title>

    <link
        rel="stylesheet"
        href="../css/admin.css"
    >

    <style>

        .page-container {
            width: 95%;
            max-width: 1200px;
            margin: 30px auto;
        }

        .report-controls {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .control-row {
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }

        .control-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .control-group select {
            padding: 10px;
            border: 1px solid #aaa;
            border-radius: 5px;
        }

        .report-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #0066a1;
            color: white;
            padding: 12px;
            text-align: left;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }

        .status-attended {
            color: #198754;
            font-weight: bold;
        }

        .status-not-attended {
            color: #dc3545;
            font-weight: bold;
        }

        .back-button {
            display: inline-block;
            margin-bottom: 15px;
            padding: 10px 15px;
            background: #555;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .report-heading {
            color: #003b6f;
            margin-bottom: 15px;
        }

    </style>

</head>

<body>


<div class="header">

    <div class="header-left">

        <img
            src="../images/drdo-logo.jpg"
            class="header-logo"
            alt="DRDO Logo"
        >

        <div class="header-title">

            <h1>
                DRDO Hindi Karyashala Management
            </h1>

            <p>
                Defence Research and Development Organisation
            </p>

        </div>

    </div>

    <a
        href="../logout.php"
        class="logout-btn"
    >
        Logout
    </a>

</div>


<div class="page-container">

    <a
        href="dashboard.php"
        class="back-button"
    >
        ← Back to Dashboard
    </a>


    <div class="report-controls">

        <h2 class="report-heading">
            Generate Karyashala Report
        </h2>


        <form method="GET">

            <div class="control-row">


                <!-- PERIOD -->

                <div class="control-group">

                    <label>
                        Reporting Period
                    </label>

                    <select
                        name="period"
                        onchange="this.form.submit()"
                    >

                        <option
                            value="2023-2025"
                            <?php
                            if ($selected_period == "2023-2025")
                                echo "selected";
                            ?>
                        >
                            2023-2025
                        </option>

                        <option
                            value="2024-2026"
                            <?php
                            if ($selected_period == "2024-2026")
                                echo "selected";
                            ?>
                        >
                            2024-2026
                        </option>

                        <option
                            value="2025-2027"
                            <?php
                            if ($selected_period == "2025-2027")
                                echo "selected";
                            ?>
                        >
                            2025-2027
                        </option>

                        <option
                            value="2026-2028"
                            <?php
                            if ($selected_period == "2026-2028")
                                echo "selected";
                            ?>
                        >
                            2026-2028
                        </option>

                    </select>

                </div>


                <!-- STATUS -->

                <div class="control-group">

                    <label>
                        Attendance Status
                    </label>

                    <select
                        name="status"
                        onchange="this.form.submit()"
                    >

                        <option
                            value="attended"
                            <?php
                            if ($status == "attended")
                                echo "selected";
                            ?>
                        >
                            Attended
                        </option>

                        <option
                            value="not_attended"
                            <?php
                            if ($status == "not_attended")
                                echo "selected";
                            ?>
                        >
                            Not Attended
                        </option>

                    </select>

                </div>

            </div>


            <p style="margin-top:15px;">

                Report period:

                <strong>
                    <?php echo $start_date; ?>
                    →
                    <?php echo $end_date; ?>
                </strong>

            </p>

        </form>

    </div>


    <div class="report-box">

        <?php if ($status == "attended"): ?>

            <h3 class="report-heading">
                Employees Who Attended
            </h3>

        <?php else: ?>

            <h3 class="report-heading">
                Employees Who Did Not Attend
            </h3>

        <?php endif; ?>


        <table>

            <thead>

                <tr>

                    <th>
                        ICNO
                    </th>

                    <th>
                        Employee Name
                    </th>

                    <th>
                        Status
                    </th>

                </tr>

            </thead>


            <tbody>

            <?php if (mysqli_num_rows($result) > 0): ?>

                <?php while ($employee = mysqli_fetch_assoc($result)): ?>

                    <tr>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $employee["ICNO"]
                            );
                            ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $employee["ENAME"]
                            );
                            ?>
                        </td>

                        <td>

                            <?php if (
                                $employee["STATUS"]
                                == "Attended"
                            ): ?>

                                <span class="status-attended">
                                    Attended
                                </span>

                            <?php else: ?>

                                <span class="status-not-attended">
                                    Not Attended
                                </span>

                            <?php endif; ?>

                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>

                    <td
                        colspan="3"
                        style="text-align:center;"
                    >

                        No employees found.

                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

</body>

</html>