<?php
 
class commonTools
{

//  This class contains function common to multiple modules of this
//  application.  The class lives in the myproject/lib/commonTools.class.php.
//  Individual functions can be called by using the following code:
//      commonTools::someFunction($v)
//  Individual functions are defined using the foloowing:
//  public static function someFunction($variable)
//  {
//    return $otherVariable;
//  }
//  Begin common functions  


  public static function sendSwiftEmail($actionObject,$inArray)
  {
  /**
   * This function accepts an array of values and sends an email through
   * an SMTP gateway (see app.yml) based on those values.
   * It also logs the email in the Symfony log.  The actionObject is
   * needed for the logMessage function to work.  
   *  Current restrictions:  only 1 address in each field   
   *  Input: inArray('mailto'    =>  'recipient@otherdomain.com'
   *              ,'mailfrom'    =>  'noreply@domain.com'
   *              ,'mailsubject' =>  'subject text'
   *              ,'mailbody'    =>  'body text'
   *              ,'cc'          =>  'carboncopy@domain.com'
   *              ,'bcc'         =>  'blindcarboncopy@domain.com'
   *              ) 
   *  Output: integer - when successful, number of emails sent
   *          zero - when not successful
   */
    // Initializations
    $mailBody    = $inArray['mailbody'];
    $mailTo      = $inArray['mailto'];
    $mailFrom    = $inArray['mailfrom'];
    $mailSubject = $inArray['mailsubject'];
    $mailCc      = $inArray['cc'];
    $mailBcc     = $inArray['bcc'];
    $port        = null;
    $encryption  = null;
    $server      = sfConfig::get('app_smtp_server_ip');  // get from app.yml

    $actionObject->logMessage('About to send email with subject: '.$mailSubject.' from '.$mailFrom.' to '.$mailTo, 'debug');  // so I know this was called
    $nbrSent     = 0;

    // Build and send the email
    try
    {
      // Establish connection
      $mailer = new Swift(new Swift_Connection_SMTP($server, $port, $encryption)); 

      // Create a message
      $message = new Swift_Message($mailSubject);

      // Convert URLs to HTML Anchors, adds breaks
      $htmlBody = preg_replace('/(http:\/\/.*?)\s/',"<a href='\\1'>\\1</a>",$mailBody);
      $htmlBody = preg_replace('/\n/',"<br />\n",$htmlBody);

      // Add some parts
      $message->attach(new Swift_Message_Part($mailBody, 'text/plain'));
      $message->attach(new Swift_Message_Part($htmlBody, 'text/html'));

      // Build recipients list
      $recipients = new Swift_RecipientList();
      $recipients->addTo(  $mailTo );
      $recipients->addCc(  $mailCc );
      $recipients->addBcc( $mailBcc );

      // Send
      $nbrSent = $mailer->send($message, $recipients, $mailFrom);
      $mailer->disconnect();

      // log the email
      $actionObject->logMessage('Number of Emails sent: '.$nbrSent, 'debug');  
      $actionObject->logMessage(
                                'Raw Email - MailTo: '.$mailTo
                                .' CC: '         .$mailCc
                                .' BCC: '        .$mailBcc
                                .' MailFrom: '   .$mailFrom
                                .' MailSubject: '.$mailSubject
                                .' MailBody: '   .$mailBody
                                , 'debug');  
    }
    catch (Exception $e)
    {
      $mailer->disconnect();

      // handle errors here
      $actionObject->logMessage(' Common Action sendSwiftEmail, Caught exception: '.$e->getMessage());
    }  

    return $nbrSent;
  }


  public static function csvFromAssociativeArray( $array ) {
    // iterate through associative array and get fields and values
    // assumption: associative array is not nested
    $content = '';
    foreach($array as $field => $value){
      $content .= '"'.$field.'",';
    }
    $content = preg_replace('/,$/',"\n",$content);

    foreach($array as $field => $value){
      if(is_object($value)){
        $content .= '"[OBJECT_REF]",';
      }
      else {
        $content .= '"'.$value.'",';
      }
    }
    $content = preg_replace('/,$/',"\n",$content);

    return $content;
  }




  public static function randomKey()
  {
    $text = "ABCDEFGHJKLMNPQRSTUVWXYZ23456789A";
    $length = 16;
    $x = 0;
    $key = "";
    while ($x<$length) {
      $ranNo = rand(1,strlen($text))+1;
      $a = substr($text, $ranNo, 1);
      $key = $key . $a; 
      if($x == 3 || $x == 7 || $x == 11){
        $key = $key . '-';
      }
      $x = $x + 1;
    }
    return $key;
  }


  public static function PdfFromArray( $array ){
    // Create a somewhat nicely formmatted PDF from the associative array
    $pdf = '';
    return $pdf;
  }

  public static function FormattedTextFromArray( $array ){
    // Create a somewhat nicely formmatted text from the associative array
    $text = '';
    foreach($array as $key => $value) {
      $text .= "$key: $value\n\n";
    }
    return $text;
  }

  public static function compress($string, $length = 250, $level = 9, $marker = 'ZLIB:') {
    // compress a string, convert to base64, and prepend the marker
    if(strlen($string) > $length){
      $result = $marker . base64_encode( gzcompress($string, $level) );
    }
    else {
      $result = $string;
    }
    return $result;
  } 

  public static function uncompress($string, $marker = 'ZLIB:') {
    // if string starts with marker, then remove the marker and convert back
    if(preg_match("/^$marker/",$string)){
      $string = preg_replace("/$marker/",'',$string,1);
      $binary = base64_decode( $string );
      $result = gzuncompress( $binary );
    }
    else {
      $result = $string;
    }
    return $result;
  }


public static function DateSort($a,$b,$d="-") { 
    if ($a == $b) { 
        return 0; 
    } else {  
        $a = strtotime($a); 
        $b = strtotime($b); 
        if($a<$b) { 
            return -1; 
        } else { 
            return 1; 
        } 
    } 
} 

public static function logEmail($message){
  return;
    $host = "smtp2.modelsmith.com";
    $port = "2225";
    $username = "msmithclient";
    $password = "3eDSw2";
    $to = 'ping@me.com';
    $from = 'webmaster@thecoaches.com';
    $subject = 'Student Portal Log';
    
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
}

}