<!doctype html>
<html>
<head>
    <meta charset="utf-8" />
    <title>jQuery Demo</title>
    <style type="text/css">
        div,pre,span{
            border:1px solid black;
            margin:5px;
            padding:5px;
        }
        form{
            border:3px double black;
            margin:5px;
            padding:5px;
        }
        .layer,.ribbon{
            background-color:#cccccc;
        }
    </style>
    <script type="text/javascript" src="jquery-2.1.1.js"></script>
<script type="text/javascript">
$( document ).ready(function(){
    
    $('form#ribbons_form :input').change(function(event){
        myRibbons.update(this,event);
    });
    
    myRibbons = new Object();
    myRibbons.update = function(target,event){
        var splitPatt = /([^\/]*)\/(.*)/ig;
        
        target.siblings = $(':input[name="'+target.name+'"]');
        target.siblings.each(function(index){
            var thisSib = target.siblings[index];
            thisSib.groupText = thisSib.name.replace(splitPatt,'$1');
            thisSib.propText = thisSib.name.replace(splitPatt,'$2');
            thisSib.planet = $('.planet-'+thisSib.groupText);
            thisSib.ribbon = $('.ribbon-'+thisSib.groupText);
            if( thisSib.type === 'checkbox' ){
                thisSib.valText = thisSib.checked;
            }else{
                thisSib.valText = thisSib.value;
            }
            if( typeof thisSib.valText !== 'boolean' ){
                thisSib.layer = $(thisSib.ribbon).find('.layer-'+thisSib.valText.replace(/\s+/ig,'_'));
                if( thisSib.layer.length ){
                    thisSib.layerText = thisSib.layer.prop('class').replace(/^layer-([^\s]*).*$/,'$1');
                }else{
                    thisSib.layerText = '';
                }
            }
        });
        
        if( ! target.valText || target.valText === "None" ){ // Negative selection.
            target.bool = true;
            if( target.propText === 'Achieved' ){
                target.planet.find(':input').prop('checked',false);
                target.planet.find(':input[value="None"]').prop('checked',true);
                target.planet.find('select').val('0');
            }
        }else{ // Positive selection.
            target.bool = false;
            $(':input[name="'+target.groupText+'/Achieved"]').prop('checked',true);
        }
        target.achieved = $(':input[name="'+target.groupText+'/Achieved"]').prop('checked');
        if( target.achieved ){
            makeVis(target.ribbon);
            if( target.siblings.length > 1 ){ // Is radio.
                target.siblings.each(function(index){
                    var thisSib = target.siblings[index];
                    if( thisSib.layerText && thisSib.layerText === target.layerText ){
                        makeVis(thisSib.layer);
                    }else{
                        makeInvis(thisSib.layer);
                    }
                });
            }else{
                if( target.bool && target.layer ){
                    makeVis(target.layer);
                }else{
                    makeInvis(target.layer);
                }
            }
        }else{
            makeInvis(target.ribbon);
            target.layers = target.ribbon.find('[class^="layer-"]');
            makeInvis(target.layers);
        }
        
        function makeVis(targets){
            targets.css('background-color','#eeeeee');
        }
        function makeInvis(targets){
            targets.css('background-color','#cccccc');
        }
$('.debug_display').html(
    'group: '+target.groupText+'\r\n'
    +'prop: '+target.propText+'\r\n'
    +'val: '+target.valText+'\r\n'
);
        
    }
    
});
</script>
</head>
<body>
    <form id="ribbons_form" method="post"><fieldset>
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
        </div>
        <hr/>
        <div>
            Output:
            <div class="ribbon-Grand_Tour ribbon">
                ribbon-Grand_Tour ribbon
                <span class="layer-Orbit-1 layer">Orbit-1</span>
                <span class="layer-Orbit-2 layer">Orbit-2</span>
                <span class="layer-Orbit-3 layer">Orbit-3</span>
                <span class="layer-Aircraft layer">Aircraft</span>
                <span class="layer-Multi-Part_Ship layer">Multi-Part Ship</span>
            </div>
        </div>
        <hr/>
        <div class="planet-Grand_Tour planet">
            planet-Grand_Tour planet
            <div>
                <div>
                    <label for="id-0">Achieved</label>
                    <input id="id-0" type="checkbox" name="Grand_Tour/Achieved"/>
                </div>
                <div>
                    <label for="id-1">Orbits</label>
                    <select id="id-1" name="Grand_Tour/Orbits">
                        <option selected="selected">0</option>
                        <option>1</option>
                        <option>2</option>
                        <option>3</option>
                    </select>
                </div>
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