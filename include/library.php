<?php
$catList = $this->music_catList();
$prePage = $this->settings('prePage');

/*CYP的代码*/
$catid = isset($_GET['catid']) && $_GET['catid'] ? $_GET['catid'] : null;
$keyword = isset($_GET['keyword']) && $_GET['keyword'] ? $_GET['keyword'] : null;
$count    = $this->music_count($catid,$keyword);
$countz = $this->music_count();
$maxPage = ceil($count / $prePage);
/*CYP的代码*/
?>
    <div class="wrap">
        <h2>Hermit X 音乐库 <a href="javascript:;" class="add-new-h2 add-one">新建音乐</a><a href="javascript:;" class="add-new-h2 add-list">批量下载音乐</a></h2>

        <div class="hermit-list-table">

            <ul class="subsubsub"></ul>
            <!--CYP的代码-->
            <p class="search-box">
                <label class="screen-reader-text" for="post-search-input">搜索歌名,歌手,网易云音乐id:</label>
                <input type="search" id="music-search-input" name="s" value="<?php if(isset($_GET['keyword'])){
    echo $_GET['keyword'];
}?>">
                <input type="submit" id="search-submit" class="button" value="搜索音乐"></p>
            <!--CYP的代码-->
            <div class="tablenav top">
                <div class="alignleft actions bulkactions">
                    <label class="screen-reader-text">选择批量操作</label>
                    <select name="action" class="hermit-action-selector">
					<option value="no">批量操作</option>
					<option value="trash">删除</option>
				</select>
                    <button class="button action hermit-delete-all">应用</button>
                </div>
                <div class="tablenav-pages">
                </div>
            </div>

            <table class="wp-list-table widefat fixed striped posts">
                <colgroup>
                    <!--CYP的代码-->
                    <col width="4%" />
                    <col width="6%" />
                    <col width="10%" />
                    <col width="10%" />
                    <col width="10%" />
                    <col width="10%" />
                    <col width="15%" />
                    <col width="15%" />
                    <col width="15%" />
                    <col width="5%" />
                    <!--CYP的代码-->
                </colgroup>
                <thead>
                    <tr>
                        <td class="manage-column column-cb check-column">
                            <label class="screen-reader-text" for="cb-select-all">全选</label>
                            <input id="cb-select-all" type="checkbox">
                        </td>
                        <!--CYP的代码-->
                        <th scope="col" class="manage-column column-id">歌曲id</th>
                        <th scope="col" class="manage-column column-title">歌曲名称</th>
                        <th scope="col" class="manage-column column-author">作者</th>
                        <th scope="col" class="manage-column column-album">专辑名称</th>
                        <th scope="col" class="manage-column column-categories">分类</th>
                        <th scope="col" class="manage-column column-lrc">歌词</th>
                        <th scope="col" class="manage-column column-cover">封面</th>
                        <th scope="col" class="manage-column column-url">地址</th>
                        <th scope="col" class="manage-column column-action">操作</th>
                        <!--CYP的代码-->
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
                        <!--CYP的代码-->
                        <th scope="col" class="manage-column column-id">歌曲id</th>
                        <th scope="col" class="manage-column column-title">名称</th>
                        <th scope="col" class="manage-column column-author">作者</th>
                        <th scope="col" class="manage-column column-album">专辑</th>
                        <th scope="col" class="manage-column column-categories">分类</th>
                        <th scope="col" class="manage-column column-lrc">歌词</th>
                        <th scope="col" class="manage-column column-cover">封面</th>
                        <th scope="col" class="manage-column column-url">地址</th>
                        <th scope="col" class="manage-column column-action">操作</th>
                        <!--CYP的代码-->
                    </tr>
                </tfoot>
            </table>
            <div class="tablenav">
                <div class="alignleft actions bulkactions">
                    <label class="screen-reader-text">选择批量操作</label>
                    <select name="action" class="hermit-action-selector">
					<option value="no">批量操作</option>
					<option value="trash">删除</option>
				</select>
                    <button class="button action hermit-delete-all">应用</button>
                </div>
                <div class="tablenav-pages">
                </div>
            </div>
        </div>

        <!-- 表单模板 -->
        <!--CYP的代码-->
        <style>
            .hermit-form-netease_s_id{
                width:50%!important;
                display:inline-block!important;
            }
            .downform{
                
            }
            .resultalbum{
                list-style: none!important;
                overflow: auto;
            }
            .resultalbum img{
                width:10%;
                float: left;
                display: block;
                min-width: 60px;
            }
            .resultalbum div{
                width: 61%;
                float: left;
                margin-left: 2%;
                height: 60px;
            }
            .resultalbum div h2,.resultalbum div h3,.resultalbum div h4{
                margin: 0;
            }
            .resultalbum div h2{
                margin-bottom: 0.4em;
                font-size: 1em;
                max-height: 40px;
                overflow: hidden;
                text-overflow: ellipsis;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
            }
            .resultalbum div h3{
                font-size: 0.5em;
                margin-bottom: 0.3em;
                max-height: 20px;
            }
            .resultalbum div h4{
                font-size: 0.5em;
            }
            .resultalbum a{
                width:20%;
                float: left;
                font-size: 0.8em;
            }
            .resultalbum .idtext{
                float: left;
                font-size: 0.8em;
            }
            .resultalbum li{
                float:left;
                width:100%;
            }
        </style>
        <script id="hermit-addlist-form-template" type="text/x-handlebars-template">
            <table class="form-table netease-downlist">
                <tbody>
                    <tr>
                        <td valign="top"><strong>网易云音乐批量收集</strong></td>
                    </tr>
                    <tr>
                        <td valign="top">
                            网易云音乐id<input type="text" name="netease_s_id" class="hermit-form-netease_s_id" /><a href="javascript:;" class="netease-download-button">下载音乐</a>
                        </td>
                    </tr>
                    
                </tbody>
            </table>
            <a href="javascript:;" class="netease-download-add">+新增下载项</a>
            <table class="form-table">
                <tbody>
                    <tr>
                        <td valign="top">
                            网易云音乐专辑<input type="text" name="netease_album_id" class="hermit-form-netease_album_id" /><a href="javascript:;" class="netease-search-album-button">列出专辑<?php ?></a>
                        </td>
                    </tr>
                </tbody>
            </table>
            <ul class="resultalbum">
            
            </ul>
            <ul class="resultlist">
            <input type="hidden" name="done" id="done"/>
            <input type="hidden" name="infook" id="infook"/>
                </ul>
            
            <table class="form-table downform">
                <tbody>
                    <!--CYP的代码-->
                    <tr>
                        <td valign="top"><strong>网易云音乐id</strong></td>
                        <td valign="top">
                            <input type="text" id="hermit-form-netease_id" name="netease_id" value="{{netease_id}}" />
                            <a href="javascript:;" class="netease-confirm-button">填写信息</a>
                            <a href="javascript:;" class="netease-download-one-button">下载歌曲</a>
                            
                        </td>
                    </tr>

                    <tr>
                        <td valign="top"><strong>歌曲名称</strong></td>
                        <td valign="top">
                            <input type="text" id="hermit-form-song_name" name="song_name" value="{{song_name}}" />
                        </td>
                    </tr>
                    <tr>
                        <td valign="top"><strong>作者</strong></td>
                        <td valign="top">
                            <input type="text" id="hermit-form-song_author" name="song_author" value="{{song_author}}" />
                        </td>
                    </tr>
                    <tr>
                        <td valign="top"><strong>专辑名称</strong></td>
                        <td valign="top">
                            <input type="text" id="hermit-form-song_album" name="song_album" value="{{song_album}}" />
                        </td>
                    </tr>

                    <tr>
                        <td valign="top"><strong>歌词</strong></td>
                        <td valign="top">
                            <textarea name="song_lrc" rows="3" id="hermit-form-song_lrc" class="large-text code">{{song_lrc}}</textarea><br />
                            <textarea name="song_lrc_detail" rows="3" id="hermit-form-song_lrc_detail" class="large-text code">{{song_lrc_detail}}</textarea><a href="javascript:;" id="get-song_lrc_detail">获取歌词</a><img class="loadingimg" src="http://<?php echo $_SERVER['SERVER_NAME'];?>/wp-admin/images/spinner.gif" /><br />
                        </td>
                    </tr>
                    <tr>
                        <td valign="top"><strong>封面</strong></td>
                        <td valign="top">
                            <textarea name="cover_url" rows="3" id="hermit-form-cover_url" class="large-text code">{{cover_url}}</textarea><br />
                            <a href="javascript:;" id="hermit-form-cover_url-upload">上传或添加图片</a> （本地图片需要注意盗链）<a href="javascript:;" id="get-cover-url">获取地址</a><img class="loadingimg" src="http://<?php echo $_SERVER['SERVER_NAME'];?>/wp-admin/images/spinner.gif" />
                        </td>
                    </tr>
                    <tr>
                        <td valign="top"><strong>歌曲地址</strong></td>
                        <td valign="top">
                            <textarea name="song_url" rows="3" id="hermit-form-song_url" class="large-text code">{{song_url}}</textarea><br />
                            <a href="javascript:;" id="hermit-form-song_url-upload">上传或添加音乐</a> （本地音乐需要注意盗链）<a href="javascript:;" id="get-songs-url">获取地址</a><a href="javascript:;" id="get-songs-url1">获取地址1</a><img class="loadingimg" src="http://<?php echo $_SERVER['SERVER_NAME'];?>/wp-admin/images/spinner.gif" />
                        </td>

                    </tr>
                    <tr>
                        <td valign="top"><strong>搜索歌曲</strong></td>
                        <td valign="top">
                            <input name="song_name_search" class="song_name_search" type="text" /><br />
                            <div class="list_music"></div>
                            <a href="javascript:;" class="song_search">搜索</a><a href="javascript:;" class="get_search_song_url">获取地址</a>
                        </td>
                    </tr>
                    <!--CYP的代码-->
                    <tr>
                        <td valign="top"><strong>分类</strong></td>
                        <td valign="top">
                            <select id="hermit-form-song_cat" name="song_cat" value="2">
						{{#catOption catList song_cat}}{{/catOption}}
					</select>
                        </td>
                    </tr>
                </tbody>
            </table>
        </script>
        <!--CYP的代码-->
        <script id="hermit-form-template" type="text/x-handlebars-template">
            <table class="form-table">
                <tbody>
                    <!--CYP的代码-->
                    <tr>
                        <td valign="top"><strong>网易云音乐id</strong></td>
                        <td valign="top">
                            <input type="text" id="hermit-form-netease_id" name="netease_id" value="{{netease_id}}" />
                            <a href="javascript:;" class="netease-confirm-button">填写信息</a>
                            <a href="javascript:;" class="netease-download-one-button">下载歌曲</a>
                        </td>
                    </tr>

                    <tr>
                        <td valign="top"><strong>歌曲名称</strong></td>
                        <td valign="top">
                            <input type="text" id="hermit-form-song_name" name="song_name" value="{{song_name}}" />
                        </td>
                    </tr>
                    <tr>
                        <td valign="top"><strong>作者</strong></td>
                        <td valign="top">
                            <input type="text" id="hermit-form-song_author" name="song_author" value="{{song_author}}" />
                        </td>
                    </tr>
                    <tr>
                        <td valign="top"><strong>专辑名称</strong></td>
                        <td valign="top">
                            <input type="text" id="hermit-form-song_album" name="song_album" value="{{song_album}}" />
                        </td>
                    </tr>

                    <tr>
                        <td valign="top"><strong>歌词</strong></td>
                        <td valign="top">
                            <textarea name="song_lrc" rows="3" id="hermit-form-song_lrc" class="large-text code">{{song_lrc}}</textarea><br />
                            <textarea name="song_lrc_detail" rows="3" id="hermit-form-song_lrc_detail" class="large-text code">{{song_lrc_detail}}</textarea><a href="javascript:;" id="get-song_lrc_detail">获取歌词</a><img class="loadingimg" src="http://<?php echo $_SERVER['SERVER_NAME'];?>/wp-admin/images/spinner.gif" /><br />
                        </td>
                    </tr>
                    <tr>
                        <td valign="top"><strong>封面</strong></td>
                        <td valign="top">
                            <textarea name="cover_url" rows="3" id="hermit-form-cover_url" class="large-text code">{{cover_url}}</textarea><br />
                            <a href="javascript:;" id="hermit-form-cover_url-upload">上传或添加图片</a> （本地图片需要注意盗链）<a href="javascript:;" id="get-cover-url">获取地址</a><img class="loadingimg" src="http://<?php echo $_SERVER['SERVER_NAME'];?>/wp-admin/images/spinner.gif" />
                        </td>
                    </tr>
                    <tr>
                        <td valign="top"><strong>歌曲地址</strong></td>
                        <td valign="top">
                            <textarea name="song_url" rows="3" id="hermit-form-song_url" class="large-text code">{{song_url}}</textarea><br />
                            <a href="javascript:;" id="hermit-form-song_url-upload">上传或添加音乐</a> （本地音乐需要注意盗链）<a href="javascript:;" id="get-songs-url">获取地址</a><a href="javascript:;" id="get-songs-url1">获取地址1</a><img class="loadingimg" src="http://<?php echo $_SERVER['SERVER_NAME'];?>/wp-admin/images/spinner.gif" />
                        </td>

                    </tr>
                    <tr>
                        <td valign="top"><strong>搜索歌曲</strong></td>
                        <td valign="top">
                            <input name="song_name_search" class="song_name_search" type="text" /><br />
                            <div class="list_music"></div>
                            <a href="javascript:;" class="song_search">搜索</a><a href="javascript:;" class="get_search_song_url">获取地址</a>
                        </td>
                    </tr>
                    <!--CYP的代码-->
                    <tr>
                        <td valign="top"><strong>分类</strong></td>
                        <td valign="top">
                            <select id="hermit-form-song_cat" name="song_cat">
						{{#catOption catList song_cat}}{{/catOption}}
					</select>
                        </td>
                    </tr>
                </tbody>
            </table>
        </script>

        <!-- 菜单模板 -->
        <script id="hermit-nav-template" type="text/x-handlebars-template">
            {{#catNav catList countz}}{{/catNav}} | <a href="javascript:;" class="hermit-new-nav">+ 新建分类</a>
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
                <!--CYP的代码-->
                <td>{{id}}</td>
                <td>{{song_name}}</td>
                <td>{{song_author}}</td>
                <td>{{song_album}}</td>
                <td>{{#catName song_cat}}{{/catName}}</td>
                <td>{{song_lrc}}</td>
                <td>{{cover_url}}</td>
                <td>{{song_url}}</td>
                <td><a href="javascript:;" class="hermit-edit" data-index="{{@index}}">编辑</a> | <a href="javascript:;" class="hermit-delete" data-id="{{id}}">删除</a></td>
                <!--CYP的代码-->
            </tr>
            {{/data}}
        </script>

        <script>
            /*CYP的代码*/
            var hermit = {
                catList: <?php echo json_encode($catList);?>,
                count: <?php echo $count;?>,
                countz: <?php echo $countz;?>,
                prePage: <?php echo $prePage;?>,
                maxPage: <?php echo $maxPage;?>,
                adminUrl: '<?php echo HERMIT_ADMIN_URL. "admin-ajax.php?action=hermit_source".($catid ? ' & catid = '.$catid : '').$catid ;?>',
                data: <?php if(isset($_GET['keyword'])){
                                //echo $maxPage;
                                echo json_encode($this->music_search_list($_GET['keyword'],1, $catid));
                            }else{
                                //echo $maxPage;
                                echo json_encode($this->music_list(1, $catid));
                            }?>,
                currentCatId: <?php echo $catid ? $catid : 0;?>
            };
            //console.log(<?php echo $maxPage; ?>);
            $(document).on('click', '#search-submit', function() {
                window.location.href = '<?php echo HERMIT_ADMIN_URL."admin.php?page=hermit&keyword=";?>' + $('#music-search-input').val();
            });
            /*CYP的代码*/

        </script>

    </div>
