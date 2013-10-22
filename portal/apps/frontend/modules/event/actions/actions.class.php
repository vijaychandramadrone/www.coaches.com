<?php

/**
 * event actions.
 *
 * @package    sf_sandbox
 * @subpackage event
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */


  // NEXT STEPS:
  // - finish up Register, using Cancel as pattern
  // - Add buttons to Register template, as in Cancel
  // - test registration



class eventActions extends sfActions
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

// see  /home/thomas/code/cti_scripts/portal_events in corp9 for list of course types

  public function executeRegistration(sfWebRequest $request)
  {
    $this->student       = $this->getContext()->getUser()->getStudent();
    $this->events        = $this->student->getEligibleEvents();
    $this->cae_labs      = array( );
    $this->corp_labs     = array( );
    $this->learning_labs = array( );
    foreach( $this->events as $event ){
      if(preg_match('/cae/i',$event->getName())){
        $this->cae_labs[] = $event;
      }
      else if(preg_match('/corp/i',$event->getName())){
        $this->corp_labs[] = $event;
      }
      else {
        $this->learning_labs[] = $event;
      }
    }

    return sfView::SUCCESS;
  }


  public function executeRegistration1(sfWebRequest $request)
  {
    $this->student       = $this->getContext()->getUser()->getStudent();
    $this->events        = $this->student->getEligibleEvents();
    $this->cae_labs      = array( );
    $this->corp_labs     = array( );
    $this->learning_labs = array( );
    foreach( $this->events as $event ){
      if(preg_match('/cae/i',$event->getName())){
        $this->cae_labs[] = $event;
      }
      else if(preg_match('/corp/i',$event->getName())){
        $this->corp_labs[] = $event;
      }
      else {
        $this->learning_labs[] = $event;
      }
    }

    return sfView::SUCCESS;
  }


  public function executeEventInfo(sfWebRequest $request)
  {
    $this->info = 'Description not provided';
    $id = $request->getParameter('id',0);
    $event = EventPeer::retrieveByPk( $id );
    if(isset($event)){
      $this->info = $event->getDescription();
    }
    
    return sfView::SUCCESS;
  }


    
  public function executeRegisterNotEligible(sfWebRequest $request)
  {
    $this->diagnostics = $this->getUser()->getAttribute('register_fail');
    return sfView::SUCCESS;
  }


  public function executeRegister(sfWebRequest $request)
  {
    $student = $this->getContext()->getUser()->getStudent();
    $event_id_to_register = $request->getParameter('e',0);
    $nobtn                   = $request->getParameter('nobtn');
    $yesbtn                  = $request->getParameter('yesbtn');

    $this->event_id =  preg_replace("/[^0-9]/",'',$event_id_to_register); // strip non-numeric characters
    $this->msg = ''; 
    $result = array( );


    // if no, redirect to mySchedule
    if($nobtn == 'No'){ 
      $this->redirect('event/registration');
    }

    $this->event    = EventPeer::retrieveByPk( $this->event_id );

    // check if student is eligible to enroll in this event
    if(! $student->eventIdIsEligible( $this->event_id  ) ) {
      $this->getUser()->setAttribute('register_fail','not eligible for event id: '.$this->event_id.', min_level: '.$this->event->getMinimumLevel().', my level: '.$student->getLevel() );
      $this->redirect('event/registerNotEligible');
    }
    
// TODO: 11/12/2012
// - change $student->waitlist to $student->enroll

    if(preg_match('/yes/i',$yesbtn)){ 
      // register enrollment and redirect

      if($this->event->isWaitlisted() ){
        //$result = $student->waitlist( $this->event_id );
        //$result['msg'] = 'Enrollment failed: waitlisting is not yet available';
        $result = $student->enroll( $this->event_id );
      }
      else {
        $result = $student->enroll( $this->event_id );
      }


      $this->msg = $result['msg'];
      if(preg_match("/ok/",$this->msg)){
        $this->redirect('event/registerThankYou?e='.$event_id_to_register);
      }
    }
    
    // else get event name and show confirm dialog
    $this->forward404Unless( isset($this->event) ); // should never happen
    return sfView::SUCCESS;
  }

  public function executeRegisterThankYou(sfWebRequest $request)
  {
    $event_id_to_register = $request->getParameter('e',0);
    $this->event_id =  preg_replace("/[^0-9]/",'',$event_id_to_register); // strip non-numeric characters
    $this->event    = EventPeer::retrieveByPk( $this->event_id );

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
