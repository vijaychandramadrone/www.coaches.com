<!DOCTYPE html>
<html lang="en">
<head>

<?php include_http_metas() ?>
<?php include_metas() ?>
<?php include_title() ?>

<link rel="icon" href="/favicon.ico" />
<link rel="shortcut icon" href="/favicon.ico" />
<link rel="stylesheet" type="text/css" href="/docs/res/css/coaches-training-institute.css" />
<link rel="stylesheet" type="text/css" href="/docs/res/css/section_coach-training.css" />

<link rel="stylesheet" type="text/css" href="/docs/res/css/content_internal_11c.css" /> 
<link rel="stylesheet" type="text/css" href="/docs/res/css/mentor.css" />
<link rel="stylesheet" type="text/css" href="/docs/res/css/colorbox.css"/>

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>

<script src="/docs/res/js/jquery.colorbox.js"></script>

<script src="/docs/res/js/jquery.validate.js"></script>

<script type="text/javascript">
$(document).ready(function(){

 $('.cancelbtn').click( function(){
 id = $(this).attr('id');
 window.location.href = "cancel?e="+id;
});
 $('.regbtn').click( function(){
 id = $(this).attr('id');
 window.location.href = "register?e="+id;
});
 $('.regassistbtn').click( function(){
 id = $(this).attr('id');
 window.location.href = "regAssist?e="+id;
});

$(".info-popup").colorbox({width:"60%"});
});
</script>
<style>
table.events { width: 525px; }
table.events th { text-align:left; font-size:1.1em; }


table.events tr:nth-child(even) { background-color: #F0F5F8; border: solid 1px #AAD }
table.events tr:nth-child(odd) { background-color: #D9E6EE; }
table.events tr:first-child { background-color: #EBE9DA; }

table.account-edit { width: 400px; } 
table.account td:nth-child(odd) { text-align:right; }
table.account input { width:170px; }
a.info-popup { font-size:90%; }
</style>
</head>
<body>
<div id="page">
  <img id="portraits" src="/docs/images/course-materials-portraits.png" />
<div id="tools">
  <!-- generalID --><!-- -->
  <!-- eCommerceID --><!-- -->
  <!-- courseMaterialsID --><ul id="ecommerce">
<li><!--<a href="/" class="offsite">CTI <b>Home</b></a> &raquo;--> </li>

</ul>
<!-- -->
</div>
<div id="primary">
  <!-- contactNavID --><!-- -->
  <ul id="main-nav">
  </ul>
  
<div id="broadcast">
  <img src="/docs/res/img/portal-banners/content.gif" width="920" height="124" alt="Coach Training" usemap="#logobutton" />
  <map id="logobutton" name="logobutton">
  <area shape="rect" coords="822,0,899,103" href="/" title="CTI Home" alt="CTI Home" />
  </map>
</div>


<div id="content">

<div id="main">
  <div id="breadcrumbs">
  </div>


<div class="sec">

<ul class="bulleted-arrowed">
  <li><a href="<?php echo url_for('main/mySchedule'); ?>">My Schedule</a></li>
  <li><a href="<?php echo url_for('event/registration'); ?>">New Event Registration</a></li>
  <li><a href="http://www.thecoaches.com/docs/coach-training/be-an-assistant/index-portal1.html">New Assisting Registration</a></li>
<p>&nbsp;</p>
  <li><a href="<?php echo url_for('account/index'); ?>">My Account</a></li>
<p>&nbsp;</p>
  <li><a href="<?php echo url_for('main/help'); ?>">Help</a></li>
</ul>    
</div>


<div class="pri">
<p style="font-size:1.1em"><b>Welcome <?php echo $sf_user->getStudent()->getFirstName(); ?></b><br />&raquo; <a href="<?php echo url_for('main/logout'); ?>">Logout</a>
</p>
  
  <?php echo $sf_content ?>

</div>

</div>

</p>


<!-- ctaAreaId -->

<div id="cta-area">

</div>

<!-- -->

</div>


<!-- used for cross-browser stabilizion of layout -->
<div class="pageclear"></div>
</div>
<div id="footer">
  <img id="cti-logo-tagged" src="/docs/res/img/cti-changing-business-transforming-lives.gif" width="176" height="57" alt="cit: changing business. transforming lives."/>
<div id="footer-content">
  <!-- footerNavId --><div id="footer-nav">
<div id="footer-legal">Copyright &#169; 2011 The Coaches Training Institute. All rights reserved. <a id="privacy-link" href="http://www.thecoaches.com/privacy">Privacy Policy</a><span class="divider">&#160;|&#160;</span><a href="http://www.thecoaches.com/disclosure">Disclosure</a></div>
<ul id="quick-nav">
</ul>
</div>
<!-- -->
  <img id="icf-logo" src="/docs/res/img/icf-logo.gif" width="74" height="57" alt="International Coach Federation"/>
</div>
</div>
</div>
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-5715102-1");
pageTracker._trackPageview();
} catch(err) {}</script>
</body>
</html>
