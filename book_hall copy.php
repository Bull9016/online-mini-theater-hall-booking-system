<?php 
require_once('./config.php');
if(isset($_GET['id'])){
    $qry = $conn->query("SELECT b.*,h.name as `hall` from `booking_list` b inner join `hall_list` h on b.hall_id = h.id where b.id in ({$_GET['id']}) ");
    if($qry->num_rows > 0){
        $res = $qry->fetch_array();
        foreach($res as $k => $v){
            if(!is_numeric($k))
                $$k = $v;
        }
        $services_ids = explode(',', str_replace("|","",$services_ids));
    }
}
?>
<div class="container-fluid">
    <form action="" id="book-form">
        <input type="hidden" name="id" value="<?= isset($id) ? $id : '' ?>">
        <input type="hidden" name="client_id" value="<?= $_settings->userdata('id') ?>">
        <div class="row">
            <div class="col-md-12 form-group">
                <label for="hall_id" class="control-label">Hall</label>
                <select name="hall_id" id="hall_id" class="form-control form-control-sm form-control-border select2" required>
                    <option value="" disabled="disabled" <?= !isset($hall_id) ? 'selected' : '' ?>></option>
                    <?php 
                    $hall = $conn->query("SELECT * FROM `hall_list` where delete_flag = 0 and status = 1 ".(isset($hall_id) ? " or id = '{$hall_id}'" : "")." order by `name` asc");
                    while($row = $hall->fetch_assoc()):
                    ?>
                        <option value="<?= $row['id'] ?>"><?= $row['code']. " - " .$row['name'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 form-group">
                <label for="services_ids" class="control-label">Services</label>
                <select name="services_ids[]" id="services_ids" class="form-control form-control-sm form-control-border select2" multiple required>
                    <?php 
                    $service = $conn->query("SELECT * FROM `service_list` where delete_flag = 0 and status = 1 ".(isset($services_ids) ? " or id in (".(implode(',',$services_ids)).")" : "")." order by `name` asc");
                    while($row= $service->fetch_assoc()):
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
                <label for="time_difference" class="control-label">Time Difference:</label>
                <div id="time_difference" class="form-control form-control-sm form-control-border" readonly></div>
                <dt class="text-muted">Price</dt>
                <div id="hall_price" readonly></div>
                <script>
        $(document).ready(function() {
            $('#hall_id').change(function() {
                var hallId = $(this).val();
                if (hallId) {
                    $.ajax({
                        url: 'fetch_hall_price.php',
                        type: 'POST',
                        data: { id: hallId },
                        success: function(data) {
                            $('#hall_price').html(data);
                        },
                        error: function(xhr, status, error) {
                            $('#hall_price').html('<p>An error occurred: ' + error + '</p>');
                        }
                    });
                } else {
                    $('#hall_price').html('');
                }
            });
        });
    </script><!-- <script><?php
// fetch_price.php

// // Include the database connection
// include('DBConnectio.php');

// // Check if the hall_id is set
// if (isset($_POST['hall_id'])) {
//     $hall_id = $_POST['hall_id'];

//     // Prepare and execute the SQL query
//     $stmt = $conn->prepare("SELECT price FROM hall_list WHERE id = ?");
//     $stmt->bind_param("i", $hall_id);
//     $stmt->execute();
//     $stmt->bind_result($price);
//     $stmt->fetch();

//     // Return the price in JSON format
//     echo json_encode(['price' => $price]);

//     // Close the statement and connection
    
// }
//$conn->close();
//?>
</script> -->
            </div>
            <div class="col-md-6 form-group">
                <label for="total_guests" class="control-label">Total Guests</label>
                <input type="number" id="total_guests" name="total_guests" value="<?= isset($total_guests) ? $total_guests : "" ?>" class="form-control form-control-sm form-control-border text-right" required>
            </div>
            <div class="col-md-6 form-group">
                <label for="total_price" class="control-label">Total Price</label>
                <div id="total_price" class="form-control form-control-sm form-control-border" readonly></div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 form-group">
                <label for="remarks" class="control-label">Remarks</label>
                <textarea name="remarks" id="remarks" class="form-control form-control-sm rounded-0" rows="3" required><?= isset($remarks) ? $remarks : "" ?></textarea>
            </div>
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
                    const diff = new Date(to - from);
                    const hours = diff.getUTCHours();
                    const minutes = diff.getUTCMinutes();
                    $('#time_difference').text(`${hours} hour(s) and ${minutes} minute(s)`);
                } else {
                    $('#time_difference').text("Invalid time range");
                }
            } else {
                $('#time_difference').text("");
            }
        }

        $('#from_time, #to_time').on('change', calculateTimeDifference);
        
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
