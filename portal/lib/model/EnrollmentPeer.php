<?php

  // TO DO:  T. Beutel 11/28/2011
  // - Create portal_enroll.php on webcomp
  // - Create portal_unenroll.php on webcomp

  // - Create portal_waitlist.php on webcomp
  // - Create portal_unwaitlist.php on webcomp



// curl 'http://webcomp.modelsmith.com/fmi-test/webcomp/portal_courses.php?postkey=fjgh15t&fmid=25780'
// {"status":ok,"courses":[{
// "course_id":"153449",
// "customer code":"25780",
// "Event ID":"6573",
// "Event_Name":"Balance",
// "course_type_id":"5",                   <-- course_type_id
// "course_start_date":"06/25/2004",
// "z_Student_LastFirst":"Beutel, Thomas",
// "advisors":"",
// "creation_date":"06/02/2004",
// "reg_action":"",
// "reg_status":"Student",                 <-- Student or Assistant
// "canceled_date":"",
// "canceled_by":""},
// ...
// {
// "course_id":"365305",
// "customer code":"25780",
// "Event ID":"18151",
// "Event_Name":"Fulfillment",
// "course_type_id":"4",
// "course_start_date":"05/18/2012",
// "z_Student_LastFirst":"Beutel, Thomas",
// "advisors":"Web",
// "creation_date":"03/12/2012",
// "reg_action":"",
// "reg_status":"Assistant",                <-- Student or Assistant
// "canceled_date":"",
// "canceled_by":""}]}


class EnrollmentPeer extends BaseEnrollmentPeer
{
 
  public $debug_file = '/tmp/portal_assist.log';

  protected static $valid_course_type_ids    = array( 89, 114, 115, 101, 102, 103, 104, 105, 106, 107, 108, 153, 161, 162, 163, 164, 165 );
  protected static $valid_assisting_type_ids = array( 3, 4, 5, 6, 7 );

  public static function updateCache( $student ){
    // retrieve enrollments from FileMaker if more than 60 minutes since last time
    $timestamp = $student->getExtra1();
    $now = time();
    if($now - $timestamp > 3600){
      // cache out of date, get course records from FileMaker
      $ch = curl_init();

      $baseurl  = sfConfig::get('app_webcomp_baseurl');
      $postkey  = sfConfig::get('app_webcomp_postkey');
      $endpoint = sfConfig::get('app_webcomp_courses');

      $url = $baseurl.'/'.$endpoint.'?postkey='.$postkey.'&fmid='.$student->getFmid();

      //set the url, number of POST vars, POST data
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      curl_setopt($ch, CURLOPT_TIMEOUT, 10);  // timeout after 10 seconds

      //execute post
      $json     = curl_exec($ch);   
      $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
    
      if($httpCode == 200){
        // $result is JSON
        // if result = ok, then update transaction status in org record 
        $result = json_decode( $json );  // json_decode( $json, true ) for array 

        //commonTools::logEmail( $json );  // comment this - Thomas

// echo "<pre>";
// print_r($result);
// echo "</pre>";

        // loop through result and update enrollment records
        if( $result->{'status'}=='ok' && array_key_exists('courses',$result) ){
          $courses = $result->{'courses'};
          foreach($courses as $course){
            if( in_array($course->{'course_type_id'}, EnrollmentPeer::$valid_course_type_ids) 
                || ( in_array($course->{'course_type_id'}, EnrollmentPeer::$valid_assisting_type_ids) && $course->{'reg_status'} == 'Assistant' ) )
            {

              // Start date
              $start_datetime = strtotime( $course->{'course_start_date'} );

              // check first to see if event exists and get id
              $event_id = EventPeer::cacheByFmid( $course->{'Event ID'}, $course->{'Event_Name'}, $course->{'course_start_date'} );
            
              // check for enrollment
              $c = new Criteria();
              $c->add(EnrollmentPeer::STUDENT_ID, $student->getId() );
              $c->add(EnrollmentPeer::EVENT_ID, $event_id );
              $c->add(EnrollmentPeer::EXTRA1, $course->course_id ); // added 3/25/13 for cases where student enrolls, drops, enrolls again
              $enrollment = EnrollmentPeer::doSelectOne( $c );


              // if not exists, create enrollment
              if(!isset($enrollment)){
                $enrollment = new Enrollment();
                $enrollment->setStudentId( $student->getId() );
                $enrollment->setEventId( $event_id );
                $enrollment->setFmCourseId( $course->course_id );
                $enrollment->setDate( date("Y-m-d H:i:s",$start_datetime) );
                $enrollment->setStartDate( $course->{'course_start_date'} );
              }

              if($course->reg_action == '10' || $course->reg_action == '8'){
                $enrollment->setType( 'canceled' ); // not sure if this is correct for all course records
              }
              else if($course->reg_status == 'Assistant' ){
                if($course->reg_action == '0'){
                  $enrollment->setType( 'assistWaitlisted' ); // not sure if this is correct for all course records
                }
                else {  
                  $enrollment->setType( 'assist' ); // not sure if this is correct for all course records
                }
              }
              else {
                $enrollment->setType( 'enrolled' ); // do not do this for fun through syn
              }
              $enrollment->save();
            
            }
            else {
              // not in list
              //echo "course ".$course->{'Event_Name'}." ".$course->{'course_type_id'}." not in list<br />";
            }
          }
        }
      }
    
      // update timestamp
      $timestamp = time();
      $student->setExtra1( $timestamp );
      $student->save();
    }
  }

  public static function retrieveByStudentId( $student_id ){
    $c = new Criteria();
    $c->add(EnrollmentPeer::STUDENT_ID, $student_id);
    $enrollments = EnrollmentPeer::doSelect( $c );

    return $enrollments;
  }

  public static function currentEnrollmentsByName( $name ){
    // foreach enrollment in the future, try to match student by name (first, last, first last) using soundex

  }

  // ======= ENROLLMENT FUNCTIONS =======

  // These functions connect with FileMaker and
  // - create/delete an enrollment
  // - add an entry into the commlog



// Steelhead:webcomp thomas$ curl 'http://webcomp.modelsmith.com/fmi-test/webcomp/portal_course_register.php?postkey=fjgh15t&student_id=25780&event_id=17830'
// {"status":"ok","courses":[{

// Enroll (Register) Example:

// {
// "course_id":"382134",
// "customer code":"107285",
// "Event ID":"19607",
// "Event_Name":"Test FUN Learning Lab",
// "course_start_date":"01/14/2013",
// "z_Student_LastFirst":"Purrl, Miss",
// "advisors":"Web",
// "creation_date":"11/08/2012",
// "zkc_reg_action_n":"1",
// "zkc_reg_action_t":"Registered",
// "reg_action":"Registered",
// "reg_status":"Student",
// "canceled_date":"",
// "canceled_by":""},


// Waitlist Example:
// {
// "course_id":"382089",
// "customer code":"81219",
// "Event ID":"19607",
// "Event_Name":"Test FUN Learning Lab",
// "course_start_date":"01/14/2013",
// "z_Student_LastFirst":"Anderson, Amy",
// "advisors":"Web",
// "creation_date":"11/08/2012",
// "zkc_reg_action_n":"0",
// "zkc_reg_action_t":"Wait List",
// "reg_action":"Wait List",
// "reg_status":"Student",
// "canceled_date":"",
// "canceled_by":""},


// Cancel Example:

// {"course_id":"17557",
// "customer code":"16660",
// "Event ID":"504",
// "Event_Name":"CCC",
// "course_start_date":"03/24/2000",
// "z_Student_LastFirst":"Armbruster, David"
// "advisors":""
// "creation_date":"03/16/2000"
// "reg_action":"Canceled"
// "reg_status":"Student"
// "canceled_date":""
// "canceled_by":""
// },

// ]}
//
// NOTE that extraneous record should be ignored, only use record with correct "customer code"
//


  public static function register( $student, $event_id, $type = "enrolled" ){
    // if Filemaker returns "Wait List", student will be marked waitlisted instead of enrolled
    $msg = '';

    $assist = ($type == 'assist') ? 1 : 0; // set $assist to 1 if type equals 'assist'

    // check if already enrolled with this type
    $c = new Criteria;
    $c->add(EnrollmentPeer::EVENT_ID,   $event_id);
    $c->add(EnrollmentPeer::STUDENT_ID, $student->getId() );
    $c->add(EnrollmentPeer::TYPE,       $type);
    $enrollment = EnrollmentPeer::doSelectOne( $c );

    $register_log = '/tmp/portal_register.log';
    $info = Date('Y-m-d H:i:s')." Student email: ".$student->getEmail()." event_id: $event_id type: $type\n";
    file_put_contents($register_log,$info,FILE_APPEND);

    if(!isset($enrollment)){
      $enrollment = null; // in case the following fails
      $event      = EventPeer::retrieveByPk( $event_id );

      if(isset($event)){ // it is assumed when calling register that the event is already in the event table. 

        $event_fmid = $event->getFmid();

        // enroll in Filemaker. If successful, save result locally
        // curl to webcomp (timeout 10 secs)
        $ch = curl_init();

        $baseurl  = sfConfig::get('app_webcomp_baseurl');
        $postkey  = sfConfig::get('app_webcomp_postkey');

        $endpoint = sfConfig::get('app_webcomp_register');


        // If this is a request for assisting, determine whether this student should be waitlisted 
        // according to certain time based criteria (see isAssistingShouldBeWaitlisted function)
        $should_be_waitlisted = 0;
        if($type == 'assist' && $event->isAssistingShouldBeWaitlisted( $student )){
          $should_be_waitlisted = 1;
        }

        if($should_be_waitlisted == 0){
          $student->cancelEventWaitlist( $event_id ); // cancel waitlist if necessary
        }

        $info = Date('Y-m-d H:i:s')." assisting should_be_waitlisted: $should_be_waitlisted\n";
        file_put_contents($register_log,$info,FILE_APPEND);

        
        $url = $baseurl.'/'.$endpoint.'?postkey='.$postkey.'&student_id='.$student->getFmid().'&event_id='.$event_fmid.'&assist='.$assist.'&regstat='.$should_be_waitlisted;


        $info = Date('Y-m-d H:i:s')." url: $url\n";
        file_put_contents($register_log,$info,FILE_APPEND);


        //set the url, number of POST vars, POST data, etc.
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);  // timeout after 10 seconds



        //execute post
        $json     = curl_exec($ch);    
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
    
        if($httpCode == 200){
          // $result is JSON
          // if result = ok, then update transaction status in org record
 
          commonTools::logEmail( $json );  // comment this - Thomas

          $result = json_decode( $json );
          if(isset($result->{'result'}) && $result->{'result'} == 'ok'){
            // multiple courses are returned, but only one is the actual new course record. Let's find it.
            $courses = $result->{'courses'};
            $new_course = array( 'customer code' => 0 );
            foreach($courses as $course){
              if($course->{'customer code'} == $student->getFmid()){
                $new_course = $course;
                break;
              }
            }

            if($new_course->{'customer code'} == $student->getFmid()){
            
              // check for reg_action here...............
              if($new_course->{'reg_action'} == 'Registered'){

                // transaction status = "complete"
                $msg = 'ok, enrolled';

                $start_datetime = strtotime( $course->{'course_start_date'} );

// Unable to execute INSERT statement. [wrapped: SQLSTATE[23000]: 
// Integrity constraint violation: 1452 Cannot add or update a child row: 
// a foreign key constraint fails 
// (`portaldb/enrollment`, CONSTRAINT `enrollment_FK_2` FOREIGN KEY (`event_id`) REFERENCES `event` (`id`))]

                $enrollment = new Enrollment();
                $enrollment->setEventId( $event_id );
                $enrollment->setStudentId( $student->getId() );
                $enrollment->setFmCourseId( $new_course->{'course_id'} );
                $enrollment->setDate( date("Y-m-d H:i:s",$start_datetime) );
                $enrollment->setStartDate( $new_course->{'course_start_date'} );
                $enrollment->setType( $type );
                if($type == 'assist'){
                  $enrollment->setWasAssisting( 'assist' ); // this is in case this is canceled later
                }
                $enrollment->save();
                
              }

              if($new_course->{'reg_action'} == 'Wait List'){

                // transaction status = "complete"
                $msg = 'ok, waitlisted';

                $start_datetime = strtotime( $course->{'course_start_date'} );

// Unable to execute INSERT statement. [wrapped: SQLSTATE[23000]: 
// Integrity constraint violation: 1452 Cannot add or update a child row: 
// a foreign key constraint fails 
// (`portaldb/enrollment`, CONSTRAINT `enrollment_FK_2` FOREIGN KEY (`event_id`) REFERENCES `event` (`id`))]

                $enrollment = new Enrollment();
                $enrollment->setEventId( $event_id );
                $enrollment->setStudentId( $student->getId() );
                $enrollment->setFmCourseId( $new_course->{'course_id'} );
                $enrollment->setDate( date("Y-m-d H:i:s",$start_datetime) );
                $enrollment->setStartDate( $new_course->{'course_start_date'} );
                if($type == 'assist'){
                  $enrollment->setType( 'assistWaitlisted' );
                  $enrollment->setWasAssisting( 'assist' ); // this is in case this is canceled later
                }
                else {
                  $enrollment->setType( 'waitlisted' );
                }
                $enrollment->save();
                
              }


              if($enrollment->getType() == 'assist'){
                $info = Date('Y-m-d H:i:s')." registered to assist, enrollment id is: ".$enrollment->getId()."\n";
                file_put_contents($register_log,$info,FILE_APPEND);

                $student->newCommlogEntry( 'Registered to assist in '.$event->getName().' via Student Portal' );
                $event->incrementCurrentAssistingEnrollment();
                $enrollment->triggerAssistingEnrollmentLetter( ); 
              }
              else if($enrollment->getType() == 'assistWaitlisted'){
                $info = Date('Y-m-d H:i:s')." waitlisted to assist, enrollment id is: ".$enrollment->getId()."\n";
                file_put_contents($register_log,$info,FILE_APPEND);

                $student->newCommlogEntry( 'Waitlisted to assist in '.$event->getName().' via Student Portal' );
                $enrollment->triggerAssistingConfirmWaitlistLetter( ); 
              }
              else if($enrollment->getType() == 'waitlisted') {
                $info = Date('Y-m-d H:i:s')."waitlisted, enrollment id is: ".$enrollment->getId()."\n";
                file_put_contents($register_log,$info,FILE_APPEND);

                $student->newCommlogEntry( 'Waitlisted in '.$event->getName().' via Student Portal' );
                $enrollment->triggerWaitlistLetter( ); 
              }
              else {
                $info = Date('Y-m-d H:i:s')."registered, enrollment id is: ".$enrollment->getId()."\n";
                file_put_contents($register_log,$info,FILE_APPEND);

                $student->newCommlogEntry( 'Registered in '.$event->getName().' via Student Portal' );
                $event->incrementCurrentEnrollment();
                $enrollment->triggerEnrollmentLetter( ); 
              }
            }
            else {
              $msg = 'failed, system refused enrollment for unknown reason (Code 401)';
            }
          }
          else {
            $msg = 'failed, system refused enrollment for unknown reason (Code 402)';
          }
        }
        else {
          $msg = 'failed, was not able to connect to database (Code 403)';
        }
    
        //close connection
        curl_close($ch);

      }
      else {
        $msg = "failed, event or course does not exist (Code 404)";
      }
    }
    else {
      $msg = "ok, already enrolled (Code 405)";
    }

    $info = Date('Y-m-d H:i:s').' '.$msg."\n";
    file_put_contents($register_log,$info,FILE_APPEND);


    $results = array( 'enrollment' => $enrollment, 'msg' => $msg );
    return $results;
  }



  public static function waitlist_zzz( $student, $event_id, $type = "waitlisted" ){
    $msg = '';

    // check if already enrolled
    $c = new Criteria;
    $c->add(EnrollmentPeer::EVENT_ID,   $event_id);
    $c->add(EnrollmentPeer::STUDENT_ID, $student->getId() );
    $c->add(EnrollmentPeer::TYPE,       $type);
    $enrollment = EnrollmentPeer::doSelectOne( $c );

    if(!isset($enrollment)){
      $enrollment = null; // in case the following fails
      $event      = EventPeer::retrieveByPk( $event_id );

      if(isset($event)){ // it is assumed when calling register that the event is already in the event table. 

        $event_fmid = $event->getFmid();

        // enroll in Filemaker. If successful, save result locally
        // curl to webcomp (timeout 10 secs)
        $ch = curl_init();

        $baseurl  = sfConfig::get('app_webcomp_baseurl');
        $postkey  = sfConfig::get('app_webcomp_postkey');
        $endpoint = sfConfig::get('app_webcomp_waitlist');  

        $url = $baseurl.'/'.$endpoint.'?postkey='.$postkey.'&student_id='.$student->getFmid().'&event_id='.$event_fmid;

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);  // timeout after 10 seconds

        //execute post
        $json     = curl_exec($ch);    
        $json     = preg_replace('/("[^"]+)\n([^"]+")/ms','$1$2',$json); // special case: removes newline from within a value (see JsonFix in wiki)

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
    
        if($httpCode == 200){
          // $result is JSON
          // if result = ok, then update transaction status in org record 
          $result = json_decode( $json );
          if(isset($result->{'result'}) && $result->{'result'} == 'ok'){
            // multiple courses are returned, but only one is the actual new course record. Let's find it.
            $courses = $result->{'courses'};
            $new_course = array( 'customer code' => 0 );
            foreach($courses as $course){
              if($course->{'customer code'} == $student->getFmid()){
                $new_course = $course;
                break;
              }
            }

            if($new_course->{'customer code'} == $student->getFmid()){
            
              // transaction status = "complete"
              $msg = 'ok, enrolled';

              $start_datetime = strtotime( $course->{'course_start_date'} );

// Unable to execute INSERT statement. [wrapped: SQLSTATE[23000]: 
// Integrity constraint violation: 1452 Cannot add or update a child row: 
// a foreign key constraint fails 
// (`portaldb/enrollment`, CONSTRAINT `enrollment_FK_2` FOREIGN KEY (`event_id`) REFERENCES `event` (`id`))]

              $enrollment = new Enrollment();
              $enrollment->setEventId( $event_id );
              $enrollment->setStudentId( $student->getId() );
              $enrollment->setFmCourseId( $new_course->{'course_id'} );
              $enrollment->setDate( date("Y-m-d H:i:s",$start_datetime) );
              $enrollment->setStartDate( $new_course->{'course_start_date'} );
              $enrollment->setType( $type );
              $enrollment->save();

              //$enrollment->triggerWaitlistLetter( ); // triggered automatically in FileMaker script
              
              $student->newCommlogEntry( 'Enrolled in '.$event->getName().' via Student Portal' );
            }
            else {
              $msg = 'failed, system refused enrollment for unknown reason (Code 401)';
            }
          }
          else {
            $msg = 'failed, system refused enrollment for unknown reason (Code 402)';
          }
        }
        else {
          $msg = 'failed, was not able to connect to database (Code 403)';
        }
    
        //close connection
        curl_close($ch);

      }
      else {
        $msg = "failed, event or course does not exist (Code 404)";
      }
    }
    else {
      $msg = "ok, already enrolled (Code 405)";
    }

    $results = array( 'enrollment' => $enrollment, 'msg' => $msg );
    return $results;
  }


  public static function checkAssistingWaitlists( ){
    $result = 'No emails sent';
    $students_sent = array( );

    // check events to see if there are less than 3 registered assistants (count updated by enrollment cancellation or update from filemaker)
    $events = EventPeer::getCoreEventsWithAssistingOpenings();

    // for those events, check to see if there are students waitlisted for assisting
    foreach($events as $event){
      $enrollments = EnrollmentPeer::getAssistantsWaiting( $event );

      // for those students, check to see if they are eligible for assisting $event->isAssistingShouldBeWaitlisted( $student )
      foreach($enrollments as $enrollment){
        $student = $enrollment->getStudent();
        $last_notification = $student->getLastAssistingNotification();
        $three_days_ago = strtotime('3 days ago');
        if( $last_notification < $three_days_ago && ! $event->isAssistingShouldBeWaitlisted( $student ) ){
          $enrollment->triggerAssistingSeatAvailableLetter();
          $student->setLastAssistingNotification( time() );
          $student->save();
          $students_sent[] = $student->getFirstName().' '.$student->getLastName();
        }
      }

    }
    if(count($students_sent) > 0){
      $result = implode(', ',$students_sent);
    }
    return $result;
  }
 

  public static function getAssistantsWaiting( $event ){
    $c = new Criteria();
    $c->add(EnrollmentPeer::EVENT_ID, $event->getId() );
    $c->add(EnrollmentPeer::TYPE, 'assistWaitlisted' );

    $enrollments = EnrollmentPeer::doSelect( $c );
    return $enrollments;
  }

  // ======= MISC FUNCTIONS =======


  public static function DateSort($a,$b,$d="-") {  // for use in usort, most recent date first. Reverse a and b for most recent date last 
    if ($a->getStartDate() == $b->getStartDate() ) { 
        return 0; 
    } else {  
        $a = strtotime($a->getStartDate()); 
        $b = strtotime($b->getStartDate()); 
        if($a<$b) { 
          return -1; 
        } else { 
            return 1; 
        } 
    } 
  } 


}
