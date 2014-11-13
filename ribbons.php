<?php

new RIBBONS;
//var_dump(@$_SESSION['ribbons']['post']);
echo RIBBONS::$output;
//return RIBBONS::$output;

class RIBBONS{
    static
    $db_file = './_gitignore/kerbaltek.sqlite3'
    ,$images_root = './KSP_images'
    ,$ribbons_table = 'ribbons'
    ,$bad_db = '<p class="error message">Database failure.  Please try again.</p>'
    ,$output = null
    ,$dbcnnx = null
    ,$planets = null
    ,$layout = null
    ,$asteroids = null
    ,$devices = null
    ,$devices_ordered = null
    ,$effects = null
    ;
    // ALL SOI bodies are called "planets" here.
    
    function __construct(){
        static::$planets = array( // In order of display, by column > row.
            'Kerbol'   =>'0010001'
            ,'Moho'	    =>'1000001'
            ,'Asteroid' =>'1010010'
            ,'Eve'      =>'1110101'
            ,'Gilly'    =>'1010010'
            ,'Eeloo'    =>'1010000'
            ,'Kerbin'   =>'1111101'
            ,'Mun'      =>'1001000'
            ,'Minmus'   =>'1011010'
            ,'Duna'     =>'1111001'
            ,'Ike'      =>'1011010'
            ,'Dres'     =>'1010000'
            ,'Jool'     =>'0110001'
            ,'Laythe'   =>'1100000'
            ,'Vall'     =>'1000000'
            ,'Tylo'     =>'1001100'
            ,'Bop'      =>'1001010'
            ,'Pol'      =>'1000010'
            ,'Grand Tour' =>''
        );
        $planet_attributes = array('surface', 'atmosphere', 'geosynchronous', 'anomaly', 'challenge', 'eeva', 'asteroid'); // Number strings above.
        foreach( static::$planets as $planet => $attribs ){
            static::$planets[$planet] = array();
            foreach( $planet_attributes as $key => $val ){
                static::$planets[$planet][$val] = !!@$attribs[$key]; // Strings are ~like arrays.
            }
        }
        
        static::$effects = array( // In order of display.
            'types' => array(
                'Ribbon'
                ,'High Contrast Ribbons'
                ,'Lightened HC'
                ,'Dense HC'
                ,'Lightened Dense HC'
            )
            ,'singles' => array(
                'Darken Bevel'
                ,'Lighten Bevel'
            )
        );
        
        // Devices in order of inputs display: Category => Type => Device => Priority, Description.
        static::$devices = array(
            'mans' => array( // Category
                'common' => array( // Type
                    'Orbit'             => array(8, 'Periapsis above the atmosphere and apoapsis within the sphere of influence.')
                    ,'Equatorial'       => array(5, 'Inclination less than 5 degrees and, apoapsis and periapsis within 5% of each other.')
                    ,'Polar'            => array(4, 'Polar orbit capable of surveying the entire surface.')
                    ,'Rendezvous'       => array(6, 'Docked two craft in orbit, or maneuvered two orbiting craft within 100m for a sustained time.')
                )
                ,'surface' => array(
                    'Land Nav'          => array(9, 'Ground travel at least 30km or 1/5ths a world\'s circumference (whichever is shorter).')
                )
                ,'atmosphere' => array(
                    'Atmosphere'        => array(3, 'Controlled maneuvers using wings or similar. Granted only if craft can land and then take off, or perform maneuvers and then attain orbit.')
                )
                ,'special' => array(
                    'Geosynchronous'    => array(7, 'Achieve geosynchronous orbit around the world; or drag the body into geosynchronous orbit around another; or construct a structured, line-of-sight satellite network covering a specific location.')
                    ,'Kerbol Escape'    => array(10, 'Achieved solar escape velocity - for Kerbol only.')
                )
            )
            ,'crafts' => array(
                'common' => array(
                    'Probe'             => array(0, 'Autonomous craft which does not land.')
                    ,'Capsule'          => array(0, 'Manned craft which does not land, or only performs a single, uncontrolled landing.')
                    ,'Resource'         => array(0, 'Installation on the surface or in orbit, capable of mining and/or processing resources.')
                    ,'Aircraft'         => array(0, 'Winged craft capable of atmospheric flight, with or without any atmosphere - does not grant Flight Wings device.')
                    ,'Multi-Part Ship'  => array(0, 'Orbital vessel capable of docking and long-term habitation by multiple Kerbals.')
                    ,'Station'          => array(0, 'A craft constructed from multiple parts in orbit.')
                    ,'Armada'           => array(0, 'Three or more vessels, staged in orbit for a trip to another world, and launched within one week during one encounter window.')
                    ,'Armada 2'         => array(0, 'Three or more vessels, staged in orbit for a trip to another world, and launched within one week during one encounter window.')
                )
                ,'surface' => array(
                    'Impactor'          => array(0, 'Craft was destroyed by atmospheric or surface friction.')
                    ,'Probe Lander'     => array(0, 'Autonomous craft which landed on a world\'s surface.')
                    ,'Probe Rover'      => array(0, 'Autonomous craft which landed and performed controlled surface travel.')
                    ,'Flag or Monument' => array(0, 'A marker left on the world.')
                    ,'Lander'           => array(0, 'A craft carrying one or more Kerbals which landed without damage.')
                    ,'Rover'            => array(0, 'A vehicle which landed and then carried one or more Kerbals across the surface of the world.')
                    ,'Base'             => array(0, 'A permanent ground construction capable of long-term habitation by multiple Kerbals')
                    ,'Base 2'           => array(0, 'A permanent ground construction capable of long-term habitation by multiple Kerbals')
                )
                ,'atmosphere' => array(
                    'Meteor'            => array(0, 'Craft was destroyed due to atmospheric entry.')
                )
                ,'special' => array(
                    'Extreme EVA'       => array(0, 'Landed and returned to orbit without the aid of a spacecraft.')
                )
            )
            ,'misc' => array(
                'common' => array(
                    'Kerbal Lost'       => array(0, 'A Kerbal was killed or lost beyond the possibility of rescue.')
                    ,'Kerbal Saved'     => array(1, 'Returned a previously stranded Kerbal safely to Kerbin.')
                    ,'Return Chevron'   => array(12, 'Returned any craft safely to Kerbin from the world.')
                )
                ,'special' => array(
                    'Anomaly'           => array(2, 'Discovered and closely inspected a genuine Anomaly.')
                    ,'Challenge Wreath' => array(11, 'A special challenge for each world.')
                )
            )
        );
        static::$devices_ordered = array();
        foreach( static::$devices as $cat => $types ){
            if( $cat === 'crafts' ){ continue; }
            foreach( $types as $type => $devices ){
                foreach( $devices as $device => $details ){
                    static::$devices_ordered[$details[0]] = array($device, $type, $cat, $details[1]);
                }
            }
        }
        ksort(static::$devices_ordered);
        foreach( static::$devices['crafts'] as $type => $crafts ){
            foreach( $crafts as $craft => $details ){
                static::$devices_ordered[] = array($craft, $type, $cat, $details[1]);
            }
        }
        
        static::$output .= $this->get_ribbons();
        
    }
    
    private function get_ribbons(){
        $return = '';
        $return .= '<div class="ribbons">';
        $i=0;
        foreach( static::$planets as $planet => $attribs ){
            $i++;
            if( ($i-1) % 3 === 0 ){
                if( $i > 1 ){
                    $return .= '
    </div>';
                }
                $return .= '
    <div class="column">';
            }
            $image = static::$images_root.'/ribbons/'.$planet.'.png';
            $height = '';
            if( $planet === 'Grand Tour' ){
                $image = static::$images_root.'/ribbons/shield/Base Colours.png';
                $height = 'height:96px;line-height:96px;';
            }
            
            if( is_readable($image) && ! is_dir($image) ){
                $image = 'background-image:url(\''.$image.'\');';
            }else{ $image = ''; }
            $return .= '
        <div class="ribbon" style="'.$height.$image.'">';
        
            // BEGIN Ribbon guts.
            
            foreach( static::$devices_ordered as $device ){
                // Devices in order of priority.
                $name = $device[0];
                $type = $device[1];
                $cat = $device[2];
                $desc = $device[3];
                if(
                    $type !== 'common'
                    && empty( static::$planets[$planet][$type] )
                    && $planet !== 'Grand Tour'
                ){ continue; }
                $image = static::$images_root.'/ribbons';
                if( $planet === 'Grand Tour' ){ $image .= '/shield'; }
                $image .= '/'.$name.'.png';
                if( is_readable($image) && ! is_dir($image) ){
                    if( // Check for default or posted value.
                        @$effect === 'Ribbon'
                    ){
                        $selected = ' class="selected"';
                    }else{ $selected = ''; }
                    $image = '
            <img alt="'.$name.'" src="'.$image.'"'.$selected.'/>';
                }else{ $image = ''; }
                $return .= $image;
            }
            
            if( $planet === 'Grand Tour' ){
                foreach( static::$planets as $planet2 => $attribs ){
                    if( $planet2 === 'Grand Tour' ){ continue; }
                    $image = static::$images_root.'/ribbons/shield/'.$planet2.' Visit.png';
                    if( is_readable($image) && ! is_dir($image) ){
                        if( // Check for default or posted value.
                            @$effect === 'Ribbon'
                        ){
                            $selected = ' class="selected"';
                        }else{ $selected = ''; }
                        $image = '
            <img alt="'.$planet2.'" src="'.$image.'"'.$selected.'/>';
                    }else{ $image = ''; }
                    $return .= $image;
                }
                
                $i=1;while($i <= 8){
                    foreach( array('Orbit','Landing') as $each ){
                        foreach( array('',' Silver') as $each2 ){
                            $image = static::$images_root.'/ribbons/shield/'.$each.' '.$i.$each2.'.png';
                            if( is_readable($image) && ! is_dir($image) ){
                                if( // Check for default or posted value.
                                    @$effect === 'Ribbon'
                                ){
                                    $selected = ' class="selected"';
                                }else{ $selected = ''; }
                                $image = '
            <img alt="'.$each.' '.$i.$each2.'" src="'.$image.'"'.$selected.'/>';
                            }else{ $image = ''; }
                            $return .= $image;
                        }
                    }
                    $i++;
                }
            }
            
            foreach( static::$effects as $val ){
                foreach( $val as $effect ){
                    $image = static::$images_root.'/ribbons';
                    if( $planet === 'Grand Tour' ){ $image .= '/shield'; }
                    $image .= '/'.$effect.'.png';
                    if( is_readable($image) && ! is_dir($image) ){
                        if( // Check for default or posted value.
                            @$effect === 'Ribbon'
                        ){
                            $selected = ' class="selected"';
                        }else{ $selected = ''; }
                        $image = '
            <img alt="'.$effect.'" src="'.$image.'"'.$selected.'/>';
                    }else{ $image = ''; }
                    $return .= $image;
                }
            }
            
            // END Ribbon guts.
            
            $return .= '
            <span>'.$planet.'</span>
        </div>';
        }
        $return .= '
</div>
';
        return $return;
    }
    
}
?>