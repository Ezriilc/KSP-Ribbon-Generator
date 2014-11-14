$(document).ready(function(){
    $('.ribbons .planet').fadeOut(0);
    
    $('.ribbons .ribbon').click(function(event){
        $('.ribbons input[type="submit"]').fadeIn(0);
        var planetText = this.className.replace(/.*ribbon ([^\s]*).*/i,'$1');
        $('.ribbons .planet').not('.'+planetText).fadeOut(0);
        $('.ribbons .planet.'+planetText).fadeTo('slow',1);
    });
    
    $('.ribbons :input').change(function(event){
        myRibbons.update(this,event);
    });
    
    myRibbons = new Object();
    myRibbons.update = function(target,event){
        var nameSplitPatt = /^([^\/]*)\/(.*)$/i;
        function makeVis(JQobj){
            if( ! JQobj ){ return; }
            JQobj.fadeTo('slow',1);
            JQobj.addClass('selected');
        }
        function makeInvis(JQobj){
            if( ! JQobj ){ return; }
            JQobj.fadeTo('slow',0);
            JQobj.removeClass('selected');
        }
        target.siblings = $(':input[name="'+target.name+'"]'); // Siblings include self.
        target.siblings.each(function(index){
            var thisSib = target.siblings[index];
            thisSib.groupText = thisSib.name.replace(nameSplitPatt,'$1');
            thisSib.propText = thisSib.name.replace(nameSplitPatt,'$2');
            if( thisSib.type === 'checkbox' ){
                thisSib.valText = thisSib.checked;
            }else{
                thisSib.valText = thisSib.value;
            }
            if( thisSib.valText && thisSib.valText !== 'None' ){
                thisSib.bool = true;
            }else{
                thisSib.bool = false;
            }
            if( thisSib.groupText === 'effects' ){
                if( thisSib.type !== 'checkbox' ){
                    thisSib.effects = $('.ribbons .effect.'+thisSib.valText.replace(/\s+/g,'_'));
                }else{
                    thisSib.effects = $('.ribbons .effect.'+thisSib.propText);
                }
            }else{
                thisSib.planet = $('.planet.'+thisSib.groupText);
                thisSib.ribbon = $('.ribbon.'+thisSib.groupText);
                thisSib.devices = thisSib.ribbon.find('.device');
                if( typeof thisSib.valText !== 'boolean' ){
                    thisSib.device = thisSib.devices.filter('.device.'+thisSib.valText.replace(/\s+/g,'_'));
                }else{
                    thisSib.device = thisSib.devices.filter('.device.'+thisSib.propText.replace(/\s+/g,'_'));
                }
                if( thisSib.device.length ){
                    thisSib.deviceText = thisSib.device.prop('class').replace(/.*device ([^\s]*).*/i,'$1');
                }else{ thisSib.deviceText = ''; }
            }
        });
        
        // Data collected, now do stuff.
        
        if( target.groupText === 'effects' ){
            target.siblings.each(function(index){
                var thisSib = target.siblings[index];
                if( thisSib === target ){ return; }
                makeInvis(thisSib.effects);
            });
        }
        if( target.bool ){
            makeVis(target.effects);
            $(':input[name="'+target.groupText+'/Achieved"]').prop('checked',true);
            if(
                target.propText === 'Equatorial'
                || target.propText === 'Polar'
                || target.propText === 'Geosynchronous'
            ){
                $(':input[name="'+target.groupText+'/Orbit"]').prop('checked',true);
                makeVis(target.ribbon.find('.device.Orbit'));
                if( target.propText === 'Geosynchronous' ){
                    $(':input[name="'+target.groupText+'/Equatorial"]').prop('checked',true);
                    makeVis(target.ribbon.find('.device.Equatorial'));
                }
            }
        }else{
            makeInvis(target.effects);
            if( target.propText === 'Orbit' ){
                $(':input[name="'+target.groupText+'/Equatorial"]').prop('checked',false);
                makeInvis(target.ribbon.find('.device.Equatorial'));
                $(':input[name="'+target.groupText+'/Polar"]').prop('checked',false);
                makeInvis(target.ribbon.find('.device.Polar'));
                $(':input[name="'+target.groupText+'/Geosynchronous"]').prop('checked',false);
                makeInvis(target.ribbon.find('.device.Geosynchronous'));
            }else if( target.propText === 'Achieved' ){
                target.planet.find(':input').prop('checked',false);
                target.planet.find(':input[value="None"]').prop('checked',true);
                target.planet.find('select').val('0');
            }
        }
        target.achieved = $(':input[name="'+target.groupText+'/Achieved"]').prop('checked');
        if( ! target.achieved ){
            makeInvis(target.devices);
            if( target.ribbon ){
                target.ribbon.fadeTo('slow',0.5);
                target.ribbon.find('.title').fadeTo('slow',1);
                target.ribbon.removeClass('selected');
            }
            return;
        }
        
        target.ribbon.fadeTo('slow',1);
        target.ribbon.find('.title').fadeTo('slow',0);
        target.ribbon.addClass('selected');
        
        if( target.siblings.length > 1 ){ // Radio.
            target.siblings.each(function(index){
                var thisSib = target.siblings[index];
                if( thisSib.deviceText && thisSib.deviceText === target.deviceText ){
                    makeVis(thisSib.device);
                }else{
                    makeInvis(thisSib.device);
                }
            });
        }else{ // NOT radio - includes single select.
            if(
                target.propText === 'Orbits'
                || target.propText === 'Landings'
            ){
                target.OL = target.propText.replace(/s$/,'');
                if( target.valText+0 > 0 ){
                    makeVis( target.devices.filter('.device.'+target.OL+'_1') );
                    target.OLrepeats = target.valText - 1;
                }else{
                    makeInvis( target.devices.filter('.device.'+target.OL+'_1') );
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
                    thisS = target.devices.filter('.device.'+target.OL+'_'+i+'_Silver');
                    thisG = target.devices.filter('.device.'+target.OL+'_'+i);
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
            }else{ // NOT orbits or landings - still NOT radio.
                if( target.bool ){
                    makeVis(target.device);
                }else{
                    makeInvis(target.device);
                }
            }
        }
        
$('.debug_display').html(
    'group: '+target.groupText+'\r\n'
    +'prop: '+target.propText+'\r\n'
    +'val: '+target.valText+'\r\n'
);
        
    }
    
});
