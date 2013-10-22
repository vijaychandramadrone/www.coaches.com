<?php

class StudentPeer extends BaseStudentPeer
{

  public static function login( $email, $password ){
    $result = array( 'student' => null, 'status' => 'failed', 'return_code'=>'', 'url' => '', 'email' => $email );
 
    // untaint email
    $email = preg_replace("/[^a-zA-Z0-9\.\-\_\@]/",'',$email);

    // URL encode email and password
    $email    = urlencode( $email );
    $password = urlencode( $password );

    // curl to webcomp (timeout 10 secs)
    $ch = curl_init();

    $baseurl  = sfConfig::get('app_webcomp_baseurl');
    $postkey  = sfConfig::get('app_webcomp_postkey');
    $endpoint = sfConfig::get('app_webcomp_authen');

    $url = $baseurl.'/'.$endpoint.'?postkey='.$postkey.'&em='.$email.'&pw='.$password;

    $result['url'] = $url;

    //set the url
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
      $result['return_code'] = json_decode( $json );
      if(isset($result['return_code']) && $result['return_code'] == 'ok'){
        // find student in database and authenticate. If not in database, add student

        //$msg = 'ok';
        $result['status'] = 'ok';

      }
      else {
        //$msg = 'failed';
      }
    }
    
    //close connection
    curl_close($ch);
    

    // if timeout
    //   if local available
    //     if match, return student
    //   return "try again later"

    // if ok
    //   if local does not exist
    //     new Student
    //   return student
    return $result;
  }

  public static function retrieveByEmail( $email ){
    $c = new Criteria();
    $c->add(StudentPeer::EMAIL, $email);
    $student = StudentPeer::doSelectOne( $c );
   
    return $student;
  }

  public static function retrieveByKey( $key ){
    $c = new Criteria();
    $c->add(StudentPeer::RESET_KEY, $key);
    $student = StudentPeer::doSelectOne( $c );
   
    return $student;
  }

  public static function newStudent( $email ){
    $student = new Student();
    $student->setEmail( $email );
    $student->save();


    return $student;
  }

}
