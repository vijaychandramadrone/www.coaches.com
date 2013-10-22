<?php

  // =============== student portal email letters are below ===============


class Enrollment extends BaseEnrollment  
{

  // ======= EXTRA FIELDS =======
  public function getFmCourseId(){
    return $this->getExtra1();
  }

  public function setFmCourseId( $v ){
    if($this->getExtra1() !== $v){
      $this->setExtra1( $v );
    }
    return;
  }

  public function getLastNotificationTime(){
    return $this->getExtra2();
  }

  public function setLastNotificationTime( $v ){
    if($this->getExtra2() !== $v){
      $this->setExtra2( $v );
    }
    return;
  }


 public function getWasAssisting(){
    return $this->getExtra3();
  }

  public function setWasAssisting( $v ){
    if($this->getExtra3() !== $v){
      $this->setExtra3( $v );
    }
    return;
  }


 public function getCanceledBy(){
    return $this->getExtra4();
  }

  public function setCanceledBy( $v ){
    if($this->getExtra4() !== $v){
      $this->setExtra4( $v );
    }
    return;
  }

  // ======= TEST =======

  public function isAssisting(){
    if($this->getType() == 'assist' || $this->getType() == 'assistWaitlisted' || $this->getWasAssisting() == 'assist'){
      return true;
    }
    return false;
  }

  public function isCompleted(){
    if($this->getType() != 'canceled' && time() > ( strtotime($this->getStartDate()) + 86400 ) ){
      return true;
    }
    return false;
  }


  public function isCanceled(){
    if($this->getType() == 'canceled' ){
      return true;
    }
    return false;
  }

  // ======= DISPLAY FUNCTIONS =======

  public function getDisplayStatus() {
    $type = $this->getType();
    if($type == 'canceled' || $type == 'cancelled'){
      if($this->getCanceledBy() != ''){
        return 'Canceled by '.$this->getCanceledBy();
      }
      return 'Canceled by CTI';
    }
    if( time() > ( strtotime($this->getStartDate()) + 86400 ) ){ // show complete if now is greater than start date plus one day
      return 'Completed';
    }
    if($type == 'enrolled' || $type == 'registered'){
      return 'Registered';
    }
    if($type == 'waitlisted'){
      return 'Waitlisted';
    }
    if($type == 'assist'){
      return 'Registered to assist';
    }
    if($type == 'assistWaitlisted'){
      return 'Waitlisted for assisting';
    }
    

  }

  public function eligibleToCancel(){
    $type = $this->getType();
    if($type == 'enrolled' || $type == 'waitlisted' ){
      // return true if course is in the future or today
      if(86400 + strtotime($this->getStartDate()) > time()) { 
        return true; 
      } 
    }
    if($type == 'assist'  || $type == 'assistWaitlisted'){
      // assisting can't cancel within two days
      // if start date is greater than 2 days from now, then it is eligible for cancellation
      if(strtotime($this->getStartDate()) > (time() + 172800)){ 
        return true;
      }
    }
    return false;
  }
  
  public function eligibleToUnWaitlist(){
    $student = $this->getStudent();
    $event = $this->getEvent();

    // regular waitlisted
    if($this->getType() == 'waitlisted'){
      if($event->getCurrentEnrollment() < $event->getMaxEnrollment()){
        return true;
      }
    }

    // assisting waitlisted
    if($this->getType() == 'assistWaitlisted'){
      if(!$event->isAssistingShouldBeWaitlisted($student)){
        return true;
      }
    }

    // nope, not eligible to be un-waitlisted
    return false;
  }


  // ======= ENROLLMENT FUNCTIONS =======

  // These functions connect with FileMaker and
  // - create/delete an enrollment
  // - add an entry into the commlog

  public function cancelEnrollment( $suppress_email = 0 ){

    $type = $this->getType();
    if($type == 'canceled'){
      $msg = 'ok, already cancelled';
      return $msg;
    }

    if($this->isAssisting()){
      $assist  = 1;
    } 
    else {
      $assist  = 0;
    }

    $student = $this->getStudent();
    $event   = $this->getEvent();

// Steelhead:webcomp thomas$ curl 'http://webcomp.modelsmith.com/fmi-test/webcomp/portal_course_cancel.php?postkey=fjgh15t&course_id=358444'
// {"status":"ok","courses":[{
// "course_id":"358444",
// "customer code":"25780",
// "Event ID":"17830",
// "Event_Name":"Fundamentals Webinar",
// "course_start_date":"02/28/2012",
// "z_Student_LastFirst":"Beutel, Thomas"
// "advisors":"Web"
// "creation_date":"01/06/2012"
// "reg_action":"Canceled"
// "reg_status":"Student"
// "canceled_date":"01/06/2012"
// "canceled_by":"Web"
// }]}

    $info = Date('Y-m-d H:i:s')." Student email: ".$student->getEmail()." event_id: ".$event->getId()." type: cancel assist: $assist suppress_email: $suppress_email\n";
    file_put_contents('/tmp/portal_assist.log',$info,FILE_APPEND);

    // curl to webcomp (timeout 10 secs)
    $ch = curl_init();

    $baseurl  = sfConfig::get('app_webcomp_baseurl');
    $postkey  = sfConfig::get('app_webcomp_postkey');
    $endpoint = sfConfig::get('app_webcomp_cancel');

    $url = $baseurl.'/'.$endpoint.'?postkey='.$postkey.'&course_id='.$this->getFmCourseId().'&assist='.$assist;

    //set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);  // timeout after 10 seconds

    //execute post
    $json     = curl_exec($ch);    
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
    
    if($httpCode == 200){
      // $result is JSON
      // if result = ok, then update transaction status in org record 
      $result = json_decode( $json, true );

      if(isset($result) && $result['result'] == 'ok'){
        // multiple courses are returned, but only one is the actual new course record. Let's find it.
        $courses = $result['courses'];
        $new_course = array( 'customer code' => 0 );
        foreach($courses as $course){
          if($course['customer code'] == $student->getFmid()){
            $new_course = $course;
            break;
          }
        }

        if($new_course['customer code'] == $student->getFmid()){
            
          // transaction status = "complete"
          $msg = 'ok, cancelled';

          commonTools::logEmail( $json );  // comment this - Thomas
              
          if($this->isAssisting()){
            if(! $suppress_email){
              $this->triggerAssistingCancelLetter( ); // triggered automatically in FileMaker script
            }
            $info = Date('Y-m-d H:i:s')." assisting canceled\n";
            file_put_contents('/tmp/portal_assist.log',$info,FILE_APPEND);
            $student->newCommlogEntry( 'Student cancelled assisting in '.$event->getName().' via Student Portal' );
            if($this->getType() == 'assist'){
              $event->decrementCurrentAssistingEnrollment();
            }
          }
          else {
            if(! $suppress_email){
              $this->triggerCancelLetter( ); // triggered automatically in FileMaker script
            }
            $info = Date('Y-m-d H:i:s')." enrollment canceled\n";
            file_put_contents('/tmp/portal_assist.log',$info,FILE_APPEND);
            $student->newCommlogEntry( 'Student cancelled '.$event->getName().' via Student Portal' );
            $event->decrementCurrentEnrollment();
          }     

          if($this->getType() == 'enrolled'){
            $event->triggerWaitlistEmails(); // sends letter #94 (seat available) to waitlisted students
          }
            // trigger assisting waitlist emails here T. Beutel 1/29/13

          // change type to canceled
          $this->setType( 'canceled' );
          $this->setCanceledBy( 'student' );
          $this->save();

        }
        else {
          $msg = 'failed, system refused cancellation for unknown reason (Code 401)';
        }
      }
      else {
        $msg = 'failed, system refused cancellation for unknown reason (Code 402) ';
      }
    }
    else {
      $msg = 'failed, was not able to cancellation to database (Code 403)';
    }
    
    //close connection
    curl_close($ch);

    return $msg;
  }

  public function cancelWaitlist( ){
    $student = $this->getStudent();
    $event = $this->getEvent();

    return $status;
  }


  public function triggerWaitlistLetter( ){  // #93 Portal Learning Lab Waitlist Status Confirmation (checked 11/27/12)
    $student = $this->getStudent();
    $event   = $this->getEvent();

    $first_name  = $student->getFirstName();
    $course      = $event->getName();
    $location    = $event->getLocation();
    $start_date  = $event->getStartDate();

    $price = 'Free'; //$event->getPrice();

    $subject = 'CTI Waitlist Status Confirmation';
    $message = <<<EOT
Dear $first_name,

Thank you for your interest to attend the below listed event.  Availability is currently at capacity and you have been added to the waitlist: 

Course: $course
Date: $start_date

If a seat becomes available, we will advise you by email.  The seat will be available on a first-come, first-served basis to all waitlisted students.

In the event you want to change or view your registration status (e.g. cancel, change the date or remove yourself from the waitlist), please log on to the Student Portal at http://www.thecoaches.com/portal.

Sincerely,
CTI Customer Service


EOT;

    $student->sendPortalEmail( $subject, $message );
  }



  public function triggerWaitlistSeatAvailableLetter( ){  // #95 Portal Learning Lab Waitlist Seat Available (checked 11/27/12)
    // Do not send this letter more than once every 24 hours
    $time = time() - 86400; // 24 hours ago

    if($this->getLastNotificationTime() < $time){                            

      $student = $this->getStudent();
      $event   = $this->getEvent();
      
      $first_name  = $student->getFirstName();
      $course      = $event->getName();
      $location    = $event->getLocation();
      $start_date  = $event->getStartDate();
      
      $subject = 'CTI Waitlist - Opening Available';
      $message = <<<EOT
Dear $first_name,

You are on the waitlist for the below event and an opening is now available:

Course: $course
Date: $start_date

To register for this call, please log on to the Student Portal http://www.thecoaches.com/portal

If you are still interested in attending this call, please keep in mind that this opportunity will be filled on a first-come, first-serve basis.  

Sincerely,
CTI Customer Service


EOT;

    $student->sendPortalEmail( $subject, $message );
    $this->setLastNotificationTime( time() ); // update notification time
    $this->save();
    }

  }




  public function triggerEnrollmentLetter( ){  // #98 Portal Learning Lab Registration Confirmation (checked 11/27/12)
    $student = $this->getStudent();
    $event   = $this->getEvent();

    $course_type_id = $event->getCourseTypeId();

    $first_name  = $student->getFirstName();
    $course      = $event->getName();
    $location    = $event->getLocation();
    $start_date  = $event->getStartDate();
    $call_time   = $event->getCallTime();
    $dial_number = $event->getDialNumber();

    $price = 'Free'; //$event->getPrice();

    $subject = 'CTI Registration - Confirmation & Information';

    if($course_type_id == 161){
      // corporate teleseries - Fundamentals
      $message = $this->corpFunLetter($first_name, $course, $start_date, $call_time, $dial_number);
    }
    else if($course_type_id == 162 || $course_type_id == 163 || $course_type_id == 164 || $course_type_id == 165){
      // corporate teleseries - Fulfillment through Synergy
      $message = $this->corpFulSynLetter($first_name, $course, $start_date, $call_time, $dial_number);
    }
    else {
      // default message
      $message = $this->defaultEnrollmentLetter($first_name, $course, $start_date, $call_time, $dial_number);
    }

    $student->sendPortalEmail( $subject, $message );
  }



  public function defaultEnrollmentLetter( $first_name, $course, $start_date, $call_time, $dial_number ){
$message = <<<EOT
Dear $first_name,

Thank you for your registration to attend the following event:  

Course: $course
Date: $start_date
Call Time: $call_time
Dial-in Number: $dial_number

As a courtesy to the facilitator and the rest of your fellow participants, please read the following prior to your phone call:

• Important – Please join the call promptly or no later than five (5) minutes after the start of the event.  
• In the event the phone continually rings unanswered, please remain on the line until you are joined by a second caller.
• When possible, use a land line for connecting to this call.  Skype and/or cell phones can create interference and noise disturbance on the line.  
• Do not record the call or place it on hold as we have had feedback that it creates intermittent beeping.
• To minimize background sound, please use your phone’s mute feature whenever possible.

In the event you want to change or view your registration status (e.g. cancel, change the date), please log on to the Student Portal at http://www.thecoaches.com/portal.
Sincerely,
CTI Customer Service


EOT;
return $message;

  }


  public function corpFunLetter( $first_name, $course, $start_date, $call_time, $dial_number ){
$message = <<<EOT
Dear $first_name,

As a follow up to your course, you are confirmed to participate in a free 90-minute Co-Active Coaching Corporate Teleseries phone call. This letter confirms your registration of the below.

Course: $course
Date: $start_date
Call Time: $call_time
Dial-in Number: $dial_number

This is an opportunity for you to deepen your Co-Active Coaching skills and get inspired for your next course.  

Please refer to the Learning Guide for your lab objectives, teleclass tips and pre-assignment.  

Learning Guide:  http://www.thecoaches.com/info/corporate-teleseries-fundamentals
 
As a courtesy to the facilitator and the rest of your fellow participants:

• Important – Please join the call promptly or no later than 5 minutes after the start of the teleclass. 
• When possible use a land line for connecting to this call.  Skype and/or cell phones can create interference and noise disturbance on the line.  
• Do not record the call or place it on hold as we have had feedback that it creates intermittent beeping.
• To minimize background sound, please use your phone’s mute feature whenever possible.

Best regards,
CTI

EOT;
return $message;

  }

  public function corpFulSynLetter( $first_name, $course, $start_date, $call_time, $dial_number ){
$message = <<<EOT
Dear $first_name,

As a follow up to your course, you are confirmed to participate in a free 90-minute Co-Active Coaching Corporate Teleseries phone call. This letter confirms your registration of the below.

Course: $course
Date: $start_date
Call Time: $call_time
Dial-in Number: $dial_number

This is an opportunity for you to deepen your Co-Active Coaching skills and get inspired for your next course.  

Please refer to the Learning Guide for your lab objectives, teleclass tips and pre-assignment.  

Learning Guide Link: http://www.thecoaches.com/docs/corp-teleseries/ful-syn.html

As a courtesy to the facilitator and the rest of your fellow participants:

• Important – Please join the call promptly or no later than 5 minutes after the start of the teleclass.  
• When possible use a land line for connecting to this call. Skype and/or cell phones can create interference and noise disturbance on the line.  
• Do not record the call or place it on hold as we have had feedback that it creates intermittent beeping.
• To minimize background sound, please use your phone’s mute feature whenever possible.

Best regards,
CTI

EOT;
return $message;

  }



  public function triggerAssistingEnrollmentLetter( ){ // Subject:  CTI Assistant Confirmation of Registration - Sent upon registration to assist.  (FM Letter #7) 4/5/2013

  
    $student = $this->getStudent();
    $event   = $this->getEvent();

    $first_name  = $student->getFirstName();
    $course      = $event->getName();
    $location    = $event->getLocation();
    $start_date  = $event->getStartDate();
    $call_time   = ''; //$event->getCallTime();
    $dial_number = ''; //$event->getDialNumber();

    $price = 'Free'; //$event->getPrice();

    $subject = 'CTI Assistant Confirmation of Registration';
    $message = <<<EOT
Thank you for registering to be a CTI course assistant.  Assisting is both a privilege and a responsibility.  As an assistant, you are making a commitment to being of service to both the leaders and the course participants for all days of the course.  Providing support and holding the space for learning in the room will be your primary focus. 
 
Assisting is a key component of our course, so if you must cancel, please provide at least 14 days’ notice.   Any cancellation less than 14 days may impact your eligibility to assist in future courses and subject to a $50.00 administrative fee.
 
This letter confirms your participation as an assistant in the following course:
 
Course: $course
Start Date: $start_date
Location: $location
 
In the event you want to change or view your registration status (e.g. cancel, change the date or remove yourself from the waitlist), please log on to the Student Portal at http://www.thecoaches.com/portal.
 
Hotel Information:  For hotel information from this page http://www.coactive.com/coach-training/dates-locations, you can click on your specific course date to launch a page detailing information about room rates, room blocks, venue parking fees  and airport travel information.  To ensure you receive CTI’s negotiated rate, please book your hotel room at least 30 days in advance.  For any course location listed as “TBA - To Be Announced,” we are in contract negotiation with the venue and you will soon receive an updated confirmation letter providing the location and address.
 
Hotel Policy Regarding Outside Food:  Due to public health regulations and hotel policy, participants may not bring any food or beverage into any course held at a hotel.  All food and beverage must be purchased from the hotel.
 
Fragrance-Free Environment:  During the course, please be aware of those who may be chemically sensitive to fragrances, especially to perfume, aftershave, scented hand lotion, fragranced hair products, and/or similar products.  Please honor this request to refrain from wearing scented products during the course. 
 
Logistics:  Please arrive one hour early on each day to set up the room and allow plenty of time for the leaders to design their alliance with you before the course begins.  You will be staying about an hour after the course ends each day to pack up and debrief with the leaders.
 
CTI may cancel/reschedule courses at its discretion.  CTI will not be responsible for costs that may be incurred by assistants as a result of such cancellations or rescheduling.
 
Sincerely,
CTI Customer Service



EOT;

    $student->sendPortalEmail( $subject, $message );
  }




  public function triggerAssistingSeatAvailableLetter( ){ // Subject:  CTI Assistant Waitlist Update - Seat Available
                                                       // Sent to any waitlisted student when a seat becomes available.   (FM Letter #195) 4/5/2013
    $student = $this->getStudent();
    $event   = $this->getEvent();

    $first_name  = $student->getFirstName();
    $course      = $event->getName();
    $location    = $event->getLocation();
    $start_date  = $event->getStartDate();

    $subject = 'CTI Assisting Position Available';
    $message = <<<EOT
Dear $first_name,
 
Our records indicate that you are currently on the waitlist to assist.  This e-mail is to inform you that a position has become available for the following:
 
Course: $course
Start Date: $start_date
Location: $location
 
If you are still interested in assisting for this course please register through the Student Portal at http://www.thecoaches.com/portal/main/login.  Please keep in mind that spots are filled on a first come, first serve basis.
 
 
Sincerely,
CTI Customer Service


EOT;

    $student->sendPortalEmail( $subject, $message );
   
  }

  public function triggerAssistingConfirmWaitlistLetter( ){ // CTI Assistant Confirmation of Waitlist
                                                            // Sent to waitlisted student upon registration. (FM Letter #193) 4/5/2013
    $student = $this->getStudent();
    $event   = $this->getEvent();

    $first_name  = $student->getFirstName();
    $course      = $event->getName();
    $location    = $event->getLocation();
    $start_date  = $event->getStartDate();

    $subject = 'CTI Assistant Waitlist Status Confirmation';
    $message = <<<EOT
Thank you for your interest to assist.  This confirms your waitlist status for the following course:
 
Course: $course
Start Date: $start_date
Location: $location
 
You will be notified by email if a seat becomes available.  Notification is sent to all waitlisted students on a first-come, first-served basis.
 
In the event you want to change or view your registration status (e.g. cancel, change the date or remove yourself from the waitlist), please log on to the Student Portal at http://www.thecoaches.com/portal/main/login.
 
Sincerely,
CTI Customer Service


EOT;

    $student->sendPortalEmail( $subject, $message );
  }



  public function triggerAssistingCancelLetter( ){ // Subject:  CTI Assistant Confirmation of Cancellation: 
                                                   // Sent upon student initiated cancellation.   (FM letter #192) 4/5/2013
    $student = $this->getStudent();
    $event   = $this->getEvent();

    $first_name  = $student->getFirstName();
    $course      = $event->getName();
    $location    = $event->getLocation();
    $start_date  = $event->getStartDate();

    $subject = 'CTI Confirmation of Cancellation';
    $message = <<<EOT
This confirms that your registration or waitlist status has been cancelled for the below event.
 
Course: $course
Start Date: $start_date
Location: $location
 
Sincerely,
CTI Customer Service
 

EOT;

    $student->sendPortalEmail( $subject, $message );
  }





  public function triggerCancelLetter( ){  
    $student = $this->getStudent();
    $event   = $this->getEvent();

    $first_name  = $student->getFirstName();
    $course      = $event->getName();
    $location    = $event->getLocation();
    $start_date  = $event->getStartDate();

    $price = 'Free'; //$event->getPrice();

    $subject = 'CTI Student Portal Confirmation of Cancellation';
    $message = <<<EOT
This confirms that your registration or waitlist status has been cancelled for the below listed event. Please contact CTI Customer Service at 415-451-6000 Option 1 should you have any questions.

Course: $course
Date: $start_date

Sincerely,
CTI Customer Service


EOT;
    $student->sendPortalEmail( $subject, $message );
  }


  public function triggerEnrollmentLetter_xxx( ){
    $msg     = '';
    $student = $this->getStudent();
    $event   = $this->getEvent();

    // enroll in Filemaker. If successful, save result locally
    // curl to webcomp (timeout 10 secs)
    $ch = curl_init();

    $baseurl  = sfConfig::get('app_webcomp_baseurl');
    $postkey  = sfConfig::get('app_webcomp_postkey');
    $endpoint = sfConfig::get('app_webcomp_enrollletter');

    $url = $baseurl.'/'.$endpoint.'?postkey='.$postkey.'&em='.$student->getEmail().'&fmid='.$fmid;

    //set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, count($fields));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
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
        $msg = 'ok, enroll letter triggered';
        

      }
      else {
        $msg = 'failed, system refused trigger for unknown reason';
      }
    }
    else {
      $msg = 'failed, was not able to connect to database';
    }
    
  }


}
