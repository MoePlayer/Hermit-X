<?php
require_once( ABSPATH . WPINC . '/wp-db.php' );
$user_count = $wpdb->get_var( "SELECT COUNT(*) FROM mywp_hermit" );
echo "<p>User count is {$user_count}</p>";
?>