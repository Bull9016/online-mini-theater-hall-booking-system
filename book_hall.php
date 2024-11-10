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
    <form action="" id="book-form" enctype="multipart/form-data"> <!-- Added enctype here -->
        <input type="hidden" name="id" value="<?= isset($id) ? $id : '' ?>">
        <input type="hidden" name="client_id" value="<?= $_settings->userdata('id') ?>">
        <div class="row">
            <div class="col-md-12 form-group">
                <label for="hall_id" class="control-label">Hall</label>
                <select name="hall_id" id="hall_id" class="form-control form-control-sm form-control-border select2" required>
                    <option value="" disabled="disabled" <?= !isset($hall_id) ? 'selected' : '' ?>></option>
                    <?php 
                    $hall = $conn->query("SELECT * FROM `hall_list` WHERE delete_flag = 0 AND status = 1 ".(isset($hall_id) ? " OR id = '{$hall_id}'" : "")." ORDER BY `name` ASC");
                    while ($row = $hall->fetch_assoc()):
                    ?>
                        <option value="<?= $row['id'] ?>"><?= $row['code'] . " - " . $row['name'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 form-group">
                <label for="services_ids" class="control-label">Services</label>
                <select name="services_ids[]" id="services_ids" class="form-control form-control-sm form-control-border select2" >
                    <?php 
                    $service = $conn->query("SELECT * FROM `service_list` WHERE delete_flag = 0 AND status = 1 ".(isset($services_ids) ? " OR id IN (".(implode(',', $services_ids)).")" : "")." ORDER BY `name` ASC");
                    while ($row = $service->fetch_assoc()):
                    ?>
                        <option value="<?= $row['id'] ?>" <?= isset($services_ids) && in_array($row['id'], $services_ids) ? "selected" : '' ?>><?= $row['name'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 form-group">
                <label for="wedding_schedule" class="control-label">Event Date</label>
                <input type="date" id="wedding_schedule" name="wedding_schedule" value="<?= isset($wedding_schedule) ? $wedding_schedule : "" ?>" class="form-control form-control-sm form-control-border" required>
            </div>
            <div class="col-md-6 form-group">
                <label for="from_time" class="control-label">From time:</label>
                <input type="time" id="from_time" name="from_time" value="<?= isset($from_time) ? $from_time : "" ?>" class="form-control form-control-sm form-control-border" required>
            </div>
            <div class="col-md-6 form-group">
                <label for="to_time" class="control-label">To time:</label>
                <input type="time" id="to_time" name="to_time" value="<?= isset($to_time) ? $to_time : "" ?>" class="form-control form-control-sm form-control-border" required>
            </div>
            <div class="col-md-6 form-group">
<div class="col-md-6 form-group">
    <label for="time_difference" class="control-label">Time Difference:</label>
    <div id="time_difference" class="form-control form-control-sm form-control-border" readonly></div>
    <input type="hidden" id="time_difference_input" name="time_difference" value="">
</div>
                <dt class="text-muted">Price</dt>
                <div id="hall_price" class="form-control form-control-sm form-control-border" readonly></div>
            </div>
            <div class="col-md-6 form-group">
                <label for="total_guests" class="control-label">Total Guests</label>
                <input type="number" id="total_guests" name="total_guests" value="<?= isset($total_guests) ? $total_guests : "" ?>" class="form-control form-control-sm form-control-border text-right" required>
            </div>
         <div class="col-md-6 form-group">
    <label for="total_price" class="control-label">Total Price</label>
    <div id="total_price_display" class="form-control form-control-sm form-control-border" readonly></div>
    <input type="hidden" id="total_price" name="total_price" value="<?= isset($total_price) ? $total_price : "" ?>">
</div>
        </div>
        <div class="row">
            <div class="col-md-6 form-group">
                <label for="payment_ss_path" class="control-label">Payment Screenshot:</label> <!-- Corrected label -->
                <input type="file" id="payment_ss_path" name="payment_ss_path" class="form-control form-control-sm form-control-border text-right" required> <!-- Ensure required attribute is present -->
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 form-group">
                <label for="remarks" class="control-label">Remarks</label>
                <textarea name="remarks" id="remarks" class="form-control form-control-sm rounded-0" rows="3" required><?= isset($remarks) ? $remarks : "" ?></textarea>
            </div>
        </div>
        <!-- <div class="col-md-6 form-group">
                <label for="total_guests" class="control-label">Total Guests</label>
                <input type="file" id="total_guests" name="total_guests" value="<?= isset($total_guests) ? $total_guests : "" ?>" class="form-control form-control-sm form-control-border text-right" required>
            </div> -->
               <div class="col-md-6 form-group"> 
                <label for="total_guests" class="control-label">Transactioon id</label>
                <input type="text" id="Transax_id" name="Transax_id" value="<?= isset($Transax_id) ? $Transax_id : "" ?>" class="form-control form-control-sm form-control-border text-right" required>
            </div> 
    </form>
</div>

<script>
    $(function(){
        $('#uni_modal').on('shown.bs.modal', function(){
            $('.select2').select2({
                placeholder: "Please select here",
                width: "100%",
                dropdownParent: $('#uni_modal')
            });
        });

        $('#uni_modal').trigger('shown.bs.modal');

        // Calculate time difference and validate
function calculateTimeDifference() {
    const fromTime = $('#from_time').val();
    const toTime = $('#to_time').val();
    
    if (fromTime && toTime) {
        const from = new Date('1970-01-01T' + fromTime + 'Z');
        const to = new Date('1970-01-01T' + toTime + 'Z');
        
        if (to > from) {
            const diff = (to - from) / 1000; // difference in seconds
            const hours = Math.floor(diff / 3600);
            const minutes = Math.floor((diff % 3600) / 60);
            $('#time_difference').text(`${hours} hour(s) and ${minutes} minute(s)`);
            
            // Update the hidden input with the total time difference in minutes
            const totalMinutes = (hours * 60) + minutes;
            $('#time_difference_input').val(totalMinutes); // Store the total minutes in the hidden input
            
            return { hours, minutes };
        } else {
            $('#time_difference').text("Invalid time range");
            $('#time_difference_input').val(''); // Clear hidden input if invalid
            return null;
        }
    } else {
        $('#time_difference').text("");
        $('#time_difference_input').val(''); // Clear hidden input if no time is set
        return null;
    }
}

        $('#from_time, #to_time').on('change', function() {
            const timeDiff = calculateTimeDifference();
            updateTotalPrice(timeDiff);
        });
        
        $('#hall_id').change(function() {
            var hallId = $(this).val();
            if (hallId) {
                $.ajax({
                    url: 'fetch_hall_price.php',
                    type: 'POST',
                    data: { id: hallId },
                    success: function(data) {
                        $('#hall_price').html(data);
                        const timeDiff = calculateTimeDifference();
                        updateTotalPrice(timeDiff);
                    },
                    error: function(xhr, status, error) {
                        $('#hall_price').html('<p>An error occurred: ' + error + '</p>');
                    }
                });
            } else {
                $('#hall_price').html('');
                $('#total_price').text('');
            }
        });

    function updateTotalPrice(timeDiff) {
    const pricePerHour = parseFloat($('#hall_price').text().replace(/[^0-9.]/g, '').replace(/,/g, '')); // Extract price from response
    if (timeDiff) {
        const totalHours = timeDiff.hours + (timeDiff.minutes / 60);
        const totalPrice = pricePerHour * totalHours; 
        $('#total_price_display').text(`INR${totalPrice.toFixed(4)}`);
        $('#total_price').val(totalPrice.toFixed(4)); // Update hidden input
    }
}
        $('#uni_modal #book-form').submit(function(e){
            e.preventDefault();
            $('.pop-msg').remove();

            // Validate that "To" time is later than "From" time
            const fromTime = $('#from_time').val();
            const toTime = $('#to_time').val();

            if (toTime <= fromTime) {
                const errorEl = $('<div>').addClass("pop-msg alert alert-danger").text("'To Time' must be later than 'From Time'.");
                $(this).prepend(errorEl);
                errorEl.show('slow');
                return; // Stop form submission
            }

            var _this = $(this);
            var el = $('<div>').addClass("pop-msg alert").hide();

            start_loader();
            $.ajax({
                url: _base_url_ + "classes/Master.php?f=save_book",
                data: new FormData(this),
                cache: false,
                contentType: false,
                processData: false,
                method: 'POST',
                type: 'POST',
                dataType: 'json',
                error: err => {
                    console.log(err);
                    alert_toast("An error occurred", 'error');
                    end_loader();
                },
                success: function(resp){
                    if (resp.status == 'success') {
                        location.href = './?page=my_bookings';
                    } else if (!!resp.msg) {
                        el.addClass("alert-danger").text(resp.msg);
                        _this.prepend(el);
                    } else {
                        el.addClass("alert-danger").text("An error occurred due to an unknown reason.");
                        _this.prepend(el);
                    }
                    el.show('slow');
                    $('html, body, .modal').animate({ scrollTop: 0 }, 'fast');
                    end_loader();
                }
            });
        });
    });
</script>
