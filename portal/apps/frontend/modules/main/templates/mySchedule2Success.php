

<h1>My Schedule</h1>


<?php if($msg): ?>
<p class="alert"><?php echo $msg ?></p>
<?php endif; ?>


<form id="cancel">


<h2>Upcomings Events</h2>
<table class="events">
<tr>
<th>Event Name</th>
<th>Date</th>
<th>Status</th>
<th></th>
</tr>



<?php foreach($enrollments as $e): ?>
   <?php if(!$e->isCompleted()): ?>
<tr>
   <td><?= $e->getEvent()->getName() ?></td>
   <td><?= $e->getEvent()->getStartDate() ?></td>
   <td><?= $e->getDisplayStatus() ?></td>
   <td style="width:120px"><?php if($e->eligibleToCancel()){ echo '<input type="button" class="cancelbtn" id="id'.$e->getId().'" value="Cancel" />'; } ?></td>
</tr>
   <?php endif; ?>
<?php endforeach ?>


</table>

<h2>Completed Events</h2>
<table class="events">
<tr>
<th>Event Name</th>
<th>Date</th>
<th>Status</th>
<th></th>
</tr>



<?php foreach($enrollments as $e): ?>
  <?php if($e->isCompleted()): ?>
<tr>
   <td><?= $e->getEvent()->getName() ?></td>
   <td><?= $e->getEvent()->getStartDate() ?></td>
   <td><?= $e->getDisplayStatus() ?></td>
   <td style="width:120px"><?php if($e->eligibleToCancel()){ echo '<input type="button" class="cancelbtn" id="id'.$e->getId().'" value="Cancel" />'; } ?></td>
</tr>
 <?php endif; ?>
<?php endforeach ?>


</table>

</form>

