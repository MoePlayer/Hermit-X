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

	}

	/**
	 * 插入插件更新信息
	 *
	 * @since Hermit X 2.5.9
	 */
	public function insert_update_data( $update_data ) {
		if ( $my_update_data = $this->get_update_data() ) {
			if ( !isset( $update_data->response ) )
				$update_data->response = array();

			$update_data->response = $my_update_data + $update_data->response;
		}

		return $update_data;
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

		$response = wp_remote_post( $this->api, array(
			'timeout'    => 10,
			'user-agent' => "WordPress/{$wp_version}; {$home_url}",

			'body' => array(
				'url'         => $home_url,
				'file'        => plugin_basename( HERMIT_FILE ),
				'version'     => HERMIT_VERSION,
				'wp_version'  => $wp_version,
				'php_version' => phpversion(),
				'wp_locale'   => get_locale()
			)
		) );

		if ( wp_remote_retrieve_response_code( $response ) == 200 ) {
			$body = wp_remote_retrieve_body( $response );
			$body = json_decode( trim( $body ) );

			if ( $body && is_object( $body ) )
				$this->update_data = $body;
		}

		return $this->update_data;
	}

	/**
	 * 获取插件文件名
	 *
	 * 
	 */

}

// End of page.
