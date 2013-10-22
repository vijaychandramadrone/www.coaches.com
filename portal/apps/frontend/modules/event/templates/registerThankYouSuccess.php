


<h1>Thank You!</h1>

 
<p>You have been registered<p>

<?php if(isset($event)): ?>
<p>Name: <b><?= $event->getName() ?></b><br />
   Date: <b><?= $event->getStartDate() ?></b><br />
   <?php if($event->getCallTime() != ''): ?>
   Call Time: <b><?= $event->getCallTime() ?></b><br />
   <?php endif; ?>
   <?php if($event->getLocation() != '' && $event->getLocation() != 'Bridge'): ?>
   Location: <b><?= $event->getLocation() ?></b><br />
   <?php endif; ?>
</p>
<?php endif; ?>

<p><a href="<?php echo url_for('main/mySchedule') ?>">Return to my schedule</a></p>




