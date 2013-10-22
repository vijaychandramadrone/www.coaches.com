

<h1>Registration Cancelled</h1>


<?php if($msg): ?>
<p class="alert"><?php echo $msg ?></p>
<?php else: ?>
Your registration  or waitlist status has been cancelled.
<?php endif; ?>


<p>&raquo; Return to <a href="<?php echo url_for('main/mySchedule'); ?>">My Schedule</a></p>



