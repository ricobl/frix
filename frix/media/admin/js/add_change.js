$(function(){
	
	var hint_box = $('<span id="hint_box" />').prependTo('body');
	
	var change = function () {
		
		var self = $(this);
		
		var chars_left = self.attr('maxlength') - self.val().length;
		hint_box.text(chars_left + ' characters left.');
		
	};
	
	$('form :text').filter(function(){
		
		// IE gives 2147483647 when not specified, Firefox gives -1
		var ml = $(this).attr('maxlength');
		return (ml != -1) && (ml < 1000);
		
	}).bind('change keydown keyup click focus paste', change).focus(function(){
		
		var self = $(this);
		
		offset = self.offset();
		offset.left += self.width();
		
		$.each(['border-left-width', 'border-right-width', 'padding-left', 'padding-right'], function () {
			offset.left += Number(self.css(this).replace('px', ''));
		});
		
		hint_box.css(offset);
		hint_box.show();
		
	}).blur(function(){
		hint_box.hide();
	});
	
});
