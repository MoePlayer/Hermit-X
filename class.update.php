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

		if ( empty( $hook_extra['bulk'] ) ) {
			if ( $hook_extra['plugin'] != $this->get_plugin_file() ) return;
		} else {
			if ( !in_array( $this->get_plugin_file(), $hook_extra['plugins'] ) ) return;
		}

		$args = array( $this->get_plugin_version() );
	    wp_schedule_single_event( time(), 'hermit_maybe_notify_email', $args );
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
		if (($data = $this->get_update_data()) && !empty($data->response) ) {
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
				'blogname'    => get_option('blogname'),
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
		$message_plain = "尊敬的 ".get_option('blogname')." 站长，您好！您站点的 Hermit X 插件已成功更新至".$version."版本！".PHP_EOL."您可以在 Github 查看有关此次更新的详细信息".PHP_EOL."https://github.com/liwanglin12/Hermit-X/commits/master".PHP_EOL."此致    ".PHP_EOL."Hermit X 开发团队敬上".PHP_EOL.PHP_EOL."此电子邮件地址无法接收回复。如需更多信息，请前往 Github 或插件发布页查询.";
        $message_plain = strip_tags($message_plain);
        //HTML Version
        $message_html = '<div style="margin:0;padding:0" bgcolor="#FFFFFF"> <table width="100%" height="100%" style="min-width:348px" border="0" cellspacing="0" cellpadding="0"> <tbody> <tr height="32px"></tr> <tr align="center"> <td width="32px"></td> <td> <table border="0" cellspacing="0" cellpadding="0" style="max-width:600px"> <tbody> <tr> <td> <table width="100%" border="0" cellspacing="0" cellpadding="0"> <tbody> <tr> <td align="left" style="font-family:Roboto-Regular,Helvetica,Arial,sans-serif;font-size:20px;color:#4285f4;line-height:1.25;"><b>LWL</b>&nbsp;<span style="color:#8bb7c5;">Labs</span></td> <td align="right"><img width="32" height="32" style="display:block;width:32px;height:32px" alt="wrench" src="https://img.cdn.lwl12.com/images/2017/02/03/cb55439832985360b2ea336746a21036.png"></td> </tr> </tbody> </table> </td> </tr>
		<tr height="16"></tr> <tr> <td> <table bgcolor="#e6e6e6" width="100%" border="0" cellspacing="0" cellpadding="0" style="min-width:332px;max-width:600px;border:1px solid #e0e0e0;border-bottom:0;border-top-left-radius:3px;border-top-right-radius:3px"> <tbody> <tr> <td height="72px" colspan="3"></td> </tr> <tr> <td width="32px"></td> <td style="font-family:Roboto-Regular,Helvetica,Arial,sans-serif;font-size:24px;color:#000000;line-height:1.25">您站点的 Hermit X 已成功更新</td> <td width="32px"></td> </tr> <tr> <td height="18px" colspan="3"></td> </tr> </tbody> </table> </td> </tr>
		<tr> <td> <table bgcolor="#FAFAFA" width="100%" border="0" cellspacing="0" cellpadding="0" style="min-width:332px;max-width:600px;border:1px solid #f0f0f0;border-bottom:1px solid #c0c0c0;border-top:0;border-bottom-left-radius:3px;border-bottom-right-radius:3px"> <tbody> <tr height="16px"> <td width="32px" rowspan="3"></td> <td></td> <td width="32px" rowspan="3"></td> </tr> <tr> <td> <table style="min-width:300px" border="0" cellspacing="0" cellpadding="0"> <tbody> <tr> <td style="font-family:Roboto-Regular,Helvetica,Arial,sans-serif;font-size:13px;color:#202020;line-height:1.5">尊敬的 '.get_option('blogname').' 站长，您好！</td> </tr> <tr> <td style="font-family:Roboto-Regular,Helvetica,Arial,sans-serif;font-size:13px;color:#202020;line-height:1.5">您站点的 Hermit X 插件已成功更新至'.$version.'版本。<span style="display: none; color: #fafafa;">*uniqueID*</span>
		<br><br>'.($version==="2.7.0" ? "此次更新为<b>重要更新</b>，" : "").'您可以在 <a style="text-decoration:none;color:#4285f4" target="_blank" href="https://github.com/liwanglin12/Hermit-X/commits/master">Github</a> 查看有关此次更新的详细信息<span style="display: none; color: #fafafa;">*uniqueID*</span> <tr height="26px"></tr>
		<tr> <td style="font-family:Roboto-Regular,Helvetica,Arial,sans-serif;font-size:13px;color:#202020;line-height:1.5">此致<br>Hermit X 开发团队敬上<span style="display: none; color: #fafafa;">*uniqueID*</span></td> </tr> <tr height="20px"></tr> <tr> <td> <table style="font-family:Roboto-Regular,Helvetica,Arial,sans-serif;font-size:12px;color:#b9b9b9;line-height:1.5"> <tbody> <tr> <td>此电子邮件地址无法接收回复。如需更多信息，请前往 <a href="https://blog.lwl12.com/read/hermit-x.html" style="text-decoration:none;color:#4285f4" target="_blank">插件发布页</a> 或 Github 查询。<span style="display: none; color: #fafafa;">*uniqueID*</span></td> </tr> </tbody> </table> </td> </tr></td> </tr> </tbody> </table> </td> </tr>
		<tr height="32px"></tr> </tbody> </table> </td> </tr> <tr height="16"></tr> <tr> <td style="max-width:600px;font-family:Roboto-Regular,Helvetica,Arial,sans-serif;font-size:10px;color:#bcbcbc;line-height:1.5"></td> </tr> <tr> <td> <table style="font-family:Roboto-Regular,Helvetica,Arial,sans-serif;font-size:10px;color:#666666;line-height:18px;padding-bottom:10px"> <tbody> <tr> <td>我们向您发送这封电子邮件通知，目的是让您了解与您站点相关的变化。</td> </tr> <tr> <td> <div style="direction:ltr;text-align:left">©&nbsp;'.date('Y').'&nbsp;LWL的自由天空, Hermit X Developer Team</div> </td> </tr> </tbody> </table> </td> </tr> </tbody> </table> </td> <td width="32px"></td> </tr> <tr height="32px"></tr> </tbody> </table> </div>';
        //Handle HTML Message. 1.Convert smilies 2.Generation random code 3.Replace *uniqueID* mark to random code to fix Gmail <span class="im"> issue.
        $message_html = str_replace("*uniqueID*", substr(md5(uniqid() . microtime()), 0, 5), convert_smilies($message_html));
        //Set mail headers
        $subject = '您站点 ['.get_option('blogname').'] 的 Hermit X 插件已成功更新<(￣v￣)/';
        //Hook phpmailer init action, then force change message_type to alt.
        global $phpmailer;
        add_action('phpmailer_init', function (&$phpmailer) use ($message_html, $message_plain){
            $phpmailer->isHTML(true);
            $phpmailer->Body = $message_html;
            $phpmailer->AltBody = $message_plain;
            $phpmailer->CharSet = 'utf-8';
            $phpmailer->Encoding = 'base64';
	    $phpmailer->MessageID = '<' . md5($message_plain.microtime().uniqid()).'@hermit.x.update>';
        });
        wp_mail(get_option('blogname')." <".get_option('admin_email').">", $subject, $message_html);
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
