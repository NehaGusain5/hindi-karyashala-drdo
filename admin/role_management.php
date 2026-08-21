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

$message = "";
$message_type = "";

if (isset($_POST["add_role"])) {

    $icno = intval($_POST["icno"]);
    $role = trim($_POST["role"]);

    $allowed_roles = [
        "admin",
        "karyashala_admin"
    ];

    if (!in_array($role, $allowed_roles)) {

        $message = "Invalid role selected.";
        $message_type = "error";

    } else {

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

        } else {

            $employee = mysqli_fetch_assoc($result);

            $ename = $employee["ENAME"];

            $check_sql = "
                SELECT ICNO
                FROM roles
                WHERE ICNO = ?
            ";

            $check_stmt =
                mysqli_prepare(
                    $conn,
                    $check_sql
                );

            mysqli_stmt_bind_param(
                $check_stmt,
                "i",
                $icno
            );

            mysqli_stmt_execute(
                $check_stmt
            );

            $check_result =
                mysqli_stmt_get_result(
                    $check_stmt
                );


            if (mysqli_num_rows($check_result) > 0) {

                $message =
                    "This employee already has a role.";

                $message_type = "error";

            } else {

                $insert_sql = "
                    INSERT INTO roles
                    (
                        ICNO,
                        ENAME,
                        ROLE
                    )
                    VALUES (?, ?, ?)
                ";

                $insert_stmt =
                    mysqli_prepare(
                        $conn,
                        $insert_sql
                    );

                mysqli_stmt_bind_param(
                    $insert_stmt,
                    "iss",
                    $icno,
                    $ename,
                    $role
                );

                if (
                    mysqli_stmt_execute(
                        $insert_stmt
                    )
                ) {

                    $message =
                        "Role added successfully.";

                    $message_type =
                        "success";

                } else {

                    $message =
                        "Unable to add role.";

                    $message_type =
                        "error";
                }
            }
        }
    }
}

if (isset($_POST["edit_role"])) {

    $icno = intval($_POST["edit_icno"]);
    $role = trim($_POST["role"]);

    $allowed_roles = [
        "admin",
        "karyashala_admin"
    ];

    if (!in_array($role, $allowed_roles)) {

        $message = "Invalid role selected.";
        $message_type = "error";

    } else {

        if ($icno == $_SESSION["ICNO"]) {

            $message =
                "You cannot change your own role.";

            $message_type =
                "error";

        } else {

            $update_sql = "
                UPDATE roles
                SET ROLE = ?
                WHERE ICNO = ?
            ";

            $stmt =
                mysqli_prepare(
                    $conn,
                    $update_sql
                );

            mysqli_stmt_bind_param(
                $stmt,
                "si",
                $role,
                $icno
            );

            if (
                mysqli_stmt_execute(
                    $stmt
                )
            ) {

                $message =
                    "Role updated successfully.";

                $message_type =
                    "success";

            } else {

                $message =
                    "Unable to update role.";

                $message_type =
                    "error";
            }
        }
    }
}

if (isset($_POST["delete_role"])) {

    $icno =
        intval($_POST["delete_icno"]);

    if ($icno == $_SESSION["ICNO"]) {

        $message =
            "You cannot delete your own role.";

        $message_type =
            "error";

    } else {

        $delete_sql = "
            DELETE FROM roles
            WHERE ICNO = ?
        ";

        $stmt =
            mysqli_prepare(
                $conn,
                $delete_sql
            );

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $icno
        );

        if (
            mysqli_stmt_execute(
                $stmt
            )
        ) {

            $message =
                "Role deleted successfully.";

            $message_type =
                "success";

        } else {

            $message =
                "Unable to delete role.";

            $message_type =
                "error";
        }
    }
}

$sql = "
    SELECT
        ICNO,
        ENAME,
        ROLE
    FROM roles
    ORDER BY ICNO ASC
";

$result =
    mysqli_query(
        $conn,
        $sql
    );

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

$available_result =
    mysqli_query(
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
        Role Management
    </title>

    <link
        rel="stylesheet"
        href="../css/admin.css"
    >

    <link
        rel="stylesheet"
        href="../css/role_management.css"
    >

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


    <div class="page-header">

        <div>

            <h2>
                Role Management
            </h2>

            <p>
                Manage Admin and Karyashala Admin roles.
            </p>

        </div>


        <button
            class="add-button"
            onclick="openAddModal()"
        >
            + Add Role
        </button>

    </div>

    <?php if ($message != ""): ?>

        <div
            class="message <?php echo $message_type; ?>"
        >

            <?php
            echo htmlspecialchars($message);
            ?>

        </div>

    <?php endif; ?>

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

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $row["ICNO"]
                            );
                            ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $row["ENAME"]
                            );
                            ?>
                        </td>


                        <td>

                            <span
                                class="role-badge"
                            >

                                <?php
                                echo htmlspecialchars(
                                    $row["ROLE"]
                                );
                                ?>

                            </span>

                        </td>

                        <td>

                            <button
                                class="action-button view-button"
                                onclick='viewRole(
                                    <?php
                                    echo json_encode($row);
                                    ?>
                                )'
                            >
                                View
                            </button>

                            <button
                                class="action-button edit-button"
                                onclick='editRole(
                                    <?php
                                    echo json_encode($row);
                                    ?>
                                )'
                            >
                                Edit
                            </button>

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

                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>

                    <td
                        colspan="4"
                        class="no-data"
                    >

                        No Admin or Karyashala Admin found.

                    </td>

                </tr>

            <?php endif; ?>


            </tbody>

        </table>

    </div>

</div>

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
            Add Administrative Role
        </h2>

        <form method="POST">

            <div class="form-group">

                <label>
                    Employee
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

            <div class="form-group">

                <label>
                    Role
                </label>

                <select
                    name="role"
                    required
                >

                    <option value="">
                        Select Role
                    </option>

                    <option value="admin">
                        Admin
                    </option>

                    <option value="karyashala_admin">
                        Karyashala Admin
                    </option>

                </select>

            </div>

            <button
                type="submit"
                name="add_role"
                class="save-button"
            >

                Add Role

            </button>


        </form>

    </div>

</div>

<div
    class="modal"
    id="editModal"
>

    <div class="modal-content">

        <span
            class="close"
            onclick="closeModal('editModal')"
        >
            &times;
        </span>


        <h2>
            Edit Role
        </h2>


        <form method="POST">


            <div class="form-group">

                <label>
                    ICNO
                </label>

                <input
                    type="number"
                    name="edit_icno"
                    id="edit_icno"
                    readonly
                >

            </div>

            <div class="form-group">

                <label>
                    Employee Name
                </label>

                <input
                    type="text"
                    id="edit_name"
                    readonly
                >

            </div>

            <div class="form-group">

                <label>
                    Role
                </label>


                <select
                    name="role"
                    id="edit_role"
                    required
                >

                    <option value="admin">
                        Admin
                    </option>

                    <option value="karyashala_admin">
                        Karyashala Admin
                    </option>

                </select>

            </div>


            <button
                type="submit"
                name="edit_role"
                class="save-button"
            >

                Save Changes

            </button>


        </form>

    </div>

</div>

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


        <div class="view-field">

            <strong>
                ICNO
            </strong>

            <span id="view_icno"></span>

        </div>


        <div class="view-field">

            <strong>
                Employee Name
            </strong>

            <span id="view_name"></span>

        </div>


        <div class="view-field">

            <strong>
                Role
            </strong>

            <span id="view_role"></span>

        </div>


    </div>

</div>

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

function openAddModal() {

    document.getElementById(
        "addModal"
    ).style.display = "flex";

}

function editRole(row) {

    document.getElementById(
        "edit_icno"
    ).value = row.ICNO;


    document.getElementById(
        "edit_name"
    ).value = row.ENAME;


    document.getElementById(
        "edit_role"
    ).value = row.ROLE;


    document.getElementById(
        "editModal"
    ).style.display = "flex";

}

function viewRole(row) {

    document.getElementById(
        "view_icno"
    ).textContent = row.ICNO;


    document.getElementById(
        "view_name"
    ).textContent = row.ENAME;


    document.getElementById(
        "view_role"
    ).textContent = row.ROLE;


    document.getElementById(
        "viewModal"
    ).style.display = "flex";

}

function deleteRole(icno) {

    let confirmation = confirm(
        "Are you sure you want to delete this role?"
    );

    if (!confirmation) {
        return;
    }

    let typed = prompt(
        "Type DELETE to confirm:"
    );

    if (typed !== "DELETE") {

        alert(
            "Role deletion cancelled."
        );

        return;
    }

    document.getElementById(
        "delete_icno"
    ).value = icno;


    document.getElementById(
        "deleteForm"
    ).submit();

}

function closeModal(id) {

    document.getElementById(
        id
    ).style.display = "none";

}

window.onclick = function(event) {

    let addModal =
        document.getElementById(
            "addModal"
        );

    let editModal =
        document.getElementById(
            "editModal"
        );

    let viewModal =
        document.getElementById(
            "viewModal"
        );

    if (event.target === addModal) {
        addModal.style.display = "none";
    }

    if (event.target === editModal) {
        editModal.style.display = "none";
    }

    if (event.target === viewModal) {
        viewModal.style.display = "none";
    }

};

</script>

</body>

</html>