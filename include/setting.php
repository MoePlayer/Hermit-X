<div class="wrap">
	<h2>Hermit X 插件设置</h2>
	<?php if (isset($_REQUEST['settings-updated'])) {
    echo '<div id="setting-error-settings_updated" class="updated settings-error"><p><strong>设置已保存。</strong></p></div>';
} ?>
	<?php if (isset($_POST['hermit_empty_cache'])) {
    $this->empty_cache();
    echo '<div id="setting-error-settings_updated" class="updated settings-error"><p><strong>缓存已清空。</strong></p></div><script>window.localStorage.clear();</script>';
} ?>
	<form method="post" action="options.php">
		<?php settings_fields('hermit_setting_group'); ?>
		<table class="form-table">
			<tbody>
			<tr valign="top">
				<th scope="row"><label>资源加载策略</label></th>
				<td>
					<p>
						<label title="按需加载">
							<input type="radio" name="hermit_setting[strategy]"
							       value="1" <?php if ($this->settings('strategy') == 1) {
    echo 'checked="checked"';
} ?>>
							<span>按需加载</span>
						</label>
					</p>

					<p>
						<label title="全局加载">
							<input type="radio" name="hermit_setting[strategy]"
							       value="2" <?php if ($this->settings('strategy') == 2) {
    echo 'checked="checked"';
} ?>>
							<span>全局加载</span>
						</label>
					</p>

					<p class="description">默认：<strong>按需加载</strong>，只有文章列表中使用了短代码才会加载CSS、JS资源。<br/>全局加载：无论是否使用了短代码都会加载，适合侧边栏。
					</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label>JavaScript 位置</label></th>
				<td>
					<p>
						<label title="页面顶部">
							<input type="radio" name="hermit_setting[jsplace]"
							       value="0" <?php if ($this->settings('jsplace') == 0) {
    echo 'checked="checked"';
} ?>/>
							<span>页面顶部</span>
						</label>
					</p>

					<p>
						<label title="页面底部">
							<input type="radio" name="hermit_setting[jsplace]"
							       value="1" <?php if ($this->settings('jsplace') == 1) {
    echo 'checked="checked"';
} ?>/>
							<span>页面底部</span>
						</label>
					</p>

					<p class="description">默认：<strong>页面顶部</strong></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label>颜色选择</label></th>
				<td>
					<?php $color_array = array(
                        'default' => '默认',
                        'red'     => '新年红',
                        'blue'    => '青葱绿',
                        'yellow'  => '淡淡黄',
                        'pink'    => '少女粉',
                        'purple'  => '基情紫',
                        'black'   => '暗色灰',
                        'customize'   => '自定义'
                    );
                    foreach ($color_array as $key => $title) {
                        ?>
						<label class="hermit-radio-<?php echo $key; ?>" title="<?php echo $title; ?>">
							<input type="radio" name="hermit_setting[color]"
							       value="<?php echo $key; ?>" <?php if ($this->settings('color') == $key) {
                            echo 'checked="checked"';
                        } ?>/>
							<span><?php echo $title; ?></span>
							<?php if ($key === "customize"): ?>
								<input type="text" class="regular-text" style="width:100px;" name="hermit_setting[color_customize]"
									  value="<?php echo $this->settings('color_customize'); ?>"/>
							<?php endif; ?>
						</label>
					<?php

                    }
                    ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label>音乐库每页数量</label></th>
				<td>
					<p><input type="text" class="regular-text" name="hermit_setting[prePage]"
					          value="<?php echo $this->settings('prePage'); ?>"/></p>

					<p class="description">默认数量：20。</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label>音质</label></th>
				<td>
					<?php $quality_array = array(
                        '320' =>  '极高 (320kbit/s)',
                        '192' =>  '较高 (192kbit/s)',
                        '128' =>  '普通 (128kbit/s)',
                    );
                    foreach ($quality_array as $key => $title) {
                        ?>
						<label title="<?php echo $title; ?>">
							<input type="radio" name="hermit_setting[quality]"
							       value="<?php echo $key; ?>" <?php if ($this->settings('quality') == $key) {
                            echo 'checked="checked"';
                        } ?>/>
							<span><?php echo $title; ?></span>
						</label>
					<?php

                    }
                    ?>
					<p class="description">实际音质<b>小于等于</b>所选音质。默认极高</p>
				</td>
			</tr>
			<!-- <tr valign="top">
				<th scope="row"><label>网易云镜像地址</label></th>
				<td>
					<p><label><input type="checkbox" name="hermit_setting[NeteaseMirror_status]"
					          value="1" disabled="true"/>
					<span>使用镜像地址</span></label></p>
					<p><input type="text" placeholder="网址末尾请不要包括斜线/" class="regular-text" name="hermit_setting[NeteaseMirror]"
					          value="/<?php /*echo $this->settings('NeteaseMirror');*/ ?>"/></p>

					<p class="description">
					您可以通过镜像 Hermit X 歌曲信息解析接口以提供 HTTPS 的封面和歌曲<br>
					要启用此功能，请依照以下步骤操作：<br>
					1.添加 rewrite 规则到您的 HTTP 服务器配置文件，以下是 NGINX 可用的 rewrite 规则<br>
					<pre><code>rewrite ^/wp-admin/hermit/netease_song_url/id/(\d+)$ /wp-admin/admin-ajax.php?action=hermit&amp;scope=netease_song_url&amp;id=$1 last;</code></pre><pre><code>rewrite ^/wp-admin/hermit/netease_pic_url/id/(\d+)/picid/(\d+)$ /wp-admin/admin-ajax.php?action=hermit&scope=netease_pic_url&amp;id=$1&amp;picid=$2 last;</code></pre>
					2.通过七牛、又拍云或其他服务商提供的 “镜像储存” 或类似功能镜像以下 URL：<br>
					https://your_domain/wp-admin/hermit/<br>
					3.将镜像 URL 填入上方输入框<br>
					Hermit 将会修改歌曲和封面调用地址为您的镜像地址<br>
					*如您修改了后台路径，请修改规则中两个 /wp-admin/admin-ajax.php 部分的 /wp-admin 为您正确的后台路径<br>
					*镜像歌曲可能带来高昂的流量费用以及法律风险，如您决定使用此功能，一切后果与 Hermit X 开发成员无关<br>
					*此镜像功能仅对网易云音乐单曲/播放列表/专辑有效
					</p>
				</td>
			</tr> -->
			<tr valign="top">
				<th scope="row"><label>高级缓存</label></th>
				<td>
					<p><label><input type="checkbox" name="hermit_setting[advanced_cache]"
					          value="1" <?php if ($this->settings('advanced_cache') == 1) {
                        echo 'checked="checked"';
                    } ?>/>
						<span>开启高级缓存</span></label></p>

					<p class="description">开启高级缓存需要支持 Memcached 或 Redis 相关支持</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label>网易云音乐 COOKIES</label></th>
				<td>
					<p><input type="text" class="regular-text" name="hermit_setting[netease_cookies]"
					          value="<?php echo $this->settings('netease_cookies'); ?>"/></p>

					<p class="description">如需播放网易云音乐付费歌曲类特殊曲目，请将有权限的网易云音乐帐号（如 VIP 帐号）COOKIES 填入，建议使用手机 APP COOKIES。<br>如您不理解该选项有何意义或是如何使用，请忽略。</p>
				</td>
			</tr>
			<!-- <tr valign="top">
				<th scope="row"><label>浏览器缓存时间</label></th>
				<td>
					<p><input type="text" class="small-text" name="hermit_setting[remainTime]"
					          value="<?php/* echo $this->settings('remainTime'); */?>"/>小时</p>

					<p class="description">默认数量：10小时，最大不宜超过48小时。</p>
				</td>
			</tr> -->
			<tr valign="top">
				<th scope="row"><label>播放列表最大高度</label></th>
				<td>
					<p><input type="text" class="small-text" name="hermit_setting[playlist_max_height]"
					          value="<?php echo $this->settings('playlist_max_height'); ?>"/>px</p>

					<p class="description">限制播放列表的最大高度，0为不限制。</p>
				</td>
			</tr>
			<!-- <tr valign="top">
				<th scope="row"><label>服务器地域</label></th>
				<td>
					<p><label><input type="checkbox" name="hermit_setting[within_China]" disabled="true"
					          value="1" <?php /*if ($this->settings('within_China') == 1) {
                        echo 'checked="checked"';
                    } */?>/>
						<span>服务器位于中国境内</span></label></p>

					<p class="description">此选项由 LWL API 自动控制，当检查到服务器不在中国境内时，将自动使用 LWL API 接管部分音乐信息解析操作以保证正常播放。</p>
				</td>
			</tr> -->
			<tr>
				<th scope="row"><label>新建权限</label></th>
				<td>
					<?php foreach (get_editable_roles() as $role => $details) {
                        ?>
						<label title="开启调试信息">
							<input type="checkbox" name="hermit_setting[roles][]"
							       value="<?php echo esc_attr($role); ?>" <?php checked(in_array(esc_attr($role), $this->settings('roles'))); ?> />
							<span><?php echo translate_user_role($details['name']); ?></span>
						</label>
					<?php
                    } ?>
					<p class="description">默认：<strong>管理员权限</strong> 才可以在新建或编辑文章时添加音乐</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"></th>
				<td>
					<input type="submit" class="button-primary" name="save" value="保存"/>
				</td>
			</tr>
			</tbody>
		</table>
	</form>
	<h2>清空所有数据库缓存</h2>

	<form method="post">
		<input type="submit" class="button-primary" value="清空缓存"/>
		<input name="hermit_empty_cache" type="hidden" value="1"/>
	</form>
	<style>label{margin-right:8px}input[type=checkbox],input[type=radio]{margin-right:0!important}.hermit-radio-default input[type=radio]{border-color:#5895be}.hermit-radio-default{color:#5895be}.hermit-radio-default input[type=radio]:checked:before{background-color:#5895be}.hermit-radio-red{color:#dd4b39}.hermit-radio-red input[type=radio]{border-color:#dd4b39}.hermit-radio-red input[type=radio]:checked:before{background-color:#dd4b39}.hermit-radio-blue{color:#5cb85c}.hermit-radio-blue input[type=radio]{border-color:#5cb85c}.hermit-radio-blue input[type=radio]:checked:before{background-color:#5cb85c}.hermit-radio-yellow{color:#f0ad4e}.hermit-radio-yellow input[type=radio]{border-color:#f0ad4e}.hermit-radio-yellow input[type=radio]:checked:before{background-color:#f0ad4e}.hermit-radio-pink{color:#f489ad}.hermit-radio-pink input[type=radio]{border-color:#f489ad}.hermit-radio-pink input[type=radio]:checked:before{background-color:#f489ad}.hermit-radio-purple{color:orchid}.hermit-radio-purple input[type=radio]{border-color:orchid}.hermit-radio-purple input[type=radio]:checked:before{background-color:orchid}.hermit-radio-black{color:#aaa}.hermit-radio-black input[type=radio]:checked:before{background-color:#aaa}</style>
</div>
