$( document ).ready(function(){
    
    $('form.ribbons :input').change(function(event){
        myRibbons.update(this,event);
    });
    
    myRibbons = new Object();
    myRibbons.update = function(target,event){
        var splitPatt = /([^\/]*)\/(.*)/ig;
        target.siblings = $(':input[name="'+target.name+'"]');
// Add "sisters (exclusive choice) for radio, and brothers (inclusive/multi, for select)"?
        target.siblings.each(function(index){
            var thisSib = target.siblings[index];
            thisSib.groupText = thisSib.name.replace(splitPatt,'$1');
            thisSib.propText = thisSib.name.replace(splitPatt,'$2');
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
            }else{ // NOT radio, including single select.
                if(
                    target.propText === 'Orbits'
                    || target.propText === 'Landings'
                ){
                    target.OL = target.propText.replace(/s$/,'');
                    target.golds = Math.floor(target.valText / 5);
                    target.silvers = target.valText - (target.golds * 5);
                    i=1;while( i <= target.golds ){
                        makeVis(target.layers.filter('.layer-'+target.OL+'_'+i));
                        makeInvis(target.layers.filter('.layer-'+target.OL+'_'+i+'_Silver'));
                        i++;
                    }
                    i=1;while( i <= target.silvers ){
                        makeVis(target.layers.filter('.layer-'+target.OL+'_'+(target.golds+i)+'_Silver'));
                        makeInvis(target.layers.filter('.layer-'+target.OL+'_'+(target.golds+i)));
                        i++;
                    }
                    i=target.golds+target.silvers+1;
                    while(i<=8){
                        makeInvis(target.layers.filter('.layer-'+target.OL+'_'+i));
                        makeInvis(target.layers.filter('.layer-'+target.OL+'_'+i+'_Silver'));
                        i++;
                    }
                }else{
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
);
        
    }
    
});
