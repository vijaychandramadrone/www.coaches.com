

<h1>Registration</h1>

<p>Please confirm your selection:<p>

<p>Name: <b><?= $event->getName() ?></b><br />
   Date: <b><?= $event->getStartDate() ?></b><br />
   <?php if($event->getCallTime() != ''): ?>
   Call Time: <b><?= $event->getCallTime() ?></b><br />
   <?php endif; ?>
   <?php if($event->getLocation() != '' && $event->getLocation() != 'Bridge'): ?>
   Location: <b><?= $event->getLocation() ?></b><br />
   <?php endif; ?>

</p>

<?php if($msg): ?>
<p style="color:#f44"><?= $msg ?></p>
<?php endif ?>


<form action="register" method="POST">
<input type="hidden" name="e" value="id<?= $event_id ?>" />
<input type="submit" name="nobtn" value="No" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
   <?php if($event->isWaitlisted() ): ?>
<input type="submit" name="yesbtn" value="Yes, Please add me to waitlist" />
   <?php else: ?>
<input type="submit" name="yesbtn" value="Yes, Please Register" />
   <?php endif; ?>
</form>
