<?php
/**
 * 自动更新系统类
 *
 * @package Hermit X
 * @since Hermit X 2.6.4
 */

/**
 * 自动更新系统
 *
 * @since Hermit X 2.6.4
 */
final class Hermit_Automatic_Updater extends WP_Automatic_Updater {

	/**
	 * 更新一个项目
	 *
	 * @since Hermit X 2.6.4
	 */
	public function update( $type, $item ) {
		if ( $type != 'plugin' )
			return parent::update( $type, $item );

		if ( $item->plugin != plugin_basename( HERMIT_FILE ) )
			return parent::update( $type, $item );

		$skin     = new Automatic_Upgrader_Skin();
		$upgrader = new Hermit_Plugin_Upgrader( $skin );

		if ( !$this->should_update( $type, $item, WP_PLUGIN_DIR ) )
			return false;

		do_action( 'pre_auto_update', $type, $item, WP_PLUGIN_DIR );

		$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $item->plugin );
		$skin->feedback( __( 'Updating plugin: %s' ), $plugin_data['Name'] );

		$result = $upgrader->upgrade( $item->plugin, array(
			'clear_update_cache'           => false,
			'pre_check_md5'                => false,
			'attempt_rollback'             => true,
			'allow_relaxed_file_ownership' => false
		) );

		if ( $result === false )
			$result = new WP_Error( 'fs_unavailable', __( 'Could not access filesystem.' ) );

		$this->update_results[$type][] = (object) array(
			'item'     => $item,
			'result'   => $result,
			'name'     => $plugin_data['Name'],
			'messages' => $skin->get_upgrade_messages()
		);

		return $result;
	}

}

// End of page.
