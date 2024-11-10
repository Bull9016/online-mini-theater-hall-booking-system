<?php 
require_once('./config.php');

if (isset($_GET['id'])) {
    $qry = $conn->query("SELECT b.*, h.name as `hall` FROM `booking_list` b INNER JOIN `hall_list` h ON b.hall_id = h.id WHERE b.id = {$_GET['id']}");
    if ($qry->num_rows > 0) {
        $res = $qry->fetch_array();
        foreach ($res as $k => $v) {
            if (!is_numeric($k)) {
                $$k = $v;
            }
        }
        $services_ids = explode(',', str_replace("|", "", $services_ids));
    }
}
?>

<div class="container-fluid">
    <form id="book-form" method="POST">
        <input type="hidden" name="id" value="<?= isset($id) ? htmlspecialchars($id) : '' ?>">
        <input type="hidden" name="client_id" value="<?= htmlspecialchars($_settings->userdata('id')) ?>">
        
        <div class="row">
            <div class="col-md-12 form-group">
                <label for="hall_id" class="control-label">Hall</label>
                <select name="hall_id" id="hall_id" class="form-control form-control-sm form-control-border select2" required>
                    <option value="" disabled="disabled" <?= !isset($hall_id) ? 'selected' : '' ?>></option>
                    <?php 
                    $hall = $conn->query("SELECT * FROM `hall_list` WHERE delete_flag = 0 AND status = 1 ".(isset($hall_id) ? " OR id = '{$hall_id}'" : "")." ORDER BY `name` ASC");
                    while ($row = $hall->fetch_assoc()):
                    ?>
                        <option value="<?= htmlspecialchars($row['id']) ?>"><?= htmlspecialchars($row['code']) . " - " . htmlspecialchars($row['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 form-group">
                <label for="services_ids" class="control-label">Services</label>
                <select name="services_ids[]" id="services_ids" class="form-control form-control-sm form-control-border select2" multiple required>
                    <?php 
                    $service = $conn->query("SELECT * FROM `service_list` WHERE delete_flag = 0 AND status = 1 ".(isset($services_ids) ? " OR id IN (".(implode(',', $services_ids)).")" : "")." ORDER BY `name` ASC");
                    while ($row = $service->fetch_assoc()):
                    ?>
                        <option value="<?= htmlspecialchars($row['id']) ?>" <?= isset($services_ids) && in_array($row['id'], $services_ids) ? "selected" : '' ?>><?= htmlspecialchars($row['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 form-group">
                <label for="wedding_schedule" class="control-label">Event Date</label>
                <input type="date" id="wedding_schedule" name="wedding_schedule" value="<?= isset($wedding_schedule) ? htmlspecialchars($wedding_schedule) : "" ?>" class="form-control form-control-sm form-control-border" required>
            </div>
            <div class="col-md-6 form-group">
                <label for="from_time" class="control-label">From time:</label>
                <input type="time" id="from_time" name="from_time" value="<?= isset($from_time) ? htmlspecialchars($from_time) : "" ?>" class="form-control form-control-sm form-control-border" required>
            </div>
            <div class="col-md-6 form-group">
                <label for="to_time" class="control-label">To time:</label>
                <input type="time" id="to_time" name="to_time" value="<?= isset($to_time) ? htmlspecialchars($to_time) : "" ?>" class="form-control form-control-sm form-control-border" required>
            </div>
            <div class="col-md-6 form-group">
                <label for="time_difference" class="control-label">Time Difference:</label>
                <div id="time_difference" class="form-control form-control-sm form-control-border" readonly></div>
                <dt class="text-muted">Price</dt>
                <div id="hall_price" class="form-control form-control-sm form-control-border" readonly></div>
            </div>
            < div class="col-md-12 form-group">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </div>
    </form>
</div>

<script>
    document.getElementById('from_time').addEventListener('change', calculateTimeDifference);
    document.getElementById('to_time').addEventListener('change', calculateTimeDifference);

    function calculateTimeDifference() {
        const fromTime = document.getElementById('from_time').value;
        const toTime = document.getElementById('to_time').value;
        if (fromTime && toTime) {
            const from = new Date(`1970-01-01T${fromTime}:00`);
            const to = new Date(`1970-01-01T${toTime}:00`);
            const difference = (to - from) / 1000 / 60; // difference in minutes
            document.getElementById('time_difference').innerText = difference + ' minutes';
            const pricePerMinute = 10; // Example price per minute
            document.getElementById('hall_price').innerText = '$' + (difference * pricePerMinute);
        }
    }

    document.getElementById('book-form').addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent the default form submission

        const formData = new FormData(this);
        fetch('submit_booking.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Booking submitted successfully!');
                // Optionally, you can redirect or reset the form here
                this.reset();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while submitting the form.');
        });
    });
</script>