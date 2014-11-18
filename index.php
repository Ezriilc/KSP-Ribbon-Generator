<?php
    @session_start();
    include('ribbons.php');
    new RIBBONS;
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8" />
    <title>KSP Ribbon Generator - TESTING</title>
    <link rel="stylesheet" type="text/css" href="ribbons.css"/>
    <script type="text/javascript" src="jquery-2.1.1.js"></script>
    <script type="text/javascript" src="ribbons.js"></script>
</head>
<body>
<div style="
    width:840px;
    margin:auto;
    text-align:center;
">
    <h2>KSP Ribbon Generator - Testing</h2>
    <p>We're still under construction, so don't forget to clear the cache.</p>
    <p>I'm currently working on the GENERATE function, so no need to report that (unless you have a fix), but everything else should be working.  Please test everything carefully and tell us about anything that isn't perfect.</p>
    <h3><a onclick="window.open(this.href);return false;" title="Contact Us" href="http://www.kerbaltekaerospace.com/?page=contact">Contact Form</a></h3>
    <h3><a onclick="window.open(this.href);return false;" title="[WEB APP] KSP Ribbon Generator" href="http://forum.kerbalspaceprogram.com/threads/86422">KSP Forum Thread</a></h3>
    <p><a onclick="window.open(this.href);return false;" title="KSP-Ribbon-Generator on GitHub" href="https://github.com/Ezriilc/KSP-Ribbon-Generator">Source Code</a> (Comments welcome!)</p>
    <hr/>
<?php
    if(
        isset( RIBBONS::$user_id )
        AND RIBBONS::$user_id !== null
    ){
?>
    <h3 style="background-color:green;color:white;">You're logged in!</h3>
<?php }else{ ?>
    <p>You don't need to be logged in to use this new generator, but your <a title="Login" href="http://ribbons.kerbaltek.com/">normal login</a> should work.<br/>Just come right back here and refresh this page.</p>
<?php } ?>
    <p>Don't worry, your ribbons in the old generator are safe - they won't be affected by this test page.</p>
    <?php echo RIBBONS::$output; ?>
</div>
</body>
</html>