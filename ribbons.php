<?php

// The KSP Ribbon Generator was created by Ezriilc Swifthawk in August of 2014, based on an original design by Moustachauve.
// Anyone is free to use and/or modify this code, with proper credit given as above.

include_once('class/Ribbons.php');
new Ribbons;

$return = '';
$return1 = '';
$ribbons = array_reverse(Ribbons::load_all());
$ribbons_count = 0;
$ribbons_hits = 0;
if(
    class_exists('USER')
    AND $users = USER::get_users()
){
    include_once('class/Database.php');
    $db = new Database;
    foreach( $ribbons as $key => $ribbon ){
        $ribbon['username'] = $users[$ribbon['id']]['username'];
        $image_name = $ribbon['username'].'/ribbons.png';
        $image_pathname = './users/'.$image_name;
        if( is_readable($image_pathname) ){
            $ribbons_count++;
            $ribbon['image'] = $image_pathname;
            
            $read_data = $db->read('downloads','*',array('file'=>$image_name));
            $db_err = $db->get_error();
            if(
                ! empty($read_data[0])
                AND empty($db_err)
            ){
                $image_info = $read_data[0];
                $ribbon['hits'] = $image_info['count'];
                $ribbons_hits += $image_info['count'];
            }
        }
        $ribbons[$key] = $ribbon;
    }
}
$return1 .= '
<h2>KSP Ribbon Generator</h2>
<p>The KSP Ribbons were created by Unistrut, and are used here with special permission.</p>
<p>You can use this generator to build your personal ribbon set, or <a title="Download Unistrut\'s Ribbon Images" href="./_downloads/KSP-Ribbons_Version-8.zip">download the raw images</a> if you prefer to use an image editor like <a title="Get GIMP - it\'s free!" href="http://www.gimp.org/">GIMP</a>.</p>
<p>Members have generated <strong>'.number_format($ribbons_count).'</strong> ribbon sets, and they\'ve been seen a total of <strong>'.number_format($ribbons_hits).'</strong> times.</p>
<hr/>';
if(
    ! empty($_SESSION['logged_in'])
    AND ! empty($_SESSION['user']['username'])
){
    $username = $_SESSION['user']['username'];
    if( is_readable('./users/'.$username.'/ribbons.png') ){
        $return1 .= '
<p>Here\'s your BBC code - copy this to your signature. Note: This is hosted from our server.  If you\'d like to help with our costs, please consider donating.<br/>
<span class="click_to_select">[URL="http://'.$_SERVER['HTTP_HOST'].'/ribbons"][IMG]http://'.$_SERVER['HTTP_HOST'].'/users/'.$username.'/ribbons.png[/IMG][/URL]</span></p>';
    }else{
        $return1 .= '
<p>Click Generate to create your ribbon set, then refresh this page to show some code you can copy to your signature.</p>';
    }
    if( $users ){
        $return .= '
<hr/>
Members\' ribbons: (newest member first)<br/>
<ul class="ribbons_list">';
        foreach( $ribbons as $ribbon ){
            if( $ribbon['username'] === 'TestTickle' ){ continue; }
            if( ! empty($ribbon['image']) ){
                $return .= '
    <li class="stretchy">
        <span class="info">
            <span class="username">'.$ribbon['username'].'</span>';
                if( ! empty($ribbon['hits']) ){
                    $return .= '
            <small>(<strong>'.number_format($ribbon['hits'] ).'</strong> hits)</small>';
                }
                $return .= '
        </span>
        <img alt="'.$ribbon['username'].' ribbon set" src="'.$ribbon['image'].'"/>
        <div style="clear:both;"></div>
    </li>';
            }
        }
        $return .= '
</ul>
';
    }
}

return $return1.Ribbons::$output.$return;
?>