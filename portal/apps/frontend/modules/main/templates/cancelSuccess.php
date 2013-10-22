

<h1>Cancel <?php if($assist){ echo "Assisting"; } ?> Registration</h1>

<p>Are you sure you want to cancel the following course?<p>

<p>Name: <b><?= $event->getName() ?></b><br />
   Date: <b><?= $event->getStartDate() ?></b></p>

<?php if($msg): ?>
<p style="color:#f44"><?= $msg ?></p>
<?php endif ?>


<form action="cancel" method="POST">
<input type="hidden" name="e" value="id<?= $enrollment_id ?>" />
<input type="submit" name="nobtn" value="No" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="submit" name="yesbtn" value="Yes, Cancel Course" />
</form>




