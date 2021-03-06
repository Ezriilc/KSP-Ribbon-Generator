<?php

/**
 * Ribbons Class
 *
 * The KSP Ribbon Generator was created by Ezriilc Swifthawk in August of 2014, based on
 * an original design by Moustachauve.
 * Anyone is free to use and/or modify this code, with proper credit given as above.
 *
 **/

class Ribbons {
    static
        $images_root = 'KSP_images/ribbons'
        , $ribbon_width = 98
        , $ribbon_height = 26
        , $db_tables = array(
            'ribbons' => array(
                'schema' => "
id INTEGER NOT NULL UNIQUE,
data TEXT NOT NULL
",
            )
        )
        , $db
        , $db_table_name
        , $output
        , $planets // ALL SOI bodies are called "planets" here.
        , $devices
        , $devices_ordered
        , $gt_devices
        , $effects
        , $user_id
    ;
    
    function __construct(){
        static::$planets = array( // In order of display, by column then row.
            'Kerbol'        =>'0010001'
            ,'Moho'         =>'1010001'
            ,'Asteroid'     =>'1010010'
            
            ,'Eve'          =>'1110101'
            ,'Gilly'        =>'1010010'
            ,'Dres'         =>'1010000'
            
            ,'Kerbin'       =>'1111101'
            ,'Mun'          =>'1011000'
            ,'Minmus'       =>'1011010'
            
            ,'Duna'         =>'1111001'
            ,'Ike'          =>'1011010'
            ,'Vall'         =>'1011000'
            
            ,'Jool'         =>'0110001'
            ,'Laythe'       =>'1110000'
            ,'Tylo'         =>'1011100'
            
            ,'Eeloo'        =>'1010000'
            ,'Bop'          =>'1011010'
            ,'Pol'          =>'1010010'
            
            ,'Grand Tour'   =>'1100000'
        );
        $planet_attributes = array('Surface', 'Atmosphere', 'Geosynchronous', 'Anomaly', 'Challenge Wreath', 'Extreme EVA', 'Asteroid'); // Number strings above.
        foreach( static::$planets as $planet => $attribs ){
            static::$planets[$planet] = array();
            foreach( $planet_attributes as $key => $val ){
                static::$planets[$planet][$val] = !!@$attribs[$key]; // Strings are ~like arrays.
            }
        }
        
        static::$effects = array( // In order of display.
            'Textures' => array(
                'Ribbon'
                ,'High Contrast Ribbons'
                ,'Lightened High Contrast'
                ,'Dense HC'
                ,'Lightened Dense HC'
            )
            ,'Bevels' => array(
                'Darken Bevel'
                ,'Lighten Bevel'
            )
        );
        
        // Devices in order of inputs display: Category => Type => Device => Priority, Description.
        static::$devices = array(
            'Maneuvers' => array( // Category
                'Common' => array( // Type
                    'Orbit'             => array(8, 'Periapsis above the atmosphere and apoapsis within the sphere of influence.')
                    ,'Equatorial'       => array(5, 'Inclination less than 5 degrees and, apoapsis and periapsis within 5% of each other.')
                    ,'Polar'            => array(4, 'Polar orbit capable of surveying the entire surface.')
                    ,'Rendezvous'       => array(6, 'Docked two craft in orbit, or maneuvered two orbiting craft within 100m for a sustained time.')
                )
                ,'Special' => array(
                    'Geosynchronous'    => array(7, 'Achieve geosynchronous orbit around the world; or drag the body into geosynchronous orbit around another; or construct a structured, line-of-sight satellite network covering a specific location.')
                    ,'Kerbol Escape'    => array(10, 'Achieved solar escape velocity - for Kerbol only.')
                )
                ,'Surface' => array(
                    'Land Nav'          => array(9, 'Ground travel at least 30km or 1/5th of a world\'s circumference (whichever is shorter).')
                )
                ,'Atmosphere' => array(
                    'Atmosphere'        => array(3, 'Controlled maneuvers using wings or similar. Granted only if craft can land and then take off, or perform maneuvers and then attain orbit.')
                )
            )
            ,'Crafts' => array(
                'Common' => array(
                    'Probe'             => array(0, 'Autonomous craft which does not land.')
                    ,'Capsule'          => array(0, 'Manned craft which does not land, or only performs a single, uncontrolled landing.')
                    ,'Resource'         => array(0, 'Installation on the surface or in orbit, capable of mining and/or processing resources.')
                    ,'Aircraft'         => array(0, 'Winged craft capable of atmospheric flight, with or without any atmosphere - does not grant Flight Wings device.')
                    ,'Multi-Part Ship'  => array(0, 'A craft constructed from multiple parts in orbit.')
                    ,'Station'          => array(0, 'Orbital vessel capable of docking and long-term habitation by multiple Kerbals.')
                    ,'Armada'           => array(0, 'Three or more vessels, staged in orbit for a trip to another world, and launched within one week during one encounter window.')
                    ,'Armada 2'         => array(0, 'Three or more vessels, staged in orbit for a trip to another world, and launched within one week during one encounter window.')
                )
                ,'Surface' => array(
                    'Impactor'          => array(0, 'Craft was destroyed by atmospheric or surface friction.')
                    ,'Probe Lander'     => array(0, 'Autonomous craft which landed on a world\'s surface.')
                    ,'Probe Rover'      => array(0, 'Autonomous craft which landed and performed controlled surface travel.')
                    ,'Flag or Monument' => array(0, 'A marker left on the world.')
                    ,'Lander'           => array(0, 'A craft carrying one or more Kerbals which landed without damage.')
                    ,'Rover'            => array(0, 'A vehicle which landed and then carried one or more Kerbals across the surface of the world.')
                    ,'Base'             => array(0, 'A permanent ground construction capable of long-term habitation by multiple Kerbals')
                    ,'Base 2'           => array(0, 'A permanent ground construction capable of long-term habitation by multiple Kerbals')
                )
                ,'Atmosphere' => array(
                    'Meteor'            => array(0, 'Craft was destroyed due to atmospheric entry.')
                )
                ,'Special' => array(
                    'Extreme EVA'       => array(0, 'Landed and returned to orbit without the aid of a spacecraft.')
                )
            )
            ,'Misc' => array(
                'Common' => array(
                    'Kerbal Lost'       => array(0, 'A Kerbal was killed or lost beyond the possibility of rescue.')
                    ,'Kerbal Saved'     => array(1, 'Returned a previously stranded Kerbal safely to Kerbin.')
                    ,'Return Chevron'   => array(12, 'Returned any craft safely to Kerbin from the world.')
                )
                ,'Special' => array(
                    'Anomaly'           => array(2, 'Discovered and closely inspected a genuine Anomaly.')
                    ,'Challenge Wreath' => array(11, 'A special challenge for each world.')
                )
            )
        );
        static::$gt_devices = array(
            'Kerbal Lost'
            ,'Kerbal Saved'
            ,'Probe'
            ,'Capsule'
            ,'Aircraft'
            ,'Probe Lander'
            ,'Probe Rover'
            ,'Lander'
            ,'Multi-Part Ship'
            ,'Rover'
        );
        static::$devices_ordered = array();
        foreach( static::$devices as $cat => $types ){
            if( $cat === 'Crafts' ){ continue; }
            foreach( $types as $type => $devices ){
                foreach( $devices as $device => $details ){
                    static::$devices_ordered[$details[0]] = array($device, $type, $cat, $details[1]);
                }
            }
        }
        ksort(static::$devices_ordered);
        foreach( static::$devices['Crafts'] as $type => $crafts ){
            foreach( $crafts as $craft => $details ){
                static::$devices_ordered[] = array($craft, $type, $cat, $details[1]);
            }
        }
        
        if( isset($_SESSION['user']['id']) ){
            static::$user_id = $_SESSION['user']['id'];
        }elseif( isset($_SESSION['user_id']) ){
            static::$user_id = $_SESSION['user_id'];
        }
        
        // Done setting up.  Now do stuff.
        
        $this->get_input();
        $this->generate_image();
//        static::init_database(); // Here for testing, not needed unless save/load fires.
        
        static::$output .= $this->get_preview();
        static::$output .= $this->get_form();
        
    }// END of __construct()
    
    static public function load_all(){
        if( ! static::init_database() ){ return; }
        $table = static::$db_table_name;
        $db = static::$db;
        $read_data = $db->read($table,'*');
        $db_err = $db->get_error();
        if(
            ! empty($read_data)
            AND empty($db_err)
        ){
            $ribbons = array();
            foreach( $read_data as $row ){
                if( ! $row['id'] ){ continue; }
                $ribbons[$row['id']] = $row;
            }
            ksort($ribbons);
            return $ribbons;
        }
    }
    
    private function my_urlencode($inout){
        return preg_replace('/\s/','%20',$inout);
    }
    
    private function de_space($in_out){
        return preg_replace('/\s+/','_',$in_out);
    }
    private function re_space($in_out){
        return preg_replace('/_/',' ',$in_out);
    }
    
    private function get_input(){
        $new_data = false;
        if( ! empty( $_POST['ribbons_submit'] ) ){
            $new_data = true;
            $_SESSION['ribbons'] = array();
            foreach( $_POST as $key => $val ){
                // Basic post scrubbing.
                if(
                    strlen($val) > 40
                    || strlen($key) > 40
                    || ! $key
                    || ! $val
                    || $val === 'None'
                    || preg_match('/^ribbons_/i',$key)
                ){ continue; }
                $_SESSION['ribbons'][$key] = $val;
            }
        }else{
            $this->load_ribbons();
            if( ! isset($_SESSION['ribbons']) ){
                
                // Set defaults.
                $_SESSION['ribbons'] = array(
                    'effects/Texture' => 'Ribbon'
                );
                
            }
        }
        
        // Loading and defaults are done - do any weird stuff to the data here.
        
        if( ! empty( $_SESSION['ribbons']['Asteroid/Asteroid'] ) ){
            $_SESSION['ribbons']['Asteroid/Achieved'] = 'on';
        }else{
            unset($_SESSION['ribbons']['Asteroid/Achieved']);
        }
        
        // Weird stuff done, save if needed.
        if(
            $new_data
//            AND empty( $_POST['ribbons_generate'] ) // Uncomment to NOT save on Generate (use separate save button).
        ){
            $this->save_ribbons();
        }
    }
    
    static private function init_database(){
        if( ! empty( static::$db ) ){ return true; }
        
        include_once('class/Database.php');
        $db = new Database;
        $db_err = $db->get_error();
        if( ! empty($db_err) ){
            die(get_called_class().': Can\'t open DB.');
        }
        foreach( static::$db_tables as $key => $val ){
            static::$db_table_name = $key;
            break;
        }
        $db_out = $db->check_add_tables(static::$db_tables);
        $db_err = $db->get_error();
        if(
            empty($db_out)
            OR ! empty($db_err)
        ){
            die(get_called_class().': DB: Table check failed. '.$db_err);
        }
        static::$db = $db;
        return true;
    }

    private function load_ribbons(){
        if( static::$user_id === null ){ return false; }
        static::init_database();
        $table = static::$db_table_name;
        $id = static::$user_id;
        $db = static::$db;
        $read_data = $db->read($table,'*',array('id'=>$id));
        $db_err = $db->get_error();
        if( ! empty($db_err) ){
            die(get_called_class().': Can\'t read db data.');
        }
        if( empty($read_data[0]['data']) ){return;}
        $data = explode('|',$read_data[0]['data']);
        $_SESSION['ribbons'] = array();
        $split_patt = '/^([^=]*)=(.*)$/';
        foreach($data as $pair){
            $prop = preg_filter($split_patt,'$1',$pair);
            $val = preg_filter($split_patt,'$2',$pair);
            if( $prop AND $val ){
                $_SESSION['ribbons'][$prop] = $val;
            }
        }
    }
    
    private function save_ribbons(){
        $id = static::$user_id;
        if(
            $id === null
            OR ! isset($_SESSION['ribbons'])
        ){ return false; }
        static::init_database();
        $data = '';
        $i = count($_SESSION['ribbons']);
        foreach( $_SESSION['ribbons'] as $key => $val ){
            $data .= $key.'='.$val;
            if( --$i ){ $data .= '|'; }
        }
        $table = static::$db_table_name;
        $db = static::$db;
        $write_data = array('id'=>$id,'data'=>$data);
        $write_data = array(array_keys($write_data),array_values($write_data));
        $row_count = $db->write($table,$write_data,true);
        $db_err = $db->get_error();
        if(
            empty($row_count)
            OR ! empty($db_err)
        ){
            die('FATAL ERROR: Can\'t save data.');
        }
    }
    
    private function generate_image(){
        if( empty( $_POST['ribbons_generate'] ) ){ return false; }
        $data = array();
        $split_patt = '/^([^\/]+)\/(.+)$/';
        $at_least_one = false;
        foreach( $_SESSION['ribbons'] as $key => $val ){
            if( $val === 'None' OR $val === '0' OR $val === 0 ){ continue; }
            $key = $this->re_space( $key );
            $group = preg_filter($split_patt,'$1',$key);
            $prop = preg_filter($split_patt,'$2',$key);
            if( $group === null OR $prop === null ){ continue; }
            if( ! isset( $data[$group] ) ){
                $data[$group] = array();
            }
            $data[$group][$prop] = $val;
            if($group !== 'effects'){
                $at_least_one = true;
            }
        }
        if( ! $at_least_one ){
            die('
<!doctype html>
<html style="text-align:center;">
<head>
    <meta charset="utf-8" />
    <title>Empty Result</title>
</head>
<body>
    <span style="
        display:inline-block;
        margin:1em auto 0;
        padding:1em;
        border:1px solid green;
        border-radius:1em;
        text-align:center;
        font-size:200%;
    ">Empty result.</span>
    <script type="text/javascript">setTimeout("window.close();",1000);</script>
</body>
</html>
');
        }
        
        // VERY rough.  I'm a total noob at imaging in PHP.
        
        
        $base_image = imagecreatetruecolor( (7 * static::$ribbon_width), (3 * static::$ribbon_height) );
        imagesavealpha($base_image, true);
        $bg = imagecolorallocatealpha($base_image, 0,0,255, 127);
        imagefill($base_image, 0,0, $bg);
        
        $ribbons = array();
        foreach($data as $group => $props){
            if( $group === 'effects' ){ continue; }
            if(
                $group === 'Asteroid'
                && ! empty($props['Asteroid'])
            ){
                $image_name = 'Asteroid - '.$props['Asteroid'];
            }elseif( $group === 'Grand Tour' ){
                $image_name = 'shield/Base Colours';
            }else{
                $image_name = $group;
            }
            $image = static::$images_root.'/'.$image_name.'.png';
            if( ! is_readable($image) ){ sleep(5); }
            if( is_readable($image) AND ! is_dir($image) ){
                $ribbons[$group] = imagecreatefrompng($image);
            }else{
                die('<br/>FATAL ERROR: Can\'t read ribbon image: '.$image);
            }
            
            foreach( static::$effects['Textures'] as $effect ){
                if(
                    empty($data['effects'])
                    OR ! is_array($data['effects'])
                    OR(
                        ! in_array($effect, $data['effects'])
                        AND ! array_key_exists($effect, $data['effects'])
                    )
                ){ continue; }
                $name = $this->de_space($effect);
                $image = static::$images_root;
                if( $group === 'Grand Tour' ){ $image .= '/shield'; }
                $image .= '/'.$effect.'.png';
                $height = static::$ribbon_height;
                if( $group === 'Grand Tour' ){
                    $height = (3 * static::$ribbon_height);
                }
                if( ! is_readable($image) ){ sleep(5); }
                if( is_readable($image) AND ! is_dir($image) ){
                    imagecopy( $ribbons[$group], imagecreatefrompng($image), 0,0,0,0, static::$ribbon_width, $height );
                }else{
                    die('<br/>FATAL ERROR: Can\'t read "'.$image.'" for: '.$group.'/'.$prop.'='.$val);
                }
            }
            
            foreach( static::$devices_ordered as $device ){ // Devices in order of priority.
                $type = $device[1];
                $cat = $device[2];
                $desc = $device[3];
                $device = $device[0];
                if(
                    ! in_array($device, $props)
                    AND ! array_key_exists($device, $props)
                ){ continue; }
                
                $image = static::$images_root;
                if( $group === 'Grand Tour' ){ $image .= '/shield'; }
                $image .= '/'.$device.'.png';
                $height = static::$ribbon_height;
                if( $group === 'Grand Tour' ){
                    $height = (3 * static::$ribbon_height);
                }
                if( ! is_readable($image) ){ sleep(5); }
                if( is_readable($image) AND ! is_dir($image) ){
                    imagecopy( $ribbons[$group], imagecreatefrompng($image), 0,0,0,0, static::$ribbon_width, $height );
                }else{
                    die('<br/>FATAL ERROR: Can\'t read "'.$image.'" for: '.$group.'/'.$prop.'='.$val);
                }
            }
            
            if( $group === 'Grand Tour' ){
                foreach( static::$planets as $planet2 => $attribs2 ){
                    if(
                        ! in_array($planet2, $props)
                        AND ! array_key_exists($planet2, $props)
                    ){ continue; }
                    if(
                        $planet2 === 'Kerbol'
                        || $planet2 === 'Asteroid'
                        || $planet2 === 'Grand Tour'
                    ){ continue; }
                    $image = static::$images_root.'/shield/'.$planet2.' Visit.png';
                    if( ! is_readable($image) ){ sleep(5); }
                    if( is_readable($image) AND ! is_dir($image) ){
                        imagecopy( $ribbons[$group], imagecreatefrompng($image), 0,0,0,0, static::$ribbon_width, (3 * static::$ribbon_height) );
                    }else{
                        die('<br/>FATAL ERROR: Can\'t read "'.$image.'" for: '.$group.'/'.$prop.'='.$val);
                    }
                }
                
                $o_l = array('Orbit','Landing');
                foreach( $o_l as $each ){
                    $$each = array('count'=>'','silvers'=>array(),'golds'=>array());
                    $this_array = &$$each;
                    if( ! isset($data['Grand Tour'][$each.'s']) ){ continue; }
                    $this_array['count'] = 0 + $data['Grand Tour'][$each.'s'];
                    if( $this_array['count'] > 0 ){
                        
                        array_push($this_array['golds'], 1);
                        $count_i = $this_array['count']-1;
                        $silvers = 0;
                        $divisor = 7;
                        while( $count_i > $divisor ){
                            $silvers++;
                            $count_i -= 5;
                            $divisor -= 1;
                        }
                        $golds = $this_array['count'] - ($silvers * 5);
                        
                        $i=2;while( $i <= 8 ){
                            if( $i <= $silvers + 1 ){
                                array_push($this_array['silvers'], $i);
                            }elseif( $i <= $silvers + $golds ){
                                array_push($this_array['golds'], $i);
                            }
                            $i++;
                        }
                        
                    }
                }
                
                $i=1;while($i <= 8){
                    foreach( array('Orbit','Landing') as $each ){
                        $this_array = &$$each;
                        if( ! isset($data['Grand Tour'][$each.'s']) ){ continue; }
                        $each_count = 0 + $data['Grand Tour'][$each.'s'];
                        foreach( array('',' Silver') as $each2 ){
                            if( $each2 AND $i === 1 ){ continue; }
                            $OLname = $each.' '.$i.$each2;
                            if( $each2 === '' ){ $each_rl = 'golds'; }
                            else{ $each_rl = 'silvers'; }
                            
                            if( ! in_array( $i, $this_array[$each_rl] ) ){ continue; }
                            
                            $image = static::$images_root.'/shield/'.$OLname.'.png';
                            if( ! is_readable($image) ){ sleep(5); }
                            if( is_readable($image) AND ! is_dir($image) ){
                                imagecopy( $ribbons[$group], imagecreatefrompng($image), 0,0,0,0, static::$ribbon_width, (3 * static::$ribbon_height) );
                            }else{
                                die('<br/>FATAL ERROR: Can\'t read "'.$image.'" for: '.$group.'/'.$prop.'='.$val);
                            }
                        }
                    }
                    $i++;
                }
            }
            
            foreach( static::$effects['Bevels'] as $effect ){
                if(
                    empty($data['effects'])
                    OR ! is_array($data['effects'])
                    OR(
                        ! in_array($effect, $data['effects'])
                        AND ! array_key_exists($effect, $data['effects'])
                    )
                ){ continue; }
                $name = $this->de_space($effect);
                $image = static::$images_root;
                if( $group === 'Grand Tour' ){ $image .= '/shield'; }
                $image .= '/'.$effect.'.png';
                $height = static::$ribbon_height;
                if( $group === 'Grand Tour' ){
                    $height = (3 * static::$ribbon_height);
                }
                if( ! is_readable($image) ){ sleep(5); }
                if( is_readable($image) AND ! is_dir($image) ){
                    imagecopy( $ribbons[$group], imagecreatefrompng($image), 0,0,0,0, static::$ribbon_width, $height );
                }else{
                    die('<br/>FATAL ERROR: Can\'t read "'.$image.'" for: '.$group.'/'.$prop.'='.$val);
                }
            }
            
        }
        
        $cell_w = static::$ribbon_width;
        $cell_h = static::$ribbon_height;
        
        $cell = 1;
        $dst_x = 0;
        $dst_y = 0;
        $end_w = 0;
        $end_h = 0;
        $occupied = false;
        foreach( static::$planets as $planet => $attribs ){ // By column, then row.
            // Paint ribbon here.
            $is_on = array_key_exists($planet, $ribbons);
            $height = $cell_h;
            if( $planet === 'Grand Tour' ){
                $height = $cell_h * 3;
            }
            if( $is_on ){
                imagecopy($base_image, $ribbons[$planet], $dst_x, $dst_y, 0,0, static::$ribbon_width, $height );
                $dst_y += $cell_h;
                $occupied = true;
                if( $height > $end_h ){
                    $end_h = $height;
                }
                if( $dst_y > $end_h ){
                    $end_h = $dst_y;
                }
            }
            if( ($cell) % 3 === 0 ){
                $dst_y = 0;
                if( $occupied ){
                    $dst_x += $cell_w;
                    $end_w += $cell_w;
                }
                $occupied = false;
            }
            $cell++;
        }
        if( $occupied ){ $end_w += $cell_w; }
        
        $resize_factor = 1;
        $end_w_s = $end_w * $resize_factor;
        $end_h_s = $end_h * $resize_factor;
        
        // Crop and resize.
        $fixed_image = imagecreatetruecolor( $end_w_s, $end_h_s );
        imagesavealpha($fixed_image, true);
        $bg = imagecolorallocatealpha($fixed_image, 0,0,255, 127);
        imagefill($fixed_image, 0,0, $bg);
        imagecopyresampled( $fixed_image, $base_image, 0,0, 0,0, $end_w_s, $end_h_s, $end_w, $end_h );
        $base_image = $fixed_image;
        
        // Save static image.
        if(
            ! empty($_SESSION['logged_in'])
            AND ! empty($_SESSION['user']['username'])
        ){
            $username = $_SESSION['user']['username'];
            $dir = './users/'.$username;
            if( ! is_writable($dir) ){
                sleep(5);
                if( ! is_writable($dir) ){
                    sleep(5);
                    if( ! is_writable($dir) ){
                        mkdir($dir);
                    }
                }
            }
            $image_file = $dir.'/ribbons.png';
            if( ! imagepng($base_image, $image_file) ){
                die('Failed to save image to file.');
            }
        }
        
        
        
// Display image.  For testing.
//header('Content-Type: image/png'); imagepng($base_image); exit();
        
        // Serve download.
        $filename = 'KSP-Ribbons.png';
        
        $filename = preg_replace('/;/i', '_', $filename);
            // No semi-colons inside HTTP headers - it ends the line.
        header( 'Content-Description: File Transfer' );
        header( 'Content-Type: application/octet-stream' );
        header( 'Content-Disposition: attachment; filename="'.$filename.'"' );
            // filename string double-quoted to handle spaces.
        header( 'Content-Transfer-Encoding: binary' );
        header( 'Expires: 0' );
        header( 'Cache-Control: must-revalidate' );
        header( 'Pragma: public' );
        imagepng($base_image);
        exit;
    }

    private function get_preview(){
        $return = '';
        $return .= '
<div style="clear:both;"></div>
<div style="text-align:center;"><small>Click each ribbon to select your awards.</small></div>
<div id="ribbons_output" class="ribbons">';
        $ri=0;
        foreach( static::$planets as $planet => $attribs ){
            $ri++;
            if( ($ri-1) % 3 === 0 ){
                if( $ri > 1 ){
                    $return .= '
    </div>';
                }
                $return .= '
    <div class="column">';
            }
            $image = static::$images_root.'/'.$planet.'.png';
            if( $planet === 'Grand Tour' ){
                $image = static::$images_root.'/shield/Base Colours.png';
                $height = 'height:'.(3*static::$ribbon_height).'px;line-height:'.(3*static::$ribbon_height).'px;';
            }else{ $height = ''; }
            if( $planet !== 'Asteroid' ){
                $image = '
            <img class="ribbon_image" alt="'.$planet.'" src="'.$this->my_urlencode($image).'"/>';
            }else{ $image = ''; }
            if(
                ! empty( $_SESSION['ribbons'][$this->de_space($planet.'/Achieved')] )
            ){
                $selected = ' selected';
            }else{ $selected = ''; }
            
            $name_vis = '';
            if( ! empty( $_SESSION['ribbons'][$this->de_space($planet.'/Achieved')] ) ){
                $name_vis = ' style="opacity:0;"';
            }
            $return .= '
        <div  title="'.$planet.'" class="ribbon '.$this->de_space($planet).$selected.'" style="'.$height.'">'.$image.'
            <span class="title"'.$name_vis.'>'.$planet.'</span>';
            
            // BEGIN Ribbon guts.
            
            if( $planet === 'Asteroid' ){
                foreach( static::$planets as $planet2 => $attribs2 ){
                    if(
                        empty( $attribs2['Asteroid'] )
                        || $planet2 === 'Asteroid'
                    ){ continue; }
                    if( // Check for default or posted value.
                        $planet2 === @$_SESSION['ribbons'][$this->de_space($planet.'/Asteroid')]
                        AND ! empty( $_SESSION['ribbons'][$this->de_space($planet.'/Achieved')] )
                    ){
                        $selected = ' selected';
                    }else{ $selected = ''; }
                    $image = static::$images_root.'/Asteroid - '.$planet2.'.png';
                    $image = '
            <img class="device '.$this->de_space($planet2).$selected.'" alt="Image:'.$device.'" src="'.$this->my_urlencode($image).'"/>';
                    $return .= $image;
                }
            }
            
            foreach( static::$effects['Textures'] as $effect ){
                $name = $this->de_space($effect);
                if( // Check for default or posted value.
                    (
                        $effect === @$_SESSION['ribbons']['effects/Texture']
                        || ! empty( $_SESSION['ribbons']['effects/'.$name] )
                    )
                ){
                    $selected = ' selected';
                }else{ $selected = ''; }
                $image = static::$images_root;
                if( $planet === 'Grand Tour' ){ $image .= '/shield'; }
                $image .= '/'.$effect.'.png';
                $image = '
        <img class="effect '.$name.$selected.'" alt="Image:'.$effect.'" src="'.$this->my_urlencode($image).'"/>';
                $return .= $image;
            }
            
            foreach( static::$devices_ordered as $device ){
                // Devices in order of priority.
                $type = $device[1];
                $cat = $device[2];
                $desc = $device[3];
                $device = $device[0];
                if(
                    $type !== 'Common'
                    AND empty($attribs[$type])
                    AND empty($attribs[$device])
                    AND $planet !== 'Grand Tour'
                    AND ! (
                        $planet === 'Kerbol'
                        AND (
                            $device === 'Kerbol Escape'
                            || $device === 'Meteor'
                        )
                    )
                ){ continue; }
                if(
                    $planet === 'Grand Tour'
                    AND ! in_array($device,static::$gt_devices)
                ){
                    continue;
                }
                if( // Check for default or posted value.
                    (
                        ! empty( $_SESSION['ribbons'][$this->de_space($planet.'/'.$device)] )
                        || $device === @$_SESSION['ribbons'][$this->de_space($planet.'/craft')]
                    )
                    AND ! empty( $_SESSION['ribbons'][$this->de_space($planet.'/Achieved')] )
                ){
                    $selected = ' selected';
                }else{ $selected = ''; }
                $image = static::$images_root;
                if( $planet === 'Grand Tour' ){ $image .= '/shield'; }
                $image .= '/'.$device.'.png';
                $image = '
            <img class="device '.$this->de_space($device).$selected.'" alt="Image:'.$device.'" src="'.$this->my_urlencode($image).'"/>';
                $return .= $image;
            }
            
            if( $planet === 'Grand Tour' ){
                foreach( static::$planets as $planet2 => $attribs2 ){
                    if(
                        $planet2 === 'Kerbol'
                        || $planet2 === 'Asteroid'
                        || $planet2 === 'Grand Tour'
                    ){ continue; }
                    if( // Check for default or posted value.
                        ! empty( $_SESSION['ribbons'][$this->de_space($planet.'/'.$planet2)] )
                        AND ! empty( $_SESSION['ribbons'][$this->de_space($planet.'/Achieved')] )
                    ){
                        $selected = ' selected';
                    }else{ $selected = ''; }
                    $image = static::$images_root.'/shield/'.$planet2.' Visit.png';
                    $image = '
            <img class="device '.$this->de_space($planet2).$selected.'" alt="Image:'.$planet2.'" src="'.$this->my_urlencode($image).'"/>';
                    $return .= $image;
                }
                
                $o_l = array('Orbit','Landing');
                foreach( $o_l as $each ){
                    $$each = array('count'=>'','silvers'=>array(),'golds'=>array());
                    $this_array = &$$each;
                    $this_array['count'] = 0 + @$_SESSION['ribbons']['Grand_Tour/'.$each.'s'];
                    if( $this_array['count'] > 0 ){
                        
                        array_push($this_array['golds'], 1);
                        $count_i = $this_array['count']-1;
                        $silvers = 0;
                        $divisor = 7;
                        while( $count_i > $divisor ){
                            $silvers++;
                            $count_i -= 5;
                            $divisor -= 1;
                        }
                        $golds = $this_array['count'] - ($silvers * 5);
                        
                        $i=2;while( $i <= 8 ){
                            if( $i <= $silvers + 1 ){
                                array_push($this_array['silvers'], $i);
                            }elseif( $i <= $silvers + $golds ){
                                array_push($this_array['golds'], $i);
                            }
                            $i++;
                        }
                        
                    }
                }
                
                $i=1;while($i <= 8){
                    foreach( array('Orbit','Landing') as $each ){
                        $this_array = &$$each;
                        $each_count = 0 + @$_SESSION['ribbons']['Grand_Tour/'.$each];
                        foreach( array('',' Silver') as $each2 ){
                            if( $each2 AND $i === 1 ){ continue; }
                            $OLname = $each.' '.$i.$each2;
                            if( $each2 === '' ){ $each_rl = 'golds'; }
                            else{ $each_rl = 'silvers'; }
                            if( // Check for default or posted value.
                                in_array( $i, $this_array[$each_rl] )
                                AND ! empty( $_SESSION['ribbons'][$this->de_space($planet.'/Achieved')] )
                            ){
                                $selected = ' selected';
                            }else{ $selected = ''; }
                            $image = static::$images_root.'/shield/'.$OLname.'.png';
                            $image = '
            <img class="device '.$this->de_space($OLname).$selected.'" alt="Image:'.$OLname.'" src="'.$this->my_urlencode($image).'"/>';
                            $return .= $image;
                        }
                    }
                    $i++;
                }
            }
            
            foreach( static::$effects['Bevels'] as $effect ){
                $name = $this->de_space($effect);
                if( // Check for default or posted value.
                    (
                        $effect === @$_SESSION['ribbons']['effects/Texture']
                        || ! empty( $_SESSION['ribbons']['effects/'.$name] )
                    )
                ){
                    $selected = ' selected';
                }else{ $selected = ''; }
                $image = static::$images_root;
                if( $planet === 'Grand Tour' ){ $image .= '/shield'; }
                $image .= '/'.$effect.'.png';
                $image = '
        <img class="effect '.$name.$selected.'" alt="Image:'.$effect.'" src="'.$this->my_urlencode($image).'"/>';
                $return .= $image;
            }
            
            // END Ribbon guts.
            
            $return .= '
        </div>';
            if( $ri === count(static::$planets) ){
                $return .= '
    </div>';
            }
        }
        $return .= '
    <div style="clear:both;"></div>
</div>
<div style="clear:both;"></div>
';
        return $return;
    }
    
    private function get_form(){
        $return = '';
        $submit_message = '<strong>You\'re <em>not</em> logged in.</strong> Settings will be lost when you leave.';
        if( static::$user_id !== null ){
            $submit_message = '<strong>You\'re logged in!</strong> Settings will be remembered.';
        }
        $return .= '
<div style="clear:both;"></div>
<form class="ribbons" method="post"><fieldset>
    <div class="submit">
        '.$submit_message.'
        <input type="hidden" name="ribbons_submit" value="default"/>';
//        $return .= '\r\n        <input title="Save these ribbons." type="submit" name="ribbons_save" value="Save"/>';
        $butt_text = empty($_SESSION['logged_in']) ? 'Generate' : 'Save &amp; Generate';
        $return .= '
        &nbsp;&nbsp;
        <small><input class="generate" title="Save and generate a downloadable image." type="submit" name="ribbons_generate" value="'.$butt_text.'"/></small>
        &nbsp;&nbsp;
        <small><input title="Revert to your last save." type="reset" value="Cancel"/></small>
        <hr/>
    </div>
    ';
        
        // Submit:
        
        // Effects:
            $return .= '
    <div class="effects">
        <h3 class="title">Effects</h3>';
        foreach( static::$effects as $type => $effects ){
            $return .= '
        <div class="category '.$this->de_space($type).'">';
            $first_texture = true;
            foreach( $effects as $effect ){
                $input_type = 'checkbox';
                $name = $this->de_space('effects/'.$effect);
                $id = $name;
                $value = '';
                $checked = '';
                
                if( $type === 'Textures' ){
                    $name = 'effects/Texture';
                    $id = $this->de_space($name.'/'.$effect);
                    $value = ' value="'.$effect.'"';
                    $input_type = 'radio';
                    if( $effect === @$_SESSION['ribbons'][$name] ){
                        $checked = ' checked="checked"';
                    }
                    if( $first_texture ){
                        if( empty( $_SESSION['ribbons'][$name] ) ){
                            $checked2 = ' checked="checked"';
                        }else{ $checked2 = ''; }
                        $first_texture = false;
                        $return .= '
            <div class="input_box">
                <label for="'.$id.'/None">No Texture</label>
                <input type="'.$input_type.'" id="'.$id.'/None" name="'.$name.'" value="None"'.$checked2.'/>
            </div>';
                    }
                }elseif( ! empty( $_SESSION['ribbons'][$name] ) ){
                    $checked = ' checked="checked"';
                }
                
                $image = static::$images_root.'/'.$effect.'.png';
                $image = '
                    <img alt="Image:'.$effect.'" src="'.$this->my_urlencode($image).'"/>';
                $return .= '
            <div class="input_box">
                <label for="'.$id.'">
                    '.$effect.$image.'
                </label>
                <input type="'.$input_type.'" id="'.$id.'" name="'.$name.'"'.$value.$checked.'/>
            </div>';
            }
            $return .= '
            <div style="clear:both;"></div>
        </div>';
        }
        $return .= '
    </div>';
        
        // Planets:
        
        foreach( static::$planets as $planet => $attribs ){
            $return .= '
    <div class="planet '.$this->de_space($planet).'">
        <hr/>
        <h3 class="title">'.$planet.'</h3>';
            
            // BEGIN Planet guts.
            
            if( $planet !== 'Grand Tour' ){
                $image = static::$images_root.'/icons/'.$planet.'.png';
                $image = '
                    <img alt="Image:'.$planet.'" src="'.$this->my_urlencode($image).'"/>';
            }else{ $image = ''; }
            $name = $this->de_space($planet.'/Achieved');
            if( ! empty( $_SESSION['ribbons'][$this->de_space($planet.'/Achieved')] ) ){
                $checked = ' checked="checked"';
            }else{ $checked = ''; }
            if( $planet === 'Asteroid' ){
                $disabled = ' disabled="disabled"';
            }else{ $disabled = ''; }
            $return .= '
        <div class="category Achieved">
            <div class="input_box Achieved">
                <label for="'.$name.'">
                    '.$image.'
                </label>
                <input type="checkbox" id="'.$name.'" name="'.$name.'"'.$checked.$disabled.'/>
            </div>';
            if( $planet === 'Asteroid' ){
                if( empty( $_SESSION['ribbons']['Asteroid/Asteroid'] ) ){
                    $checked = ' checked="checked"';
                }else{ $checked = ''; }
                $return .= '
            <div class="input_box Asteroid">
                <label for="Asteroid/Asteroid/None">No Asteroid</label>
                <input type="radio" id="Asteroid/Asteroid/None" name="Asteroid/Asteroid" value="None"'.$checked.'/>
            </div>';
                foreach( static::$planets as $planet2 => $attribs2 ){
                    if(
                        empty( $attribs2['Asteroid'] )
                        || $planet2 === 'Asteroid'
                    ){ continue; }
                    $image = static::$images_root.'/Asteroid - '.$planet2.'.png';
                    $image = '
                    <img alt="Image:'.$planet2.'" src="'.$this->my_urlencode($image).'"/>';
                    if( // Check for default or posted value.
                        $planet2 === @$_SESSION['ribbons'][$this->de_space($planet.'/Asteroid')]
                    ){
                        $checked = ' checked="checked"';
                    }else{ $checked = ''; }
                    $name = 'Asteroid/Asteroid';
                    $id = $name.'/'.$this->de_space($planet2);
                    $return .= '
            <div class="input_box Asteroid">
                <label for="'.$id.'">
                    '.$planet2.$image.'
                </label>
                <input type="radio" id="'.$id.'" name="'.$name.'" value="'.$planet2.'"'.$checked.'/>
            </div>';
                }
            }elseif( $planet === 'Grand Tour' ){
                // Orbits & Landings:
                foreach( array('Orbits','Landings') as $each ){
                    $name = 'Grand_Tour/'.$each;
                    $return .= '
            <div class="input_box '.$name.'">
                <label for="'.$name.'">
                    '.$each.'
                </label>
                <select id="'.$name.'" name="'.$name.'">';
                    $i=0;
                    while(
                        $i <= 16
                        &&(
                            $i <= 14
                            OR $each !== 'Landings'
                        )
                    ){
                        $selected = '';
                        if(
                            $i == @$_SESSION['ribbons'][$name]
                            ||(
                                $i == 0
                                AND empty( $_SESSION['ribbons'][$name] )
                            )
                        ){
                            $selected = ' selected="selected"';
                        }
                        $return .= '
                    <option value="'.$i.'"'.$selected.'>'.$i.'</option>';
                        $i++;
                    }
                    $return .= '
                </select>
            </div>';
                }
            }
            $return .= '
            <div style="clear:both;"></div>
        </div>';
            
            
            foreach( static::$devices as $cat => $types ){
                $return .= '
        <div class="category '.$this->de_space($cat).'">';
                $first_craft = true;
                foreach( $types as $type => $devices ){
                    foreach( $devices as $device => $details ){
                        $desc = $details[1] ? : '';
                        if(
                            empty($attribs[$type])
                            AND empty($attribs[$device])
                            AND $type !== 'Common'
                            AND ! (
                                $planet === 'Kerbol'
                                AND (
                                    $device === 'Kerbol Escape'
                                    || $device === 'Meteor'
                                )
                            )
                        ){ continue; }
                        if(
                            $planet === 'Grand Tour'
                            AND ! in_array( $device, static::$gt_devices )
                        ){ continue; }
                        $input_type = 'checkbox';
                        $name = $this->de_space($planet.'/'.$device);
                        $id = $name;
                        $value = '';
                        $checked = '';
                        
                        if( $cat === 'Crafts' ){
                            $name = $this->de_space($planet.'/craft');
                            $id = $this->de_space($name.'/'.$device);
                            $value = ' value="'.$device.'"';
                            $input_type = 'radio';
                            if( $device === @$_SESSION['ribbons'][$name] ){
                                $checked = ' checked="checked"';
                            }
                            if( $first_craft ){
                                $first_craft = false;
                                if( empty( $_SESSION['ribbons'][$name] ) ){
                                    $checked2 = ' checked="checked"';
                                }else{ $checked2 = ''; }
                                $return .= '
            <div class="input_box">
                <label for="'.$id.'/None">No Craft</label>
                <input type="'.$input_type.'" id="'.$id.'/None" name="'.$name.'" value="None"'.$checked2.'/>
            </div>';
                            }
                        }elseif( ! empty( $_SESSION['ribbons'][$name] ) ){
                            $checked = ' checked="checked"';
                        }
                        
                        $image = static::$images_root.'/icons/'.$device.'.png';
                        $image = '
                    <img alt="'.$device.'" src="'.$this->my_urlencode($image).'"/>';
                        $return .= '
            <div class="input_box">
                <label for="'.$id.'" title="'.$desc.'">
                    '.$device.$image.'
                </label>
                <input type="'.$input_type.'" id="'.$id.'" name="'.$name.'"'.$value.$checked.'/>
            </div>';
                    }
                }
                $return .= '
            <div style="clear:both;"></div>
        </div>';
            }
            
            // Grand Tour specifics:
            if( $planet === 'Grand Tour' ){
                $return .= '
        <div class="category planets">';
                foreach( static::$planets as $planet2 => $attribs2 ){
                    if(
                        $planet2 === 'Grand Tour'
                        || $planet2 === 'Kerbol'
                        || $planet2 === 'Asteroid'
                    ){ continue; }
                    $image = static::$images_root.'/icons/'.$planet2.'.png';
                    $image = '
                    <img alt="'.$planet2.'" src="'.$this->my_urlencode($image).'"/>';
                    $name = $this->de_space('Grand Tour/'.$planet2);
                    if( // Check for default or posted value.
                        ! empty( $_SESSION['ribbons'][$name] )
                    ){
                        $checked = ' checked="checked"';
                    }else{ $checked = ''; }
                    $return .= '
            <div class="input_box">
                <label for="'.$name.'">
                    '.$planet2.$image.'
                </label>
                <input type="checkbox" id="'.$name.'" name="'.$name.'"'.$checked.'/>
            </div>';
                }
                $return .= '
            <div style="clear:both;"></div>
        </div>';
            }
            
            // END Planet guts.
            
            $return .= '
    </div>';
        }
        $return .= '
</fieldset></form>
<div style="clear:both;"></div>';
        return $return;
    }
    
}

?>