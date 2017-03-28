/**
 * simpleForm gexing
 * @param {Object} options
 */
(function($) {
  $.fn.simpleForm = function(options) {
	var defaultOpt = { 
		checkboxCls   	:  'xq-checkbox' , radioCls :  'xq-radio' ,	
		checkedCls 		:  'xq-checked'  , selectedCls :  'xq-selected' , 
		hideCls  	 	: 'xq-hide',
		selectWidth     : 180
	};
	defaultOpt=$.extend({},defaultOpt,options)
    return this.each(function() {
    	var $this = $(this);
    	var wrapTag = $this.attr('type') == 'checkbox' ? '<div class="'+defaultOpt.checkboxCls+'">' : '<div class="'+defaultOpt.radioCls+'">';
		var wrapTagSelect='<div class="xq_selectDiv">';
    	// for checkbox
    	if( $this.attr('type') == 'checkbox') {
			$.form.makeCheckbox($this,wrapTag,defaultOpt);
		} 
    	else if( $this.attr('type') == 'radio') {
    		$.form.makeRadio($this,wrapTag,defaultOpt);
    	}
		else if( $this.is('select') ){
		    $.form.makeSelect($this,wrapTagSelect,defaultOpt);
		}
    });
  }
  $.form={
	 makeCheckbox:function(el,wrapTag,defaultOpt){
	 		var elParent=el.parent();
	 		if(!elParent.is('div.xq-checkbox,div.xq-checked')){
				//elParent.replaceWith(elParent.html());
				el.wrap(wrapTag);
			}
			el.addClass(defaultOpt.hideCls).change(function() {
    			if( $(this).is(':checked') ) { 
    				$(this).parent().addClass(defaultOpt.checkedCls); 
    			} 
    			else { $(this).parent().removeClass(defaultOpt.checkedCls); 	}
    		});
    		
    		if( el.is(':checked') ) {
				el.parent().addClass(defaultOpt.checkedCls);    		
    		}else{
				el.parent().removeClass(defaultOpt.checkedCls);    	
			}
		
	 },
	 makeRadio:function(el,wrapTag,defaultOpt){
			el.addClass(defaultOpt.hideCls).wrap(wrapTag).change(function() {
   				$('input[name="'+$(this).attr('name')+'"]').each(function() {
   	    			if( $(this).is(':checked') ) { 
   	    				$(this).parent().addClass(defaultOpt.selectedCls); 
   	    			} else {
   	    				$(this).parent().removeClass(defaultOpt.selectedCls);     	    			
   	    			}
   				});
    		});
    		if( el.is(':checked') ) {
				el.parent().addClass(defaultOpt.selectedCls);    		
    		}    		
	 },
	 makeSelect:function(el,wrap,defaultOpt){
	 		el.hide();
			div=$(wrap).clone().css('width',defaultOpt.selectWidth);
			el.wrap(div);
	 		var selectVal=el.find('option[selected]').text(),ul=$('<ul>');
			selectVal=selectVal?selectVal:el.find('option').eq(0).text()
			ul.css('width',defaultOpt.selectWidth);
			ul.addClass('xq_selectUl').hide();
	 		var a=$('<a>').html('<label>'+selectVal+'</label>').addClass('xq_select');
			 a.css('width',defaultOpt.selectWidth);
			 a.bind('click',function(){
			 	var next=$(this).next();
			 	if(next.is(':hidden')){
					next.slideDown(100);
				}else{
					next.hide();
				}
			 });
			el.find('option').each(function(){
				var s=$(this);
				var li=$('<li/>').attr('for',s.attr('value'));
				li.html(s.text());
				li.bind('click',function(e){
					$(e.currentTarget).parent().hide();
					el.val(li.attr('for'));
					el.trigger('change');
				//	console.log(el.val());
					a.find('label').text(li.text());
				});
				ul.append(li);
			});
			el.parent().append(a).append(ul);
			$(document).click(function(e){
				//console.log(a[0]);
				//console.log(e.target)
				if($(e.target).is(".xq_select,.xq_select *")){
					return false;
				}
				e.stopPropagation();
				$('.xq_selectUl').hide();
			});
	 }
  }
})(jQuery);