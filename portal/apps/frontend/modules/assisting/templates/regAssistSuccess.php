

<h1>Assisting Registration</h1>

<p>Please confirm your selection:<p>

<p>Name: <b><?= $event->getName() ?></b><br />
   Date: <b><?= $event->getStartDate() ?></b></p>

<?php if($msg): ?>
<p style="color:#f44"><?= $msg ?></p>
<?php endif ?>


<form action="regAssist" method="POST">
<input type="hidden" name="e" value="id<?= $event_id ?>" />
<input type="submit" name="nobtn" value="No" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="submit" name="yesbtn" value="Yes, Please Register to Assist" />
</form>
