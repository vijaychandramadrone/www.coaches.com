<h1>Event Eligible</h1>
<p>Event id: <?php echo $event_id ?> - <?php echo $eligible ?></p>


<h1>Courses Taken</h1>

<?php foreach(array(3,4,5,6,7) as $id){

  echo "<p>$id: ";
  echo $student->courseTakenWithin5Years( $id )?'yes':'no';
  echo "</p>";

}
?>
