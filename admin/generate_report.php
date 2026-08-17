<?php

session_start();

if (!isset($_SESSION['ic_number'])) {

    header("Location: ../login.php");
    exit();

}

if ($_SESSION['role'] != "Admin") {

    header("Location: ../login.php");
    exit();

}

$db_connection = mysqli_connect(
    "localhost",
    "root",
    "",
    "karyashala"
);


if (!$db_connection) {

    die("Database connection failed");

}


$from_year = $_GET['from_year'];

$to_year = $_GET['to_year'];


$query = "

SELECT

    employees.ic_number,

    employees.name,

    employees.designation,

    workshops.workshop_name,

    workshops.workshop_year,

    workshops.attendance_date,

    workshops.attendance_status,

    workshops.remarks

FROM employees

LEFT JOIN workshops

ON employees.ic_number =
   workshops.employee_ic

WHERE workshops.workshop_year
BETWEEN '$from_year'
AND '$to_year'

ORDER BY

    employees.ic_number,

    workshops.workshop_year,

    workshops.attendance_date

";


$result = mysqli_query(
    $db_connection,
    $query
);

?>

<!DOCTYPE html>

<html>

<head>

    <meta charset="UTF-8">

    <title>
        Employee Workshop Report
    </title>

    <link
        rel="stylesheet"
        href="../css/admin.css"
    >

</head>

<body>

<div class="main-content">

    <div class="content-section">

        <h1>
            Employee Workshop Report
        </h1>

        <p>

            Period:
            <strong>
                <?php echo $from_year; ?>
            </strong>

            to

            <strong>
                <?php echo $to_year; ?>
            </strong>

        </p>


        <br>


        <table>

            <thead>

                <tr>

                    <th>
                        IC Number
                    </th>

                    <th>
                        Name
                    </th>

                    <th>
                        Designation
                    </th>

                    <th>
                        Workshop / Event
                    </th>

                    <th>
                        Year
                    </th>

                    <th>
                        Date
                    </th>

                    <th>
                        Attendance
                    </th>

                    <th>
                        Remarks
                    </th>

                </tr>

            </thead>


            <tbody>

            <?php

            while (
                $row =
                mysqli_fetch_assoc($result)
            ) {

            ?>

                <tr>

                    <td>
                        <?php
                        echo $row['ic_number'];
                        ?>
                    </td>

                    <td>
                        <?php
                        echo $row['name'];
                        ?>
                    </td>

                    <td>
                        <?php
                        echo $row['designation'];
                        ?>
                    </td>

                    <td>
                        <?php
                        echo $row['workshop_name'];
                        ?>
                    </td>

                    <td>
                        <?php
                        echo $row['workshop_year'];
                        ?>
                    </td>

                    <td>
                        <?php
                        echo $row['attendance_date'];
                        ?>
                    </td>

                    <td>
                        <?php
                        echo $row['attendance_status'];
                        ?>
                    </td>

                    <td>
                        <?php
                        echo $row['remarks'];
                        ?>
                    </td>

                </tr>

            <?php

            }

            ?>

            </tbody>

        </table>

    </div>

</div>

</body>

</html>

<?php

mysqli_close($db_connection);

?>