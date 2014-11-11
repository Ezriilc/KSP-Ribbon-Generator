<!doctype html>
<html>
<head>
    <meta charset="utf-8" />
    <title>KSP Ribbons SANDBOX</title>
    <link rel="stylesheet" type="text/css" href="ribbons_SANDBOX.css"/>
    <script type="text/javascript" src="jquery-2.1.1.js"></script>
    <script type="text/javascript" src="ribbons_SANDBOX.js"></script>
</head>
<body>
    <div>
        Output:
        <div class="ribbon-Grand_Tour ribbon">
            ribbon-Grand_Tour ribbon
            <br/>
            <br/>5x:
            <?php
                for( $i=1; $i <= 8; $i++ ){
                    echo '<span class="layer-Orbit_'.$i.' layer">'.$i.'</span>';
                }
            ?>
            <br/>
            <br/>1x:
            <?php
                for( $i=1; $i <= 8; $i++ ){
                    echo '<span class="layer-Orbit_'.$i.'_Silver layer">'.$i.'</span>';
                }
            ?>
            <br/>
            <br/>
            <span class="layer-Aircraft layer">Aircraft</span>
            <span class="layer-Multi-Part_Ship layer">Multi-Part Ship</span>
        </div>
    </div>
    <hr/>
    <form class="ribbons" method="post"><fieldset>
        ribbons_form
        <div>
            Effects:
            <div>
                <label for="id-5">None</label>
                <input id="id-5" type="radio" name="effect/type" value="None" checked="checked"/>
            </div>
            <div>
                <label for="id-6">Ribbon</label>
                <input id="id-6" type="radio" name="effect/type" value="Ribbon"/>
            </div>
            <div>
                <label for="id-7">Bevel</label>
                <input id="id-7" type="checkbox" name="effect/Bevel" value="Bevel"/>
            </div>
        </div>
        <hr/>
        <div class="planet-Grand_Tour planet">
            planet-Grand_Tour planet
            <div>
                <label for="id-0">Achieved</label>
                <input id="id-0" type="checkbox" name="Grand_Tour/Achieved"/>
            </div>
            <div>
                <label for="id-1">Orbits</label>
                <select id="id-1" name="Grand_Tour/Orbits">
                    <option selected="selected">0</option>
                    <?php
                        for( $i=1; $i <= 40; $i++ ){
                            echo '<option>'.$i.'</option>\r\n';
                        }
                    ?>
                </select>
            </div>
            <div>
                Craft:
                <div>
                    <label for="id-2">None</label>
                    <input id="id-2" type="radio" name="Grand_Tour/craft" value="None" checked="checked"/>
                </div>
                <div>
                    <label for="id-3">Aircraft</label>
                    <input id="id-3" type="radio" name="Grand_Tour/craft" value="Aircraft"/>
                </div>
                <div>
                    <label for="id-4">Multi-Part Ship</label>
                    <input id="id-4" type="radio" name="Grand_Tour/craft" value="Multi-Part Ship"/>
                </div>
            </div>
        </div>
        <input type="submit"/>
    </fieldset></form>

    <pre class="debug_display">debug_display</pre>
    <?php var_dump(@$_POST); ?>
</body>
</html>