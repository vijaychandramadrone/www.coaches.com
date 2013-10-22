

<h1>Thank You!</h1>

<p>You have been <?= $reg_action ?> in the following course. 
   <?php if(preg_match('/reg/',$reg_action)): ?>
As an assistant you must arrive one (1) hour early. Please refer to your CTI registration confirmation email for additional information.
   <?php endif; ?>
<p>

<p>Name: <b><?= $event->getName() ?></b><br />
   Date: <b><?= $event->getStartDate() ?></b><br />
  <?php if($event->getLocation() != '' && $event->getLocation() != 'Bridge'): ?>
   Location: <b><?= $event->getLocation() ?></b><br />
   <?php endif; ?>

</p>

<p><a href="<?php echo url_for('main/mySchedule') ?>">Return to my schedule</a></p>




