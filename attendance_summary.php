<?php
// attendance_summary.php
require_once "db.php";
require_once "auth.php";
require_once "csrf.php";
require_once "classes/print_pdf_tabelle.class.php";
require_once "helpers.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_login();
$current_user = current_user();
// Fetch users and events for dropdowns
$users = $conn->query("SELECT id, name FROM users ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$events = $conn->query("SELECT id, event_type FROM veranstaltung ORDER BY event_type")->fetchAll(PDO::FETCH_ASSOC);

// Add / Edit record
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // var_dump($_POST);exit;

    verify_csrf_or_die($_POST['csrf_token'] ?? '');
    $action = $_POST['action'] ?? '';
    $name = sanitize_text($_POST['user_id'] ?? '');
    $event_type = sanitize_text($_POST['event_id'] ?? '');
    $status = sanitize_text($_POST['status'] ?? '');
    // $status = 'present';
    $recorded_at = date('Y-m-d H:i:s');

    if ($action === 'add') {

        //   Get existing user ID
        $stmtUser = $conn->prepare("SELECT id FROM users WHERE id = ?");
        $stmtUser->execute([$name]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo "<script>alert('❌ User not found. Please select a valid user.');window.location.reload();</script>";
            exit;
        }
        $user_id = $user['id'];

        // Get existing event ID
        $stmtEvent = $conn->prepare("SELECT id FROM veranstaltung WHERE id = ?");
        $stmtEvent->execute([$event_type]);
        $event = $stmtEvent->fetch(PDO::FETCH_ASSOC);

        if (!$event) {
            echo "<script>alert(' Event not found. Please select a valid event.');window.location.reload();</script>";
            exit;
        }
        $event_id = $event['id'];

        //  Check if this attendance already exists
        $stmtCheck = $conn->prepare("SELECT id FROM anwesenheits_kontrolle WHERE event_id = ? AND user_id = ?");
        $stmtCheck->execute([$event_id, $user_id]);
        if ($stmtCheck->fetch()) {
           
              echo "<script>
                alert(' Attendance already recorded for this user in this event.');
                window.location.href = 'attendance_summary.php';
            </script>";
            exit;
        }

        // Add attendance
        $stmt = $conn->prepare("INSERT INTO anwesenheits_kontrolle (event_id, user_id, status, recorded_at) VALUES (?, ?, ?, ?)");
        $stmt->execute([$event_id, $user_id, $status, $recorded_at]);

        echo "<script>
            alert(' Attendance added successfully');
            window.location.href = 'attendance_summary.php';
        </script>";
        exit;
    }

  if ($action === 'edit') {
    $record_id = (int)($_POST['id'] ?? 0);
    $user_id = (int)($_POST['user_id'] ?? 0);
    $event_id = (int)($_POST['event_id'] ?? 0);
    $status = trim($_POST['status'] ?? '');

    //   Check if another record already exists with same user & event
    $stmtCheck = $conn->prepare("
        SELECT id FROM anwesenheits_kontrolle 
        WHERE user_id = ? AND event_id = ? AND id != ?
    ");
    $stmtCheck->execute([$user_id, $event_id, $record_id]);
    $duplicate = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($duplicate) {
        echo "<script>
            alert(' Duplicate record: This user is already marked for the selected event.');
            window.location.href = 'attendance_summary.php';
        </script>";
        exit;
    }

    // Proceed with update
    $stmt = $conn->prepare("
        UPDATE anwesenheits_kontrolle 
        SET user_id = ?, event_id = ?, status = ?
        WHERE id = ?
    ");
    $stmt->execute([$user_id, $event_id, $status, $record_id]);

    echo "<script>
        alert(' Record updated successfully');
        window.location.href = 'attendance_summary.php';
    </script>";
    exit;
}


    if ($action === 'delete') {
        $record_id = (int)($_POST['id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM anwesenheits_kontrolle WHERE user_id = ?");
        $stmt->execute([$record_id]);

         echo "<script>
            alert('Record deleted successfully');
            window.location.href = 'attendance_summary.php';
        </script>";
        exit;
    }
}




// $data = get_data($conn);
// $csrf_token = generate_csrf_token();
//

// Accept filter from GET or POST (sanitize)
$filter = [];
if (!empty($_GET['month'])) {
    $f = sanitize_text($_GET['month']);
    if (preg_match('/^\d{4}-\d{2}$/', $f)) $filter['month'] = $f;
}
if (!empty($_GET['event_type'])) {
    $filter['event_type'] = sanitize_text($_GET['event_type']);
}

function get_data($pdo, $filter = null)
{
    global $current_user;
    $where = ["ak.status = :status"];
    $params = [':status' => 'present'];

    if ($current_user['role'] === 'admin') {
        // full access
    } elseif ($current_user['role'] === 'manager') {
        $where[] = "n.department_id = :department_id";
        $params[':department_id'] = $current_user['department_id'];
    } elseif ($current_user['role'] === 'viewer') {
        // viewers see aggregated results only
    } else {
        http_response_code(403);
        die("Access denied: invalid role.");
    }

    if (!empty($filter['month'])) {
        $where[] = "DATE_FORMAT(v.event_date, '%Y-%m') = :month";
        $params[':month'] = $filter['month'];
    }

    if (!empty($filter['event_type'])) {
        $where[] = "v.event_type = :event_type";
        $params[':event_type'] = $filter['event_type'];
    }

    $sql = "
      SELECT 
    n.id as user_id,
    v.id as event_id,
    n.name, 
            COUNT(*) AS total_present, 
            v.event_type
        FROM anwesenheits_kontrolle ak
        INNER JOIN veranstaltung v ON ak.event_id = v.id
        INNER JOIN users n ON ak.user_id = n.id
        WHERE " . implode(' AND ', $where) . "
        GROUP BY ak.user_id, v.event_type
    ";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) {
        if ($k === ':department_id') {
            $stmt->bindValue($k, (int)$v, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($k, $v);
        }
    }
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($current_user['role'] === 'viewer') {
        $summary = [];
        foreach ($results as $row) {
            $summary[$row['event_type']] = ($summary[$row['event_type']] ?? 0) + $row['total_present'];
        }
        $results = [];
        foreach ($summary as $event => $total) {
            $results[] = ['name' => 'All Users', 'event_type' => $event, 'total_present' => $total];
        }
    }
    return $results;
}

function generate_pdf($data)
{
    $pdf = new PrintPdfTabelle();
    $pdf->add_row(["User", "Total Present", "Event Type"]);
    foreach ($data as $row) {
        $pdf->add_row([$row['name'], $row['total_present'], $row['event_type']]);
    }
    $pdf->output_pdf();
}

if (!empty($_GET['download']) && $_GET['download'] === 'pdf') {
    $data = get_data($conn, $filter);
    generate_pdf($data);
    exit;
}

$data = get_data($conn, $filter);
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Attendance Summary</title>
    <link rel="stylesheet" href="https://getbootstrap.com/docs/5.0/examples/headers/headers.css">
    <link rel="stylesheet" href="https://getbootstrap.com/docs/5.0/dist/css/bootstrap.min.css">
    <script src="https://getbootstrap.com/docs/5.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">

</head>

<body class="d-flex flex-column min-vh-100">
    <?php include 'header.php'; ?>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="text-primary">Attendance Summary</h3>
            <div>
                <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#addModal">+ Add</button>
                <a href="?<?php echo http_build_query(array_merge($filter, ['download' => 'pdf'])); ?>" class="btn btn-outline-primary">Download PDF</a>
            </div>
        </div>

        <form method="get" class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label">Month</label>
                <input type="month" name="month" class="form-control" value="<?php echo htmlspecialchars($filter['month'] ?? ''); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Event Type</label>
                <input type="text" name="event_type" class="form-control" value="<?php echo htmlspecialchars($filter['event_type'] ?? ''); ?>">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>

        <table id="attendanceTable" class="table table-bordered table-striped">
            <thead class="table-primary">
                <tr>
                    <th>User</th>
                    <th>Total Present</th>
                    <th>Event Type</th>
                   <?php  if( $current_user['role'] !== 'viewer' ){ ?>
                    <th>Action</th>
                     <?php } ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['name']); ?></td>
                        <td><?= htmlspecialchars($r['total_present']); ?></td>
                        <td><?= htmlspecialchars($r['event_type']); ?></td>
                        <?php

                        if( $current_user['role'] !== 'viewer' ){ ?>

                       
                       
                        <td>
                            <button
                                class="btn btn-warning btn-sm editBtn"
                                data-bs-toggle="modal"
                                data-bs-target="#editModal"
                                data-id="<?= htmlspecialchars($r['user_id']); ?>"
                                data-user="<?= htmlspecialchars($r['user_id']); ?>"
                                data-event="<?= htmlspecialchars($r['event_type']); ?>"
                                data-status="present"> Edit </button>
                        
                            <form method="post" style="display:inline">
                                <?php echo csrf_input_field(); ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $r['user_id']; ?>">
                                <button class="btn btn-sm btn-danger" onclick="return confirm('Delete record?')">Delete</button>
                            </form>
                        </td>
                        
                        
                        <?php } ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <form class="modal-content" method="post" action="attendance_summary.php">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Add Attendance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php echo csrf_input_field(); ?>
                    <input type="hidden" name="action" id="action" value="add">
                    <input type="hidden" name="id" id="record_id">
                    <div class="mb-3">
                        <label>User Name</label>
                        <select name="user_id" class="form-select" required>
                            <option value="">-- Select User --</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= htmlspecialchars($u['id']) ?>"><?= htmlspecialchars($u['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Event Type</label>
                        <select name="event_id" class="form-select" required>
                            <option value="">-- Select Event --</option>
                            <?php foreach ($events as $e): ?>
                                <option value="<?= htmlspecialchars($e['id']) ?>"><?= htmlspecialchars($e['event_type']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Status</label>
                        <select name="status" class="form-select" required>
                            <option value="">-- Select Status --</option>
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                        </select>
                    </div>

                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-success">Save</button></div>
            </form>
        </div>
    </div>

  
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <form class="modal-content" method="post" action="attendance_summary.php">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">Edit Attendance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <?php echo csrf_input_field(); ?>
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" id="edit_id" name="id">

                    <div class="mb-3">
                        <label>User</label>
                        <select id="edit_user" name="user_id" class="form-select" required>
                            <option value="">-- Select User --</option>
                            <?php
                            $users = $conn->query("SELECT id, name FROM users ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($users as $u) {
                                echo "<option value='{$u['id']}'>{$u['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Event</label>
                        <select id="edit_event" name="event_id" class="form-select" required>
                            <option value="">-- Select Event --</option>
                            <?php
                            $events = $conn->query("SELECT id, event_type FROM veranstaltung ORDER BY event_type ASC")->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($events as $e) {
                                echo "<option value='{$e['id']}'>{$e['event_type']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Status</label>
                        <select id="edit_status" name="status" class="form-select" required>
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-warning">Update</button>
                </div>
            </form>
        </div>
    </div>


    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#attendanceTable').DataTable({
                dom: 'Bfrtip',
                buttons: ['excel', 'csv', 'pdf', 'print']
            });

            $(document).on('click', '.editBtn', function() {
                const id = $(this).data('id');
                const userId = $(this).data('user');
                const eventType = $(this).data('event');
                const status = $(this).data('status');

                $('#edit_id').val(id);

                $('#edit_user').val(userId).trigger('change');
                $('#edit_event option').filter(function() {
                    return $(this).text() === eventType; 
                }).prop('selected', true);

                $('#edit_status').val(status).trigger('change');
            });



        });

        function openAddModal() {
            document.getElementById('action').value = 'add';
            document.getElementById('record_id').value = '';
            document.getElementById('name').value = '';
            document.getElementById('event_type').value = '';
            new bootstrap.Modal(document.getElementById('recordModal')).show();
        }

        function openEditModal(id, name, eventType) {
            document.getElementById('action').value = 'edit';
            document.getElementById('record_id').value = id;
            document.getElementById('name').value = name;
            document.getElementById('event_type').value = eventType;
            new bootstrap.Modal(document.getElementById('recordModal')).show();
        }
    </script>

</body>

</html>

