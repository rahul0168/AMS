<?php
require_once "db.php";
require_once "auth.php";
require_once "csrf.php";
require_once "helpers.php";

require_login();
$current_user = current_user();
$can_edit = in_array($current_user['role'], ['admin', 'manager']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $can_edit) {
    verify_csrf_or_die($_POST['csrf_token'] ?? '');
    $action = $_POST['action'] ?? '';

    $name = sanitize_text($_POST['name'] ?? '');
    $type = sanitize_text($_POST['event_type'] ?? '');
    $date = $_POST['event_date'] ?? null;

    if ($action === 'create') {
        $stmt = $conn->prepare("INSERT INTO veranstaltung (name, event_type, event_date) VALUES (:name, :type, :date)");
        $stmt->execute([':name' => $name, ':type' => $type, ':date' => $date]);
        echo "<script>
            alert(' Event added successfully');
            window.location.href = 'events_crud.php';
        </script>";
        exit;
    } elseif ($action === 'update') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE veranstaltung SET name=:name, event_type=:type, event_date=:date WHERE id=:id");
        $stmt->execute([':name' => $name, ':type' => $type, ':date' => $date, ':id' => $id]);
        echo "<script>
            alert(' Event update successfully');
            window.location.href = 'events_crud.php';
        </script>";
        exit;
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("DELETE FROM veranstaltung WHERE id=:id");
        $stmt->execute([':id' => $id]);
        echo "<script>
            alert(' Event delete successfully');
            window.location.href = 'events_crud.php';
        </script>";
    }

    header('Location: events_crud.php');
    exit;
}

$stmt = $conn->query("SELECT * FROM veranstaltung ORDER BY event_date DESC LIMIT 200");
$events = $stmt->fetchAll();
?>

<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Events CRUD</title>
    <link rel="stylesheet" href="https://getbootstrap.com/docs/5.0/examples/headers/headers.css">
    <link rel="stylesheet" href="https://getbootstrap.com/docs/5.0/dist/css/bootstrap.min.css">
    <script src="https://getbootstrap.com/docs/5.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body class="">
    <?php include 'header.php'; ?>

    <div class="container">

        <h2 class="mb-4">Events</h2>

        <?php if ($can_edit): ?>
            <button class="btn btn-primary mb-3" onclick="openModal()">Add Event</button>
        <?php endif; ?>
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

        <table id="eventsTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Date</th>
                    <?php if ($can_edit) echo "<th>Action</th>"; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($events as $key => $e): ?>
                    <tr>
                        <td><?php echo $key + 1 ?></td>
                        <td><?php echo htmlspecialchars($e['name']); ?></td>
                        <td><?php echo htmlspecialchars($e['event_type']); ?></td>
                        <td><?php echo htmlspecialchars($e['event_date']); ?></td>
                        <?php if ($can_edit): ?>
                            <td>
                                <button class="btn btn-sm btn-warning"
                                    onclick='openModal(<?php echo json_encode($e); ?>)'>Edit</button>

                                <form method="post" style="display:inline">
                                    <?php echo csrf_input_field(); ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $e['id']; ?>">
                                    <button class="btn btn-sm btn-danger" type="submit" onclick="return confirm('Delete?')">Delete</button>
                                </form>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" id="eventForm">
                    <?php echo csrf_input_field(); ?>
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Add Event</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" id="formAction" value="create">
                        <input type="hidden" name="id" id="eventId">

                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" id="eventName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <input type="text" class="form-control" name="event_type" id="eventType">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" name="event_date" id="eventDate" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="modalSubmit">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<!-- Add DataTables JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- Initialize DataTable -->
<script>
$(document).ready(function() {
    $('#eventsTable').DataTable({
        responsive: true,
        order: [[0, 'asc']], // Sort by first column
        pageLength: 10,
        language: {
            search: "Search events:",
            lengthMenu: "Show _MENU_ entries per page"
        }
    });
});
</script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openModal(event = null) {
            const modalTitle = document.getElementById('modalTitle');
            const formAction = document.getElementById('formAction');
            const eventId = document.getElementById('eventId');
            const eventName = document.getElementById('eventName');
            const eventType = document.getElementById('eventType');
            const eventDate = document.getElementById('eventDate');

            if (event) {
                modalTitle.textContent = 'Edit Event';
                formAction.value = 'update';
                eventId.value = event.id;
                eventName.value = event.name;
                eventType.value = event.event_type;
                eventDate.value = event.event_date;
            } else {
                modalTitle.textContent = 'Add Event';
                formAction.value = 'create';
                eventId.value = '';
                eventName.value = '';
                eventType.value = '';
                eventDate.value = '';
            }

            const eventModal = new bootstrap.Modal(document.getElementById('eventModal'));
            eventModal.show();
        }
    </script>
</body>

</html>