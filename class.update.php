<?php
/**
 * 插件更新类
 *
 * @package Hermit X
 * @since Hermit X 2.5.9
 */

/**
 * 插件更新
 *
 * @since Hermit X 2.5.9
 */
final class Hermit_Update {

	/**
	 * API 服务器
	 *
	 * @since Hermit X 2.5.9
	 * @var object
	 */
	private $api = 'https://api.lwl12.com/project/hermit/update/';

	/**
	 * 更新数据
	 *
	 * @since Hermit X 2.5.9
	 * @var object
	 */
	private $update_data;

	/**
	 * 构造函数
	 *
	 * @since Hermit X 2.5.9
	 */
	public function __construct() {
		add_filter(
			'pre_set_site_transient_update_plugins',
			array( $this, 'insert_update_data' )
		);
	}

	/**
	 * 插入插件更新信息
	 *
	 * @since Hermit X 2.5.9
	 */
	public function insert_update_data( $update ) {
		if ( $update_data = $this->get_update_data() ) {
			$file = $this->get_plugin_file();
			$update->response[$file] = $update_data;
		}

		return $update;
	}

	/**
	 * 从远程服务器获取插件更新数据
	 *
	 * @since Hermit X 2.5.9
	 */
	private function get_update_data() {
		if ( isset( $this->update_data ) )
			return $this->update_data;

		$wp_version  = get_bloginfo( 'version' );
		$home_url    = home_url();

		$plugin  = get_plugin_data( HERMIT_FILE );
		$version = $plugin['Version'];

		$response = wp_remote_post( $this->api, array(
			'timeout'    => 10,
			'user-agent' => "WordPress/{$wp_version}; {$home_url}",

			'body' => array(
				'url'         => $home_url,
				'file'        => $this->get_plugin_file(),
				'version'     => $version,
				'wp_version'  => $wp_version,
				'php_version' => phpversion(),
				'wp_locale'   => get_locale()
			)
		) );

		if ( wp_remote_retrieve_response_code( $response ) == 200 ) {
			$body = wp_remote_retrieve_body( $response );
			$body = json_decode( trim( $body ) );

			if ( $body && is_object( $body ) && isset( $body->response ) )
				$this->update_data = $body->response;
		}

		return $this->update_data;
	}

	/**
	 * 获取插件文件名
	 *
	 * @since Hermit X 2.5.9
	 */
	private function get_plugin_file() {
		return plugin_basename( HERMIT_FILE );
	}

}

// End of page.
