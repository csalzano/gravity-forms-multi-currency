jQuery(document).on("gform_load_form_settings", function(event, form){
	var currencySelect = document.getElementById('gform_setting_currency');
	if ( currencySelect ) {
		currencySelect.addEventListener( 'change', function( e2 ) {
			form.currency = this.value;
			console.log( 'Changed to ' + form.currency );
		});
	} else {
		console.error( 'not found');
	}
});