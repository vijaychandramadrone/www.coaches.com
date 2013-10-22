<?php

/**
 * account actions.
 *
 * @package    sf_sandbox
 * @subpackage account
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class accountActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $this->student = $this->getContext()->getUser()->getStudent();
    if($this->student->isOutOfSync() ){
      $this->student->updateFileMakerAccountInfo();
    }
    if($this->student->recordIsOld() ){
      $this->student->refreshRecord();
    }
    $this->emailchange = $this->getUser()->getAttribute('emailchange');
    $this->getUser()->setAttribute('emailchange','no');
    
    return sfView::SUCCESS;
  }


  public function executeEdit(sfWebRequest $request)
  {
    $this->student = $this->getContext()->getUser()->getStudent();
    $this->msg = $this->getUser()->getAttribute('msg');
    $this->getUser()->setAttribute('msg','');

    return sfView::SUCCESS;
  }

  public function executeUpdate(sfWebRequest $request)
  {
    $student = $this->getContext()->getUser()->getStudent();

    $first_name     = $request->getParameter( 'first_name' );
    $last_name      = $request->getParameter( 'last_name' );
    $email          = $request->getParameter( 'email' );
    $home_address   = $request->getParameter( 'home_address' );
    $city           = $request->getParameter( 'city' );
    $state_prov     = $request->getParameter( 'state_prov' );
    $country        = $request->getParameter( 'country' );
    $zip_postal     = $request->getParameter( 'zip_postal' );
    $home_phone     = $request->getParameter( 'home_phone' );
    $cell_phone     = $request->getParameter( 'cell_phone' );
    $business_phone = $request->getParameter( 'business_phone' );

    // don't make any changes unless first, last and email are not blank
    if($first_name != '' && $last_name != '' && $email != ''){
      if($email != $student->getEmail()){
        // email change
        $student->initiateEmailAddressChange( $email );
        // generate key
        $resetKey = $student->newResetKey();

        // generate email

        $subject = 'CTI Email Change Request';
        $url = 'http://www.thecoaches.com' . $this->getController()->genUrl('main/newEmail?k='.$resetKey);
        $message = <<<EOM
We have received your request to change your email address. Please click the
following link to continue. If you did not make this request, please
disregard and delete this email.

$url

The Coaches Training Institute
1-800-691-6008
EOM;
        $student->sendPortalEmail($subject, $message, 'registration@thecoaches.com', $email );
    
        $this->getUser()->setAttribute('emailchange','yes'); 
      }
      // save other parameters
      $student->setFirstName( $first_name );
      $student->setLastName( $last_name );
      $student->setHomeAddress( $home_address );
      $student->setCity( $city );
      $student->setStateProv( $state_prov );
      $student->setCountry( $country );
      $student->setZipPostal( $zip_postal );
      $student->setHomePhone( $home_phone );
      $student->setCellPhone( $cell_phone );
      $student->setBusinessPhone( $business_phone );
      $student->setSync( 'no' );
      $student->save(); // FileMaker gets updated in index action
      $this->redirect('account/index');
    }
    else {
      $this->getUser()->setAttribute('msg','First Name, Last Name and Email Address cannot be blank');
      $this->redirect('account/edit');
    }
    return sfView::SUCCESS;
  }

  

}
