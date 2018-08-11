<script id="hermit-template" type="text/x-handlebars-template">
	<div id="hermit-shell">
		<div id="hermit-shell-content" class="media-modal">
			<div class="media-modal-content">
				<a id="hermit-shell-close" class="media-modal-close" href="javascript:;"><span class="media-modal-icon"><span
							class="screen-reader-text">关闭媒体面板</span></span></a>

				<div id="hermit-shell-body">
					<div class="media-frame-title">
						<h1>插入音乐<span class="dashicons dashicons-arrow-down"></span></h1>
					</div>
					<div class="media-frame-router clearfix">
						<div class="media-router">
							<a href="javascript:;" class="media-menu-item active">网易音乐</a>
							<a href="javascript:;" class="media-menu-item">虾米音乐</a>
							<a href="javascript:;" class="media-menu-item">腾讯音乐</a>
							<a href="javascript:;" class="media-menu-item">酷狗音乐</a>
							<a href="javascript:;" class="media-menu-item">千千音乐</a>
							<a href="javascript:;" class="media-menu-item">本地音乐</a>
						</div>
					</div>
					<div class="media-frame-content">
						<ul class="hermit-ul">
							<li class="hermit-li active" data-type="netease">
								<div>
									<label><input type="radio" name="netease_type" value="netease_songlist" checked="checked">单曲</label>
									<label><input type="radio" name="netease_type" value="netease_album">专辑</label>
									<label><input type="radio" name="netease_type" value="netease_playlist">歌单</label>
								</div>
								<textarea class="hermit-textarea large-text code" cols="30" rows="9"
										  placeholder="输入网易云音乐音乐地址……"></textarea>
							</li>
							<li class="hermit-li" data-type="xiami">
								<div>
									<label><input type="radio" name="xiami_type" value="xiami_songlist" checked="checked">单曲</label>
									<label><input type="radio" name="xiami_type" value="xiami_album">专辑</label>
									<label><input type="radio" name="xiami_type" value="xiami_playlist">精选集</label>
								</div>
								<textarea class="hermit-textarea large-text code" cols="30" rows="9"
										  placeholder="输入虾米音乐地址……"></textarea>
							</li>
							<li class="hermit-li" data-type="tencent">
								<div>
									<label><input type="radio" name="tencent_type" value="tencent_songlist" checked="checked">单曲</label>
									<label><input type="radio" name="tencent_type" value="tencent_album">专辑</label>
									<label><input type="radio" name="tencent_type" value="tencent_playlist">歌单</label>
								</div>
								<textarea class="hermit-textarea large-text code" cols="30" rows="9"
										  placeholder="输入QQ音乐地址……"></textarea>
							</li>
							<li class="hermit-li" data-type="kugou">
								<div>
									<label><input type="radio" name="kugou_type" value="kugou_songlist" checked="checked">单曲</label>
									<label><input type="radio" name="kugou_type" value="kugou_album">专辑</label>
									<label><input type="radio" name="kugou_type" value="kugou_playlist">歌单</label>
								</div>
								<textarea class="hermit-textarea large-text code" cols="30" rows="9"  placeholder="直接输入酷狗音乐歌曲/专辑/播放列表ID
如不知道歌曲 ID, 可使用 https://api.lwl12.com/music/kugou/search?output=pre&keyword=歌曲名 进行搜索。
将该页面返回结果中 [id] => 后字符串复制至该输入框即可。
（PS：[name] => 对应歌名，[artist] => 对应所有歌手名）"></textarea>
							</li>
							<li class="hermit-li" data-type="baidu">
								<div>
									<label><input type="radio" name="baidu_type" value="baidu_songlist" checked="checked">单曲</label>
									<label><input type="radio" name="baidu_type" value="baidu_album">专辑</label>
									<label><input type="radio" name="baidu_type" value="baidu_playlist">歌单</label>
								</div>
								<textarea class="hermit-textarea large-text code" cols="30" rows="9"
										  placeholder="输入千千音乐地址……"></textarea>
							</li>
							<li class="hermit-li" data-type="remote">
								<div id="hermit-remote-content">
									<ul></ul>
									<div class="hermit-remote-footer">
										<a id="hermit-remote-button" href="javascript:;">加载更多</a>
									</div>
								</div>
							</li>
						</ul>
						<div>
							<label for="hermit-auto"><input type="checkbox" id="hermit-auto">自动播放</label>
							<label for="hermit-mode"><select id="hermit-mode">
  								<option value ="circulation" selected>循环播放</option>
  								<option value ="random">随机播放</option>
  								<option value="order">顺序播放</option>
  								<option value="single">单曲循环</option>
							</select></label>
							<label for="hermit-preload"><select id="hermit-preload">
								<option value="auto" selected>自动预加载</option>
  								<option value ="metadata">元数据</option>
  								<option value ="none">无</option>
							</select></label>
						</div>
						<div id="hermit-preview">
						</div>
					</div>
					<div class="media-frame-toolbar">
						<div class="media-toolbar">
							<div class="media-toolbar-primary search-form">
								<a id="hermit-shell-insert" href="javascript:;"
								   class="button media-button button-primary button-large media-button-insert"
								   disabled="disabled">插入至文章</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div id="hermit-shell-backdrop" class="media-modal-backdrop">
		</div>
	</div>
</script>
<script id="hermit-remote-template" type="text/x-handlebars-template">
	{{#data}}
	<li data-id="{{id}}">{{song_name}} - {{song_author}}</li>
	{{/data}}
</script>
