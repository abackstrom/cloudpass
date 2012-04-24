(function($){

$(document).on( 'click', '.password-view', function(e){
	e.preventDefault();

	var $obj = $(e.currentTarget);

	$.get( $obj.attr('href'), function( data, ts, xhr ) {
		var $input = $( document.createElement( 'input' ) ).val( data );
		$obj.replaceWith( $input );
	});
});

})(jQuery);
