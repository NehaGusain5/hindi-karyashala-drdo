<?php

session_start();

require_once "../config/db.php";


/*
|--------------------------------------------------------------------------
| KARYASHALA ADMIN ACCESS CONTROL
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["ICNO"]) || !isset($_SESSION["ROLE"])) {

    header("Location: ../login.php");
    exit();

}

if ($_SESSION["ROLE"] !== "karyashala_admin") {

    header("Location: ../login.php");
    exit();

}


$message = "";
$message_type = "";


/*
|--------------------------------------------------------------------------
| ADD KARYASHALA ADMIN
|--------------------------------------------------------------------------
*/

if (isset($_POST["add_role"])) {

    $icno = intval($_POST["icno"]);


    /*
     * Find employee from employees table
     */

    $sql = "
        SELECT ICNO, ENAME
        FROM employees
        WHERE ICNO = ?
    ";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $icno
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);


    if (mysqli_num_rows($result) == 0) {

        $message = "Employee not found.";
        $message_type = "error";

    }

    else {

        $employee = mysqli_fetch_assoc($result);

        $ename = $employee["ENAME"];


        /*
         * Check whether employee already
         * has an administrative role
         */

        $check_sql = "
            SELECT ICNO
            FROM roles
            WHERE ICNO = ?
        ";

        $check_stmt = mysqli_prepare(
            $conn,
            $check_sql
        );

        mysqli_stmt_bind_param(
            $check_stmt,
            "i",
            $icno
        );

        mysqli_stmt_execute($check_stmt);

        $check_result =
            mysqli_stmt_get_result(
                $check_stmt
            );


        if (
            mysqli_num_rows($check_result) > 0
        ) {

            $message =
                "This employee already has an administrative role.";

            $message_type = "error";

        }

        else {

            /*
             * Only Karyashala Admin can be added
             */

            $insert_sql = "
                INSERT INTO roles
                (
                    ICNO,
                    ENAME,
                    ROLE
                )
                VALUES
                (
                    ?,
                    ?,
                    'karyashala_admin'
                )
            ";

            $insert_stmt = mysqli_prepare(
                $conn,
                $insert_sql
            );

            mysqli_stmt_bind_param(
                $insert_stmt,
                "is",
                $icno,
                $ename
            );


            if (
                mysqli_stmt_execute(
                    $insert_stmt
                )
            ) {

                $message =
                    "Karyashala Admin added successfully.";

                $message_type = "success";

            }

            else {

                $message =
                    "Unable to add Karyashala Admin.";

                $message_type = "error";

            }

        }

    }

}


/*
|--------------------------------------------------------------------------
| DELETE KARYASHALA ADMIN
|--------------------------------------------------------------------------
*/

if (isset($_POST["delete_role"])) {

    $icno = intval($_POST["delete_icno"]);


    /*
     * Prevent deleting own role
     */

    if ($icno == $_SESSION["ICNO"]) {

        $message =
            "You cannot delete your own role.";

        $message_type = "error";

    }

    else {

        /*
         * IMPORTANT:
         *
         * Only karyashala_admin can be deleted.
         *
         * Admin records cannot be deleted here.
         */

        $delete_sql = "
            DELETE FROM roles
            WHERE ICNO = ?
            AND ROLE = 'karyashala_admin'
        ";

        $stmt = mysqli_prepare(
            $conn,
            $delete_sql
        );

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $icno
        );

        mysqli_stmt_execute($stmt);


        if (
            mysqli_stmt_affected_rows($stmt) > 0
        ) {

            $message =
                "Karyashala Admin role deleted successfully.";

            $message_type = "success";

        }

        else {

            $message =
                "Admin roles cannot be deleted by Karyashala Admin.";

            $message_type = "error";

        }

    }

}


/*
|--------------------------------------------------------------------------
| GET ALL ADMINISTRATIVE USERS
|--------------------------------------------------------------------------
|
| We get:
|
| roles.ICNO
| roles.ENAME
| roles.ROLE
| employees.EDESIG
|
| using ICNO.
|
*/

$sql = "

    SELECT

        r.ICNO,
        r.ENAME,
        r.ROLE,
        e.EDESIG

    FROM roles r

    INNER JOIN employees e
        ON r.ICNO = e.ICNO

    ORDER BY r.ICNO ASC

";

$result = mysqli_query(
    $conn,
    $sql
);


/*
|--------------------------------------------------------------------------
| GET EMPLOYEES WITHOUT ADMINISTRATIVE ROLE
|--------------------------------------------------------------------------
|
| These employees can be added as
| Karyashala Admin.
|
*/

$available_sql = "

    SELECT

        e.ICNO,
        e.ENAME

    FROM employees e

    LEFT JOIN roles r
        ON e.ICNO = r.ICNO

    WHERE r.ICNO IS NULL

    ORDER BY e.ICNO ASC

";

$available_result = mysqli_query(
    $conn,
    $available_sql
);

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
        Karyashala Admin Dashboard
    </title>


    <!-- Admin CSS -->

    <link
        rel="stylesheet"
        href="../css/admin.css"
    >


    <!-- Role Management CSS -->

    <link
        rel="stylesheet"
        href="../css/role_management.css"
    >

</head>


<body>


<!-- =========================================================
     HEADER
========================================================= -->

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



<!-- =========================================================
     MAIN CONTENT
========================================================= -->

<div class="page-container">


    <!-- =====================================================
         PAGE HEADER
    ===================================================== -->

    <div class="page-header">


        <div>

            <h2>
                Karyashala Admin Dashboard
            </h2>

            <p>

                Welcome,

                <?php

                echo htmlspecialchars(
                    $_SESSION["ICNO"]
                );

                ?>

            </p>

        </div>


    <div class="dashboard-actions">

    <!-- Add Karyashala Admin -->

    <button
        class="add-button"
        onclick="openAddModal()"
    >
        + Add Karyashala Admin
    </button>


    <!-- Karyashala Management -->

    <a
        href="karyashala/karyashala_report.php"
        class="add-button"
    >
        Karyashala Management
    </a>


    <!-- Generate Report -->

    <a
        href="karyashala/generate_report.php"
        class="add-button"
    >
        Generate Report
    </a>

</div>

</div>


    </div>



    <!-- =====================================================
         MESSAGE
    ===================================================== -->

    <?php if ($message != ""): ?>

        <div
            class="message <?php echo $message_type; ?>"
        >

            <?php

            echo htmlspecialchars(
                $message
            );

            ?>

        </div>

    <?php endif; ?>



    <!-- =====================================================
         PERMISSION INFORMATION
    ===================================================== -->

    <div class="info-box">


        <strong>
            Role Management Permissions
        </strong>


        <p>

            Karyashala Admin can add and delete
            Karyashala Admin roles.

            Admin roles are view-only.

        </p>


    </div>



    <!-- =====================================================
         ROLE MANAGEMENT TABLE
    ===================================================== -->

    <div class="table-container">


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
                        Designation
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


            <?php if (
                mysqli_num_rows($result) > 0
            ): ?>


                <?php while (
                    $row =
                    mysqli_fetch_assoc($result)
                ): ?>


                    <tr>


                        <!-- ICNO -->

                        <td>

                            <?php

                            echo htmlspecialchars(
                                $row["ICNO"]
                            );

                            ?>

                        </td>



                        <!-- EMPLOYEE NAME -->

                        <td>

                            <?php

                            echo htmlspecialchars(
                                $row["ENAME"]
                            );

                            ?>

                        </td>



                        <!-- DESIGNATION -->

                        <td>

                            <?php

                            echo htmlspecialchars(
                                $row["EDESIG"]
                            );

                            ?>

                        </td>



                        <!-- ROLE -->

                        <td>

                            <span class="role-badge">

                                <?php

                                echo htmlspecialchars(
                                    $row["ROLE"]
                                );

                                ?>

                            </span>

                        </td>



                        <!-- ACTION -->

                        <td>


                            <!-- VIEW BUTTON -->

                            <button
                                class="action-button view-button"

                                onclick='viewRole(
                                    <?php

                                    echo json_encode(
                                        $row
                                    );

                                    ?>
                                )'
                            >

                                View

                            </button>



                            <?php

                            /*
                             * Karyashala Admin:
                             *
                             * View
                             * Delete
                             *
                             * Admin:
                             *
                             * View only
                             */

                            if (
                                $row["ROLE"]
                                ===
                                "karyashala_admin"
                            ):

                            ?>


                                <?php if (
                                    $row["ICNO"]
                                    !=
                                    $_SESSION["ICNO"]
                                ): ?>


                                    <button
                                        class="action-button delete-button"

                                        onclick="deleteRole(
                                            <?php

                                            echo $row["ICNO"];

                                            ?>
                                        )"
                                    >

                                        Delete

                                    </button>


                                <?php else: ?>


                                    <span
                                        class="view-only"
                                    >

                                        Current User

                                    </span>


                                <?php endif; ?>


                            <?php else: ?>


                                <span
                                    class="view-only"
                                >

                                    View Only

                                </span>


                            <?php endif; ?>


                        </td>


                    </tr>


                <?php endwhile; ?>


            <?php else: ?>


                <tr>

                    <td
                        colspan="5"
                        class="no-data"
                    >

                        No administrative users found.

                    </td>

                </tr>


            <?php endif; ?>


            </tbody>


        </table>


    </div>


</div>



<!-- =========================================================
     ADD KARYASHALA ADMIN MODAL
========================================================= -->

<div
    class="modal"
    id="addModal"
>


    <div class="modal-content">


        <span
            class="close"
            onclick="closeModal('addModal')"
        >

            &times;

        </span>


        <h2>
            Add Karyashala Admin
        </h2>



        <form method="POST">


            <div class="form-group">


                <label>
                    Select Employee
                </label>


                <select
                    name="icno"
                    required
                >


                    <option value="">

                        Select Employee

                    </option>


                    <?php while (
                        $employee =
                        mysqli_fetch_assoc(
                            $available_result
                        )
                    ): ?>


                        <option
                            value="<?php

                            echo $employee["ICNO"];

                            ?>"
                        >

                            <?php

                            echo htmlspecialchars(
                                $employee["ICNO"]
                            );

                            ?>

                            -

                            <?php

                            echo htmlspecialchars(
                                $employee["ENAME"]
                            );

                            ?>

                        </option>


                    <?php endwhile; ?>


                </select>


            </div>



            <!--
                Role is fixed.

                Karyashala Admin cannot
                create an Admin role.
            -->

            <input
                type="hidden"
                name="role"
                value="karyashala_admin"
            >



            <button
                type="submit"
                name="add_role"
                class="save-button"
            >

                Add Karyashala Admin

            </button>


        </form>


    </div>


</div>



<!-- =========================================================
     VIEW MODAL
========================================================= -->

<div
    class="modal"
    id="viewModal"
>


    <div class="modal-content">


        <span
            class="close"
            onclick="closeModal('viewModal')"
        >

            &times;

        </span>


        <h2>
            Employee Role Details
        </h2>



        <!-- ICNO -->

        <div class="view-field">


            <strong>
                ICNO
            </strong>


            <span
                id="view_icno"
            ></span>


        </div>



        <!-- EMPLOYEE NAME -->

        <div class="view-field">


            <strong>
                Employee Name
            </strong>


            <span
                id="view_name"
            ></span>


        </div>



        <!-- DESIGNATION -->

        <div class="view-field">


            <strong>
                Designation
            </strong>


            <span
                id="view_designation"
            ></span>


        </div>



        <!-- ROLE -->

        <div class="view-field">


            <strong>
                Role
            </strong>


            <span
                id="view_role"
            ></span>


        </div>


    </div>


</div>



<!-- =========================================================
     HIDDEN DELETE FORM
========================================================= -->

<form
    method="POST"
    id="deleteForm"
    style="display:none;"
>


    <input
        type="hidden"
        name="delete_icno"
        id="delete_icno"
    >


    <input
        type="hidden"
        name="delete_role"
        value="1"
    >


</form>



<script>


/*
|--------------------------------------------------------------------------
| OPEN ADD MODAL
|--------------------------------------------------------------------------
*/

function openAddModal() {

    document.getElementById(
        "addModal"
    ).style.display = "flex";

}



/*
|--------------------------------------------------------------------------
| VIEW EMPLOYEE ROLE
|--------------------------------------------------------------------------
*/

function viewRole(row) {


    document.getElementById(
        "view_icno"
    ).textContent = row.ICNO;



    document.getElementById(
        "view_name"
    ).textContent = row.ENAME;



    document.getElementById(
        "view_designation"
    ).textContent = row.EDESIG;



    document.getElementById(
        "view_role"
    ).textContent = row.ROLE;



    document.getElementById(
        "viewModal"
    ).style.display = "flex";

}



/*
|--------------------------------------------------------------------------
| DELETE KARYASHALA ADMIN
|--------------------------------------------------------------------------
*/

function deleteRole(icno) {


    /*
     * First confirmation
     */

    let confirmation = confirm(
        "Are you sure you want to delete this Karyashala Admin role?"
    );


    if (!confirmation) {

        return;

    }



    /*
     * Second confirmation
     */

    let typed = prompt(
        "Type DELETE to confirm deletion:"
    );


    if (typed !== "DELETE") {

        alert(
            "Role deletion cancelled."
        );

        return;

    }



    /*
     * Submit delete request
     */

    document.getElementById(
        "delete_icno"
    ).value = icno;


    document.getElementById(
        "deleteForm"
    ).submit();

}



/*
|--------------------------------------------------------------------------
| CLOSE MODAL
|--------------------------------------------------------------------------
*/

function closeModal(id) {

    document.getElementById(
        id
    ).style.display = "none";

}



/*
|--------------------------------------------------------------------------
| CLOSE MODAL WHEN CLICKING OUTSIDE
|--------------------------------------------------------------------------
*/

window.onclick = function(event) {


    const addModal =
        document.getElementById(
            "addModal"
        );


    const viewModal =
        document.getElementById(
            "viewModal"
        );



    if (event.target === addModal) {

        addModal.style.display =
            "none";

    }



    if (event.target === viewModal) {

        viewModal.style.display =
            "none";

    }

};


</script>


</body>

</html>