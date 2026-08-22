<?php

session_start();

require_once "../../config/db.php";

if (!isset($_SESSION["ICNO"]) || !isset($_SESSION["ROLE"])) {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION["ROLE"] !== "karyashala_admin") {
    header("Location: ../../login.php");
    exit();
}

$selected_period = $_GET["period"] ?? "2023-2025";

$period_parts = explode("-", $selected_period);

if (count($period_parts) != 2) {
    $period_parts = [2023, 2025];
}

$start_year = intval($period_parts[0]);
$end_year = intval($period_parts[1]);

/*
 * According to the project requirement:
 *
 * 2023-2025 means:
 * 01 January 2023
 * to
 * 31 December 2024
 */

$start_date = $start_year . "-01-01";
$end_date = ($end_year - 1) . "-12-31";

$message = "";
$message_type = "";

if (isset($_POST["add_karyashala"])) {

    $icno = intval($_POST["icno"]);
    $karyashala_date = $_POST["karyashala_date"];
    $remark = trim($_POST["karyashala_remark"]);

    $employee_sql = "
        SELECT ENAME
        FROM employees
        WHERE ICNO = ?
    ";

    $employee_stmt = mysqli_prepare($conn, $employee_sql);

    mysqli_stmt_bind_param(
        $employee_stmt,
        "i",
        $icno
    );

    mysqli_stmt_execute($employee_stmt);

    $employee_result = mysqli_stmt_get_result($employee_stmt);

    if (mysqli_num_rows($employee_result) == 0) {

        $message = "Employee not found.";
        $message_type = "error";

    } else {

        $employee = mysqli_fetch_assoc($employee_result);

        $ename = $employee["ENAME"];

        if ($karyashala_date < $start_date ||
            $karyashala_date > $end_date) {

            $message =
                "Invalid date. Please select a date between "
                . $start_date
                . " and "
                . $end_date
                . ".";

            $message_type = "error";

        } else {

            $insert_sql = "
                INSERT INTO karyashalamgt
                (
                    ICNO,
                    ENAME,
                    karyashala_date,
                    karyashala_remark
                )
                VALUES (?, ?, ?, ?)
            ";

            $stmt = mysqli_prepare($conn, $insert_sql);

            mysqli_stmt_bind_param(
                $stmt,
                "isss",
                $icno,
                $ename,
                $karyashala_date,
                $remark
            );

            if (mysqli_stmt_execute($stmt)) {

                $message = "Karyashala attendance added successfully.";
                $message_type = "success";

            } else {

                $message = "Unable to add attendance.";
                $message_type = "error";
            }
        }
    }
}

if (isset($_POST["edit_karyashala"])) {

    $record_id = intval($_POST["record_id"]);
    $karyashala_date = $_POST["karyashala_date"];
    $remark = trim($_POST["karyashala_remark"]);

    if ($karyashala_date < $start_date ||
        $karyashala_date > $end_date) {

        $message =
            "Invalid date. Date must be between "
            . $start_date
            . " and "
            . $end_date
            . ".";

        $message_type = "error";

    } else {

        $sql = "
            UPDATE karyashalamgt
            SET
                karyashala_date = ?,
                karyashala_remark = ?
            WHERE id = ?
        ";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "ssi",
            $karyashala_date,
            $remark,
            $record_id
        );

        if (mysqli_stmt_execute($stmt)) {

            $message = "Karyashala attendance updated successfully.";
            $message_type = "success";

        } else {

            $message = "Unable to update attendance.";
            $message_type = "error";
        }
    }
}

if (isset($_POST["delete_karyashala"])) {

    $record_id = intval($_POST["delete_id"]);

    $sql = "
        DELETE FROM karyashalamgt
        WHERE id = ?
    ";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $record_id
    );

    if (mysqli_stmt_execute($stmt)) {

        $message = "Karyashala attendance deleted successfully.";
        $message_type = "success";

    } else {

        $message = "Unable to delete attendance.";
        $message_type = "error";
    }
}

$employee_sql = "
    SELECT ICNO, ENAME
    FROM employees
    ORDER BY ICNO ASC
";

$employee_result = mysqli_query(
    $conn,
    $employee_sql
);

$records_sql = "
    SELECT
        id,
        ICNO,
        ENAME,
        karyashala_date,
        karyashala_remark
    FROM karyashalamgt
    WHERE karyashala_date BETWEEN ? AND ?
    ORDER BY ICNO ASC, karyashala_date ASC
";

$records_stmt = mysqli_prepare(
    $conn,
    $records_sql
);

mysqli_stmt_bind_param(
    $records_stmt,
    "ss",
    $start_date,
    $end_date
);

mysqli_stmt_execute($records_stmt);

$records_result = mysqli_stmt_get_result(
    $records_stmt
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Karyashala Report Management</title>

    <link rel="stylesheet"
          href="../../css/admin.css">

    <style>

        .page-container {
            width: 95%;
            max-width: 1300px;
            margin: 30px auto;
        }

        .period-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .period-box label {
            font-weight: bold;
            margin-right: 10px;
        }

        .period-box select {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #aaa;
        }

        .record-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow-x: auto;
        }

        .add-button {
            background: #0066a1;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .action-button {
            border: none;
            padding: 7px 10px;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 4px;
        }

        .edit-button {
            background: #f0ad4e;
            color: white;
        }

        .delete-button {
            background: #dc3545;
            color: white;
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
            padding: 11px;
            border-bottom: 1px solid #ddd;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            width: 90%;
            max-width: 500px;
            padding: 25px;
            border-radius: 10px;
        }

        .modal-content h2 {
            color: #003b6f;
            margin-bottom: 20px;
        }

        .close {
            float: right;
            font-size: 25px;
            cursor: pointer;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #aaa;
            border-radius: 5px;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .save-button {
            width: 100%;
            padding: 11px;
            border: none;
            background: #0066a1;
            color: white;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
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

    </style>

</head>

<body>


<div class="header">

    <div class="header-left">

        <img
            src="../../images/drdo-logo.jpg"
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
        href="../../logout.php"
        class="logout-btn"
    >
        Logout
    </a>

</div>


<div class="page-container">

    <a
        href="../dashboard.php"
        class="back-button"
    >
        ← Back to Dashboard
    </a>


    <div class="period-box">

        <form method="GET">

            <label>
                Select Reporting Period:
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

            <p style="margin-top:10px;">
                Attendance dates allowed:
                <strong>
                    <?php echo $start_date; ?>
                    to
                    <?php echo $end_date; ?>
                </strong>
            </p>

        </form>

    </div>


    <?php if ($message != ""): ?>

        <div class="message <?php echo $message_type; ?>">

            <?php echo htmlspecialchars($message); ?>

        </div>

    <?php endif; ?>


    <div class="record-box">

        <button
            class="add-button"
            onclick="openAddModal()"
        >
            + Mark Employee Present
        </button>


        <table>

            <thead>

                <tr>

                    <th>ICNO</th>

                    <th>Employee Name</th>

                    <th>Karyashala Attended Date</th>

                    <th>Remark</th>

                    <th>Action</th>

                </tr>

            </thead>

            <tbody>

            <?php if (mysqli_num_rows($records_result) > 0): ?>

                <?php while ($record = mysqli_fetch_assoc($records_result)): ?>

                    <tr>

                        <td>
                            <?php echo htmlspecialchars($record["ICNO"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($record["ENAME"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($record["karyashala_date"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($record["karyashala_remark"]); ?>
                        </td>

                        <td>

                            <button
                                class="action-button edit-button"
                                onclick='openEditModal(
                                    <?php echo json_encode($record); ?>
                                )'
                            >
                                Edit
                            </button>

                            <button
                                class="action-button delete-button"
                                onclick="deleteRecord(
                                    <?php echo $record['id']; ?>
                                )"
                            >
                                Delete
                            </button>

                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>

                    <td colspan="5"
                        style="text-align:center;">

                        No Karyashala attendance records
                        found for this period.

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
            Mark Employee Present
        </h2>

        <form
            method="POST"
            onsubmit="return validateAdd()"
        >

            <div class="form-group">

                <label>
                    Employee
                </label>

                <select
                    name="icno"
                    id="add_icno"
                    required
                >

                    <option value="">
                        Select Employee
                    </option>

                    <?php

                    mysqli_data_seek(
                        $employee_result,
                        0
                    );

                    while (
                        $employee =
                        mysqli_fetch_assoc(
                            $employee_result
                        )
                    ):

                    ?>

                        <option
                            value="<?php echo $employee["ICNO"]; ?>"
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
                    Karyashala Attended Date
                </label>

                <input
                    type="date"
                    name="karyashala_date"
                    id="add_date"
                    min="<?php echo $start_date; ?>"
                    max="<?php echo $end_date; ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    Remark
                </label>

                <textarea
                    name="karyashala_remark"
                    id="add_remark"
                    maxlength="255"
                ></textarea>

            </div>


            <button
                type="submit"
                name="add_karyashala"
                class="save-button"
            >
                Save Attendance
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
            Edit Karyashala Attendance
        </h2>

        <form
            method="POST"
            onsubmit="return validateEdit()"
        >

            <input
                type="hidden"
                name="record_id"
                id="edit_id"
            >


            <div class="form-group">

                <label>
                    ICNO
                </label>

                <input
                    type="number"
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
                    Karyashala Attended Date
                </label>

                <input
                    type="date"
                    name="karyashala_date"
                    id="edit_date"
                    min="<?php echo $start_date; ?>"
                    max="<?php echo $end_date; ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    Remark
                </label>

                <textarea
                    name="karyashala_remark"
                    id="edit_remark"
                    maxlength="255"
                ></textarea>

            </div>


            <button
                type="submit"
                name="edit_karyashala"
                class="save-button"
            >
                Save Changes
            </button>

        </form>

    </div>

</div>

<form
    method="POST"
    id="deleteForm"
    style="display:none;"
>

    <input
        type="hidden"
        name="delete_id"
        id="delete_id"
    >

    <input
        type="hidden"
        name="delete_karyashala"
        value="1"
    >

</form>

<script>

function openAddModal() {

    document.getElementById("addModal")
        .style.display = "flex";

}

function openEditModal(record) {

    document.getElementById("edit_id").value =
        record.id;

    document.getElementById("edit_icno").value =
        record.ICNO;

    document.getElementById("edit_name").value =
        record.ENAME;

    document.getElementById("edit_date").value =
        record.karyashala_date;

    document.getElementById("edit_remark").value =
        record.karyashala_remark;

    document.getElementById("editModal")
        .style.display = "flex";

}

function closeModal(id) {

    document.getElementById(id)
        .style.display = "none";

}

function deleteRecord(id) {

    let confirmation = confirm(
        "Are you sure you want to delete this Karyashala attendance record?"
    );

    if (!confirmation) {
        return;
    }

    document.getElementById("delete_id").value = id;

    document.getElementById("deleteForm").submit();

}

function validateAdd() {

    let employee =
        document.getElementById("add_icno").value;

    let date =
        document.getElementById("add_date").value;

    if (employee === "") {

        alert("Please select an employee.");

        return false;
    }

    if (date === "") {

        alert("Please select the Karyashala date.");

        return false;
    }

    return true;

}

function validateEdit() {

    let date =
        document.getElementById("edit_date").value;

    if (date === "") {

        alert("Please select the Karyashala date.");

        return false;
    }

    return true;

}

window.onclick = function(event) {

    let addModal =
        document.getElementById("addModal");

    let editModal =
        document.getElementById("editModal");

    if (event.target === addModal) {
        addModal.style.display = "none";
    }

    if (event.target === editModal) {
        editModal.style.display = "none";
    }

}

</script>

</body>

</html>