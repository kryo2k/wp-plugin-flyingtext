(function($){
	if($ === undefined) throw 'jQuery is not installed. This is required.';

	var
	singleton, $el,
	optKey = 'flyTxt_config',
	config = {},
	defaultOpts = {
		enabled: false,
		selector: 'header',
		flyingText: { // these go direct to the flying text plugin
			autoStart: true,
			messages : [],
			timeFadeIn : 450,
			timeFadeOut : 800,
			timeDisplay : 1200,
			timeHidden : 500,
			timeDelayPerChar : 50
		}
	};
	function unconfigure(me) {

		if($el !== undefined) {
			$el.flyingText('stop');
			$el.remove();
			$el = undefined;
		}

		return me;
	}
	function configure(me, cfg) {

		$.extend(config, cfg);

		// normalize enabled
		config.enabled = cfg.enabled !== undefined ?
				(cfg.enabled === true) :
				config.enabled;

		return me; // return latest config
	}
	function createEl(sel) {
		return $('<div class="flying-text-area"></div>')
			.appendTo(sel);
	}
	function render(me) {

		if( !config.enabled || !config.selector ) {
			return me;
		}

		if($el === undefined) {
			$el = createEl(config.selector);
			$el.flyingText(config.flyingText);
		}

		return me;
	}

	function FLYTXT(cfg){
		var me = this;

		me.reconfigure = function(cfg, performRender) {
			unconfigure(me);
			configure(me, cfg);
			return performRender ? render(me) : me;
		};

		me.render = function() {
			return render(me);
		};

		me.destroy = function() {
			return unconfigure(me);
		};

		return configure(me, cfg);
	};

	FLYTXT.getInstance = function() {
		return singleton ? singleton :
			singleton = new FLYTXT($.extend(true,{},defaultOpts,window[optKey]));
	};

	window.FLYTXT = FLYTXT;

	$(function(){
		FLYTXT.getInstance().render();
	});

})(jQuery);