<?php

class myUser extends sfBasicSecurityUser
{
  // see http://www.symfony-project.org/cookbook/1_2/en/cookie for remember_me code

  public function signIn()
  {
    $this->setAuthenticated(true);
  }
 
  public function signOut()
  {
    $this->setAuthenticated(false);
  }

  public function setStudent( $student ){
    if(isset($student)){
      $this->setAttribute('student_id', $student->getId() );
    }
  }

  public function getStudent( ){
    $student_id = $this->getAttribute('student_id', 0);
    $student = StudentPeer::retrieveByPk( $student_id );

    return $student;
  }

  public function setReferer( $v ){
      $this->setAttribute('referer', $v );
  }
  
  public function getReferer( ){
    $referer = $this->getAttribute('referer', '');
    return $referer;
  }

  
}
