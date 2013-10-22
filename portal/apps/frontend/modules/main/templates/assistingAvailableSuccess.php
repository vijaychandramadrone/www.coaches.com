<h1>Assisting Available</h1>

<?php foreach($events as $e): ?>
<p><strong><?php echo $e->getName().' '.$e->getStartDate().' '.$e->getCurrentAssistingEnrollment(); ?></strong><br />
<?php
$enrollments = EnrollmentPeer::getAssistantsWaiting( $e );
foreach($enrollments as $n){
  $student = $n->getStudent();
  echo 'Student: '.$student->getEmail().' '.$student->getId().' '.$n->getType();
  if($e->isAssistingShouldBeWaitlisted( $student )){
    echo ' should remain waitlisted ';
  } else {
    echo ' can enroll for assisting ';
  }
  echo "<br />";
  
}
?>
</p>
<?php endforeach; ?>



