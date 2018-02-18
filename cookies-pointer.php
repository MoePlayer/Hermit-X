<?php
return function( $ignore ) {
?>
	jQuery( function() {
		var pointer = jQuery( "#toplevel_page_hermit" ).pointer( {
			content:      '<h3>请务必填写 Cookies</h3><p>由于网易云近期的封杀，请在设置界面手动填写 Cookies 以绕过限制。</p>',
			pointerWidth: 300,

			position: {
				edge:  "left",
				align: "center"
			},

			close: function() {
				jQuery( window ).off( "scroll", reposition );
				jQuery.get( "<?php echo esc_url_raw( $ignore ); ?>" );
			}
		} ).pointer( "open" ),

		reposition = function() {
			pointer.pointer( "reposition" );
		};

		jQuery( window ).on( "scroll", reposition );
	} );
<?php
};
