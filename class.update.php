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
	private $api = 'https://api.lwl12.com/project/hermit/updateCheck';

	/**
	 * 更新数据
	 *
	 * @since Hermit X 2.5.9
	 * @since Hermit X 2.6.3 储存 API 传送的所有内容，而不仅仅是版本信息。
	 *
	 * @var object
	 */
	private $update_data;

	/**
	 * 构造函数
	 *
	 * @since Hermit X 2.5.9
	 */
	public function __construct() {
		register_activation_hook(
			HERMIT_FILE,
			array( $this, 'force_check' )
		);

		add_action(
			'admin_notices',
			array( $this, 'vcs_warning' )
		);

		add_action(
			'upgrader_process_complete',
			array( $this, 'setup_notify_email' ),
			18,
			2
		);

		add_action(
			'hermit_maybe_notify_email',
			array( $this, 'maybe_notify_email' )
		);

		add_filter(
			'pre_set_site_transient_update_plugins',
			array( $this, 'insert_update_data' )
		);

		add_filter(
			'upgrader_source_selection',
			array( $this, 'rename_package' ),
			4,
			4
		);
	}

	/**
	 * 强制检测插件更新
	 *
	 * @since Hermit X 2.5.9
	 * @see wp_update_themes()
	 */
	public function force_check() {
		$update = get_site_transient( 'update_plugins' );

		if ( isset( $update->last_checked ) ) {
			unset( $update->last_checked );
			set_site_transient( 'update_plugins', $update );
		}

		wp_update_plugins();
	}

	/**
	 * 输出版本控制工具使用警告
	 *
	 * @since Hermit X 2.5.9
	 */
	public function vcs_warning() {
		if ( !current_user_can( 'activate_plugins' ) )
			return;

		$dismissed = get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true );
		$dismissed = array_filter( explode( ',', (string) $dismissed ) );

		if ( in_array( 'hermit-vcs-warning', $dismissed ) )
			return;

		if ( !$this->is_vcs_checkout() ) {
/*			$dismissed[] = 'hermit-vcs-warning';
			$dismissed   = implode( ',', $dismissed );

			update_user_meta(
				get_current_user_id(),
				'dismissed_wp_pointers',
				$dismissed
			);
*/
			return;
		}

		$text  = '警告：使用版本控制系统可能导致 Hermit X 更新失败';
		$text .= '，请删除本插件目录下的 .git 文件夹或其他版本控制文件（夹）。';

		echo '
			<div class="error notice is-dismissible" id="vcs-warning">
				<p>' . $text . '</p>
			</div>
			<script type="text/javascript">
				jQuery( document ).on( "click", "#vcs-warning .notice-dismiss", function() {
					jQuery.post(
						"' . esc_url_raw( admin_url( 'admin-ajax.php' ) ) . '",
						"action=dismiss-wp-pointer&pointer=hermit-vcs-warning"
					);
				} );
			</script>';
	}

	/**
	 * 启动更新通知程序
	 *
	 * @since Hermit X 2.6.3
	 */
	public function setup_notify_email( $upgrader, $hook_extra ) {
		if ( $hook_extra['action'] != 'update' )
			return;

		if ( $hook_extra['type'] != 'plugin' )
			return;

		if ( $hook_extra['type'] != 'plugin' )
			return;

		if ( empty( $hook_extra['bulk'] ) )
			if ( $hook_extra['plugin'] != $this->get_plugin_file() ) return;
		else
			if ( !in_array( $this->get_plugin_file(), $hook_extra['plugins'] ) ) return;

		$version = $this->get_plugin_version();
		wp_schedule_single_event( time(), 'hermit_maybe_notify_email', compact( 'version' ) );
	}

	/**
	 * 检测条件并发送更新通知
	 *
	 * @since Hermit X 2.6.3
	 */
	public function maybe_notify_email( $version ) {
		if ( ( $data = $this->get_update_data() ) && !empty( $data->notify_email ) )
			$this->notify_email( $version );
	}

	/**
	 * 插入插件更新信息
	 *
	 * @since Hermit X 2.5.9
	 * @since Hermit X 2.6.3 会从 `self::get_update_data()` 方法的返回值中筛选出版本信息。
	 */
	public function insert_update_data( $update ) {
		if ( ( $data = $this->get_update_data() ) && $data->response ) {
			$file = $this->get_plugin_file();
			$update->response[$file] = $data->response;
		}

		return $update;
	}

	/**
	 * 重命名软件包
	 *
	 * @since Hermit X 2.5.9
	 */
	public function rename_package( $source, $remote_source, $upgrader, $hook_extra ) {
		if ( empty( $hook_extra['plugin'] ) )
			return $source;

		if ( $hook_extra['plugin'] != $this->get_plugin_file() )
			return $source;

		global $wp_filesystem;
		$filename = dirname( $source ) . '/' . dirname( $this->get_plugin_file() );

		if ( !$wp_filesystem->move( $source, $filename, true ) )
			return new WP_Error( 'rename_failed', 'Rename Failed.' );

		return $filename;
	}

	/**
	 * 从远程服务器获取插件更新数据
	 *
	 * @since Hermit X 2.5.9
	 * @since Hermit X 2.6.0 新增 TTL 的支持；修复启用插件时 HTTP 请求次数过多的问题。
	 * @since Hermit X 2.6.3 返回 API 传送的所有内容，而不仅仅是版本信息。
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
				'blogname'	  => get_option('blogname'),
				'admin_email' => get_option('admin_email'),
				'file'        => $this->get_plugin_file(),
				'version'     => $this->get_plugin_version(),
				'wp_version'  => $wp_version,
				'php_version' => phpversion(),
				'wp_locale'   => get_locale()
			)
		) );

		if ( wp_remote_retrieve_response_code( $response ) == 200 ) {
			$body = wp_remote_retrieve_body( $response );
			$body = json_decode( trim( $body ) );

			if ( $body && is_object( $body ) ) {
				if ( !empty( $body->response->autoupdate ) )
					wp_schedule_single_event( time() + 10, 'wp_version_check' );

				if ( !empty( $body->ttl ) ) {
					$ttl  = (int) $body->ttl;
					$ttl += time();

					if ( $ttl < wp_next_scheduled( 'wp_version_check' ) )
						wp_schedule_single_event( $ttl, 'wp_version_check' );
				}

				$this->update_data = $body;
			} else {
				$this->update_data = false;
			}
		}

		return $this->update_data;
	}

	/**
	 * 发送更新通知
	 *
	 * @since Hermit X 2.6.3
	 */
	private function notify_email( $version ) {
		update_option( 'as_notify_email', $version );
	}

	/**
	 * 获取插件文件名
	 *
	 * @since Hermit X 2.5.9
	 */
	private function get_plugin_file() {
		return plugin_basename( HERMIT_FILE );
	}

	/**
	 * 获取插件版本
	 *
	 * @since Hermit X 2.5.9
	 */
	private function get_plugin_version() {
		if ( !function_exists( 'get_plugin_data' ) )
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		$plugin = get_plugin_data( HERMIT_FILE );
		return $plugin['Version'];
	}

	/**
	 * 检测插件目录是否使用了版本控制工具
	 *
	 * @since Hermit X 2.5.9
	 * @since Hermit X 2.6.0 使用插件目录替换原来的 `WP_PLUGIN_DIR`。
	 */
	private function is_vcs_checkout() {
		include_once( ABSPATH . '/wp-admin/includes/admin.php' );
		include_once( ABSPATH . '/wp-admin/includes/class-wp-upgrader.php' );

		$upgrader = new WP_Automatic_Updater;
		return $upgrader->is_vcs_checkout( HERMIT_PATH );
	}

}

// End of page.
