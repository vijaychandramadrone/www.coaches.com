<?php



class EventPeer extends BaseEventPeer
{
  public static function createNewEvent( $fm_data ){
    // this is used in an action that is called
    // by the /home/thomas/code/cti_scripts/geteventsR_yaml script

  }

  public static function removeOldEvents(){

  }



// Example of data structure from portal_events.php
// http://webcomp.modelsmith.com/fmi-test/webcomp/portal_events.php?postkey=fjgh15t&course_type_id=120&start_date=2011/12/08stdClass Object
// (
//     [count] => 16
//     [events] => Array
//         (
//             [0] => stdClass Object
//                 (
//                     [fmid] => 16726
//                     [course_type_id] => 120
//                     [event] => Certification Info Webinar
//                     [date] => 12/14/2011
//                     [edate] => 12/13/2011
//                     [region] => 
//                     [location] => CXL-Online
//                     [publish] => Don't Publish
//                     [call_time] => Wed 9 AM - 10 AM PT
//                     [student_count] => 
//                     [record_id] => 16726
//                     [pod_name] => 
//                     [leader_name] => 
//                     [assistant_count] => 
//                     [assistant_wait_count] => 
//                     [booking_link] => https://www1.gotomeeting.com/register/534257961
//                     [mod_id] => 11/22/2011 13:07:18
//                     [mod_date] => 11/22/2011
//                     [mod_time] => 13:07:18
//                 )
//             [1] => stdClass Object
//                 (

  public static function getLocationsFromCtiDatabase(){
    $locations = array( );
  
    $server   = sfConfig::get('app_ctidatabase_server');  // see apps/frontend/config/app.yml
    $port     = sfConfig::get('app_ctidatabase_port');
    $user     = sfConfig::get('app_ctidatabase_user');
    $password = sfConfig::get('app_ctidatabase_password');

    $rdbh = mysql_connect( $server.':'.$port, $user, $password ); // make sure this server has DB grant

    if($rdbh){
      $query = sprintf( "SELECT site_code,site_name,address,city,state,zip,region FROM CTIDATABASE.site_data");
      $result = mysql_query( $query, $rdbh );
      if($result){ 
        while( $row = mysql_fetch_array($result) ){
          $items = array( );
          if($row['site_name'] != '') $items[] = $row['site_name'];
          if($row['address']   != '') $items[] = $row['address'];
          if($row['city']      != '') $items[] = $row['city'];
          if($row['state']     != '') $items[] = $row['state'];
          if($row['zip']       != '') $items[] = $row['zip'];
          

          $locations[ $row['site_code'] ] = implode(', ',$items);
        }
      }
    }

    if($rdbh){
      $query = sprintf( "SELECT region_code,region_name,country FROM CTIDATABASE.regions");
      $result = mysql_query( $query, $rdbh );
      if($result){ 
        while( $row = mysql_fetch_array($result) ){
          $locations[ $row['region_code'] ] = $row['region_name'];
        }
      }
    }

    return $locations;
  }

  public static function updateFromFM( $json ){ // see: curl 'http://www.thecoaches.com/portal/frontend_dev.php/main/updateEventsFromFM?ctid=3&key=1qASw2'
    // the minimum level determines whether the student will see the course listed in their available course list
    $minimum_level = array( 
      '104' => '0', // per Dee Dee 11/15
      '105' => '2',
      '106' => '3',
      '107' => '4',
      '153' => '5',
      '3' => '1',
      '4' => '2',
      '5' => '3',
      '6' => '4',
      '7' => '5',
      '161' => '0',
      '162' => '2',
      '163' => '3',
      '164' => '4',
      '165' => '5',
      '187' => '2'
      );

    // get site_data and regions tables from CTIDATABASE
    $locations = EventPeer::getLocationsFromCTIDATABASE();

    $updated = 0;

    $data   = json_decode( $json );
    if(isset($data->events)){
      $events = $data->events;

      foreach($events as $e){
        $c = new Criteria();
        $c->add(EventPeer::FMID, $e->fmid);
        $event = EventPeer::doSelectOne( $c );

        if(!isset($event)){
          $event = new Event();
          $event->setFmid( $e->fmid );
        }
        $event->setStartDate( $e->date );
        $event->setEndDate( $e->edate );
        $event->setCourseTypeId( $e->course_type_id );
        $event->setCallTime( $e->call_time );
        $event->setDialNumber( $e->bridge_phone ); 

        // $event->setDescription( $e->description );
        $event->setDescription( EventPeer::getDescriptionForCourseType( $e->course_type_id) );

        $event->setCurrentEnrollment( $e->student_count );
        $event->setMaxEnrollment( $e->max_count );
        $event->setCurrentAssistingEnrollment( $e->assistant_count );
        $event->setCurrentAssistingWaitlist( $e->assistant_wait_count );
        $event->setCurrentWaitlist( $e->student_wait_count );

        $event->setLeaderName( $e->leader_name );
        $event->setName( $e->event );
        $event->setPublish("publish");
        $event->setStartDateTimestamp( $e->date );

        $event->setMinimumLevel( $minimum_level[$e->course_type_id] );

        $site_code = $e->location;
        $region_code = preg_replace('/-.*/','',$site_code);
        if(array_key_exists($site_code, $locations)) {
          $event->setLocation( $locations[$site_code] );
        }
        else if(array_key_exists($region_code, $locations)) {
          $event->setLocation( $locations[$region_code] );
        }
        else {
          $event->setLocation( $site_code );
        }

        $event->save();
        $updated++;
      }
    }

    return "Locations count: ".count($locations)." Updated count: ".$updated;
  }

  public static function unpublishFromFM( $json ){
    $data   = json_decode( $json );
    if(isset($data->events)){
      $events = $data->events;

      foreach($events as $e){
        $c = new Criteria();
        $c->add(EventPeer::FMID, $e->fmid);
        $event = EventPeer::doSelectOne( $c );

        if(isset($event)){
          $event->setPublish("don't publish");
          $event->save();
        }
      }
    }
  }


  public static function getDescriptionForCourseType( $course_type_id, $name = '' ){
    // Learning Labs
    if( ( $course_type_id >= 101 && $course_type_id <= 108 ) || $course_type_id == 153  ){
      return 'The Learning Labs are follow-up learning opportunities offered after each course.  The Learning Labs provide you with an opportunity to deepen your Co-Active coaching skills and prepare you for the next step in your coach training.  Each Learning Lab is tailored to the specific CTI courses and is designed to enhance your coaching knowledge and skills.';
    }

    // Corp Teleseries
    if( $course_type_id >= 161 && $course_type_id <= 165 ) {
      return 'CTI\'s Co-Active Coaching Corporate Teleseries provides follow-up learning opportunities that apply specifically within corporate organizations.  The Corporate Teleseries provides you with an opportunity to deepen your Co-Active coaching skills and prepare you for the next step in your coach training.  Each Corporate Teleseries lab is tailored to the corresponding CTI course and is designed to enhance your coaching knowledge and skills.';
    }

    if( $course_type_id == 115 ){
      return 'CTI is the official sponsor of the Co-Active Entrepreneur Learning Lab ';
    }


    // Course assisting
    if( $course_type_id >= 3 && $course_type_id <= 7 ) {
      return 'Your job will be to hold the space for learning in the room and manage certain logistical duties.';
    }

    // Craigslist Foundation Boot Camp
    if( $course_type_id == 89 || $course_type_id == 114 ){
      return 'CTI is the official coaching sponsor and supplier of coaches for the annual Craigslist Foundation Boot Camp.';
    }

    // Test Lab
    if( $course_type_id == 115 ){
      return 'CTI is the official sponsor of the Student Portal Test Lab';
    }

  // Test Lab
    if( $course_type_id == 187 ){
      $description = '<p><strong>CO-ACTIVE ENTREPRENEUR LEARNING LABS</strong></p>';

      if(preg_match('/discover/i',$name)){
      
      $description = <<<EOT
    <p><strong>CO-ACTIVE ENTREPRENEUR LEARNING LABS</strong></p>

<p><strong>Discover the &ldquo;X&rdquo; Factor: Building a Six Figure Coaching Practice:</strong></p>
<p>We&rsquo;ll reveal the essential ingredient every entrepreneur needs to succeed in today&rsquo;s market.&nbsp; If you answer &ldquo;yes&rdquo; to any of the following questions, you should attend this lab:</p>
<ul>
<li><em>Do you struggle to create a sustainable pipeline of clients to feed your practice?</em></li>
<li><em>Do life&rsquo;s circumstances derail your business plans?&nbsp;</em></li>
<li><em>Do you get so discouraged that it&rsquo;s hard to market your services?&nbsp;</em></li>
<li><em>Do you worry you lack what it takes to succeed?</em></li>
</ul>
EOT;
    }

      if(preg_match('/sell/i',$name)){
      
      $description = <<<EOT
    <p><strong>CO-ACTIVE ENTREPRENEUR LEARNING LABS</strong></p>
<p><strong>Why You Can't Sell Coaching:</strong></p><!-- ' -->
<p>Many coaches talk about coaching rather than addressing their potential client&rsquo;s deep urgent desire.&nbsp; In this lab we&rsquo;ll present&nbsp;<em>The Coaching Investment Cycle</em>.&nbsp; By learning this four stage model you will be able to address the potential client&rsquo;s deep urgent desire for a specific result.&nbsp;</p>
EOT;
    }

      if(preg_match('/fear/i',$name)){
      
      $description = <<<EOT
<p><strong>Enroll New Clients Without Fear:</strong></p>
<p>You know you&rsquo;re a great coach but you feel stuck when it comes to enrolling new clients. Are you ready to get clear about what may be stopping you from achieving your goal of a thriving and profitable coaching business?&nbsp; You may be suffering from &lsquo;call reluctance&rsquo; which is an emotional hesitation to prospect and self-promote. In this lab we&rsquo;ll discuss solutions for call reluctance.</p>
EOT;
    }

      if(preg_match('/worth/i',$name)){
      
      $description = <<<EOT
<p><strong>What is Your Coaching Really Worth?:</strong></p>
<p>What should you charge? What can you hold?&nbsp; What doesn&rsquo;t feel like it creates obligation on either side? Be able to determine and then speak what you are worth without emotional energy.</p>
EOT;
    }

     return $description;
    }

  }

  public static function cacheByFmid( $fmid, $name, $start_date ){
    $c = new Criteria();
    $c->add(EventPeer::FMID, $fmid );
    $event = EventPeer::doSelectOne( $c );
    if( !isset($event) ){
      // event is not cached, so create a record for it
      $event = new Event();
      $event->setFmid( $fmid );
      $event->setName( $name );
      $event->setStartDate( $start_date );
      $event->setStartDateTimestamp( $start_date );
      $event->save();
    }

    return $event->getId();
  }

  public static function retrieveByFmid( $fmid ){
    $c = new Criteria();
    $c->add(EventPeer::FMID, $fmid );
    $event = EventPeer::doSelectOne( $c );
    if( !isset($event) ){
      return null;
    }
    return $event;
  }

  public static function retrieveByLevel( $level ){
    // only show events that are eligible per this student level (see EventPeer::updateFromFM)
    $level = 0 + $level; // cast $level as number

  
    $c = new Criteria();

    $cton1 = $c->getNewCriterion(EventPeer::EXTRA2, null, Criteria::ISNULL);
    $cton2 = $c->getNewCriterion(EventPeer::EXTRA2, $level, Criteria::LESS_EQUAL);
    // combine them
    $cton1->addOr($cton2);
    $c->add($cton1);
    $c->add(EventPeer::EXTRA3, time(), Criteria::GREATER_EQUAL); // greater than today
    $c->add(EventPeer::EXTRA1, 'publish');
    $c->addDescendingOrderByColumn(EventPeer::EXTRA3);

    $events = EventPeer::doSelect( $c );
    $ev = array( );
    foreach( $events as $e ){
      if($e->getCourseTypeId() > 7){ // don't show core courses fundamentals through synergy
        $ev[ ] = $e;
      }
    }
    usort($ev,"EnrollmentPeer::DateSort");
    return $ev;
  }


  public static function retrieveCoreByLevel( $level ){
    $level = 0 + $level;
    $c = new Criteria();

    $cton1 = $c->getNewCriterion(EventPeer::EXTRA2, null, Criteria::ISNULL);
    $cton2 = $c->getNewCriterion(EventPeer::EXTRA2, $level, Criteria::LESS_EQUAL);
    // combine them
    $cton1->addOr($cton2);
    $c->add($cton1);
    $c->add(EventPeer::EXTRA3, time(), Criteria::GREATER_EQUAL); // greater than today
    $c->addAscendingOrderByColumn(EventPeer::EXTRA3);

    $events = EventPeer::doSelect( $c );
    $ev = array( );
    foreach( $events as $e ){
      if($e->getCourseTypeId() <= 7 && ($e->getStartDateTimestamp() < (time() + 7776000)) ){ // 90 days
        $ev[ ] = $e;
      }
    }
    usort($ev,"EnrollmentPeer::DateSort");
    return $ev;
  }

  public static function getCoreEventsWithAssistingOpenings( ) {
    $c = new Criteria();
    $c->add(EventPeer::COURSE_TYPE_ID, array(3,4,5,6,7), Criteria::IN );
    $c->add(EventPeer::CURRENT_ASSISTING_ENROLLMENT, 3, Criteria::LESS_THAN);
    $c->add(EventPeer::EXTRA3, time(), Criteria::GREATER_THAN ); // start date timestamp must be greater than now

    $events = EventPeer::doSelect( $c );
    return $events;
  }

  public static function doAssistingWaitlistChecks(){
    // find upcoming events within 45 days

    // foreach event, are there assisting openings and waitlisted students?
    // if yes...

    // foreach waitlisted student, determine if eligible

  }

  public static function DateSort($b,$a,$d="-") {  // for use in usort, most recent date first. Reverse a and b for most recent date last 
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
