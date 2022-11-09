bytefx.$scroll = function(){
	var	w = window,
		d = document,
		max = Math.max,
		min = Math.min,
		round = Math.round;
    function scroll(position, scroll){
        if(window.getMatchedCSSRules) return w[scroll];     // patch for WebKit
        
        return (d.documentElement ? d.documentElement[position] : w[scroll] || d.body[position]) || 0;
    };
    return {x:scroll("scrollLeft", "pageXOffset"), y:scroll("scrollTop", "pageYOffset")};
};
