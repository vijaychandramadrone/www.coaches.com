

<h1>My Schedule</h1>


<?php if($msg): ?>
<p class="alert"><?php echo $msg ?></p>
<?php endif; ?>


<form id="cancel">




   <?php $header = 0; ?>

<?php foreach($enrollments as $e): ?>
   <?php if(!$e->isCompleted() && !$e->isCanceled()): ?>

<?php if(!$header): ?>
<h4>Upcoming Events</h4>
<table class="events">
<tr>
<th>Event Name</th>
<th>Date</th>
<th>Status</th>
<th></th>
</tr>
<?php $header = 1; endif; ?>

<tr>
   <td><strong>
   <?php if($e->isAssisting()){ echo "Assisting for "; } ?>
<?= $e->getEvent()->getName() ?></strong>
   <?php if($e->getEvent()->getLocation() != ''): ?>
<br /><?php echo $e->getEvent()->getLocation() ?>
   <?php endif; ?>
</td>
   <td><?= $e->getEvent()->getStartDate() ?></td>
   <td><?= $e->getDisplayStatus() ?>
<?php if($e->eligibleToUnWaitlist()){ echo ', seat now available'; } ?>
</td>

   <td style="width:120px">

<?php if($e->eligibleToUnWaitlist()){ echo ' Click here to register<br/><input type="button" class="regassistbtn" id="id'.$e->getEvent()->getId().'" value="Register Now" /><br /><br /><br />Click here to cancel if you are no longer interested in assisting on this date<br />'; } ?>

<?php if($e->eligibleToCancel()){ echo '<input type="button" class="cancelbtn" id="id'.$e->getId().'" value="Cancel" />'; } ?>

</td>


</tr>
   <?php endif; ?>
<?php endforeach ?>


</table>


 <?php $header = 0; ?>

<?php foreach($enrollments as $e): ?>
  <?php if($e->isCompleted()): ?>

<?php if(!$header): ?>
<h4>Completed Events</h4>
<table class="events">
<tr>
<th>Event Name</th>
<th>Date</th>
<th>Status</th>
<th></th>
</tr>
<?php $header = 1; endif; ?>

<tr>


<td><?php if($e->isAssisting()){ echo "Assisting for "; } ?> <?= $e->getEvent()->getName() ?></td>
   <td><?= $e->getEvent()->getStartDate() ?></td>
   <td><?= $e->getDisplayStatus() ?></td>
   <td style="width:120px">

</td>
</tr>
 <?php endif; ?>
<?php endforeach ?>


</table>




 <?php $header = 0; ?>

<?php foreach($enrollments as $e): ?>
  <?php if($e->isCanceled()): ?>

<?php if(!$header): ?>
<h4>Canceled Events</h4>
<table class="events">
<tr>
<th>Event Name</th>
<th>Date</th>
<th>Status</th>
<th></th>
</tr>
<?php $header = 1; endif; ?>


<tr>


<td><?php if($e->isAssisting()){ echo "Assisting for "; } ?> <?= $e->getEvent()->getName() ?></td>
   <td><?= $e->getEvent()->getStartDate() ?></td>
   <td><?= $e->getDisplayStatus() ?></td>
   <td style="width:120px"></td>
</tr>
 <?php endif; ?>
<?php endforeach ?>


</table>


</form>

