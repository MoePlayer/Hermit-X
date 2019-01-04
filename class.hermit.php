<?php

class hermit
{
    private $_settings;
    protected static $playerID = 0;

    public function __construct()
    {
        /**
         ** 缓存插件设置
         */
        $this->_settings = get_option('hermit_setting');

        /**
         ** 事件绑定
         **/
        add_action('admin_menu', array(
            $this,
            'menu',
        ));
        add_shortcode('hermit', array(
            $this,
            'shortcode',
        ));
        add_action('admin_init', array(
            $this,
            'page_init',
        ));
        add_action('wp_enqueue_scripts', array(
            $this,
            'hermit_scripts',
        ));
        add_filter('plugin_action_links', array(
            $this,
            'plugin_action_link',
        ), 10, 4);
        add_action('wp_ajax_nopriv_hermit', array(
            $this,
            'hermit_callback',
        ));
        add_action('wp_ajax_hermit', array(
            $this,
            'hermit_callback',
        ));
        add_action('in_admin_footer', array(
            $this,
            'music_footer',
        ));
        add_action('wp_ajax_hermit_source', array(
            $this,
            'hermit_source_callback',
        ));
        add_action('wp_footer', array(
            $this,
            'aplayer_init',
        ));
        add_action('admin_enqueue_scripts', array(
            $this,
            'cookies_pointer',
        ));
        add_action('wp_ajax_hermit_ignore_cookies_pointer', array(
            $this,
            'ignore_cookies_pointer',
        ));
    }

    /**
     * 载入所需要的CSS和js文件
     */
    public function hermit_scripts()
    {
        $strategy = $this->settings('strategy');
        $globalPlayer = $this->settings('globalPlayer');

        if ($strategy == 1 && $globalPlayer == 0) {
            global $post, $posts;
            foreach ($posts as $post) {
                if (has_shortcode($post->post_content, 'hermit')) {
                    $this->_load_scripts();
                    break;
                }
            }
        } else {
            $this->_load_scripts();
        }
    }

    /**
     * 加载资源
     */
    private function _load_scripts()
    {
        $this->_css('APlayer.min');
        $this->_js('APlayer.min', $this->settings('jsplace'));
        if (!$this->settings('debug')) {
            $this->_js('hermit-load.min', 1);
        } else {
            $this->_js('hermit-load', 1);
        }
    }

    /**
     * 添加文章短代码
     */
    public function shortcode($atts, $content = null)
    {
        if (empty($atts["theme"])) {
            $color = $this->settings('color');
        } else {
            $color = $atts["theme"];
        }
        switch ($color) {
            case 'default':
                $color = "#5895be";
                break;
            case 'red':
                $color = "#dd4b39";
                break;
            case 'blue':
                $color = "#5cb85c";
                break;
            case 'yellow':
                $color = "#f0ad4e";
                break;
            case 'pink':
                $color = "#f489ad";
                break;
            case 'purple':
                $color = "#da70d6";
                break;
            case 'black':
                $color = "#aaaaaa";
                break;
            case 'customize':
                $color = $this->settings('color_customize');
                break;
            default:
                break;
        }
        $atts["theme"] = $color;
        $atts["songs"] = $content;
        if ($this->settings('listFolded') == 1) {
            $atts["listfolded"] = 'true';
        }

        $atts["mode"] = strtolower($atts["mode"]);

        switch ($atts["mode"]) {
            case 'random':
                $atts["loop"] = 'all';
                $atts["order"] = 'random';
                break;
            case 'order':
                $atts["loop"] = 'none';
                $atts["order"] = 'list';
                break;
            case 'single':
                $atts["loop"] = 'one';
                $atts["order"] = 'list';
                break;
            default:
                $atts["loop"] = 'all';
                $atts["order"] = 'list';
                break;
        }

        unset($atts["mode"]);

        $atts["_nonce"] = $this->settings('low_security') ? md5(NONCE_KEY . $content . NONCE_KEY) : wp_create_nonce($content);

        $playlist_max_height = $this->settings('playlist_max_height');
        if ($playlist_max_height != 0 && empty($atts["listmaxheight"])) {
            $atts["listmaxheight"] = $playlist_max_height . "px";
        }

        $keys = array_keys($atts);
        $apatts = "";
        foreach ($keys as $value) {
            if ($value == "auto") {
                $apatts = $apatts . 'data-autoplay="' . (($atts[$value] == 1) ? "true" : "false") . '" ';
                continue;
            }

            $apatts = $apatts . 'data-' . $value . '="' . $atts[$value] . '" ';
        }

        return '<!--Hermit X v' . HERMIT_VERSION . ' start--><div id="aplayer' . ++self::$playerID . '" class="aplayer" ' . $apatts . '></div><!--Hermit X end-->';
    }

    /**
     * 添加写文章按钮
     */
    public function custom_button($context)
    {
        $context .= "<a id='hermit-create' class='button' href='javascript:;' title='添加音乐'><img src='" . HERMIT_URL . "/assets/images/logo@2x.png' width='16' height='16' /> 添加音乐</a>";

        return $context;
    }

    public function nonce_verify()
    {
        if (!isset($_GET['musicset'])) {
            if (!$this->settings('low_security')) {
                $result = wp_verify_nonce($_GET['_nonce'], $_GET['scope'] . '#:' . $_GET['id']);
            } else {
                $result = md5(NONCE_KEY . $_GET['scope'] . '#:' . $_GET['id'] . NONCE_KEY) === $_GET['_nonce'];
            }
        } else {
            if (!$this->settings('low_security')) {
                $result = wp_verify_nonce($_GET['_nonce'], $_GET['musicset']);
            } else {
                $result = md5(NONCE_KEY . $_GET['musicset'] . NONCE_KEY) === $_GET['_nonce'];
            }
        }

        if (!$result) {
            http_response_code(401);
            header('Content-type: application/json;charset=UTF-8');
            $result = array(
                'status' => 401,
                'msg' => $result,
            );
            die(json_encode($result));
        }
        return true;
    }
    /**
     * JSON 音乐数据
     */
    public function hermit_callback()
    {
        if (!empty($_SERVER["HTTP_REFERER"])) {
            $referer = parse_url($_SERVER["HTTP_REFERER"]);
            $host = strtolower($referer['host']);
        }
        if (empty($_SERVER["HTTP_REFERER"]) || $host === parse_url(home_url())['host']) {
            if (!isset($_GET['musicset'])) {
                $this->hermit_route($_GET["scope"], $_GET["id"]);
            } else {
                $this->nonce_verify();
                $result = array(
                    'status' => 200,
                    'msg' => array( 'songs' => [], ),
                );
                $musicSet = explode(";", $_GET["musicset"]);
                foreach ($musicSet as $key => $music) {
                    $musicInfo = explode("#:", $music);
                    $currentResult = $this->hermit_route($musicInfo[0], $musicInfo[1])["msg"]["songs"];
                    if (count($currentResult) < 1) {
                        continue;
                    }
                    $result["msg"]["songs"] = array_merge($result["msg"]["songs"], $currentResult);
                }
            }
        } else {
            $result = array(
                'status' => 401,
                'msg' => null,
            );
        }

        //输出 JSON
        header('Content-type: application/json;charset=UTF-8');
        exit(json_encode($result));
    }

    private function hermit_route($scope, $id)
    {
        global $HMTJSON;
        switch ($scope) {
            //本地音乐部分
            case 'remote':
                $this->nonce_verify();
                $result = array(
                    'status' => 200,
                    'msg' => $this->music_remote($id),
                );
                break;
            case 'remote_lyric':
                echo $this->remote_lrc($id);
                exit;

            //默认路由
            default:
                $re = '/^(?<site>(netease|xiami|tencent|kugou|baidu)?)_?(?<scope>songs|songlist|album|playlist|collect|artist|song_url|lyric|id_parse)$/i';
                preg_match($re, $scope, $matches);
                if (!empty($matches['scope'])) {
                    $scope = $matches['scope'];
                    if (empty($matches['site'])) {
                        $site = 'xiami';
                    } else {
                        $site = $matches['site'];
                    }
                    if ($scope === 'songs') {
                        $scope = 'songlist';
                    } elseif ($scope === 'collect') {
                        $scope = 'playlist';
                    }
                    
                    if (method_exists($HMTJSON, $scope)) {
                        // if ($scope === 'pic_url') {
                        //     $this->nonce_verify();
                        //     $result = array(
                        //         'status' => 200,
                        //         'msg' => $HMTJSON->$scope($site, $id, $_GET['picid'])
                        //     );
                        //}
                        if ($scope === 'id_parse') {
                            if (array_intersect($this->settings('roles'), wp_get_current_user()->roles)){
                                $result = array(
                                    'status' => 200,
                                    'msg' => $HMTJSON->$scope($site, explode(',', $_GET['src'])),
                                );
                            } else {
                                $result = array(
                                    'status' => 401,
                                    'msg' => false,
                                );
                            }
                        } elseif ($scope === 'lyric') {
                            $this->nonce_verify();
                            echo $HMTJSON->$scope($site, $_GET['id']);
                            exit;
                        } else {
                            if (!isset($_GET['musicset'])){
                                $this->nonce_verify();
                            }

                            $result = array(
                                'status' => 200,
                                'msg' => $HMTJSON->$scope($site, $id),
                            );
                        }
                    } else {
                        $result = array(
                            'status' => 400,
                            'msg' => null,
                        );
                    }
                } else {
                    $result = array(
                        'status' => 400,
                        'msg' => null,
                    );
                }
        }

        return $result;
    }

    /**
     * 输出json数据
     */
    public function hermit_source_callback()
    {
        $type = $_REQUEST['type'];

        switch ($type) {
            case 'new':
                $result = $this->music_new();
                $this->success_response($result);
                break;

            case 'delete':
                $this->music_delete();
                $data = $this->music_catList();
                $this->success_response($data);
                break;

            case 'move':
                $this->music_cat_move();
                $this->success_response(array());
                break;

            case 'update':
                $result = $this->music_update();
                $this->success_response($result);
                break;

            case 'list':
                $paged = intval($this->get('paged'));
                $catid = $this->get('catid');
                $prePage = $this->settings('prePage');

                $catid = $catid ? $catid : null;

                $data    = $this->music_list($paged, $catid);
                $count   = intval($this->music_count()); // 歌曲总数
                $catList = $this->music_catList();
                if ($catid == null) {
                    $cat_count = $count;
                } else {
                    $cat_count = intval($this->music_count($catid));
                }
                $maxPage = ceil($cat_count / $prePage);

                $result = compact('data', 'paged', 'maxPage', 'count', 'catList');
                $this->success_response($result);
                break;

            case 'catlist':
                $data = $this->music_catList();
                $this->success_response($data);
                break;

            case 'catnew':
                $title = $this->post('title');

                if ($this->music_cat_existed($title)) {
                    $data = "分类名称已存在";
                    $this->error_response(500, $data);
                } else {
                    $this->music_cat_new($title);
                    $data = $this->music_catList();
                    $this->success_response($data);
                }
                break;

            case 'catupd':
                $result = $this->cat_updata();
                $this->success_response($result);
                break;

            case 'catdel':
                $this->cat_delete();
                $data = $this->music_catList();
                $this->success_response($data);
                break;

            default:
                $data = "不存在的请求.";
                $this->error_response(400, $data);
        }
    }

    /**
     * 添加写文章所需要的js和css
     */
    public function page_init()
    {
        global $pagenow;

        $allowed_roles = $this->settings('roles');
        $user = wp_get_current_user();

        if (array_intersect($allowed_roles, $user->roles)) {
            if ($pagenow == "post-new.php" || $pagenow == "post.php") {
                add_action('media_buttons_context', array(
                    $this,
                    'custom_button',
                ));

                $this->_css('hermit-post');
                $this->_libjs('handlebars');
                $this->_js('hermit-post');

                $prePage = $this->settings('prePage');
                $count = $this->music_count();
                $maxPage = ceil($count / $prePage);
                $roles = $user->roles;

                wp_localize_script('hermit-post', 'hermit', array(
                    "ajax_url" => admin_url() . "admin-ajax.php",
                    "max_page" => $maxPage,
                    "roles" => $roles,
                    "plugin_url" => HERMIT_URL,
                ));
            }

            if ($pagenow == "admin.php" && $_GET['page'] == 'hermit') {
                //上传音乐支持
                wp_enqueue_media();
                $this->_css('hermit-library');
                $this->_libjs('watch,handlebars,jquery.mxloader,jquery.mxpage,jquery.mxlayer');
                $this->_js('hermit-library');
            }
        }
    }

    /**
     * 显示后台菜单
     */
    public function menu()
    {
        add_menu_page('Hermit X 播放器', 'Hermit X 播放器', 'manage_options', 'hermit', array(
            $this,
            'library',
        ), HERMIT_URL . '/assets/images/logo.png');
        add_submenu_page('hermit', '音乐库', '音乐库', 'manage_options', 'hermit', array(
            $this,
            'library',
        ));
        add_submenu_page('hermit', '设置', '设置', 'manage_options', 'hermit-setting', array(
            $this,
            'setting',
        ));

        add_action('admin_init', array(
            $this,
            'hermit_setting',
        ));
    }

    /**
     * 音乐库 library
     */
    public function library()
    {
        @require_once 'include/library.php';
    }

    /**
     * 设置
     */
    public function setting()
    {
        @require_once 'include/setting.php';
    }

    /**
     * 注册设置数组
     */
    public function hermit_setting()
    {
        register_setting('hermit_setting_group', 'hermit_setting');
    }

    /**
     * 添加<音乐库>按钮
     */
    public function plugin_action_link($actions, $plugin_file, $plugin_data)
    {
        if (strpos($plugin_file, 'hermit') !== false && is_plugin_active($plugin_file)) {
            $_actions = array(
                'option' => '<a href="' . HERMIT_ADMIN_URL . 'admin.php?page=hermit">音乐库</a>',
            );
            $actions = array_merge($_actions, $actions);
        }

        return $actions;
    }

    /**
     * Handlebars 模板
     */
    public function music_footer()
    {
        global $pagenow;
        if ($pagenow == "post-new.php" || $pagenow == "post.php") {
            @require_once 'include/template.php';
        }
    }

    /**
     * settings - 插件设置
     *
     * @param $key
     *
     * @return bool
     */
    public function settings($key)
    {
        $defaults = array(
            'strategy' => 1,
            'color' => 'default',
            'playlist_max_height' => '349',
            'quality' => '320',
            'jsplace' => 0,
            'prePage' => 20,
            'remainTime' => 10,
            'roles' => array(
                'administrator',
            ),
            'debug' => false,
            'color_customize' => '#5895be',
            'netease_cookies' => '',
            'low_security' => 0,
            'globalPlayer' => 0,
            'listFolded' => 0,
            'proxy' => '',
            'assetsPublicCDN' => 1,
        );

        $settings = $this->_settings;
        $settings = wp_parse_args($settings, $defaults);

        return $settings[$key];
    }

    public function cookies_pointer()
    {
        if (in_array('hermit-cookies-setting', $this->get_current_dismissed())) {
            return;
        }

        if (!current_user_can('manage_options')) {
            return;
        }

        wp_enqueue_style('wp-pointer');
        wp_enqueue_script('wp-pointer');

        $filename = HERMIT_PATH . '/include/cookies-pointer.php';
        $callback = require $filename;

        $ignore = add_query_arg(array(
            'action' => 'hermit_ignore_cookies_pointer',
            '_wpnonce' => wp_create_nonce('hermit-ignore-cookies-pointer'),
        ), admin_url('admin-ajax.php'));

        ob_start();
        call_user_func($callback, $ignore);

        $code = ob_get_clean();
        wp_add_inline_script('wp-pointer', $code);
    }

    public function ignore_cookies_pointer()
    {
        check_ajax_referer('hermit-ignore-cookies-pointer');
        $dismissed = $this->get_current_dismissed();

        if (in_array('hermit-cookies-setting', $dismissed)) {
            return;
        }

        if (!current_user_can('manage_options')) {
            return;
        }

        $user_id = get_current_user_id();
        $dismissed[] = 'hermit-cookies-setting';

        if (update_user_meta($user_id, 'dismissed_wp_pointers', implode(',', $dismissed))) {
            wp_die(1);
        }
    }

    private function get_current_dismissed()
    {
        $dismissed = get_user_meta(get_current_user_id(), 'dismissed_wp_pointers', true);
        return array_filter(explode(',', (string) $dismissed));
    }

    private function music_remote($ids)
    {
        global $wpdb, $hermit_table_name, $HMTJSON;

        $key = "/remote/song/$ids";
        $cache = $HMTJSON->get_cache($key);
        if(!empty($cache)){
            return $cache;
        }

        $result = array();
        $data   = $wpdb->get_results("SELECT id,song_name,song_author,song_url,song_cover FROM {$hermit_table_name} WHERE id in ({$ids}) order by field(id, {$ids})");

        foreach ($data as $key => $value) {
            $result['songs'][] = array(
                "id" => $value->id,
                "title" => $value->song_name,
                "author" => $value->song_author,
                "url" => $value->song_url,
                "pic" => $value->song_cover,
                "lrc" => admin_url() . "admin-ajax.php" . "?action=hermit&scope=remote_lyric&id=" . $value->id
            );
        }

        $HMTJSON->set_cache($key, $result, 128);
        return $result;
    }

    /**
     * 本地歌词
     */
    private function remote_lrc($id)
    {
        global $HMTJSON;

        $key = "/remote/lyric/$id";
        $cache = $HMTJSON->get_cache($key);
        if(!empty($cache)){
            return $cache;
        }
        
        global $wpdb, $hermit_table_name;

        $data   = $wpdb->get_results($wpdb->prepare("SELECT song_lrc FROM `$hermit_table_name` WHERE id = %d", $id));
        if (count($data) < 0) $result = "";
        else $result = $data[0]->song_lrc;

        $HMTJSON->set_cache($key, $result, 128);
        return $result;
    }

    private function localMusicImage()
    {
        //咕咕咕
    }

    /**
     * 新增本地音乐
     */
    private function music_new()
    {
        global $wpdb, $hermit_table_name;

        $song_name = stripslashes($this->post('song_name'));
        $song_author = stripslashes($this->post('song_author'));
        $song_url    = esc_attr(esc_html($this->post('song_url')));
        $song_cover  = esc_attr(esc_html($this->post('song_cover')));
        $song_lrc    = stripslashes($this->post('song_lrc'));
        $song_cat    = $this->post('song_cat');
        $created     = date('Y-m-d H:i:s');

        $wpdb->insert($hermit_table_name, compact('song_name', 'song_author', 'song_url', 'song_cover', 'song_lrc', 'song_cat', 'created'), array(
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%d',
            '%s',
        ));
        $id = $wpdb->insert_id;

        $song_cat_name = $this->music_cat($song_cat);

        return compact('id', 'song_name', 'song_author', 'song_cat', 'song_cat_name', 'song_url', 'song_cover', 'song_lrc');
    }

    /**
     * 升级本地音乐信息
     */
    private function music_update()
    {
        global $wpdb, $hermit_table_name;

        $id = $this->post('id');
        $song_name = stripslashes($this->post('song_name'));
        $song_author = stripslashes($this->post('song_author'));
        $song_url    = esc_attr(esc_html($this->post('song_url')));
        $song_cover  = esc_attr(esc_html($this->post('song_cover')));
        $song_lrc    = stripslashes($this->post('song_lrc'));
        $song_cat    = $this->post('song_cat');

        $wpdb->update($hermit_table_name, compact('song_name', 'song_author', 'song_cat', 'song_url', 'song_cover', 'song_lrc'), array(
            'id' => $id
        ), array(
            '%s',
            '%s',
            '%d',
            '%s',
            '%s',
            '%s'
        ), array(
            '%d',
        ));

        $song_cat_name = $this->music_cat($song_cat);

        delete_transient("/remote/lyric/$id");
        delete_transient("/remote/song/$id");
        return compact('id', 'song_name', 'song_author', 'song_cat', 'song_cat_name', 'song_url', 'song_cover', 'song_lrc');
    }

    /**
     * 删除本地音乐
     */
    private function music_delete()
    {
        global $wpdb, $hermit_table_name;

        $ids = $this->post('ids');

        $wpdb->query("DELETE FROM {$hermit_table_name} WHERE id IN ({$ids})");
    }

    /**
     * 移动分类
     */
    private function music_cat_move()
    {
        global $wpdb, $hermit_table_name;

        $ids = $this->post('ids');
        $catid = $this->post('catid');

        $wpdb->query("UPDATE {$hermit_table_name} SET song_cat = {$catid} WHERE id IN ({$ids})");
    }

    /**
     * 本地音乐列表
     *
     * @param      $paged
     * @param null $catid
     *
     * @return mixed
     */
    private function music_list($paged, $catid = null)
    {
        global $wpdb, $hermit_table_name;

        $limit = $this->settings('prePage');
        $offset = ($paged - 1) * $limit;

        if ($catid) {
            $query_str = "SELECT id,song_name,song_author,song_cat,song_url,song_cover,song_lrc,created FROM {$hermit_table_name} WHERE `song_cat` = '{$catid}' ORDER BY `created` DESC LIMIT {$limit} OFFSET {$offset}";
        } else {
            $query_str = "SELECT id,song_name,song_author,song_cat,song_url,song_cover,song_lrc,created FROM {$hermit_table_name} ORDER BY `created` DESC LIMIT {$limit} OFFSET {$offset}";
        }

        $result = $wpdb->get_results($query_str);

        // 将lrc转成html格式
        for ($i = 0; $i < count($result); $i++) {
            if ($result[$i]->song_lrc == "") $result[$i]->song_lrc_html = "没有歌词<br />";
            else $result[$i]->song_lrc_html = str_replace("\n", "<br />", $result[$i]->song_lrc);
        }

        return $result;
    }

    /**
     * 本地音乐分类列表
     *
     * @return mixed
     */
    private function music_catList()
    {
        global $wpdb, $hermit_cat_name;

        $query_str = "SELECT id,title FROM {$hermit_cat_name}";
        $result = $wpdb->get_results($query_str);

        if (!empty($result)) {
            foreach ($result as $key => $val) {
                $result[$key]->count = intval($this->music_count($val->id));
            }
        }

        return $result;
    }

    /**
     * 本地分类名称
     *
     * @param $cat_id
     *
     * @return mixed
     */
    private function music_cat($cat_id)
    {
        global $wpdb, $hermit_cat_name;

        $cat_name = $wpdb->get_var("SELECT title FROM {$hermit_cat_name} WHERE id = '{$cat_id}'");

        return $cat_name;
    }

    /**
     * 判断分类是否存在
     *
     * @param $title
     *
     * @return mixed
     */
    private function music_cat_existed($title)
    {
        global $wpdb, $hermit_cat_name;

        $id = $wpdb->get_var("SELECT id FROM {$hermit_cat_name} WHERE title = '{$title}'");

        return $id;
    }

    /**
     * 新建分类
     */
    private function music_cat_new($title)
    {
        global $wpdb, $hermit_cat_name;

        $title = stripslashes($title);

        $wpdb->insert($hermit_cat_name, compact('title'), array(
            '%s',
        ));

        $new_cat_id = $wpdb->insert_id;

        return array(
            'id' => $new_cat_id,
            'title' => $title,
            'count' => intval($this->music_count($new_cat_id)),
        );
    }

    /**
     * 删除本地分类
     *
     * @return boolean
     */
    private function cat_delete()
    {
        global $wpdb, $hermit_cat_name, $hermit_table_name;

        $cat_id = $this->post('id');
        if($cat_id == 1)return false;
        $result = $wpdb->get_results($wpdb->prepare("SELECT id FROM `$hermit_table_name` WHERE song_cat = %d", $cat_id));
        for ($i = 0; $i < count($result); $i++) {
            $wpdb->update($hermit_table_name, array(
                'song_cat' => 1
            ), array(
                'id' => $result[$i]->id
            ), array(
                '%d',
            ), array(
                '%d'
            ));
        }
        $wpdb->delete($hermit_cat_name, array(
            'id' => $cat_id
        ));

        return true;
    }

    /**
     * 升级本地分类
     */
    private function cat_updata()
    {
        global $wpdb, $hermit_cat_name;

        $id      = $this->post('id');
        $title   = stripslashes($this->post('title'));

        $wpdb->update($hermit_cat_name, compact('title'), array(
            'id' => $id
        ), array(
            '%s'
        ), array(
            '%d'
        ));

        return compact('id', 'title');
    }

    /**
     * 本地音乐数量
     * 音乐库分类
     *
     * @param null $catid
     *
     * @return mixed
     */
    private function music_count($catid = null)
    {
        global $wpdb, $hermit_table_name;

        if ($catid) {
            $query_str = "SELECT COUNT(id) AS count FROM {$hermit_table_name} WHERE song_cat = '{$catid}'";
        } else {
            $query_str = "SELECT COUNT(id) AS count FROM {$hermit_table_name}";
        }

        $music_count = $wpdb->get_var($query_str);

        return $music_count;
    }

    private function _css($css_str)
    {
        $css_arr = explode(',', $css_str);

        if ($this->settings("assetsPublicCDN") && !is_admin()) {
            $hermitAssetsUrl = 'https://cdn.jsdelivr.net/gh/moeplayer/hermit-x@' . HERMIT_VERSION;
        } else {
            $hermitAssetsUrl = HERMIT_URL;
        }

        foreach ($css_arr as $key => $val) {
            $css_path = sprintf('%s/assets/css/%s.css', $hermitAssetsUrl, $val);
            wp_enqueue_style($val, $css_path, false, HERMIT_VERSION);
        }
    }

    private function _libjs($js_str, $js_place = false)
    {
        $js_arr = explode(',', $js_str);

        if ($this->settings("assetsPublicCDN") && !is_admin()) {
            $hermitAssetsUrl = 'https://cdn.jsdelivr.net/gh/moeplayer/hermit-x@' . HERMIT_VERSION;
        } else {
            $hermitAssetsUrl = HERMIT_URL;
        }

        foreach ($js_arr as $key => $val) {
            $js_path = sprintf('%s/assets/js/lib/%s.js', $hermitAssetsUrl, $val);
            wp_enqueue_script($val, $js_path, false, HERMIT_VERSION, $js_place);
        }
    }

    private function _js($js_str, $js_place = false)
    {
        $js_arr = explode(',', $js_str);

        if ($this->settings("assetsPublicCDN") && !is_admin()) {
            $hermitAssetsUrl = 'https://cdn.jsdelivr.net/gh/moeplayer/hermit-x@' . HERMIT_VERSION;
        } else {
            $hermitAssetsUrl = HERMIT_URL;
        }

        foreach ($js_arr as $key => $val) {
            $js_path = sprintf('%s/assets/js/%s.js', $hermitAssetsUrl, $val);
            wp_enqueue_script($val, $js_path, false, HERMIT_VERSION, $js_place);
        }
    }

    private function post($key)
    {
        $key = $_POST[$key];

        return $key;
    }

    private function get($key)
    {
        $key = esc_attr(esc_html($_GET[$key]));

        return $key;
    }

    private function error_response($code, $error_message)
    {
        if ($code == 404) {
            header('HTTP/1.1 404 Not Found');
        } elseif ($code == 301) {
            header('HTTP/1.1 301 Moved Permanently');
        } else {
            header('HTTP/1.0 500 Internal Server Error');
        }
        header('Content-Type: text/plain;charset=UTF-8');
        echo $error_message;
        exit;
    }

    private function success_response($result)
    {
        header('HTTP/1.1 200 OK');
        header('Content-type: application/json;charset=UTF-8');
        echo json_encode($result);
        exit;
    }

    public function aplayer_init()
    {
        if (!$this->settings('debug')) {
            wp_localize_script('hermit-load.min', 'HermitX', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'version' => HERMIT_VERSION,
            ));
        } else {
            wp_localize_script('hermit-load', 'HermitX', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'version' => HERMIT_VERSION,
            ));
        }
    }
}
