<div class="wrap">
	<h2>Hermit X 插件设置</h2>
	<?php if (isset($_REQUEST['settings-updated'])) {
    echo '<div id="setting-error-settings_updated" class="updated settings-error"><p><strong>设置已保存。</strong></p></div>';
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
			<tr valign="top">
				<th scope="row"><label>低安全性验证</label></th>
				<td>
					<p><label><input type="checkbox" name="hermit_setting[low_security]"
					          value="1" <?php if ($this->settings('low_security') == 1) {
                        echo 'checked="checked"';
                    } ?>/>
						<span>使用低安全性验证</span></label></p>

					<p class="description">默认使用 Nonce 验证保证 Hermit X 音乐信息接口不被恶意利用。<br> 仅在您需要使用全页面缓存等无法兼容 nonce 验证的情况下需要启用该选项。<b>开启该选项会显著降低接口安全性</b></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label>全局代理</label></th>
				<td>
					<p><input type="text" class="regular-text" name="hermit_setting[proxy]"
					          value="<?php echo $this->settings('proxy'); ?>"/></p>

					<p class="description">Hermit X 将使用此代理服务器请求各曲源服务器。</p>
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
			<tr valign="top">
				<th scope="row"><label>播放列表相关</label></th>
				<td>
					<p><label><input type="checkbox" name="hermit_setting[listFolded]"
							  value="1" <?php if ($this->settings('listFolded') == 1) {
						echo 'checked="checked"';
					} ?>/>
						<span>默认折叠播放列表</span></label></p>

					<p class="description">是否默认折叠播放列表</p>
					<br>
					<p>列表最大高度	<input type="text" class="small-text" name="hermit_setting[playlist_max_height]"
					          value="<?php echo $this->settings('playlist_max_height'); ?>"/>px</p>

					<p class="description">限制播放列表的最大高度，0为不限制。</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label>前台公共 CDN</label></th>
				<td>
					<input type='hidden' name='hermit_setting[assetsPublicCDN]' value='0' />
					<p><label><input type="checkbox" name="hermit_setting[assetsPublicCDN]"
					          value="1" <?php if ($this->settings('assetsPublicCDN') == 1) {
                        echo 'checked="checked"';
                    } ?>/>
						<span>启用公共 CDN</span></label></p>

					<p class="description">在前台使用 <a href="https://www.jsdelivr.com/" target="_blank" rel="noopener">jsDelivr</a> 加载 JS、CSS 等资源文件，提升访问速度。 </p>
				</td>
			</tr>
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

	<style>label{margin-right:8px}input[type=checkbox],input[type=radio]{margin-right:0!important}.hermit-radio-default input[type=radio]{border-color:#5895be}.hermit-radio-default{color:#5895be}.hermit-radio-default input[type=radio]:checked:before{background-color:#5895be}.hermit-radio-red{color:#dd4b39}.hermit-radio-red input[type=radio]{border-color:#dd4b39}.hermit-radio-red input[type=radio]:checked:before{background-color:#dd4b39}.hermit-radio-blue{color:#5cb85c}.hermit-radio-blue input[type=radio]{border-color:#5cb85c}.hermit-radio-blue input[type=radio]:checked:before{background-color:#5cb85c}.hermit-radio-yellow{color:#f0ad4e}.hermit-radio-yellow input[type=radio]{border-color:#f0ad4e}.hermit-radio-yellow input[type=radio]:checked:before{background-color:#f0ad4e}.hermit-radio-pink{color:#f489ad}.hermit-radio-pink input[type=radio]{border-color:#f489ad}.hermit-radio-pink input[type=radio]:checked:before{background-color:#f489ad}.hermit-radio-purple{color:orchid}.hermit-radio-purple input[type=radio]{border-color:orchid}.hermit-radio-purple input[type=radio]:checked:before{background-color:orchid}.hermit-radio-black{color:#aaa}.hermit-radio-black input[type=radio]:checked:before{background-color:#aaa}</style>
</div>
