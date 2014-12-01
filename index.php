<?php
    @session_start();
    $cache_age = 1; // Seconds
    header('Cache-control: must-revalidate', false);
    header('Cache-control: max-age='.$cache_age, false);
    header( 'Expires: '.date( 'r', time() + $cache_age ) );
    include('ribbons.php');
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
    <h2>KSP Ribbon Generator - TESTING</h2>
<?php echo RIBBONS::$output; ?>
</div>
</body>
</html>