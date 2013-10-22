<?php

/**
 * assisting actions.
 *
 * @package    sf_sandbox
 * @subpackage assisting
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class assistingActions extends sfActions
{
  
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $this->forward('default', 'module');
  }

  public function executeRegistration(sfWebRequest $request)
  {
    $student = $this->getContext()->getUser()->getStudent();
    $this->events = $student->getEligibleAssistingEvents();
    return sfView::SUCCESS;
  }

 

  public function executeRegisterStep1(sfWebRequest $request)
  {
    // Note: This step does not require user to be logged in. This step captures the
    // user's registration wishes from the public assisting page (http://www.thecoaches.com/coach-training/be-an-assistant/index-portal.html)
    // and stores them in the session. It then redirects to registerStep2, which does require a login.

   // store redirect in case we are diverted to login page
    $this->getUser()->setAttribute('redirect', 'assisting/registerStep2');

    // get parameter holder
    $this->params = $request->getParameterHolder()->getAll();

    foreach($this->params as $key => $value){ // loop through ALL events (event1:ATL, event1:SR, event1:DC, event2:ATL ...)
      // find only those that have numeric IDs
      if(preg_match('/event1/',$key) && preg_match('/\d+:/',$value)){
        $event1 = preg_replace('/:.*/','',$value); // remove everything after and including colon ':'
      }
      else if(preg_match('/event2/',$key) && preg_match('/\d+:/',$value)){
        $event2 = preg_replace('/:.*/','',$value); // remove everything after and including colon ':'
      }
      else if(preg_match('/event3/',$key) && preg_match('/\d+:/',$value)){
        $event3 = preg_replace('/:.*/','',$value); // remove everything after and including colon ':'
      }
    }

    // reporting: show message if frontend_dev
    $register_log = '/tmp/portal_assist.log';
    $info = Date('Y-m-d H:i:s')." registerStep1: event IDs: $event1 $event2 $event3\n";
    file_put_contents($register_log,$info,FILE_APPEND);

    $this->forward404Unless($event1 > 0 || $event2 > 0 || $event3 > 0);

    // store parameters
    $this->getUser()->setAttribute('event1', $event1);
    $this->getUser()->setAttribute('event2', $event2);
    $this->getUser()->setAttribute('event3', $event3);

   
    // redirect to registerStep2
    $this->redirect('assisting/registerStep2');

    return sfView::SUCCESS;
  }

  public function executeRegisterStep2(sfWebRequest $request)
  {
    $student = $this->getContext()->getUser()->getStudent();

    $this->getUser()->setAttribute('redirect', '');

    // get stored parameters
    $this->event1 = $this->getUser()->getAttribute('event1');
    $this->event2 = $this->getUser()->getAttribute('event2');
    $this->event3 = $this->getUser()->getAttribute('event3');

    // assign event1 if available
    $event    = EventPeer::retrieveByFmid( $this->event1 );
    if( isset($event)){
      $event_id = $event->getId();
      if($student->eventIdIsEligible( $event_id )){
        // register enrollment and redirect
       
        if($event->isWaitlisted() ){
          $result = $student->waitlistAssist( $event_id );
        }
        else {
          $result = $student->assist( $event_id );
        }
        
        $this->msg = $result['msg'];
        $enrollment = $result['enrollment'];
        if(preg_match("/ok/",$this->msg)){
          $this->getUser()->setAttribute('registered_event_id',$event_id);
          $this->getUser()->setAttribute('reg_action',@$enrollment->getDisplayStatus());
          $this->redirect('assisting/registerThankYou');
        }
      }
      else {
        // you are not eligible for this event
        $this->getUser()->setAttribute('event1_fail','not eligible for event1: '.$this->event1.' (event id: '.$event_id.')');
        $this->redirect('assisting/'.$student->getEventEligibilityStatusMsg());
      }
    }
    else {
      $this->getUser()->setAttribute('event1_fail','event1 not in system: '.$this->event1);
      //The template registerTryAgainLater_1 contains a 
      // message "An error has occurred with your account.  Please contact CTI Customer Service at 1-800-691-6008, option 1 for assistance."
      $this->redirect('assisting/registerTryAgainLater_1');
    }

    // assign event2 if available
    $event    = EventPeer::retrieveByFmid( $this->event2 );
    if(isset($event)){
      $event_id = $event->getId();
      if($student->eventIdIsEligible( $event_id )){
        // register enrollment and redirect
      
        if($event->isWaitlisted() ){
          $result = $student->waitlistAssist( $event_id );
        }
        else {
          $result = $student->assist( $event_id );
        }
        
        $this->msg = $result['msg'];
        $enrollment = $result['enrollment'];
        if(preg_match("/ok/",$this->msg)){
          $this->getUser()->setAttribute('registered_event_id',$event_id);
          $this->getUser()->setAttribute('reg_action',@$enrollment->getDisplayStatus());
          $this->redirect('assisting/registerThankYou');
        }
      }
      else {
        // you are not eligible for this event
        $this->getUser()->setAttribute('event2_fail','not eligible for event2: '.$this->event2.' (event id: '.$event_id.')');
        $this->redirect('assisting/'.$student->getEventEligibilityStatusMsg());
      }
    }
    else {
      $this->getUser()->setAttribute('event2_fail','event2 not in system: '.$this->event2);
      //The template registerTryAgainLater_1 contains a 
      // message "An error has occurred with your account.  Please contact CTI Customer Service at 1-800-691-6008, option 1 for assistance."
      $this->redirect('assisting/registerTryAgainLater_1');
    }

    // assign event3 if available
    $event    = EventPeer::retrieveByFmid( $this->event3 );
    if(isset($event)){
      $event_id = $event->getId();
      if($student->eventIdIsEligible( $event_id )){
        // register enrollment and redirect
        if($event->isWaitlisted() ){
          $result = $student->waitlistAssist( $event_id );
        }
        else {
          $result = $student->assist( $event_id );
        }
        $this->msg = $result['msg'];
        $enrollment = $result['enrollment'];
        if(preg_match("/ok/",$this->msg)){
          $this->getUser()->setAttribute('registered_event_id',$event_id);
          $this->getUser()->setAttribute('reg_action',@$enrollment->getDisplayStatus());
          $this->redirect('assisting/registerThankYou');
        }
      }
      else {
        // you are not eligible for this event
        $this->getUser()->setAttribute('event3_fail','not eligible for event3: '.$this->event3.' (event id: '.$event_id.')');
        $this->redirect('assisting/'.$student->getEventEligibilityStatusMsg());
      }
    }
    else {
      $this->getUser()->setAttribute('event3_fail','event3 not in system: '.$this->event3);
      //The template registerTryAgainLater_1 contains a 
      // message "An error has occurred with your account.  Please contact CTI Customer Service at 1-800-691-6008, option 1 for assistance."
      $this->redirect('assisting/registerTryAgainLater_1');
    }
    

    // reporting: show message if frontend_dev
    $register_log = '/tmp/portal_register.log';
    $info = Date('Y-m-d H:i:s')." registerStep2: not assigned to course, not redirected\n";
    file_put_contents($register_log,$info,FILE_APPEND);

    // if not assigned, redirect to error
    $this->redirect('assisting/registerTryAgainLater');

    return sfView::SUCCESS;
  }

 public function executeRegAssist(sfWebRequest $request)
  {
    $student = $this->getContext()->getUser()->getStudent();
    $this->getUser()->setAttribute('redirect', '');

    $event_id_to_register = $request->getParameter('e',0);

  $register_log = '/tmp/portal_register.log';
 $info = Date('Y-m-d H:i:s')." regAssist: event_id: $event_id_to_register\n";
    file_put_contents($register_log,$info,FILE_APPEND);

    //$nobtn                   = $request->getParameter('nobtn');
    //$yesbtn                  = $request->getParameter('yesbtn');

    $event_id =  preg_replace("/[^0-9]/",'',$event_id_to_register); // strip non-numeric characters
    $this->msg = ''; 
    $result = array( );

    $event = EventPeer::retrieveByPk( $event_id );
    if(isset($event)){

      if($student->eventIdIsEligible( $event_id )){

        $result = $student->assist( $event_id );
          
        $this->msg = $result['msg'];
        $enrollment = $result['enrollment'];
        if(preg_match("/ok/",$this->msg)){
          $this->getUser()->setAttribute('registered_event_id',$event_id);
          $this->getUser()->setAttribute('reg_action',@$enrollment->getDisplayStatus());
          $this->redirect('assisting/registerThankYou');
        }
      }
      else {
        // you are not eligible for this event
        $this->getUser()->setAttribute('event1_fail','not eligible for event id: '.$event_id);
        $this->redirect('assisting/'.$student->getEventEligibilityStatusMsg());
      }
    }
    else {
      $this->getUser()->setAttribute('event1_fail','event not in system: '.$event_id);
      //The template registerTryAgainLater_1 contains a 
      // message "An error has occurred with your account.  Please contact CTI Customer Service at 1-800-691-6008, option 1 for assistance."
      $this->redirect('assisting/registerTryAgainLater_1');
    }
    

    // reporting: show message if frontend_dev
  
    $info = Date('Y-m-d H:i:s')." regAssist: not assigned to course, not redirected\n";
    file_put_contents($register_log,$info,FILE_APPEND);

    // if not assigned, redirect to error
    $this->redirect('assisting/registerTryAgainLater');

    return sfView::SUCCESS;
  }


  public function executeRegisterThankYou(sfWebRequest $request)
  {
    $event_id = $this->getUser()->getAttribute('registered_event_id');
    $this->event = EventPeer::retrieveByPk( $event_id );

    $this->reg_action = strtolower( $this->getUser()->getAttribute('reg_action') );

    return sfView::SUCCESS;
  }

  public function executeRegisterTryAgainLater(sfWebRequest $request)
  {
    $this->diagnostics = $this->getUser()->getAttribute('event1_fail').' '. $this->getUser()->getAttribute('event2_fail').' '. $this->getUser()->getAttribute('event3_fail');

    $register_log = '/tmp/portal_register.log';
    $info = Date('Y-m-d H:i:s')." registerTryAgainLater diagnostics: ".$this->diagnostics."\n";
    file_put_contents($register_log,$info,FILE_APPEND);
    
    return sfView::SUCCESS;
  }
  
  public function executeRegisterTryAgainLater_1(sfWebRequest $request)
  {
    $this->diagnostics = $this->getUser()->getAttribute('event1_fail').' '. $this->getUser()->getAttribute('event2_fail').' '. $this->getUser()->getAttribute('event3_fail');

    $register_log = '/tmp/portal_register.log';
    $info = Date('Y-m-d H:i:s')." registerTryAgainLater_1 diagnostics: ".$this->diagnostics."\n";
    file_put_contents($register_log,$info,FILE_APPEND);
    
    return sfView::SUCCESS;
  }

  public function executeRegisterTryAgainLater_2(sfWebRequest $request)
  {
    $this->diagnostics = $this->getUser()->getAttribute('event1_fail').' '. $this->getUser()->getAttribute('event2_fail').' '. $this->getUser()->getAttribute('event3_fail');

    $register_log = '/tmp/portal_register.log';
    $info = Date('Y-m-d H:i:s')." registerTryAgainLater_2 diagnostics: ".$this->diagnostics."\n";
    file_put_contents($register_log,$info,FILE_APPEND);
    
    return sfView::SUCCESS;
  }
  
  public function executeRegisterTryAgainLater_3(sfWebRequest $request)
  {
    $this->diagnostics = $this->getUser()->getAttribute('event1_fail').' '. $this->getUser()->getAttribute('event2_fail').' '. $this->getUser()->getAttribute('event3_fail');

    $register_log = '/tmp/portal_register.log';
    $info = Date('Y-m-d H:i:s')." registerTryAgainLater_3 diagnostics: ".$this->diagnostics."\n";
    file_put_contents($register_log,$info,FILE_APPEND);
    
    return sfView::SUCCESS;
  }
  
  public function executeWaitlistInfo(sfWebRequest $request)
  {
    return sfView::SUCCESS;
  }

  public function executeRegisterInfo(sfWebRequest $request)
  {
    return sfView::SUCCESS;
  }

}
