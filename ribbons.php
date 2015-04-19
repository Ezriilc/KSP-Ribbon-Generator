<?php

require_once("class/Ribbons.php");
new Ribbons();

$return = '';
$return1 = '';
$ribbons = array_reverse(Ribbons::load_all());
$ribbons_count = 0;
$ribbons_hits = 0;
if(
    class_exists('USER')
    AND $users = USER::get_users()
){
    foreach( $ribbons as $key => $ribbon ){
        $ribbon['username'] = $users[$ribbon['id']]['username'];
        $image_name = $ribbon['username'].'/ribbons.png';
        $image_pathname = './users/'.$image_name;
        if( is_readable($image_pathname) ){
            $ribbons_count++;
            $ribbon['image'] = $image_pathname;
            if(
                class_exists('DOWNLOADER')
            ){
                $image_info = DOWNLOADER::get_info($image_pathname);
                $ribbon['hits'] = $image_info['count'];
                $ribbons_hits += $image_info['count'];
            }
        }
        $ribbons[$key] = $ribbon;
    }
}
$return1 .= '
<p>The KSP Ribbons were created by Unistrut, and are used here with special permission.</p>
<p>You can use this generator to build your personal ribbon set, or download the raw images if you prefer to use an image editor like GIMP.</p>
<p>Kerbaltek members have generated <strong>'.number_format($ribbons_count).'</strong> ribbon sets, and they\'ve been seen a total of <strong>'.number_format($ribbons_hits).'</strong> times.</p>
<hr/>';
if(
    ! empty($_SESSION['logged_in'])
    AND ! empty($_SESSION['user']['username'])
){
    $username = $_SESSION['user']['username'];
    if( is_readable('./users/'.$username.'/ribbons.png') ){
        $return1 .= '
<p>Here\'s your BBC code: (copy to your signature) Note: This is in BETA testing, and may change in the future.<br/>
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
