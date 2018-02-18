<?php
return function( $ignore ) {
	$title   = '请设置网易云音乐 Cookies';
	$content = '近期我们的网易云音乐解析模块遭受了网易技术团队封锁，建议您设置自己的网易云音乐 Cookies 以避免受到影响。详情请查阅 <a href="https://github.com/metowolf/Meting/wiki/special-for-netease" target="_blank">Meting Wiki</a>。';
?>
	jQuery( function() {
		var pointer = jQuery( "#toplevel_page_hermit" ).pointer( {
			content:      '<h3><?php echo $title; ?></h3><p><?php echo $content; ?></p>',
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
