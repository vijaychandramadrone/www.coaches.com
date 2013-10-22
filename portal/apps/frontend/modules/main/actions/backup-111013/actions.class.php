<?php

/**
 * main actions.
 *
 * @package    sf_sandbox
 * @subpackage main
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */


  // Get Events:  curl 'http://webcomp.modelsmith.com/fmi-test/webcomp/portal_events.php?course_type_id=120&start_date=2011/12/01&postkey=fjgh15t'
  // Get Courses: curl 'http://webcomp.modelsmith.com/fmi-test/webcomp/portal_courses.php?fmid=25780&postkey=fjgh15t'

class mainActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $this->forward('main', 'mySchedule');
  }

  public function executeHelp(sfWebRequest $request)
  {
    return sfView::SUCCESS;
  }


  public function executeError404(sfWebRequest $request)
  {
    return sfView::SUCCESS;
  }

  public function executeLogout(sfWebRequest $request)
  {
    // sign in this user session
    $this->getContext()->getUser()->signOut();

    //$this->forward('main', 'login');
    return sfView::SUCCESS;
  }

  public function executeLogin(sfWebRequest $request)
  {
    $this->getResponse()->addCacheControlHttpHeader('no-cache');
    $this->msg = $request->getParameter( 'msg' );
    $this->logMessage('********************************************* Login');

    $referer = $request->getReferer();
    $this->getContext()->getUser()->setReferer($referer);
    $this->logMessage('Referer: '.$referer);
    
    $system_available = file_get_contents('http://webcomp.modelsmith.com/fmi-test/webcomp2_newFM/abletogetrecord.php?postkey=fjgh15t&em=ping@me.com');
    if($system_available != 'ok'){
      //$this->setTemplate('maintenance');
    }

    return sfView::SUCCESS;
  }

  public function executeMaintenance(sfWebRequest $request)
  {
    $this->getResponse()->addCacheControlHttpHeader('no-cache');
    return sfView::SUCCESS;
  }

  public function executeLoginProcess(sfWebRequest $request)
  {
    $this->getResponse()->addCacheControlHttpHeader('no-cache');
    $email    = $request->getParameter( 'email' );
    $password = $request->getParameter( 'password' );
    
    $result = StudentPeer::login( $email, $password );
    $result_r = print_r($result, true);
    
    $this->logMessage('********************************************* LoginProcess');
    $this->logMessage('Login result: '.$result_r);
    if($result['status'] == 'ok'){
      // get the local student record
      $student = StudentPeer::retrieveByEmail( $email );

      if(!isset($student)){
        $student = StudentPeer::newStudent( $email );
        if($student->getFmid() < 1){

        }
      }

      // identify the student to the user session
      $this->getContext()->getUser()->setStudent( $student );

      // sign in this user session
      $this->getContext()->getUser()->signIn();


      $this->getUser()->setAttribute('result',$result);

      $result = $student->refreshRecord();
      if($student->getFmid() < 1){ // try again
        $result = $student->refreshRecord();
      }
        // if($student->getFmid() < 1){
        //   $this->redirect('main/problem');
        // }

      $redirect = $this->getUser()->getAttribute('redirect');
      if(preg_match('/registerStep2/i',$redirect)){
        $this->getUser()->setAttribute('redirect','');
        $this->redirect('assisting/registerStep2');
      }

      $this->redirect('main/mySchedule');
    }
   
    $msg = "The email address or password did not match our records - Please try again.";
    $this->redirect('main/login?msg='.$msg);
    //$this->redirect('main/login');
  }

  public function executeProblem(sfWebRequest $request)
  {
    $this->student = $this->getContext()->getUser()->getStudent();
    $this->result = $this->getUser()->getAttribute('result');

    return sfView::SUCCESS;
  }

  public function executeRequestNewPassword(sfWebRequest $request)
  {
    $this->getResponse()->addCacheControlHttpHeader('no-cache');
    $this->msg = $request->getParameter( 'msg' );

    return sfView::SUCCESS;
  }

  public function executeRequestNewPasswordProcess(sfWebRequest $request)
  {
    $this->getResponse()->addCacheControlHttpHeader('no-cache');
    $email = $request->getParameter( 'email' );
    $email = preg_replace("/[^A-Za-z0-9\.\-\_\@]/","",$email); // untaint

    $msg = '';

    // check whether email address exists in database
    // curl to webcomp (timeout 10 secs)
    $ch = curl_init();

    $baseurl  = sfConfig::get('app_webcomp_baseurl');
    $postkey  = sfConfig::get('app_webcomp_postkey');
    $endpoint = sfConfig::get('app_webcomp_recordexists');

    $url = $baseurl.'/'.$endpoint.'?postkey='.$postkey.'&em='.$email;

    //set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);  // timeout after 10 seconds

    //execute post
    $response = curl_exec($ch);    
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
    
    if($httpCode == 200){
      // $result is JSON
      // if result = ok, then update transaction status in org record 
      
      if(preg_match('/ok/i',$response)){
        // find student in local DB
        $student = StudentPeer::retrieveByEmail( $email );

        if(!isset($student)){
          // create record
          $student = StudentPeer::newStudent( $email );
        }

        // send email with resetkey
        $resetKey = $student->newResetKey();
        $subject = 'CTI Password Reset Request';
        $url = 'http://www.thecoaches.com' . $this->getController()->genUrl('main/newPassword?k='.$resetKey);
        $message = <<<EOM
We have received your request for a password reset. Please click the
following link to continue. If you did not make this request, please
disregard and delete this email.

$url

The Coaches Training Institute
1-800-691-6008
EOM;
        $student->sendPortalEmail( $subject, $message );

        $msg = 'Your request has been sent. Please check your email.';

      }
      else {
        $msg = 'Your email is not on file. Please contact Customer Service at 1-800-691-6008.';
      }
    }
    else {
      // try local database here?
      $msg = 'Your email is not on file. Please contact Customer Service at 1-800-691-6008.';
    }
    
    $this->msg = $msg;
    return sfView::SUCCESS;
    //$this->redirect('main/requestNewPassword?msg='.$msg);
  }

  public function executeNewPassword(sfWebRequest $request){
    $this->msg = '';
    $this->ok  = $request->getParameter('ok');
    if($this->ok == 1){
      return sfView::SUCCESS;
    }

    $this->key = $request->getParameter('k');
    $this->key = preg_replace('/[^A-Z0-9\-]/','',$this->key); // untaint the reset key

    // check for key
    $student = StudentPeer::retrieveByKey( $this->key );

    // if not OK, redirect to login
    if(!isset($student)){
      $this->redirect('main/login');
    }
  
    $this->email = $student->getEmail();

    return sfView::SUCCESS;
  }

 public function executeNewPasswordProcess(sfWebRequest $request){
    $password  = $request->getParameter('password');
    $verify    = $request->getParameter('verify');
    $submitbtn = $request->getParameter('submitbtn');

    $this->key = $request->getParameter('k');
    $this->key = preg_replace('/[^A-Z0-9\-]/','',$this->key); // untaint the reset key

    // check for key
    $student = StudentPeer::retrieveByKey( $this->key );


    // if not OK, redirect to login
    if(!isset($student)){
      $this->redirect('main/login');
    }

    if($password != $verify){
      $this->redirect('main/newPassword?k='.$this->key.'&msg=Passwords don\'t match, please try again');
    }

    $student->setNewPassword( $password );

    $this->redirect('main/newPassword?k='.$this->key.'&ok=1');
  }



  public function executeNewEmail(sfWebRequest $request){

    $this->key = $request->getParameter('k');
    $this->key = preg_replace('/[^A-Z0-9\-]/','',$this->key); // untaint the reset key

    // check for key
    $student = StudentPeer::retrieveByKey( $this->key );

    // if not OK, redirect to login
    if(!isset($student)){
      $this->redirect('main/mySchedule');
    }
  
    // we verified that the new email address is good
    $student->updateEmailAddress();
 
  return sfView::SUCCESS;

  }


 


  // ================ Schedules and Events ================

  public function executeMySchedule(sfWebRequest $request){

    $student = $this->getContext()->getUser()->getStudent();

    if(!isset($student)){
      $this->redirect('main/login');
    }


    $this->enrollments = $student->getMySchedule( 'all' ); 

    // get cancel message, if any
    $this->msg = $this->getUser()->getAttribute( 'cancel_msg' );
    $this->getUser()->setAttribute( 'cancel_msg', '' );

    return sfView::SUCCESS;
  }

 public function executeMySchedule2(sfWebRequest $request){
    $student = $this->getContext()->getUser()->getStudent();

    if(!isset($student)){
      $this->redirect('main/login');
    }

    $this->enrollments = $student->getMySchedule( 'all' ); 

    // get cancel message, if any
    $this->msg = $this->getUser()->getAttribute( 'cancel_msg' );
    $this->getUser()->setAttribute( 'cancel_msg', '' );

    return sfView::SUCCESS;
  }

 public function executeMySchedule3(sfWebRequest $request){
    $student = $this->getContext()->getUser()->getStudent();

    if(!isset($student)){
      $this->redirect('main/login');
    }


    $this->enrollments = $student->getMySchedule( 'all' ); 

    // get cancel message, if any
    $this->msg = $this->getUser()->getAttribute( 'cancel_msg' );
    $this->getUser()->setAttribute( 'cancel_msg', '' );

    return sfView::SUCCESS;
  }


  public function executeCancelConfirmation(sfWebRequest $request){

    $student = $this->getContext()->getUser()->getStudent();

    // get cancel message, if any
    $this->msg = $this->getUser()->getAttribute( 'cancel_msg' );
    $this->getUser()->setAttribute( 'cancel_msg', '' );

    //$enrollment    = EnrollmentPeer::retrieveByPk( $this->enrollment_id );
    //$this->assist  = $enrollment->isAssisting();

    return sfView::SUCCESS;
  }

  public function executeRegAssist(sfWebRequest $request){
    $e = $request->getParameter('e',0);
    $this->redirect('assisting/regAssist?e='.$e);
  }


  public function executeCancel(sfWebRequest $request){

    $student                 = $this->getContext()->getUser()->getStudent();
    $enrollment_id_to_cancel = $request->getParameter('e',0);
    $nobtn                   = $request->getParameter('nobtn');
    $yesbtn                  = $request->getParameter('yesbtn');

    $this->msg           = '';
    $result              = array( );
    $this->enrollment_id = preg_replace("/[^0-9]/",'',$enrollment_id_to_cancel); // strip non-numeric characters

    // if no, redirect to mySchedule
    if($nobtn == 'No'){ 
      $this->redirect('main/mySchedule');
    }

    // check if enrollment belongs to student
    $this->forward404Unless( $student->enrollmentIdIsValid( $this->enrollment_id ) );
    
    // if confirmed, cancel enrollment and redirect
    if($yesbtn == 'Yes, Cancel Course'){ 
      $this->msg = $student->cancelEnrollment( $this->enrollment_id );
      if( preg_match("/ok/",$this->msg) ){
        $enrollment = EnrollmentPeer::retrieveByPk( $enrollment_id );
        if(isset($enrollment)){
          $event = $enrollment->getEvent();
          $this->getUser()->setAttribute( 'cancel_msg','Your registration has been cancelled for the following event: '.$event->getName().', '.$event->getStartDate() );
        }
        $this->redirect('main/cancelConfirmation');
      }
    }


    // else get event name and show confirm dialog
    $enrollment    = EnrollmentPeer::retrieveByPk( $this->enrollment_id );
    $this->assist  = $enrollment->isAssisting();
    $this->forward404Unless( isset($enrollment) ); // should never happen
    $this->event   = $enrollment->getEvent();
    return sfView::SUCCESS;
  }



  // ================ CRON Actions - to be initiated from curl via cron ================

  public function executeCheckAssistingWaitlists(sfWebRequest $request){
    // call as follows: curl 'http://www.thecoaches.com/portal/frontend_dev.php/main/checkAssistingWaitlists?key=1qASw2'
    $key = $request->getParameter('key',0);
    $this->forward404Unless( $key == '1qASw2');

    $this->result = EnrollmentPeer::checkAssistingWaitlists();

    return sfView::SUCCESS;
  }

  public function executeAssistingAvailable(sfWebRequest $request){
    // call as follows: curl 'http://www.thecoaches.com/portal/frontend_dev.php/main/assistingAvailable?key=1qASw2'
    $key = $request->getParameter('key',0);
    $this->forward404Unless( $key == '1qASw2');

    $this->events = EventPeer::getCoreEventsWithAssistingOpenings();

    return sfView::SUCCESS;
  }

  public function executeUpdateEventsFromFM(sfWebRequest $request){
    // call as follows: curl 'http://www.thecoaches.com/portal/frontend_dev.php/main/updateEventsFromFM?ctid=3&key=1qASw2'
    $key = $request->getParameter('key',0);
    $this->forward404Unless( $key == '1qASw2');

    $course_type_id = $request->getParameter('ctid',0);

    // curl to webcomp (timeout 10 secs)
    $ch = curl_init();

    $baseurl  = sfConfig::get('app_webcomp_baseurl');
    $postkey  = sfConfig::get('app_webcomp_postkey');
    $endpoint = sfConfig::get('app_webcomp_events');

    // get 'Publish' events
    $url = $baseurl.'/'.$endpoint.'?postkey='.$postkey.'&course_type_id='.$course_type_id.'&start_date='.date('Y-m-d');


    //set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);  // timeout after 30 seconds

    //execute post
    $json     = curl_exec($ch);    
    $json = preg_replace('/"\n"/','""',$json);

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
    
    if($httpCode == 200){
      // $result is JSON
      // if result = ok, then update transaction status in org record 
      $result = json_decode( $json );
      
      $this->first_result = EventPeer::updateFromFM( $json );

      $this->url    = $url;
      $this->result = $result;
      $this->json   = $json;
    }
    else {
      $this->result = array( );
    }

    // curl to webcomp (timeout 10 secs)
    $ch = curl_init();

    $baseurl  = sfConfig::get('app_webcomp_baseurl');
    $postkey  = sfConfig::get('app_webcomp_postkey');
    $endpoint = sfConfig::get('app_webcomp_events');

    // get "Don't Publish" events ( np=1 )
    $url = $baseurl.'/'.$endpoint.'?postkey='.$postkey.'&np=1'.'&course_type_id='.$course_type_id.'&start_date='.date('Y-m-d');

    //set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);  // timeout after 30 seconds

    //execute post
    $json     = curl_exec($ch);   
    $json = preg_replace('/"\n"/','""',$json); 
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
    
    if($httpCode == 200){
      // $result is JSON
      // if result = ok, then update transaction status in org record 
      $result = json_decode( $json );
      
      $this->second_result = EventPeer::unpublishFromFM( $json );

      $this->json2   = $json;
      $this->result2 = $result;
      $this->url2    = $url;

    }
    else {
      $this->result2 = array( );
    }

    return sfView::SUCCESS;
  }

// ============ Misc =============

  public function executeBasicTemplate(sfWebRequest $request) {
    return sfView::SUCCESS;
  }

  public function executeCourseTaken(sfWebRequest $request) {
     $this->student                 = $this->getContext()->getUser()->getStudent();
     
    return sfView::SUCCESS;
  }

  public function executeEventEligible(sfWebRequest $request) {
     $this->student                 = $this->getContext()->getUser()->getStudent();
     $this->event_id = $request->getParameter('id');
     $this->eligible = $this->student->eventIdIsEligible( $this->event_id ) ? 'yes' : 'no';
    return sfView::SUCCESS;
  }
}
