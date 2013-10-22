<!--

<?php echo "Level: ".$student->getLevel(); ?>

-->
<h1>New Event Registration</h1>

<form id="cancel">






    <?php $header = 0; ?>
<?php foreach($corp_labs as $e): ?>

     <?php if($header == 0): ?>
<p>&nbsp;</p>
<h4>Corporate Teleseries Calls</h4>
<table class="events" style="width:600px;">
<tr>
<th>Event Name</th>
<th style="width:160px;">Date</th>
<th>Status</th>
<th></th>
</tr>
    <?php $header = 1; endif; ?>

<tr>
   <td><?= $e->getName() ?> [<a class="info-popup" href="<?php echo url_for('event/eventInfo?id='.$e->getId()); ?>">Info</a>]</td>
<td><?= $e->getStartDate() ?>
<?php if($e->getCallTime() != ''): ?>
<br /><?php echo $e->getCallTime(); ?>
<?php endif; ?>
</td>

<?php if($e->isWaitlisted() == true): ?>
<td>Waitlist [<a class="info-popup" href="<?php echo url_for('event/waitlistInfo') ?>">Info</a>]</td>
<td><input type="button" class="regbtn" id="id<?php echo $e->getId(); ?>" value="Register Now" /></td>

<?php else: ?>
<td>Available [<a class="info-popup" href="<?php echo url_for('event/registerInfo') ?>">Info</a>]</td>
<td><input type="button" class="regbtn" id="id<?php echo $e->getId(); ?>" value="Register Now" /></td>
<?php endif; ?>

</tr>
<?php endforeach ?>
</table>




<?php $header = 0; ?>
<?php foreach($learning_labs as $e): ?>

     <?php if($header == 0): ?>
<p>&nbsp;</p>
<h4>Learning Lab Calls</h4>
<table class="events" style="width:600px;">
<tr>
<th>Event Name</th>
<th style="width:160px;">Date</th>
<th>Status</th>
<th></th>
</tr>
    <?php $header = 1; endif; ?>


<tr>
   <td><?= $e->getName() ?> [<a class="info-popup" href="<?php echo url_for('event/eventInfo?id='.$e->getId()); ?>">Info</a>]</td>
<td><?= $e->getStartDate() ?>
<?php if($e->getCallTime() != ''): ?>
<br /><?php echo $e->getCallTime(); ?>
<?php endif; ?>
</td>

<?php if($e->isWaitlisted() == true): ?>
<td>Waitlist [<a class="info-popup" href="<?php echo url_for('event/waitlistInfo') ?>">Info</a>]</td>
<td><input type="button" class="regbtn" id="id<?php echo $e->getId(); ?>" value="Register Now" /></td>

<?php else: ?>
<td>Available [<a class="info-popup" href="<?php echo url_for('event/registerInfo') ?>">Info</a>]</td>
<td><input type="button" class="regbtn" id="id<?php echo $e->getId(); ?>" value="Register Now" /></td>
<?php endif; ?>

</tr>
<?php endforeach ?>
</table>



<?php $header = 0; ?>
<?php foreach($cae_labs as $e): ?>

     <?php if($header == 0): ?>
<p>&nbsp;</p>
<h4>Co-Active Entrepreneur Calls</h4>
<table class="events" style="width:600px;">
<tr>
<th>Event Name</th>
<th style="width:160px;">Date</th>
<th>Status</th>
<th></th>
</tr>
    <?php $header = 1; endif; ?>


<tr>
   <td><?= $e->getName() ?> [<a class="info-popup" href="<?php echo url_for('event/eventInfo?id='.$e->getId()); ?>">Info</a>]</td>
<td><?= $e->getStartDate() ?>
<?php if($e->getCallTime() != ''): ?>
<br /><?php echo $e->getCallTime(); ?>
<?php endif; ?>
</td>

<?php if($e->isWaitlisted() == true): ?>
<td>Waitlist [<a class="info-popup" href="<?php echo url_for('event/waitlistInfo') ?>">Info</a>]</td>
<td><input type="button" class="regbtn" id="id<?php echo $e->getId(); ?>" value="Register Now" /></td>

<?php else: ?>
<td>Available [<a class="info-popup" href="<?php echo url_for('event/registerInfo') ?>">Info</a>]</td>
<td><input type="button" class="regbtn" id="id<?php echo $e->getId(); ?>" value="Register Now" /></td>
<?php endif; ?>

</tr>
<?php endforeach ?>
</table>








</form>

