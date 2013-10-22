<?php

/**
 * admin actions.
 *
 * @package    sf_sandbox
 * @subpackage admin
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class adminActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $this->admin = $this->getContext()->getUser()->getStudent();
    if(!$this->admin->isAdmin()){
      $this->redirect('main/mySchedule');
    }

    // display search page
    
    $this->name = $request->getParameter('name');
    $this->enrollments = array( );

    if($this->name != ''){
      // search for currently enrolled students with this name
      $this->enrollments = EnrollmentPeer::currentEnrollmentsByName( $name );
    }

    return sfView::SUCCESS;
  }

  public function executeSearch(sfWebRequest $request)
  {
    $this->admin = $this->getContext()->getUser()->getStudent();
    if(!$this->admin->isAdmin()){
      $this->redirect('main/mySchedule');
    }

    // display search page
  
    // process search

    $this->students = array( );
    
    return sfView::SUCCESS;
  }

  public function executeStudent(sfWebRequest $request)
  {
    $this->admin = $this->getContext()->getUser()->getStudent();
    if(!$this->admin->isAdmin()){
      $this->redirect('main/mySchedule');
    }

    // display enrollments for this student

  }

  public function executeCancelEnrollment(sfWebRequest $request)
  {
    $this->admin = $this->getContext()->getUser()->getStudent();
    if(!$this->admin->isAdmin()){
      $this->redirect('main/mySchedule');
    }

    $this->redirect('admin/student');
  }

  public function executeCheckAssistingEvents(sfWebRequest $request)
  {
    // this function is called by a cronjob once a day, to determine if 
    // students waitlisted for assisting are now eligible for registration.
    // If so, send them an email

    $this->result = EventPeer::doAssistingWaitlistChecks();

    return sfView::SUCCESS;
  }

  public function executeFiveYearCheck(sfWebRequest $request)
  {
    $c = new Criteria();
    $c->add(StudentPeer::FMID, 94229); //Carol Green
    
    $student = StudentPeer::doSelectOne( $c );

    $course_type_id = 7; //ITB

    $this->msg = "Result: ".$student->courseTakenWithin5Years( $course_type_id );

    return sfView::SUCCESS;

  }
}
