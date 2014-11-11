$( document ).ready(function(){
    
    $('form.ribbons :input').change(function(event){
        myRibbons.update(this,event);
    });
    
    myRibbons = new Object();
    myRibbons.update = function(target,event){
        var nameSplitPatt = /^([^\/]*)\/(.*)$/i;
        target.siblings = $(':input[name="'+target.name+'"]');
        target.siblings.each(function(index){
            var thisSib = target.siblings[index];
            thisSib.groupText = thisSib.name.replace(nameSplitPatt,'$1');
            thisSib.propText = thisSib.name.replace(nameSplitPatt,'$2');
            thisSib.planet = $('.planet-'+thisSib.groupText);
            thisSib.ribbon = $('.ribbon-'+thisSib.groupText);
            thisSib.layers = thisSib.ribbon.find('[class^="layer-"]');
            if( thisSib.type === 'checkbox' ){
                thisSib.valText = thisSib.checked;
            }else{
                thisSib.valText = thisSib.value;
            }
            if( typeof thisSib.valText !== 'boolean' ){
                thisSib.layer = thisSib.ribbon.find('.layer-'+thisSib.valText.replace(/\s+/ig,'_'));
                if( thisSib.layer.length ){
                    thisSib.layerText = thisSib.layer.prop('class').replace(/^layer-([^\s]*).*$/,'$1');
                }else{ thisSib.layerText = ''; }
            }
        });
        
        // Effects...
        if( target.group === 'effects' ){
            
        }
        
        
        if( target.valText && target.valText !== "None" ){ // Positive selection.
            target.bool = true;
            $(':input[name="'+target.groupText+'/Achieved"]').prop('checked',true);
        }else{ // Negative selection.
            target.bool = false;
            if( target.propText === 'Achieved' ){
                target.planet.find(':input').prop('checked',false);
                target.planet.find(':input[value="None"]').prop('checked',true);
                target.planet.find('select').val('0');
            }
        }
        target.achieved = $(':input[name="'+target.groupText+'/Achieved"]').prop('checked');
        if( target.achieved ){
            makeVis(target.ribbon);
            if( target.siblings.length > 1 ){ // Radio.
                target.siblings.each(function(index){
                    var thisSib = target.siblings[index];
                    if( thisSib.layerText && thisSib.layerText === target.layerText ){
                        makeVis(thisSib.layer);
                    }else{
                        makeInvis(thisSib.layer);
                    }
                });
            }else{ // NOT radio - includes single select.
                if(
                    target.propText === 'Orbits'
                    || target.propText === 'Landings'
                ){
                    target.OL = target.propText.replace(/s$/,'');
                    if( target.valText+0 > 0 ){
                        makeVis( target.layers.filter('.layer-'+target.OL+'_1') );
                        target.OLrepeats = target.valText - 1;
                    }else{
                        makeInvis( target.layers.filter('.layer-'+target.OL+'_1') );
                        target.OLrepeats = 0;
                    }
                    target.silvers = 0;
                    while( target.OLrepeats > 7 ){
                        target.silvers++;
                        target.OLrepeats -= 5;
                    }
                    target.golds = target.valText - (target.silvers * 5);
                    var thisS, thisG;
                    i=2;while( i <= 8 ){
                        thisS = target.layers.filter('.layer-'+target.OL+'_'+i+'_Silver');
                        thisG = target.layers.filter('.layer-'+target.OL+'_'+i);
                        if( i <= target.silvers+1 ){
                            makeVis(thisS);
                            makeInvis(thisG);
                        }else if( i <= target.silvers + target.golds ){
                            makeVis(thisG);
                            makeInvis(thisS);
                        }else{
                            makeInvis(thisS);
                            makeInvis(thisG);
                        }
                        i++;
                    }
                }else{ // NOT orbits or landings - expected checkboxes only.
                    if( target.bool ){
                        makeVis(target.layer);
                    }else{
                        makeInvis(target.layer);
                    }
                }
            }
        }else{
            makeInvis(target.layers);
            makeInvis(target.ribbon);
        }
        
        function makeVis(JQobj){
            if( ! JQobj ){ return; }
            JQobj.css('background-color','green');
        }
        function makeInvis(JQobj){
            if( ! JQobj ){ return; }
            JQobj.css('background-color','#cccccc');
        }
$('.debug_display').html(
    'group: '+target.groupText+'\r\n'
    +'prop: '+target.propText+'\r\n'
    +'val: '+target.valText+'\r\n'
    +'5x: '+target.silvers+'\r\n'
    +'1x: '+target.golds+'\r\n'
);
        
    }
    
});
