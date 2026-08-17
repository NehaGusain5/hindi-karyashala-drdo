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


$message = "";

if (isset($_POST['add_employee'])) {

    $ic_number = $_POST['ic_number'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $designation = $_POST['designation'];
    $email = $_POST['email'];

    $password = $_POST['password'];
    $role = $_POST['role'];

    $query = "
        INSERT INTO employees
        (
            ic_number,
            name,
            phone,
            designation,
            email
        )
        VALUES
        (
            '$ic_number',
            '$name',
            '$phone',
            '$designation',
            '$email'
        )
    ";


    if (mysqli_query($db_connection, $query)) {

        $role_query = "
            INSERT INTO roles
            (
                ic_number,
                password,
                role
            )
            VALUES
            (
                '$ic_number',
                '$password',
                '$role'
            )
        ";


        if (mysqli_query($db_connection, $role_query)) {

            $message =
                "Employee added successfully.";

        } else {

            $message =
                "Employee added but login information could not be created.";

        }


    } else {

        $message =
            "Error adding employee: "
            . mysqli_error($db_connection);

    }

}

if (isset($_POST['update_employee'])) {

    $ic_number = $_POST['ic_number'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $designation = $_POST['designation'];
    $email = $_POST['email'];


    $query = "
        UPDATE employees

        SET
            name='$name',
            phone='$phone',
            designation='$designation',
            email='$email'

        WHERE ic_number='$ic_number'
    ";


    if (mysqli_query($db_connection, $query)) {

        $message =
            "Employee information updated successfully.";

    } else {

        $message =
            "Error updating employee.";

    }

}


if (isset($_POST['update_workshop'])) {

    $workshop_id =
        $_POST['workshop_id'];

    $attendance_date =
        $_POST['attendance_date'];

    $attendance_status =
        $_POST['attendance_status'];

    $remarks =
        $_POST['remarks'];

    $updated_by =
        $_SESSION['ic_number'];


    $query = "
        UPDATE workshops

        SET
            attendance_date='$attendance_date',
            attendance_status='$attendance_status',
            remarks='$remarks',
            updated_by='$updated_by',
            updated_at=CURRENT_TIMESTAMP

        WHERE workshop_id='$workshop_id'
    ";


    if (mysqli_query($db_connection, $query)) {

        $message =
            "Workshop attendance updated successfully.";

    } else {

        $message =
            "Error updating workshop.";

    }

}

$employees = mysqli_query(

    $db_connection,

    "SELECT
        employees.*,
        roles.role

     FROM employees

     LEFT JOIN roles

     ON employees.ic_number =
        roles.ic_number

     ORDER BY
        employees.ic_number ASC"

);

$result = mysqli_query(

    $db_connection,

    "SELECT COUNT(*) AS total
     FROM employees"

);

$row = mysqli_fetch_assoc($result);

$total_employees = $row['total'];

$result = mysqli_query(

    $db_connection,

    "SELECT COUNT(*) AS total
     FROM workshops"

);

$row = mysqli_fetch_assoc($result);

$total_workshops = $row['total'];

$result = mysqli_query(

    $db_connection,

    "SELECT COUNT(*) AS total
     FROM workshops

     WHERE attendance_status='Attended'"

);

$row = mysqli_fetch_assoc($result);

$total_attended = $row['total'];

$result = mysqli_query(

    $db_connection,

    "SELECT COUNT(*) AS total
     FROM workshops

     WHERE attendance_status='Pending'"

);

$row = mysqli_fetch_assoc($result);

$total_pending = $row['total'];

$workshop_year_filter = "";

$selected_workshop_year = "";


if (
    isset($_GET['workshop_year']) &&
    $_GET['workshop_year'] != ""
) {

    $selected_workshop_year =
        $_GET['workshop_year'];

    $workshop_year_filter =

        "WHERE workshops.workshop_year =
        '$selected_workshop_year'";

}

$workshops = mysqli_query(

    $db_connection,

    "SELECT
        workshops.*,
        employees.name,
        employees.designation

     FROM workshops

     INNER JOIN employees

     ON workshops.employee_ic =
        employees.ic_number

     $workshop_year_filter

     ORDER BY
        employees.ic_number ASC,
        workshops.workshop_year ASC,
        workshops.attendance_date ASC"

);

$show_report = false;

$from_year = "";

$to_year = "";

$report_rows = [];

$satisfied = 0;

$not_satisfied = 0;

$report_error = "";

if (isset($_GET['show_report'])) {

    $from_year =
        $_GET['from_year'];

    $to_year =
        $_GET['to_year'];


    if (
        $from_year == "" ||
        $to_year == ""
    ) {

        $report_error =
            "Please select both years.";

    }

    elseif (
        ($to_year - $from_year) != 1
    ) {

        $report_error =
            "Please select exactly two consecutive years.";

    }

    else {

        $show_report = true;

        $report_query = "

            SELECT

                employees.ic_number,

                employees.name,

                employees.designation,

                COUNT(

                    CASE

                        WHEN workshops.attendance_status =
                        'Attended'

                        THEN 1

                    END

                ) AS attended_count,


                COUNT(
                    workshops.workshop_id
                ) AS total_records


            FROM employees


            LEFT JOIN workshops

            ON employees.ic_number =
               workshops.employee_ic


            AND workshops.workshop_year
                BETWEEN '$from_year'
                AND '$to_year'


            GROUP BY

                employees.ic_number,

                employees.name,

                employees.designation


            ORDER BY
                employees.ic_number ASC

        ";


        $report_result = mysqli_query(

            $db_connection,

            $report_query

        );


        while (
            $row =
            mysqli_fetch_assoc($report_result)
        ) {

            $report_rows[] = $row;


            if (
                $row['attended_count'] >= 1
            ) {

                $satisfied++;

            } else {

                $not_satisfied++;

            }

        }

    }

}

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
    Admin Dashboard | Hindi Karyashala
</title>


<link
    rel="stylesheet"
    href="../css/admin.css"
>

</head>


<body>

<div class="sidebar">


    <div class="sidebar-logo">

        <img
            src="../images/drdo-logo.jpg"
            alt="DRDO"
        >

        <h2>
            Hindi Karyashala
        </h2>

        <p>
            Admin Portal
        </p>

    </div>


    <div class="menu">

        <a href="#dashboard">
            Dashboard
        </a>

        <a href="#employees">
            Employees
        </a>

        <a href="#workshops">
            Workshops
        </a>

        <a href="#reports">
            Reports
        </a>

    </div>


    <div class="sidebar-bottom">

        <a
            href="../logout.php"
            class="logout"
        >
            Logout
        </a>

    </div>


</div>

<div class="main-content">

    <div class="topbar">

        <div>

            <h1>
                Admin Dashboard
            </h1>

            <p>

                Welcome,
                <?php

                echo isset($_SESSION['name'])
                    ? $_SESSION['name']
                    : 'Admin';

                ?>

            </p>

        </div>

    </div>

    <?php

    if ($message != "") {

        echo "

        <div class='success-message'>

            $message

        </div>

        ";

    }

    ?>

    <section
        id="dashboard"
        class="dashboard-section"
    >


        <div class="stat-card">

            <h3>
                Total Employees
            </h3>

            <strong>
                <?php
                echo $total_employees;
                ?>
            </strong>

        </div>


        <div class="stat-card">

            <h3>
                Total Workshops
            </h3>

            <strong>
                <?php
                echo $total_workshops;
                ?>
            </strong>

        </div>


        <div class="stat-card">

            <h3>
                Attended
            </h3>

            <strong>
                <?php
                echo $total_attended;
                ?>
            </strong>

        </div>


        <div class="stat-card">

            <h3>
                Pending
            </h3>

            <strong>
                <?php
                echo $total_pending;
                ?>
            </strong>

        </div>


    </section>

    <section
        id="employees"
        class="content-section"
    >


        <div class="section-header">


            <div>

                <h2>
                    Employee Management
                </h2>

                <p>
                    View and manage all employees
                </p>

            </div>


            <button
                class="primary-button"
                onclick="openAddModal()"
            >

                + Add Employee

            </button>


        </div>



        <div class="table-container">


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
                        Phone
                    </th>

                    <th>
                        Designation
                    </th>

                    <th>
                        Email
                    </th>

                    <th>
                        Role
                    </th>

                    <th>
                        Action
                    </th>

                </tr>

                </thead>


                <tbody>


                <?php

                while (
                    $employee =
                    mysqli_fetch_assoc($employees)
                ) {

                ?>


                <tr>


                    <td>
                        <?php
                        echo $employee['ic_number'];
                        ?>
                    </td>


                    <td>
                        <?php
                        echo htmlspecialchars(
                            $employee['name']
                        );
                        ?>
                    </td>


                    <td>
                        <?php
                        echo $employee['phone'];
                        ?>
                    </td>


                    <td>
                        <?php
                        echo htmlspecialchars(
                            $employee['designation']
                        );
                        ?>
                    </td>


                    <td>
                        <?php
                        echo htmlspecialchars(
                            $employee['email']
                        );
                        ?>
                    </td>


                    <td>

                        <span class="role-badge">

                            <?php

                            echo $employee['role']
                                ? $employee['role']
                                : 'Employee';

                            ?>

                        </span>

                    </td>


                    <td>

                        <button
                            class="edit-button"

                            onclick="openEditEmployeeModal(
                                '<?php
                                echo $employee['ic_number'];
                                ?>',

                                '<?php
                                echo htmlspecialchars(
                                    $employee['name'],
                                    ENT_QUOTES
                                );
                                ?>',

                                '<?php
                                echo htmlspecialchars(
                                    $employee['phone'],
                                    ENT_QUOTES
                                );
                                ?>',

                                '<?php
                                echo htmlspecialchars(
                                    $employee['designation'],
                                    ENT_QUOTES
                                );
                                ?>',

                                '<?php
                                echo htmlspecialchars(
                                    $employee['email'],
                                    ENT_QUOTES
                                );
                                ?>'
                            )"
                        >

                            Edit

                        </button>

                    </td>


                </tr>


                <?php

                }

                ?>


                </tbody>


            </table>


        </div>


    </section>

    <section
        id="workshops"
        class="content-section"
    >

        <div class="section-header">


            <div>

                <h2>
                    Workshop Management
                </h2>

                <p>
                    Manage workshop attendance
                </p>

            </div>


            <!-- YEAR FILTER -->

            <form
                method="GET"
                action="#workshops"
            >

                <select
                    name="workshop_year"
                    class="year-select"
                    onchange="this.form.submit()"
                >


                    <option value="">
                        All Years
                    </option>


                    <?php

                    for (
                        $year = 2020;
                        $year <= date("Y");
                        $year++
                    ) {

                        $selected = "";

                        if (
                            $selected_workshop_year ==
                            $year
                        ) {

                            $selected =
                                "selected";

                        }


                        echo "

                        <option
                            value='$year'
                            $selected
                        >

                            $year

                        </option>

                        ";

                    }

                    ?>


                </select>


            </form>


        </div>



        <div class="table-container">


            <table>


                <thead>

                <tr>

                    <th>
                        IC Number
                    </th>

                    <th>
                        Employee
                    </th>

                    <th>
                        Designation
                    </th>

                    <th>
                        Workshop
                    </th>

                    <th>
                        Year
                    </th>

                    <th>
                        Date
                    </th>

                    <th>
                        Status
                    </th>

                    <th>
                        Action
                    </th>

                </tr>

                </thead>


                <tbody>


                <?php

                if (
                    mysqli_num_rows($workshops) > 0
                ) {


                    while (
                        $workshop =
                        mysqli_fetch_assoc($workshops)
                    ) {

                ?>


                <tr>


                    <td>

                        <?php
                        echo $workshop['employee_ic'];
                        ?>

                    </td>


                    <td>

                        <?php

                        echo htmlspecialchars(
                            $workshop['name']
                        );

                        ?>

                    </td>


                    <td>

                        <?php

                        echo htmlspecialchars(
                            $workshop['designation']
                        );

                        ?>

                    </td>


                    <td>

                        <?php

                        echo htmlspecialchars(
                            $workshop['workshop_name']
                        );

                        ?>

                    </td>


                    <td>

                        <?php

                        echo $workshop[
                            'workshop_year'
                        ];

                        ?>

                    </td>


                    <td>

                        <?php

                        echo $workshop[
                            'attendance_date'
                        ];

                        ?>

                    </td>


                    <td>


                        <span
                            class="status
                            <?php

                            echo strtolower(
                                $workshop[
                                    'attendance_status'
                                ]
                            );

                            ?>"
                        >

                            <?php

                            echo $workshop[
                                'attendance_status'
                            ];

                            ?>

                        </span>


                    </td>


                    <td>


                        <button
                            class="edit-button"

                            onclick="openWorkshopModal(
                                '<?php
                                echo $workshop[
                                    'workshop_id'
                                ];
                                ?>',

                                '<?php
                                echo htmlspecialchars(
                                    $workshop['name'],
                                    ENT_QUOTES
                                );
                                ?>',

                                '<?php
                                echo htmlspecialchars(
                                    $workshop[
                                        'workshop_name'
                                    ],
                                    ENT_QUOTES
                                );
                                ?>',

                                '<?php
                                echo $workshop[
                                    'attendance_date'
                                ];
                                ?>',

                                '<?php
                                echo $workshop[
                                    'attendance_status'
                                ];
                                ?>',

                                '<?php
                                echo htmlspecialchars(
                                    $workshop['remarks'],
                                    ENT_QUOTES
                                );
                                ?>',

                                '<?php
                                echo $selected_workshop_year;
                                ?>'
                            )"
                        >

                            Edit

                        </button>


                    </td>


                </tr>


                <?php

                    }

                } else {

                    echo "

                    <tr>

                        <td
                            colspan='8'
                            class='no-data'
                        >

                            No workshop records found.

                        </td>

                    </tr>

                    ";

                }

                ?>


                </tbody>


            </table>


        </div>


    </section>

    <section
        id="reports"
        class="content-section"
    >


        <div class="section-header">


            <div>

                <h2>
                    Two-Year Compliance Report
                </h2>

                <p>

                    Every employee must attend
                    at least one workshop in
                    a two-year period.

                </p>

            </div>


        </div>

        <form
            method="GET"
            action="#reports"
            class="report-form"
        >


            <div>

                <label>
                    From Year
                </label>


                <select
                    name="from_year"
                    required
                >

                    <option value="">
                        Select year
                    </option>


                    <?php

                    for (
                        $year = 2020;
                        $year <= date("Y");
                        $year++
                    ) {

                        $selected = "";

                        if (
                            $from_year == $year
                        ) {

                            $selected =
                                "selected";

                        }


                        echo "

                        <option
                            value='$year'
                            $selected
                        >

                            $year

                        </option>

                        ";

                    }

                    ?>

                </select>

            </div>



            <div>

                <label>
                    To Year
                </label>


                <select
                    name="to_year"
                    required
                >

                    <option value="">
                        Select year
                    </option>


                    <?php

                    for (
                        $year = 2020;
                        $year <= date("Y");
                        $year++
                    ) {

                        $selected = "";

                        if (
                            $to_year == $year
                        ) {

                            $selected =
                                "selected";

                        }


                        echo "

                        <option
                            value='$year'
                            $selected
                        >

                            $year

                        </option>

                        ";

                    }

                    ?>

                </select>

            </div>

            <button
                type="submit"
                class="primary-button"
                name="show_report"
            >

                Generate Report

            </button>

        </form>

        <div class="report-note">

            Select exactly two consecutive years.

            Example:
            <strong>
                2024 - 2025
            </strong>

        </div>

        <?php

        if ($report_error != "") {

            echo "

            <div class='error-message'>

                $report_error

            </div>

            ";

        }


        if ($show_report) {

        ?>

        <div class="report-header">


            <div>

                <h3>

                    Report:

                    <?php
                    echo $from_year;
                    ?>

                    -

                    <?php
                    echo $to_year;
                    ?>

                </h3>


                <p>

                    Minimum requirement:
                    1 attended workshop

                </p>

            </div>


        </div>

        <div class="report-summary">


            <div>

                <strong>
                    <?php
                    echo count($report_rows);
                    ?>
                </strong>

                <span>
                    Total Employees
                </span>

            </div>

            <div class="satisfied-box">

                <strong>
                    <?php
                    echo $satisfied;
                    ?>
                </strong>

                <span>
                    Requirement Satisfied
                </span>

            </div>

            <div class="not-satisfied-box">

                <strong>
                    <?php
                    echo $not_satisfied;
                    ?>
                </strong>

                <span>
                    Requirement Not Satisfied
                </span>

            </div>

        </div>

        <div class="table-container">

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
                        Workshops Attended
                    </th>

                    <th>
                        Total Records
                    </th>

                    <th>
                        Requirement
                    </th>

                </tr>

                </thead>

                <tbody>

                <?php

                foreach (
                    $report_rows
                    as $row
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

                        echo htmlspecialchars(
                            $row['name']
                        );

                        ?>

                    </td>

                    <td>

                        <?php

                        echo htmlspecialchars(
                            $row['designation']
                        );

                        ?>

                    </td>

                    <td>

                        <?php
                        echo $row['attended_count'];
                        ?>

                    </td>

                    <td>

                        <?php
                        echo $row['total_records'];
                        ?>

                    </td>

                    <td>

                        <?php

                        if (
                            $row['attended_count']
                            >= 1
                        ) {

                            echo "

                            <span
                                class='report-satisfied'
                            >

                                Satisfied

                            </span>

                            ";

                        } else {

                            echo "

                            <span
                                class='report-not-satisfied'
                            >

                                Not Satisfied

                            </span>

                            ";

                        }

                        ?>

                    </td>

                </tr>

                <?php

                }

                ?>

                </tbody>

            </table>

        </div>

        <h3 class="detail-heading">

            Detailed Workshop Records

        </h3>

        <?php

        foreach (
            $report_rows
            as $employee
        ) {

            $employee_ic =
                $employee['ic_number'];

            $details = mysqli_query(

                $db_connection,

                "SELECT *

                 FROM workshops

                 WHERE employee_ic =
                       '$employee_ic'

                 AND workshop_year
                     BETWEEN '$from_year'
                     AND '$to_year'

                 ORDER BY
                     workshop_year ASC,
                     attendance_date ASC"

            );

        ?>

        <div class="employee-report-card">

            <div class="employee-report-header">

                <div>

                    <strong>

                        <?php

                        echo htmlspecialchars(
                            $employee['name']
                        );

                        ?>

                    </strong>

                    <span>

                        IC:

                        <?php

                        echo $employee[
                            'ic_number'
                        ];

                        ?>

                    </span>

                    <span>

                        <?php

                        echo htmlspecialchars(
                            $employee[
                                'designation'
                            ]
                        );

                        ?>

                    </span>

                </div>

                <div>

                    <?php

                    if (
                        $employee[
                            'attended_count'
                        ] >= 1
                    ) {

                        echo "

                        <span
                            class='report-satisfied'
                        >

                            Satisfied

                        </span>

                        ";

                    } else {

                        echo "

                        <span
                            class='report-not-satisfied'
                        >

                            Not Satisfied

                        </span>

                        ";

                    }

                    ?>


                </div>

            </div>

            <table>

                <thead>

                <tr>

                    <th>
                        Workshop
                    </th>

                    <th>
                        Year
                    </th>

                    <th>
                        Date
                    </th>

                    <th>
                        Status
                    </th>

                    <th>
                        Remarks
                    </th>

                    <th>
                        Action
                    </th>

                </tr>

                </thead>

                <tbody>

                <?php

                if (
                    mysqli_num_rows($details)
                    > 0
                ) {

                    while (
                        $detail =
                        mysqli_fetch_assoc(
                            $details
                        )
                    ) {

                ?>

                <tr>

                    <td>

                        <?php

                        echo htmlspecialchars(
                            $detail[
                                'workshop_name'
                            ]
                        );

                        ?>

                    </td>

                    <td>

                        <?php

                        echo $detail[
                            'workshop_year'
                        ];

                        ?>

                    </td>

                    <td>

                        <?php

                        echo $detail[
                            'attendance_date'
                        ];

                        ?>

                    </td>

                    <td>

                        <span
                            class="status
                            <?php

                            echo strtolower(
                                $detail[
                                    'attendance_status'
                                ]
                            );

                            ?>"
                        >

                            <?php

                            echo $detail[
                                'attendance_status'
                            ];

                            ?>

                        </span>

                    </td>

                    <td>

                        <?php

                        echo htmlspecialchars(
                            $detail['remarks']
                        );

                        ?>

                    </td>

                    <td>

                        <button
                            class="edit-button"

                            onclick="openWorkshopModal(
                                '<?php
                                echo $detail[
                                    'workshop_id'
                                ];
                                ?>',

                                '<?php
                                echo htmlspecialchars(
                                    $employee['name'],
                                    ENT_QUOTES
                                );
                                ?>',

                                '<?php
                                echo htmlspecialchars(
                                    $detail[
                                        'workshop_name'
                                    ],
                                    ENT_QUOTES
                                );
                                ?>',

                                '<?php
                                echo $detail[
                                    'attendance_date'
                                ];
                                ?>',

                                '<?php
                                echo $detail[
                                    'attendance_status'
                                ];
                                ?>',

                                '<?php
                                echo htmlspecialchars(
                                    $detail[
                                        'remarks'
                                    ],
                                    ENT_QUOTES
                                );
                                ?>',

                                '<?php
                                echo $from_year;
                                ?>',

                                '<?php
                                echo $to_year;
                                ?>'
                            )"
                        >

                            Edit

                        </button>

                    </td>

                </tr>

                <?php

                    }

                } else {

                    echo "

                    <tr>

                        <td
                            colspan='6'
                            class='no-data'
                        >

                            No workshop records
                            found during this
                            two-year period.

                        </td>

                    </tr>

                    ";

                }

                ?>

                </tbody>

            </table>

        </div>

        <?php

        }

        ?>

        <?php

        }

        ?>

    </section>

</div>

<div
    id="addEmployeeModal"
    class="modal"
>

    <div class="modal-box">

        <span
            class="close"
            onclick="closeAddModal()"
        >

            &times;

        </span>

        <h2>
            Add Employee
        </h2>

        <form method="POST">

            <label>
                IC Number
            </label>

            <input
                type="number"
                name="ic_number"
                required
            >

            <label>
                Name
            </label>

            <input
                type="text"
                name="name"
                required
            >

            <label>
                Phone
            </label>

            <input
                type="text"
                name="phone"
                required
            >

            <label>
                Designation
            </label>


            <select
                name="designation"
                required
            >

                <option value="">
                    Select Designation
                </option>

                <option>
                    Scientist-B
                </option>

                <option>
                    Scientist-C
                </option>

                <option>
                    Scientist-D
                </option>

                <option>
                    Scientist-E
                </option>

                <option>
                    Scientist-F
                </option>

                <option>
                    TO-A
                </option>

                <option>
                    TO-B
                </option>

                <option>
                    TO-C
                </option>

                <option>
                    TO-D
                </option>

                <option>
                    Director
                </option>

                <option>
                    HRD
                </option>

                <option>
                    Other
                </option>

            </select>

            <label>
                Email
            </label>

            <input
                type="email"
                name="email"
                required
            >

            <label>
                Login Role
            </label>

            <select
                name="role"
                required
            >

                <option value="Employee">
                    Employee
                </option>

                <option value="Karyashala Admin">
                    Karyashala Admin
                </option>

            </select>

            <label>
                Login Password
            </label>

            <input
                type="text"
                name="password"
                required
            >

            <button
                type="submit"
                name="add_employee"
                class="primary-button"
            >

                Add Employee

            </button>

        </form>

    </div>

</div>

<div
    id="editEmployeeModal"
    class="modal"
>

    <div class="modal-box">

        <span
            class="close"
            onclick="closeEditEmployeeModal()"
        >

            &times;

        </span>

        <h2>
            Update Employee
        </h2>

        <form method="POST">

            <label>
                IC Number
            </label>

            <input
                type="text"
                id="edit_ic"
                name="ic_number"
                readonly
            >

            <label>
                Name
            </label>

            <input
                type="text"
                id="edit_name"
                name="name"
                required
            >

            <label>
                Phone
            </label>

            <input
                type="text"
                id="edit_phone"
                name="phone"
                required
            >

            <label>
                Designation
            </label>

            <select
                id="edit_designation"
                name="designation"
                required
            >

                <option>
                    Scientist-B
                </option>

                <option>
                    Scientist-C
                </option>

                <option>
                    Scientist-D
                </option>

                <option>
                    Scientist-E
                </option>

                <option>
                    Scientist-F
                </option>

                <option>
                    TO-A
                </option>

                <option>
                    TO-B
                </option>

                <option>
                    TO-C
                </option>

                <option>
                    TO-D
                </option>

                <option>
                    Director
                </option>

                <option>
                    HRD
                </option>

                <option>
                    Other
                </option>

            </select>

            <label>
                Email
            </label>

            <input
                type="email"
                id="edit_email"
                name="email"
                required
            >

            <button
                type="submit"
                name="update_employee"
                class="primary-button"
            >

                Save Changes

            </button>

        </form>

    </div>

</div>

<div
    id="workshopModal"
    class="modal"
>

    <div class="modal-box">


        <span
            class="close"
            onclick="closeWorkshopModal()"
        >

            &times;

        </span>

        <h2>
            Update Workshop Attendance
        </h2>

        <p
            id="workshop_employee"
            class="modal-info"
        ></p>

        <form method="POST">

            <input
                type="hidden"
                id="workshop_id"
                name="workshop_id"
            >

            <input
                type="hidden"
                id="report_from_year"
                name="report_from_year"
            >

            <input
                type="hidden"
                id="report_to_year"
                name="report_to_year"
            >

            <label>
                Workshop
            </label>

            <input
                type="text"
                id="workshop_name"
                readonly
            >

            <label>
                Attendance Date
            </label>

            <input
                type="date"
                id="attendance_date"
                name="attendance_date"
                required
            >

            <label>
                Attendance Status
            </label>

            <select
                id="attendance_status"
                name="attendance_status"
                required
            >

                <option value="Pending">
                    Pending
                </option>

                <option value="Attended">
                    Attended
                </option>

                <option value="Absent">
                    Absent
                </option>

            </select>

            <label>
                Remarks
            </label>

            <textarea
                id="remarks"
                name="remarks"
                rows="4"
            ></textarea>

            <button
                type="submit"
                name="update_workshop"
                class="primary-button"
            >

                Save Attendance

            </button>

        </form>

    </div>

</div>

<script>

function openAddModal() {

    document.getElementById(
        "addEmployeeModal"
    ).style.display = "flex";

}

function closeAddModal() {

    document.getElementById(
        "addEmployeeModal"
    ).style.display = "none";

}

function openEditEmployeeModal(
    ic,
    name,
    phone,
    designation,
    email
) {

    document.getElementById(
        "edit_ic"
    ).value = ic;


    document.getElementById(
        "edit_name"
    ).value = name;


    document.getElementById(
        "edit_phone"
    ).value = phone;


    document.getElementById(
        "edit_designation"
    ).value = designation;


    document.getElementById(
        "edit_email"
    ).value = email;


    document.getElementById(
        "editEmployeeModal"
    ).style.display = "flex";

}

function closeEditEmployeeModal() {

    document.getElementById(
        "editEmployeeModal"
    ).style.display = "none";

}

function openWorkshopModal(
    id,
    employee,
    workshop,
    date,
    status,
    remarks,
    fromYear,
    toYear
) {


    document.getElementById(
        "workshop_id"
    ).value = id;


    document.getElementById(
        "workshop_employee"
    ).innerHTML =

        "Employee: <strong>" +
        employee +
        "</strong>";


    document.getElementById(
        "workshop_name"
    ).value = workshop;


    document.getElementById(
        "attendance_date"
    ).value = date;


    document.getElementById(
        "attendance_status"
    ).value = status;


    document.getElementById(
        "remarks"
    ).value = remarks;


    document.getElementById(
        "report_from_year"
    ).value = fromYear || "";


    document.getElementById(
        "report_to_year"
    ).value = toYear || "";


    document.getElementById(
        "workshopModal"
    ).style.display = "flex";

}

function closeWorkshopModal() {

    document.getElementById(
        "workshopModal"
    ).style.display = "none";

}

window.onclick = function(event) {


    if (
        event.target.classList.contains(
            "modal"
        )
    ) {

        event.target.style.display =
            "none";

    }

};

</script>

</body>

</html>

<?php

mysqli_close(
    $db_connection
);

?>