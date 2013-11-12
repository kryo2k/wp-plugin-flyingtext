/**
 * Flys text strings into a container.
 * @see http://codepen.io/kryo2k/pen/cgAuv
 * @author Hans Doller
 */
(function($) {
  if($ === undefined) {
    throw "jQuery is not installed.";
  }
  function FlyingText(selector, config) {
    var me = this, $flyBox = $(selector), rotateIdx = 0, started = false,
      firstTimeConfigure = true, myConfig = {
      autoStart: false,
      messages: [],
      timeFadeIn: 450,
      timeFadeOut: 800,
      timeDisplay: 1200,
      timeHidden: 500,
      timeDelayPerChar: 50
    };
    me.start = function(){
      if(started) return this; // bail if started
      started = true;

      var computeDuration = function(str, baseTimeout){
          return parseInt(baseTimeout) + (str.length * parseInt(myConfig.timeDelayPerChar));
        },
        rotate = function(callback){
          callback = callback || function(){};

          var msgs = myConfig.messages, msg;
          if(rotateIdx > (msgs.length - 1)) {
            rotateIdx = 0; // reset to beginning
          }

          // make sure we have messages to display
          if((msg = msgs[rotateIdx]) === undefined) {
            me.stop();
            return;
          }

          // create offscreen element:
          var el = $('<div class="string">')
            .html(msg).appendTo($flyBox);
        
          el.css({left: $flyBox.width(), opacity: 0})
            .animate({left: (($flyBox.width() / 2) - (el.width() / 2)), opacity: 1}, myConfig.timeFadeIn, function(){
              window.setTimeout(function(){
                el.animate({left: -el.width(), opacity: 0}, myConfig.timeFadeOut,function(){
                  el.remove();
                  if(started) {
                    window.setTimeout(function(){
                      rotate(callback);
                    },myConfig.timeHidden);
                  }
                  callback(rotateIdx++);
                });
              },computeDuration(msg,myConfig.timeDisplay));
            });
        };

      rotate(function(rotationId){
        console.log('finished rotation', rotationId);
      });
      
      return this;
    };
    me.stop = function(){
      if(started) {
        started = false;
      }

      return this;
    };
    me.configure = function(cfg){
      $.extend(myConfig, cfg);

      if(firstTimeConfigure && myConfig.autoStart) {
          me.start();
      }

      firstTimeConfigure = false;

      return this;
    };
    me.invocation = function(jqe, args){
      var argc = args.length, whitelistFn = [
        'start', 'stop'
      ];
      if( argc === 1 && $.isPlainObject(args[0]) ) {
        me.configure(args[0]);
      }
      else if( argc > 0 && typeof(args[0]) === 'string' && whitelistFn.indexOf(args[0]) !== -1 ) {
        return me[args[0]].apply(jqe, Array.prototype.slice.call( args, 1 ));
      }
      return jqe; // keep fluid
    };
    if(config !== undefined) { // configure immediately
      me.configure(config);
    }
    return me;
  } 
  
  var selectorCache = {};
  function getInstance(selector) {
    return (selectorCache[selector] === undefined) ?
        selectorCache[selector] = new FlyingText(selector) :
        selectorCache[selector];
  }

  $.fn.flyingText = function() {
    return getInstance(this.selector).invocation(this, arguments);
  };

})(jQuery);