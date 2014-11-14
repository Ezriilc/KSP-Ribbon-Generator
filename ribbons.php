<?php

echo '<h2>KSP Ribbon Generator v2.0 - ALPHA</h2>';
new RIBBONS;
echo RIBBONS::$output;
//return RIBBONS::$output;
var_dump(@$_SESSION);

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
            'Kerbol'        =>'0010001'
            ,'Moho'	        =>'1000001'
            ,'Asteroid'     =>'1010010'
            ,'Eve'          =>'1110101'
            ,'Gilly'        =>'1010010'
            ,'Eeloo'        =>'1010000'
            ,'Kerbin'       =>'1111101'
            ,'Mun'          =>'1001000'
            ,'Minmus'       =>'1011010'
            ,'Duna'         =>'1111001'
            ,'Ike'          =>'1011010'
            ,'Dres'         =>'1010000'
            ,'Jool'         =>'0110001'
            ,'Laythe'       =>'1100000'
            ,'Vall'         =>'1000000'
            ,'Tylo'         =>'1001100'
            ,'Bop'          =>'1001010'
            ,'Pol'          =>'1000010'
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
                    'Land Nav'          => array(9, 'Ground travel at least 30km or 1/5ths a world\'s circumference (whichever is shorter).')
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
                    ,'Multi-Part Ship'  => array(0, 'Orbital vessel capable of docking and long-term habitation by multiple Kerbals.')
                    ,'Station'          => array(0, 'A craft constructed from multiple parts in orbit.')
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
        
        $this->get_input();
        static::$output .= $this->get_ribbons();
        static::$output .= $this->get_form();
        
    }
    
    private function de_space($in_out){
        return preg_replace('/\s+/','_',$in_out);
    }
    
    private function get_input(){
        if( empty($_POST['ribbons_submit']) ){
            if( empty($_SESSION['ribbons']) ){
                // Set everything to defaults.
                $_SESSION['ribbons'] = array(
                    'effects/Texture' => 'Ribbon'
                );
            }
        }else{
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
        }
    }
    
    private function get_form(){
        $return = '';
        $member_message = 'This will save your ribbons for this session only.';
        if( ! empty( $_SESSION['logged_in'] ) ){
            $member_message = 'This will permanently change your saved ribbons in the database.';
        }
        $return .= '
<div style="clear:both;"></div>
<form class="ribbons" method="post"><fieldset>
    <div class="submit">
        <input title="'.$member_message.'" type="submit" name="ribbons_submit" value="Save Ribbons"/>
    </div>';
        
        // Submit:
        
        // Effects:
            $return .= '
    <div class="effects">
        <hr/>
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
                    $id = $name.'/'.$effect;
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
                
                $image = static::$images_root.'/ribbons/'.$effect.'.png';
                if( is_readable($image) && ! is_dir($image) ){
                    $image = ' style="background-image:url(\''.$image.'\');"';
                }else{ $image = ''; }
                $return .= '
            <div class="input_box">
                <label for="'.$id.'"'.$image.'>'.$effect.'</label>
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
            if( ! empty( $_SESSION['ribbons'][$this->de_space($planet.'/Achieved')] ) ){
                $checked = ' checked="checked"';
            }else{ $checked = ''; }
            $planet_image = static::$images_root.'/ribbons/icons/'.$planet.'.png';
            if( is_readable($planet_image) && ! is_dir($planet_image) ){
                $planet_image = ' style="background-image:url(\''.$planet_image.'\');"';
            }
            $name = $this->de_space($planet.'/Achieved');
            $return .= '
        <div class="category achieved">
            <div class="input_box Achieved">
                <label for="'.$name.'"'.$planet_image.'>Achieved</label>
                <input type="checkbox" id="'.$name.'" name="'.$name.'"'.$checked.'/>
            </div>
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
                            && empty($attribs[$device])
                            && $type !== 'Common'
                        ){ continue; }
                        if(
                            $planet === 'Grand Tour'
                            && ! is_readable( static::$images_root.'/ribbons/shield/'.$device.'.png' )
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
                                if( empty( $_SESSION['ribbons'][$name] ) ){
                                    $checked2 = ' checked="checked"';
                                }else{ $checked2 = ''; }
                                $first_craft = false;
                                $return .= '
            <div class="input_box">
                <label for="'.$id.'/None">No Craft</label>
                <input type="'.$input_type.'" id="'.$id.'/None" name="'.$name.'" value="None"'.$checked2.'/>
            </div>';
                            }
                        }elseif( ! empty( $_SESSION['ribbons'][$name] ) ){
                            $checked = ' checked="checked"';
                        }
                        
                        $image = static::$images_root.'/ribbons/icons/'.$device.'.png';
                        if( is_readable($image) && ! is_dir($image) ){
                            $image = ' style="background-image:url(\''.$image.'\');"';
                        }else{ $image = ''; }
                        $return .= '
            <div class="input_box">
                <label for="'.$id.'" title="'.$desc.'"'.$image.'>'.$device.'</label>
                <input type="'.$input_type.'" id="'.$id.'" name="'.$name.'"'.$value.$checked.'/>
            </div>';
                    }
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
    
    private function get_ribbons(){
        $return = '';
        $return .= '
<div style="clear:both;"></div>
<div class="ribbons">';
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
            if( $planet === 'Grand Tour' ){
                $image = static::$images_root.'/ribbons/shield/Base Colours.png';
                $height = 'height:96px;line-height:96px;';
            }else{ $height = ''; }
            if( is_readable($image) && ! is_dir($image) ){
                $image = 'background-image:url(\''.$image.'\');';
            }else{ $image = ''; }
            if( ! empty( $_SESSION['ribbons'][$this->de_space($planet.'/Achieved')] ) ){
                $selected = ' selected';
            }else{ $selected = ''; }
            $return .= '
        <div  title="'.$planet.'" class="ribbon '.$this->de_space($planet).$selected.'" style="'.$height.$image.'">';
        
            // BEGIN Ribbon guts.
            
            $name_vis = '';
            if( ! empty( $_SESSION['ribbons'][$this->de_space($planet.'/Achieved')] ) ){
                $name_vis = ' style="opacity:0;"';
            }
            $return .= '
            <span class="title"'.$name_vis.'>'.$planet.'</span>';
            
            foreach( static::$devices_ordered as $device ){
                // Devices in order of priority.
                $type = $device[1];
                $cat = $device[2];
                $desc = $device[3];
                $device = $device[0];
                if(
                    $type !== 'Common'
                    && empty($attribs[$type])
                    && empty($attribs[$device])
                    && $planet !== 'Grand Tour'
                ){ continue; }
                $image = static::$images_root.'/ribbons';
                if( $planet === 'Grand Tour' ){ $image .= '/shield'; }
                $image .= '/'.$device.'.png';
                if( is_readable($image) && ! is_dir($image) ){
                    if( // Check for default or posted value.
                        ! empty( $_SESSION['ribbons'][$this->de_space($planet.'/'.$device)] )
                        || $device === @$_SESSION['ribbons'][$this->de_space($planet.'/craft')]
                    ){
                        $selected = ' selected';
                    }else{ $selected = ''; }
                    $image = '
            <img class="device '.$this->de_space($device).$selected.'" alt="'.$device.'" src="'.$image.'"/>';
                }else{ $image = ''; }
                $return .= $image;
            }
            
            if( $planet === 'Grand Tour' ){
                foreach( static::$planets as $planet2 => $attribs2 ){
                    if( $planet2 === 'Grand Tour' ){ continue; }
                    $image = static::$images_root.'/ribbons/shield/'.$planet2.' Visit.png';
                    if( is_readable($image) && ! is_dir($image) ){
                        if( // Check for default or posted value.
                            ! empty( $_SESSION['ribbons'][$this->de_space($planet.'/'.$planet2)] )
                        ){
                            $selected = ' selected';
                        }else{ $selected = ''; }
                        $image = '
            <img class="device '.$this->de_space($planet2).$selected.'" alt="'.$planet2.'" src="'.$image.'"/>';
                    }else{ $image = ''; }
                    $return .= $image;
                }
                
                $i=1;while($i <= 8){
                    foreach( array('Orbit','Landing') as $each ){
                        foreach( array('',' Silver') as $each2 ){
                            $OLname = $each.' '.$i.$each2;
                            $image = static::$images_root.'/ribbons/shield/'.$OLname.'.png';
                            if( is_readable($image) && ! is_dir($image) ){
                                if(
                                    
                                    // Check for default or posted value.
                                    @$effect === 'Ribbon'
                                    
                                ){
                                    $selected = ' selected';
                                }else{ $selected = ''; }
                                $image = '
            <img class="device '.$this->de_space($OLname).$selected.'" alt="'.$OLname.'" src="'.$image.'"/>';
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
                            $effect === @$_SESSION['ribbons']['effects/Texture']
                            || ! empty( $_SESSION['ribbons']['effects/'.$effect] )
                        ){ $selected = ' selected'; }else{ $selected = ''; }
                        $image = '
            <img class="effect '.$this->de_space($effect).$selected.'" alt="'.$effect.'" src="'.$image.'"/>';
                    }else{ $image = ''; }
                    $return .= $image;
                }
            }
            
            // END Ribbon guts.
            
            $return .= '
        </div>';
        }
        $return .= '
</div>
<div style="clear:both;"></div>
';
        return $return;
    }
    
}
?>