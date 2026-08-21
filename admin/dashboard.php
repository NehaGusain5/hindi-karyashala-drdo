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

$designations = [
    "Scientist B",
    "Scientist C",
    "Scientist D",
    "Scientist E",
    "Scientist F",
    "Scientist G",
    "Scientist H",
    "TO A",
    "TO B",
    "TO C",
    "TO D",
    "HRD",
    "Director",
    "Employee"
];

$message = "";
$message_type = "";

if (isset($_POST["add_employee"])) {

    $ename = trim($_POST["ename"]);
    $edesig = trim($_POST["edesig"]);
    $egroup = trim($_POST["egroup"]);
    $password = trim($_POST["password"]);

    if ($ename == "" || $edesig == "" || $egroup == "" || $password == "") {

        $message = "Please fill all required fields.";
        $message_type = "error";

    } elseif (!in_array($edesig, $designations)) {

        $message = "Invalid designation selected.";
        $message_type = "error";

    } else {

        $sql = "INSERT INTO employees
                (ENAME, EDESIG, EGROUP, PASSWORD)
                VALUES (?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "ssss",
            $ename,
            $edesig,
            $egroup,
            $password
        );

        if (mysqli_stmt_execute($stmt)) {

            $new_icno = mysqli_insert_id($conn);

            $role = "employee";

            $role_sql = "INSERT INTO roles (ICNO, ENAME, ROLE)
                         VALUES (?, ?, ?)";

            $role_stmt = mysqli_prepare($conn, $role_sql);

            mysqli_stmt_bind_param(
                $role_stmt,
                "iss",
                $new_icno,
                $ename,
                $role
            );

            mysqli_stmt_execute($role_stmt);

            $message = "Employee added successfully. ICNO: " . $new_icno;
            $message_type = "success";

        } else {

            $message = "Unable to add employee.";
            $message_type = "error";
        }
    }
}

if (isset($_POST["edit_employee"])) {

    $icno = intval($_POST["icno"]);
    $ename = trim($_POST["ename"]);
    $edesig = trim($_POST["edesig"]);
    $egroup = trim($_POST["egroup"]);

    if ($ename == "" || $edesig == "" || $egroup == "") {

        $message = "Please fill all required fields.";
        $message_type = "error";

    } elseif (!in_array($edesig, $designations)) {

        $message = "Invalid designation selected.";
        $message_type = "error";

    } else {

        $sql = "UPDATE employees
                SET ENAME = ?, EDESIG = ?, EGROUP = ?
                WHERE ICNO = ?";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "sssi",
            $ename,
            $edesig,
            $egroup,
            $icno
        );

        if (mysqli_stmt_execute($stmt)) {

            $role_sql = "UPDATE roles
                         SET ENAME = ?
                         WHERE ICNO = ?";

            $role_stmt = mysqli_prepare($conn, $role_sql);

            mysqli_stmt_bind_param(
                $role_stmt,
                "si",
                $ename,
                $icno
            );

            mysqli_stmt_execute($role_stmt);

            $message = "Employee details updated successfully.";
            $message_type = "success";

        } else {

            $message = "Unable to update employee.";
            $message_type = "error";
        }
    }
}

if (isset($_POST["delete_employee"])) {

    $icno = intval($_POST["delete_icno"]);

    if ($icno == $_SESSION["ICNO"]) {

        $message = "You cannot delete your own account.";
        $message_type = "error";

    } else {

        $role_sql = "DELETE FROM roles WHERE ICNO = ?";

        $role_stmt = mysqli_prepare($conn, $role_sql);

        mysqli_stmt_bind_param(
            $role_stmt,
            "i",
            $icno
        );

        mysqli_stmt_execute($role_stmt);

        $sql = "DELETE FROM employees WHERE ICNO = ?";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $icno
        );

        if (mysqli_stmt_execute($stmt)) {

            $message = "Employee deleted successfully.";
            $message_type = "success";

        } else {

            $message = "Unable to delete employee.";
            $message_type = "error";
        }
    }
}

$sql = "SELECT ICNO, ENAME, EDESIG, EGROUP
        FROM employees
        ORDER BY ICNO ASC";

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard - DRDO Hindi Karyashala</title>

    <link rel="stylesheet" href="../css/admin.css">

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

<div class="container">

    <div class="welcome">

        <h2>
            Welcome, <?php echo htmlspecialchars($_SESSION["ENAME"]); ?>!
        </h2>

        <p>
            Admin Dashboard
        </p>

    </div>

    <?php if ($message != ""): ?>

        <div class="message <?php echo $message_type; ?>">

            <?php echo htmlspecialchars($message); ?>

        </div>

    <?php endif; ?>

    <div class="action-bar">

    <h2>
        Employee Management
    </h2>

    <div>

        <button
            class="add-btn"
            onclick="openAddModal()"
        >
            + Add New Employee
        </button>

        <a
            href="karyashala_report.php"
            class="add-btn"
            style="text-decoration:none; display:inline-block;"
        >
            Karyashala Management
        </a>

        <a
            href="generate_report.php"
            class="add-btn"
            style="text-decoration:none; display:inline-block;"
        >
            Generate Report
        </a>

        <a
            href="role_management.php"
            class="add-btn"
            style="text-decoration:none; display:inline-block;"
        >
            Role Management
        </a>

    </div>

</div>

    <div class="table-container">

        <table>

            <thead>

                <tr>

                    <th>ICNO</th>

                    <th>Name</th>

                    <th>Designation</th>

                    <th>Group</th>

                    <th>Action</th>

                </tr>

            </thead>

            <tbody>

            <?php if (mysqli_num_rows($result) > 0): ?>

                <?php while ($employee = mysqli_fetch_assoc($result)): ?>

                    <tr>

                        <td>
                            <?php echo htmlspecialchars($employee["ICNO"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($employee["ENAME"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($employee["EDESIG"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($employee["EGROUP"]); ?>
                        </td>

                        <td>

                            <button
                                class="view-btn"
                                onclick='viewEmployee(
                                    <?php echo json_encode($employee); ?>
                                )'
                            >
                                View
                            </button>


                            <button
                                class="edit-btn"
                                onclick='editEmployee(
                                    <?php echo json_encode($employee); ?>
                                )'
                            >
                                Edit
                            </button>


                            <button
                                class="delete-btn"
                                onclick="deleteEmployee(
                                    <?php echo $employee['ICNO']; ?>,
                                    '<?php echo htmlspecialchars($employee['ENAME'], ENT_QUOTES); ?>'
                                )"
                            >
                                Delete
                            </button>

                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>

                    <td colspan="5" style="text-align:center;">
                        No employees found.
                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

<div
    id="addModal"
    class="modal"
>

    <div class="modal-content">

        <span
            class="close"
            onclick="closeModal('addModal')"
        >
            &times;
        </span>

        <h2>
            Add New Employee
        </h2>

        <form
            method="POST"
            onsubmit="return validateAddForm()"
        >

            <div class="form-group">

                <label>
                    Employee Name
                </label>

                <input
                    type="text"
                    name="ename"
                    id="add_ename"
                    maxlength="100"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    Designation
                </label>

                <select
                    name="edesig"
                    id="add_edesig"
                    required
                >

                    <option value="">
                        Select Designation
                    </option>

                    <?php foreach ($designations as $designation): ?>

                        <option value="<?php echo htmlspecialchars($designation); ?>">

                            <?php echo htmlspecialchars($designation); ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <div class="form-group">

                <label>
                    Group
                </label>

                <input
                    type="text"
                    name="egroup"
                    id="add_egroup"
                    maxlength="100"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    Password
                </label>

                <input
                    type="password"
                    name="password"
                    id="add_password"
                    maxlength="100"
                    required
                >

            </div>

            <button
                type="submit"
                name="add_employee"
                class="save-btn"
            >
                Add Employee
            </button>

        </form>

    </div>

</div>

<div
    id="editModal"
    class="modal"
>

    <div class="modal-content">

        <span
            class="close"
            onclick="closeModal('editModal')"
        >
            &times;
        </span>


        <h2>
            Edit Employee
        </h2>

        <form
            method="POST"
            onsubmit="return validateEditForm()"
        >

            <input
                type="hidden"
                name="icno"
                id="edit_icno"
            >

            <div class="form-group">

                <label>
                    ICNO
                </label>

                <input
                    type="number"
                    id="edit_icno_display"
                    readonly
                >

            </div>

            <div class="form-group">

                <label>
                    Employee Name
                </label>

                <input
                    type="text"
                    name="ename"
                    id="edit_ename"
                    maxlength="100"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    Designation
                </label>

                <select
                    name="edesig"
                    id="edit_edesig"
                    required
                >

                    <option value="">
                        Select Designation
                    </option>

                    <?php foreach ($designations as $designation): ?>

                        <option value="<?php echo htmlspecialchars($designation); ?>">

                            <?php echo htmlspecialchars($designation); ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <div class="form-group">

                <label>
                    Group
                </label>

                <input
                    type="text"
                    name="egroup"
                    id="edit_egroup"
                    maxlength="100"
                    required
                >

            </div>

            <button
                type="submit"
                name="edit_employee"
                class="save-btn"
            >
                Save Changes
            </button>

        </form>

    </div>

</div>

<div
    id="viewModal"
    class="modal"
>

    <div class="modal-content">

        <span
            class="close"
            onclick="closeModal('viewModal')"
        >
            &times;
        </span>

        <h2>
            Employee Details
        </h2>

        <div class="view-field">

            <strong>ICNO</strong>

            <span id="view_icno"></span>

        </div>

        <div class="view-field">

            <strong>Employee Name</strong>

            <span id="view_ename"></span>

        </div>

        <div class="view-field">

            <strong>Designation</strong>

            <span id="view_edesig"></span>

        </div>

        <div class="view-field">

            <strong>Group</strong>

            <span id="view_egroup"></span>

        </div>

    </div>

</div>

<form
    id="deleteForm"
    method="POST"
    style="display:none;"
>

    <input
        type="hidden"
        name="delete_icno"
        id="delete_icno"
    >

    <input
        type="hidden"
        name="delete_employee"
        value="1"
    >

</form>

<script>

function openAddModal() {

    document.getElementById("addModal").style.display = "flex";

}

function viewEmployee(employee) {

    document.getElementById("view_icno").textContent =
        employee.ICNO;

    document.getElementById("view_ename").textContent =
        employee.ENAME;

    document.getElementById("view_edesig").textContent =
        employee.EDESIG;

    document.getElementById("view_egroup").textContent =
        employee.EGROUP;

    document.getElementById("viewModal").style.display = "flex";

}

function editEmployee(employee) {

    document.getElementById("edit_icno").value =
        employee.ICNO;

    document.getElementById("edit_icno_display").value =
        employee.ICNO;

    document.getElementById("edit_ename").value =
        employee.ENAME;

    document.getElementById("edit_edesig").value =
        employee.EDESIG;

    document.getElementById("edit_egroup").value =
        employee.EGROUP;

    document.getElementById("editModal").style.display =
        "flex";

}

function deleteEmployee(icno, name) {

    let firstConfirm = confirm(
        "Are you sure you want to delete employee " +
        name +
        " (ICNO: " +
        icno +
        ")?"
    );

    if (!firstConfirm) {

        return;

    }

    let confirmation = prompt(
        "This action cannot be undone.\n\n" +
        "Type DELETE to confirm deletion:"
    );

    if (confirmation !== "DELETE") {

        alert("Deletion cancelled.");

        return;

    }

    document.getElementById("delete_icno").value =
        icno;

    document.getElementById("deleteForm").submit();

}

function closeModal(modalId) {

    document.getElementById(modalId).style.display =
        "none";

}

window.onclick = function(event) {

    let addModal = document.getElementById("addModal");

    let editModal = document.getElementById("editModal");

    let viewModal = document.getElementById("viewModal");


    if (event.target === addModal) {

        addModal.style.display = "none";

    }

    if (event.target === editModal) {

        editModal.style.display = "none";

    }

    if (event.target === viewModal) {

        viewModal.style.display = "none";

    }

}

function validateAddForm() {

    let name =
        document.getElementById("add_ename").value.trim();

    let designation =
        document.getElementById("add_edesig").value;

    let group =
        document.getElementById("add_egroup").value.trim();

    let password =
        document.getElementById("add_password").value;


    if (name === "") {

        alert("Please enter employee name.");

        return false;

    }

    if (designation === "") {

        alert("Please select a designation.");

        return false;

    }

    if (group === "") {

        alert("Please enter employee group.");

        return false;

    }

    if (password === "") {

        alert("Please enter password.");

        return false;

    }

    return true;

}

function validateEditForm() {

    let name =
        document.getElementById("edit_ename").value.trim();

    let designation =
        document.getElementById("edit_edesig").value;

    let group =
        document.getElementById("edit_egroup").value.trim();

    if (name === "") {

        alert("Please enter employee name.");

        return false;

    }

    if (designation === "") {

        alert("Please select a designation.");

        return false;

    }

    if (group === "") {

        alert("Please enter employee group.");

        return false;

    }

    return true;

}

</script>

</body>

</html>