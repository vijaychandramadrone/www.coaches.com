<?php

  // TO DO:  T. Beutel 11/28/2011
  // - Create portal_enroll_letter.php on webcomp
  // - Create portal_waitlist_letter.php on webcomp

class Event extends BaseEvent
{

  // ======= TRIGGER NOTIFICATIONS =======

  public function triggerWaitlistEmails( ){
    // this is triggered when someone unregisters
    // check for waitlisted students. If so, send email

    // get all enrollments with type 'waitlisted' and LastNotificationTime > 24 hours
    $c = new Criteria();
    $c->add(EnrollmentPeer::EVENT_ID, $this->getId() );
    $c->add(EnrollmentPeer::TYPE, 'waitlisted');
    $enrollments = EnrollmentPeer::doSelect( $c );

    foreach($enrollments as $enrollment){
      $enrollment->triggerWaitlistSeatAvailableLetter();
    }
  }


  // ======= OVERLOADED FIELDS =======


  public function getPublish(){
    return $this->getExtra1();
  }
  public function setPublish( $data ){
    $this->setExtra1( $data );
    //$this->save();
  }

  public function getMinimumLevel(){
    $min_level =  intval( $this->getExtra2() );
    return $min_level;
  }
  public function setMinimumLevel( $data ){
    $this->setExtra2( $data );
  }

  public function getStartDateTimestamp(){
    return $this->getExtra3();
  }
  public function setStartDateTimestamp( $data = '' ){
    if($data == ''){
      $data = $this->getStartDate();
    }
    $this->setExtra3( strtotime($data) );
  }

  public function getDescription(){
    $description = $this->getExtra4();
    if($description == '' || $this->getCourseTypeId()){
      $description = EventPeer::getDescriptionForCourseType( $this->getCourseTypeId(), $this->getName() );
    }
    return $description;
  }
  public function setDescription( $data ){
    if($this->getExtra4() !== $data){
      $this->setExtra4( $data );
    }
  }

  public function getCallTime(){
    return $this->getExtra5();
  }
  public function setCallTime( $data ){
    if($this->getExtra5() !== $data){
      $this->setExtra5( $data );
    }
  }

  public function getDialNumber(){
    return $this->getExtra6();
  }
  public function setDialNumber( $data ){
    if($this->getExtra6() !== $data){
      $this->setExtra6( $data );
    }
  }


  // ======= Enrollment Functions =======

  // NOTE: actual enrollment is updated from FileMaker every 15 minutes... this is just to keep enrollment up to date until next update
  public function incrementCurrentEnrollment(){
    $new_enrollment = $this->getCurrentEnrollment() + 1;
    $this->setCurrentEnrollment( $new_enrollment );
    $this->save();
  }

  // NOTE: actual enrollment is updated form FileMaker every 15 minutes... this is just to keep enrollment up to date until next update
  public function decrementCurrentEnrollment(){
    $new_enrollment = $this->getCurrentEnrollment() - 1;
    if($new_enrollment < 1){
      $new_enrollment = 0;
    }
    $this->setCurrentEnrollment( $new_enrollment );
    $this->save();
  }

  // NOTE: actual enrollment is updated from FileMaker every 15 minutes... this is just to keep enrollment up to date until next update
  public function incrementCurrentAssistingEnrollment(){
    $new_enrollment = $this->getCurrentAssistingEnrollment() + 1;
    $this->setCurrentAssistingEnrollment( $new_enrollment );
    $this->save();
  }

  // NOTE: actual enrollment is updated form FileMaker every 15 minutes... this is just to keep enrollment up to date until next update
  public function decrementCurrentAssistingEnrollment(){
    $new_enrollment = $this->getCurrentAssistingEnrollment() - 1;
    if($new_enrollment < 1){
      $new_enrollment = 0;
    }
    $this->setCurrentAssistingEnrollment( $new_enrollment );
    $this->save();
  }



  public function isAssistingShouldBeWaitlisted( $student ){
    // determine whether this enrollment should be put on waitlist (false = no, true = yes)

    // if course has 3 or more assistants, then student should be waitlisted
    if($this->getCurrentAssistingEnrollment() >= 3){
      return true;
    }


    // if LP student, return false (LP students can enroll for assisting at any time)
    if($student->getLeadershipProgram() == 1){
      return false;
    }

    // calculate how many days to start date
    $start_time = strtotime($this->getStartDate()); // event start date
    $today = strtotime('today');
    $num_of_days = ($start_time - $today) / 86400; // number of days from start date

    // if delta < 31 days, return false (everyone can enroll within 30 days)
	// Ticket # 282 - changed 30 days to 45 days
    if($num_of_days < 46){
      return false;
    }

    // if CPCC and delta is < 46 days, return false (CPCC students can enroll within 45 days)
	// Ticket # 282 - changed 45 days to 60 days
    if($num_of_days < 61 && $student->getCPCCCertDate() != ''){
      return false;
    }

    // else true, this student should be waitlisted
    return true;
  }

  // =======  Display Functions =======


  public function getDisplayStatus(){ // enrolled, waitlisted, completed
    return '----';
  }

  public function isWaitlisted(){
    if( ($this->getCurrentEnrollment()) >= $this->getMaxEnrollment() ){
      return true;
    }
    return false;
  }
}
