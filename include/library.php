<?php
$catList = $this->music_catList();
$prePage = $this->settings('prePage');
$count    = $this->music_count();
$maxPage = ceil($count / $prePage);
$catid = isset($_GET['catid']) && $_GET['catid'] ? $_GET['catid'] : null;
?>
<div class="wrap">
	<h2>Hermit X 音乐库 <a href="javascript:;" class="add-new-h2">新建音乐</a></h2>

	<div class="hermit-list-table">
		<ul class="subsubsub"></ul>
		<div class="tablenav top">
			<div class="alignleft actions bulkactions">
				<label class="screen-reader-text">选择批量操作</label>
				<select name="action" class="hermit-action-selector">
					<option value="no">批量操作</option>
					<option value="trash">删除</option>
                    <option value="movecat">移动分类至</option>
				</select>
				<button class="button action hermit-selector-button">应用</button>
			</div>
			<div class="tablenav-pages">
			</div>
		</div>
		<table class="wp-list-table widefat fixed striped posts">
			<colgroup>
				<col width="35"/>
                <col width="120"/>
				<col width="120"/>
				<col width="120"/>
				<col width="120"/>
				<col width="40%"/>
                <col width="120"/>
				<col width="120"/>
			</colgroup>
			<thead>
				<tr>
					<td class="manage-column column-cb check-column">
						<label class="screen-reader-text" for="cb-select-all">全选</label>
						<input id="cb-select-all" type="checkbox">
					</td>
                    <th scope="col" class="manage-column column-cover">封面</th>
					<th scope="col" class="manage-column column-title">歌曲名称</th>
					<th scope="col" class="manage-column column-author">作者</th>
					<th scope="col" class="manage-column column-categories">分类</th>
					<th scope="col" class="manage-column column-url">地址</th>
                    <th scope="col" class="manage-column column-lrc">歌词</th>
					<th scope="col" class="manage-column column-action">操作</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
			<tfoot>
				<tr>
					<td class="manage-column column-cb check-column">
						<label class="screen-reader-text" for="cb-select-all-1">全选</label>
						<input id="cb-select-all" type="checkbox">
					</td>
                    <th scope="col" class="manage-column column-cover">封面</th>
					<th scope="col" class="manage-column column-title">歌曲名称</th>
					<th scope="col" class="manage-column column-author">作者</th>
					<th scope="col" class="manage-column column-categories">分类</th>
					<th scope="col" class="manage-column column-url">地址</th>
                    <th scope="col" class="manage-column column-lrc">歌词</th>
					<th scope="col" class="manage-column column-action">操作</th>
				</tr>
			</tfoot>
		</table>
		<div class="tablenav">
			<div class="alignleft actions bulkactions">
				<label class="screen-reader-text">选择批量操作</label>
				<select name="action" class="hermit-action-selector">
					<option value="no">批量操作</option>
					<option value="trash">删除</option>
                    <option value="movecat">移动分类至</option>
				</select>
				<button class="button action hermit-selector-button">应用</button>
			</div>
			<div class="tablenav-pages">
			</div>
		</div>
	</div>

	<!-- 表单模板 -->
	<script id="hermit-form-template" type="text/x-handlebars-template">
		<table class="form-table">
			<tbody>
			<tr>
				<td valign="top"><strong>歌曲名称</strong></td>
				<td valign="top">
					<input type="text" id="hermit-form-song_name" name="song_name" value="{{song_name}}"/>
				</td>
			</tr>
			<tr>
				<td valign="top"><strong>作者</strong></td>
				<td valign="top">
					<input type="text" id="hermit-form-song_author" name="song_author" value="{{song_author}}"/>
				</td>
			</tr>
            <tr>
                <td valign="top"><strong>分类</strong></td>
                <td valign="top">
                    <select id="hermit-form-song_cat" name="song_cat">
                        {{#catOption catList song_cat}}{{/catOption}}
                    </select>
                </td>
            </tr>
			<tr>
				<td valign="top"><strong>歌曲地址</strong></td>
				<td valign="top">
					<textarea name="song_url" rows="3" id="hermit-form-song_url" class="large-text code">{{song_url}}</textarea><br />
					<a href="javascript:;" id="hermit-form-song_url-upload" >上传或添加音乐</a> （本地音乐需要注意盗链）
				</td>
			</tr>
            <tr>
                <td valign="top"><strong>封面地址</strong></td>
                <td valign="top">
                    <textarea name="song_url" rows="3" id="hermit-form-song_cover" class="large-text code">{{song_cover}}</textarea><br />
                    <a href="javascript:;" id="hermit-form-song_cover-upload" >上传或添加封面图片</a> （本地图片需要注意盗链）
                </td>
            </tr>
            <tr>
                <td valign="top"><strong>歌词</strong></td>
                <td valign="top">
                    <textarea name="song_url" rows="10" id="hermit-form-song_lrc" class="large-text code">{{song_lrc}}</textarea><br />
                </td>
            </tr>
			</tbody>
		</table>
	</script>

	<!-- 菜单模板 -->
	<script id="hermit-nav-template" type="text/x-handlebars-template">
		{{#catNav catList count}}{{/catNav}}
		| <a href="javascript:;" class="hermit-manage-nav">* 管理分类 *</a>
	</script>

	<!-- 翻页部分 -->
	<script id="hermit-navigation-template" type="text/x-handlebars-template">
		<span class="displaying-num">{{count}} 首歌曲</span>
	</script>

	<!-- 表格部分 -->
	<script id="hermit-table-template" type="text/x-handlebars-template">
		{{#data}}
			<tr>
				<th class="check-column">
					<label class="screen-reader-text" for="cb-select-th">选择</label>
					<input class="cb-select-th" type="checkbox" value="{{id}}">
				</th>
                <td>{{#catCover song_cover song_name}}{{/catCover}}</td>
				<td>{{song_name}}</td>
				<td>{{song_author}}</td>
				<td>{{#catName song_cat}}{{/catName}}</td>
				<td>{{song_url}}</td>
                <td>{{#catLrc @index}}{{/catLrc}}</td>
				<td><a href="javascript:;" class="hermit-edit" data-index="{{@index}}">编辑</a> | <a href="javascript:;" class="hermit-delete" data-id="{{id}}">删除</a></td>
			</tr>
		{{/data}}
	</script>


    <!-- 歌词模板 -->
    <script id="hermit-lrc-template" type="text/x-handlebars-template">
        <div>
            <!-- 不对html转码 -->
            {{{ song_lrc_html }}}
        </div>
	</script>

	<!-- 批量移动部分 -->
	<script id="hermit-move-cat-template" type="text/x-handlebars-template">
    	<table class="form-table">
        	<tbody>
            	<td valign="top"><strong>分类</strong></td>
            	<td valign="top">
                	<select id="hermit-move-song_cat" name="song_cat">
                    	{{#catOption catList song_cat}}{{/catOption}}
                	</select>
            	</td>
            </tbody>
        </table>
    </script>

    <!-- 分类管理部分 -->
    <script id="hermit-manage-cat-template" type="text/x-handlebars-template">
        <div class="hermit-cat-list-table">
            <a href="javascript:;" class="hermit-new-nav" style="font-size: 14px">+ 添加分类</a>
            <table class="wp-cat-list-table widefat fixed striped posts">
                <colgroup>
                    <col width="50%"/>
                    <col width="50%"/>
                </colgroup>
                <thead>
                <tr>
                    <th scope="col" class="manage-column column-cat-title">分类名称</th>
                    <th scope="col" class="manage-column column-cat-action">操作</th>
                </tr>
                </thead>
                <tbody>
                    {{#catList}}
                    <tr>
                        <td>{{title}}</td>
                        <td>{{#catAction id}}{{/catAction}}</td>
                    </tr>
                    {{/catList}}
                </tbody>
            </table>
        </div>

    </script>

	<script>
		var hermit = {
			catList: <?php echo json_encode($catList);?>,
			count: <?php echo $count;?>,
			prePage: <?php echo $prePage;?>,
			maxPage: <?php echo $maxPage;?>,
			adminUrl: '<?php echo HERMIT_ADMIN_URL. "admin-ajax.php?action=hermit_source";?>',
			data: <?php echo json_encode($this->music_list(1, $catid));?>,
			currentCatId: <?php echo $catid ? $catid : 0;?>
		};
	</script>
</div>