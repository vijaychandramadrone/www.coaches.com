

<h1>New Assisting Registration</h1>

<form id="cancel">
<table class="events">
<tr>
<th>Event Name</th>
<th>Date</th>
<th>Status</th>
<th></th>
</tr>

<?php foreach($events as $e): ?>
<tr>
<td><?= $e->getName() ?></td>
<td><?= $e->getStartDate() ?></td>

<?php if($e->isWaitlisted() == true): ?>
<td>Waitlist [<a class="info-popup" href="waitlistInfo">Info</a>]</td>
<td><input type="button" class="regassistbtn" id="id<?php echo $e->getId(); ?>" value="Register to Assist" /></td>

<?php else: ?>
<td>Available [<a class="info-popup" href="registerInfo">Info</a>]</td>
<td><input type="button" class="regassistbtn" id="id<?php echo $e->getId(); ?>" value="Register to Assist" /></td>
<?php endif; ?>

</tr>
<?php endforeach ?>


</table>
</form>

