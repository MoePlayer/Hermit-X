<?php
/**
 * 插件更新系统类
 *
 * @package Hermit X
 * @since Hermit X 2.6.4
 */

/**
 * 插件更新系统
 *
 * @since Hermit X 2.6.4
 */
final class Hermit_Plugin_Upgrader extends Plugin_Upgrader {

	/**
	 * 运行一个升级或安装
	 *
	 * @since Hermit X 2.6.4
	 */
	public function run( $options ) {
		$result = parent::run( $options );

		if ( is_wp_error( $result ) )
			$GLOBALS['hmt_update']->report_error( $result );

		return $result;
	}

}

// End of page.
