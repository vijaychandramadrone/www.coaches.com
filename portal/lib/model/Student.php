<?php
@require_once "Mail.php"; // needed because corp9 does not have local SMTP

// NEXT STEPS
// - 
// - (_DONE_) update recordIsOld() to check account age



class Student extends BaseStudent
{
  //This variable is used in eventIdIsEligible(..) to store the proper message when eligibility check fail.
  private $eventEligibilityStatusMsg = "";
  
  // ======= Extra fields =======

  public function setSync( $v ){ // used for syncing account with FileMaker. 'yes' or null = synced
    $this->setExtra2($v);
  }
  public function getSync(){
    $sync = $this->getExtra2();
    if($sync == null){
      $sync = 'yes';
    }
    return $sync;
  }

  public function setAccountAge( $v ){
    $this->setExtra3( $v );
  }

  public function getAccountAge(){
    return $this->getExtra3();
  }


  public function setLeadershipProgram( $v ){
    if($this->getExtra4() !== $v){
      $this->setExtra4( $v );
    }
  }

  public function getLeadershipProgram(){
    return $this->getExtra4();
  }


  public function setCPCCCertDate( $v ){
    if($this->getExtra5() !== $v){
      $this->setExtra5( $v );
    }
  }

  public function getCPCCCertDate(){
    return $this->getExtra5();
  }


  public function isAdmin(){
    if($this->getExtra6() == 'admin'){
      return TRUE;
    }
    return FALSE;
  }

  public function setCourseHistory( $v ){
    if($this->getExtra7() !== $v){
      $this->setExtra7( $v );
    }
  }

  public function getCourseHistory(){
    return $this->getExtra7();
  }


  public function setLastAssistingNotification( $v ){
    if($this->getExtra8() !== $v){
      $this->setExtra8( $v );
    }
  }

  public function getLastAssistingNotification(){
    return 0 + $this->getExtra8();
  }





  // ======= MAIL =======

  // See enrollment letters in Enrollment.php

  public function sendPortalEmail( $subject, $message, $from = 'registration@coactive.com', $to = '' ){
    // PLEASE NOTE the '@' signs. These are used here to suppress warnings due to PEAR not being up to date for strictness


    // Format results for email
    if($to == ''){
      $to = $this->getEmail();
    }

    // Send email
    //$sent = mail($to, $subject, $message, $headers) ;

    // needed because corp9 does not have local SMTP
    $host = "smtp2.modelsmith.com";
    $port = "2225";
    $username = "msmithclient";
    $password = "3eDSw2";

    $headers = array ('From' => $from,
                      'To' => $to,
                      'Subject' => $subject);
    $smtp = @Mail::factory('smtp',
                          array ('host' => $host,
                                 'port' => $port,
                                 'auth' => true,
                                 'username' => $username,
                                 'password' => $password));

    $mail = @$smtp->send($to, $headers, $message);

    // if (PEAR::isError($mail)) {
    //   echo("<p>" . $mail->getMessage() . "</p>");
    // } else {
    //   echo("<p>Message successfully sent!</p>");
    // }


    $this->newCommlogEntry( "Email with subject '$subject' was sent from Student Portal" );
  }



  // ======= ACCOUNT =======

  public function isOutOfSync(){
    if($this->getSync() != 'yes'){
      return true;
    }
    return false;
  }

  public function recordIsOld(){
    // record is old if it is more than X hours since retrieval from FileMaker
    // (an alternate way to do this is to check FileMaker's last update time and compare)
    if( (time() - $this->getAccountAge()) < 14400 ){ // 14400 is 4 hours
      return true;
    }
    return false;
  }

  public function initiateEmailAddressChange( $email ){
    // save new email in new_email field
    $this->setNewEmail( $email );
    $this->setNewEmailRequestTime( time() );
    $this->save();

  }

  public function updateEmailAddress(){
    $this->setEmail( $this->getNewEmail() );
    $this->save();
    
    $this->updateFileMakerAccountEmail();
  }

  public function updateFileMakerAccountEmail(){
    // push email from web record to FileMaker record
    // curl to webcomp (timeout 10 secs)
    $ch = curl_init();

    $baseurl  = sfConfig::get('app_webcomp_baseurl');
    $postkey  = sfConfig::get('app_webcomp_postkey');
    $endpoint = sfConfig::get('app_webcomp_account_email');

    $url = $baseurl.'/'.$endpoint;

    $fields_string = 
      'postkey='.$postkey
      .'&fmid='.$this->getFmid()
      .'&email='.urlencode($this->getEmail());

    //set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);
    //curl_setopt($ch, CURLOPT_POST, count($fields));
    curl_setopt($ch, CURLOPT_POST, 3);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);  // timeout after 10 seconds

    //execute post
    $json     = curl_exec($ch);    
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
    
    if($httpCode == 200){
      // $result is JSON
      // if result = ok, then update transaction status in org record 
      $result = json_decode( $json, true );
      if(isset($result['status']) && $result['status'] == 'ok'){
        // transaction status = "complete"
        //$msg = 'ok';
      }
      else {
        //$msg = 'failed';
      }
    }
    
    //close connection
    curl_close($ch);

    $this->setAccountAge( time() ); // set account age to Now
    $this->save();

  }

  public function updateFileMakerAccountInfo(){

    // push data from web record to FileMaker record
    // curl to webcomp (timeout 10 secs)
    $ch = curl_init();

    $baseurl  = sfConfig::get('app_webcomp_baseurl');
    $postkey  = sfConfig::get('app_webcomp_postkey');
    $endpoint = sfConfig::get('app_webcomp_account_update');

    $url = $baseurl.'/'.$endpoint;

    $fields_string = 
      'postkey='.$postkey
      .'&fmid='.$this->getFmid()
      .'&first_name='     .urlencode($this->getFirstName())
      .'&last_name='      .urlencode($this->getLastName())
      .'&address='        .urlencode($this->getHomeAddress())
      .'&city='           .urlencode($this->getCity())
      .'&state='          .urlencode($this->getStateProv())
      .'&zip='            .urlencode($this->getZipPostal())
      .'&country='        .urlencode($this->getCountry())
      .'&home_phone='     .urlencode($this->getHomePhone())
      .'&cell_phone='     .urlencode($this->getCellPhone())
      .'&business_phone=' .urlencode($this->getBusinessPhone());  // not yet available in code/webcomp2_newFM/portal_account.php

    //set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);
    //curl_setopt($ch, CURLOPT_POST, count($fields));
    curl_setopt($ch, CURLOPT_POST, 12);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);  // timeout after 10 seconds

    //execute post
    $json     = curl_exec($ch);    
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
    
    if($httpCode == 200){
      // $result is JSON
      // if result = ok, then update transaction status in org record 
      $result = json_decode( $json, true );
      if(isset($result['status']) && $result['status'] == 'ok'){
        // transaction status = "complete"
        //$msg = 'ok';
        $this->newCommlogEntry("Changed account information via Student Portal");
      }
      else {
        //$msg = 'failed';
      }
    }
    
    //close connection
    curl_close($ch);

    $this->setAccountAge( time() ); // set account age to Now
    $this->save();
    
  }



  public function refreshRecord(){
    // update data from FileMaker record

    // curl to webcomp (timeout 20 secs)
    $ch = curl_init();

    $baseurl  = sfConfig::get('app_webcomp_baseurl');
    $postkey  = sfConfig::get('app_webcomp_postkey');
    $endpoint = sfConfig::get('app_webcomp_account');

    $url = $baseurl.'/'.$endpoint.'?postkey='.$postkey.'&em='.$this->getEmail();

    //set the url
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);  // timeout after 10 seconds

    //execute post
    $json     = curl_exec($ch);    
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
    
    //print_r( $json );
    $result = array( );
    $result['url'] = $url;
    $result['json'] = $json;
    $result['httpCode'] = $httpCode;

    if($httpCode == 200){
      // $result is JSON
      // if result = ok, then update transaction status in org record 
      $result = json_decode( $json, true ); // true -> output an array instead of StdClass
      //print_r( $result );
      if(isset($result) && $result['status'] == 'ok'){
        // find student in database and authenticate. If not in database, add student

        $this->setFmid(          $result['fmid'] );
        $this->setFirstName(     $result['first_name'] );
        $this->setLastName(      $result['last_name'] );
        $this->setHomeAddress(   $result['address'] );
        $this->setCity(          $result['city'] );
        $this->setStateProv(     $result['state'] );
        $this->setCountry(       $result['country'] );
        $this->setZipPostal(     $result['zip'] );
        $this->setHomePhone(     $result['home_phone'] );
        $this->setCellPhone(     $result['cell_phone'] );
        
        if(array_key_exists('y_lp_n',$result)){
          $this->setLeadershipProgram( $result['y_lp_n'] );
        }
        if(array_key_exists('cpcc_cert_date',$result)){
          $this->setCPCCCertDate( $result['cpcc_cert_date'] );
        }

     
        if(array_key_exists('y_reg_complcore_ct',$result)){
          $this->setCourseHistory( $result['y_reg_complcore_ct'] );
        }

        //$this->setBusinessPhone( $result['business_phone'] );  // not yet available in code/webcomp2_newFM/portal_account.php

        // the level may come from several fields. Try zc_level_max_compl first.
        $level = $result['zc_level_max_compl'];
        if($level == 0 && $result['level'] > 0){
          $level = $result['level'];
        }
        $this->setLevel(         $level );

      }
      else {
        //$msg = 'failed';
      }
    }
    
    //close connection
    curl_close($ch);

    $this->setAccountAge( time() ); // set account age to Now
    $this->save();

    return $result;
  }

  // ======= PASSWORD and EMAIL CHANGES =======

  public function newResetKey( ){ // key for matching to reset password or email address change
    $this->setResetKey( commonTools::randomKey( ) );
    $this->save();
    return $this->getResetKey();
  }

  public function resetKeyMatches( $key ){
    if( $this->getResetKey() != '' && $key == $this->getResetKey() ){
      $this->setResetKey(''); // key is only allowed to be used once, so erase it
      $this->save();
      return true;
    }
    else {
      return false;
    }
  }

  public function setNewPassword( $password ){

    // URL encode password
    $password = urlencode( $password );

    $baseurl  = sfConfig::get('app_webcomp_baseurl');
    $postkey  = sfConfig::get('app_webcomp_postkey2');
    $endpoint = sfConfig::get('app_webcomp_newpw');

    $url = $baseurl.'/'.$endpoint.'?postkey='.$postkey.'&em='.$this->getEmail().'&pw='.$password;

    $result = file_get_contents( $url );
   
    if($result == 'ok'){
      // transaction status = "complete"
      //$msg = 'ok';
      $this->newCommlogEntry("Changed password via Student Portal");
    }
    else {
      //$msg = 'failed';
    }

  }

  public function requestEmailChange( $new_email ){
    // save request locally
    $this->setNewEmail( $new_email );

    // date_default_timezone_set('America/Los_Angeles');
    $this->setNewEmailRequestTime( date("Y-m-d H:i:s") );
    $this->save();

    $this->newResetKey();
    // trigger an email
    
  }


  // ======= ELIGIBLE EVENTS =======
  public function getEligibleEvents(){
    $events = EventPeer::retrieveByLevel( $this->getLevel() );
    return $events;
  }

  public function getEligibleAssistingEvents(){
    $events = EventPeer::retrieveCoreByLevel( $this->getLevel() );
    return $events;
  }

  public function setEventEligibilityStatusMsg($statusMessage) {
    $this->eventEligibilityStatusMsg = $statusMessage;
  }
  
  public function getEventEligibilityStatusMsg() {
    return $this->eventEligibilityStatusMsg;
  }
  public function eventIdIsEligible( $event_id ){

    $event = EventPeer::retrieveByPk( $event_id );

    if(isset($event)){
      // first check to see if student is already registered
      $c = new Criteria();
      $c->add(EnrollmentPeer::STUDENT_ID, $this->getId() );
      $c->add(EnrollmentPeer::EVENT_ID, $event_id );
      $c->add(EnrollmentPeer::TYPE, 'canceled', Criteria::NOT_EQUAL );

      $enrollment = EnrollmentPeer::doSelectOne( $c );

      if($enrollment){ // already registered...
        if( ! preg_match('/waitlist/i',$enrollment->getType() ) ){
        //Message to be shown to the student: 
        // "You have already registered for this event."
        // This message is given in the template "registerTryAgainLater_2".
        // Hence the template name is set.
          $this->setEventEligibilityStatusMsg("registerTryAgainLater_2");
          return false; // not eligible to register again
        }
      }

      // check course details are available for the student.
      $course_history = $this->getCourseHistory(); // Example: 10/4/02.Fun.SR-BW,4/11/03.Ful.SR-BW,6/25/04.Bal.SR-BW,12/10/10.Pro.OC-NEWPORT
      if ($course_history == '') {
        //Message to be shown to the student: 
        // "An error has occurred with your account.  Please contact CTI Customer Service at 1-800-691-6008, option 1 for assistance."
        // This message is given in the template "registerTryAgainLater_1".
        // Hence the template name is set.
        $this->setEventEligibilityStatusMsg("registerTryAgainLater_1");
        return false;
      }
      // now check if student is eligible
      $name = $event->getName();
      $course_type_id = $event->getCourseTypeId();
      if(!$this->courseTakenWithin5Years( $course_type_id )){
        //Message to be shown to the student: 
        // "You can only register to assist for courses you have completed and only those within the last 5 years"
        // This message is given in the template "registerTryAgainLater".
        // Hence the template name is set.
        $this->setEventEligibilityStatusMsg("registerTryAgainLater");
        return false; // course was not taken within 5 years
      }

      $level = $this->getLevel();
      $min_level = $event->getMinimumLevel();
    
      if($min_level < 1){
        if(preg_match('/ccc/i',$name) && $level >= 1){
           return true;
        }
        if(preg_match('/fund/i',$name) && $level >= 1){
           return true;
        }
        if(preg_match('/ful/i',$name) && $level >= 2){
           return true;
        }
        if(preg_match('/bal/i',$name) && $level >= 3){
           return true;
        }
        if(preg_match('/proc/i',$name) && $level >= 4){
            return true;
        }
        if(preg_match('/in the bones/i',$name) && $level >= 5){
           return true;
        }
        if(preg_match('/synergy/i',$name) && $level >= 5){
           return true;
        }
      }
      if($level >= $min_level ){
        return true;
      } else {
        //Message to be shown to the student: 
        // "Your course completion level does not match with the level required for assisting the event.  Please contact CTI Customer Service at 1-800-691-6008, option 1.
        // This message is given in the template "registerTryAgainLater_3".
        // Hence the template name is set.
        $this->setEventEligibilityStatusMsg("registerTryAgainLater_3");
        return false;
      }
    }
    return false;
  }

  public function courseTakenWithin5Years( $course_type_id ){
    if( $course_type_id > 7 ){ // ignore non-core courses
      return true;
    }
    $course_types = array( '','','','Fun','Ful','Bal','Pro','(Syn|ITB)' );
    $event_name = $course_types[ $course_type_id ];
    $five_years_ago = strtotime('5 years ago');
    $course_history = $this->getCourseHistory(); // Example: 10/4/02.Fun.SR-BW,4/11/03.Ful.SR-BW,6/25/04.Bal.SR-BW,12/10/10.Pro.OC-NEWPORT
    // find this course in courseHistory string and return true if less than 5 years
    $courses = explode(',',$course_history);
    foreach($courses as $course){
      if(preg_match("/$event_name/i",$course)){
        $course_date = preg_replace('/\..*/','',$course);
        $course_timestamp = strtotime($course_date);
        if($course_timestamp > $five_years_ago){
          return true;
        }
      }
    }
    return false;
  }

  public function eventFmidIsEligible( $fmid ){
    $event = EventPeer::retrieveByFmid( $fmid );
    if(isset($event)){
      if($this->getLevel() >= $event->getMinimumLevel() ){
        return true;
      }
    }
    return false;
  }

  // ======= ENROLLMENTS =======

  public function updateEnrollments(){
    // ======= FileMaker =======
    // get all the information from FileMaker for this student

  }

  public function getMyEnrollments( ) {
    $c = new Criteria();
    $c->add(EnrollmentPeer::STUDENT_ID, $this->getId() );
    $c->addDescendingOrderByColumn(EnrollmentPeer::ID);
    $c->add(EnrollmentPeer::TYPE, array("enrolled", "registered"), Criteria::IN );
    $enrollments = EnrollmentPeer::doSelect( $c );

    
    return $enrollments;
  }

  public function getMyRecentCancels( ) {
    $c = new Criteria();
    $c->add(EnrollmentPeer::STUDENT_ID, $this->getId() );
    $c->addDescendingOrderByColumn(EnrollmentPeer::ID);
    $c->add(EnrollmentPeer::TYPE, 'canceled' );
    $c->add(EnrollmentPeer::DATE, date('Y-m-d',strtotime('1 month ago')), Criteria::GREATER_THAN );
    $enrollments = EnrollmentPeer::doSelect( $c );
   
    
    return $enrollments;
  }

  public function getMyAssists( ) {
    $c = new Criteria();
    $c->add(EnrollmentPeer::STUDENT_ID, $this->getId() );
    $c->addDescendingOrderByColumn(EnrollmentPeer::ID);

    $cton1 = $c->getNewCriterion(EnrollmentPeer::TYPE, 'assist');
    $cton2 = $c->getNewCriterion(EnrollmentPeer::TYPE, 'assistWaitlisted' ); 

    // combine them
    $cton1->addOr($cton2);
    // add to Criteria
    $c->add($cton1);

    $enrollments = EnrollmentPeer::doSelect( $c );

    return $enrollments;
  }

  public function getMyWaitlists( ) {
    $c = new Criteria();
    $c->add(EnrollmentPeer::STUDENT_ID, $this->getId() );
    $c->addDescendingOrderByColumn(EnrollmentPeer::ID);
    $c->add(EnrollmentPeer::TYPE, 'waitlisted' );
    $enrollments = EnrollmentPeer::doSelect( $c );

    return $enrollments;
  }


  public function getMySchedule( $options = '' ) {
    EnrollmentPeer::updateCache( $this ); // update cache of enrollments

    $schedule = array( );
    $enrolls = array( );
    $events = array( );

    // events are ordered by enrollment.id

    $enrollments = $this->getMyEnrollments();
    foreach($enrollments as $e){
      $event = $e->getEvent();
      if(isset($event) && !array_key_exists($e->getEventId(),$events)){
        $schedule[ ] = $e;
        $events[$e->getEventId()] = 1;
      }
    }


    if($options == 'all' || $options == 'upcoming' || $options == 'past'){
      $assists = $this->getMyAssists();
      foreach($assists as $a){
        $event = $a->getEvent();
        if(isset($event) && !array_key_exists($a->getEventId(),$events)){
          $schedule[ ] = $a;
          $events[$a->getEventId()] = 1;
        }
      }
    }

    $waitlists   = $this->getMyWaitlists();
    foreach($waitlists as $w){
      $event = $w->getEvent();
      if(isset($event) && !array_key_exists($w->getEventId(),$events)){
        $schedule[ ] = $w;
        $events[$w->getEventId()] = 1;
      }
    }


 
    $enrollments = $this->getMyRecentCancels();
    foreach($enrollments as $e){
      $event = $e->getEvent();
      if(isset($event) && !array_key_exists($e->getEventId(),$events)){
        $schedule[ ] = $e;
        $events[$e->getEventId()] = 1;
      }
    }




    // sort enrollments by date
    usort($schedule, "EnrollmentPeer::DateSort");

    return $schedule;
  }


//  ======= ENROLL AND WAITLIST FUNCTIONS  =======


// TODO: 11/12/2012
// - $results array should contain registered or waitlist


  public function enroll( $event_id ){
    $results = EnrollmentPeer::register( $this, $event_id, 'enrolled' );
    return $results; // array( 'enrollment' => $enrollment, 'msg' => $msg );
  }

  public function cancelEnrollment( $enrollment_id ){
    $enrollment = EnrollmentPeer::retrieveByPk( $enrollment_id );
    $results = $enrollment->cancelEnrollment( 0 ); // suppress_email = 0 (do not suppress email)
    return $results;
  }





  public function waitlist( $event_id ){
    $results = EnrollmentPeer::waitlist( $this, $event_id, 'waitlisted' );
    return $results; // array( 'enrollment' => $enrollment, 'msg' => $msg );
  }

  public function cancelWaitlist( $enrollment_id ){
    $enrollment = EnrollmentPeer::retrieveByPk( $enrollment_id );
    $results =  $enrollment->cancelWaitlist( $this );
    return $results;
  }

  public function cancelEventWaitlist( $event_id ){
    // find all enrollments for this event for this student
    // typically, there should only be one enrollment
    // but we'll get all of them just in case
    $c = new Criteria();
    $c->add(EnrollmentPeer::EVENT_ID, $event_id);
    $c->add(EnrollmentPeer::STUDENT_ID, $this->getId() );
    $enrollments = EnrollmentPeer::doSelect( $c );
    
    // if there are enrollments, find the waitlisted one(s) and cancel them
    foreach($enrollments as $enrollment){
      if( preg_match('/waitlist/i',$enrollment->getType() ) ){
        $enrollment->cancelEnrollment( 1 ); // cancel enrollment and suppress email
      }
    }
    return;
  }



  public function assist( $event_id ){
    $results = EnrollmentPeer::register( $this, $event_id, 'assist' );
    return $results; // array( 'enrollment' => $enrollment, 'msg' => $msg );
  }

  public function waitlistAssist( $event_id ){

    return array( 'msg' => 'fail'); // waitlisting temporarily disabled T. Beutel 08-Oct-2013

    // NOTE: Please test EnrollmentPeer::waitlist before releasing to production. It currently is enrolling instead of waitlisting.

    $results = EnrollmentPeer::waitlist( $this, $event_id, 'assistWaitlisted' );
    return $results; // array( 'enrollment' => $enrollment, 'msg' => $msg );
  }

  public function cancelAssist( $enrollment_id ){
    $enrollment = EnrollmentPeer::retrieveByPk( $enrollment_id );
    $results =  $enrollment->cancelEnrollment( $this );
    return $results;
  }



  public function enrollmentIdIsValid( $enrollment_id ){
    $enrollment = EnrollmentPeer::retrieveByPk( $enrollment_id );
    if(isset($enrollment) && $enrollment->getStudentId() == $this->getId() ){
      return true;
    }
    return false;
  }

  // NOTE: extra1 contains enrollment cache timestamp

  // ======= COMMLOG =======

  public function newCommlogEntry( $message ){

    // URL encode message
    $message = urlencode( $message." at ".date("H:i:s")." ET" );

    // curl to webcomp (timeout 10 secs)
    $ch = curl_init();

    $baseurl  = sfConfig::get('app_webcomp_baseurl');
    $postkey  = sfConfig::get('app_webcomp_postkey');
    $endpoint = sfConfig::get('app_webcomp_commlog');

    $url = $baseurl.'/'.$endpoint.'?postkey='.$postkey.'&em='.$this->getEmail().'&m='.$message;

    //set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);
    //curl_setopt($ch, CURLOPT_POST, count($fields));
    //curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);  // timeout after 10 seconds

    //execute post
    $json     = curl_exec($ch);    
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
    
    if($httpCode == 200){
      // $result is JSON
      // if result = ok, then update transaction status in org record 
      $result = json_decode( $json );
      if(isset($result->{'result'}) && $result->{'result'} == 'ok'){
        // transaction status = "complete"
        //$msg = 'ok';
      }
      else {
        //$msg = 'failed';
      }
    }
    
    //close connection
    curl_close($ch);


  }

// == Obsolete Code ==

  public function getMyEvents( ) {
    EnrollmentPeer::updateCache( $this ); // update cache of enrollments

    $events = array( );

    $enrollments = $this->getMyEnrollments();
    foreach($enrollments as $e){
      $event = $e->getEvent();
      if(isset($event)){
        $events[ ] = $event;
      }
    }

    $assists = $this->getMyAssists();
    foreach($assists as $a){
      $event = $a->getEvent();
      if(isset($event)){
        $events[ ] = $event;
      }
    }

    $waitlists   = $this->getMyWaitlists();
    foreach($waitlists as $w){
      $event = $w->getEvent();
      if(isset($event)){
        $events[ ] = $event;
      }
    }

    // sort events by date
    usort($events, "EventPeer::DateSort");

    return $events;
  }


}
