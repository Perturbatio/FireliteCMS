/**
 * simple midpoint mixin by Kris Kelly 2012
 * 
 */
YUI.add('node-midpoint', function (Y){
	
	Y.mix(Y.Node.prototype, {
		/**
		 * @return object An object  with an x and y property
		 */
		getMidpoint: function(){
			var nodeXY = this.getXY(),
				nodeSize=[ this.get('offsetWidth'), this.get('offsetHeight') ];
				
			return {
				x: nodeXY[0] + (nodeSize[1] / 2),
				y: nodeXY[1] + (nodeSize[0] / 2)
			};
		},
		
		/**
		 * @param node Node
		 * @param granularity Integer
		 * @return boolean
		 */
		isAbove: function(node, granularity){
			if ( Y.Lang.isUndefined(granularity) || !Y.Lang.isNumber(granularity)){
				granularity = 0;
			}
			return this.getMidpoint().y < (node.getMidpoint().y - granularity);
		},
		
		/**
		 * @param node Node
		 * @param granularity Integer
		 * @return boolean
		 */
		isOver: function(node, granularity){
			
			if ( Y.Lang.isUndefined(granularity) || !Y.Lang.isNumber(granularity)){
				granularity = 0;
			}
			return !this.isAbove(node, granularity) && !this.isBelow(node, granularity);
		},
		
		/**
		 * @param node Node
		 * @param granularity Integer
		 * @return boolean
		 */
		isBelow: function(node, granularity){
			if ( Y.Lang.isUndefined(granularity) || !Y.Lang.isNumber(granularity)){
				granularity = 0;
			}
			return this.getMidpoint().y >= ( node.getMidpoint().y + granularity);
		}
	});
	
}, '1.0', {requires:['node']});