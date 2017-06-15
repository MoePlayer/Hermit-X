<?php
class HermitJson
{
    private $token;
    private $_settings;

    public function __construct()
    {
        /**
         ** 缓存插件设置
         */
        $this->_settings = get_option('hermit_setting');
        require('include/Meting.php');
    }

    public function song_url($site, $music_id)
    {
        $cacheKey = "/$site/song_url/$music_id";
        $url = $this->get_cache($cacheKey);
        if ($url) {
            Header("X-Hermit-Cached: From Cache");
            Header("Location: " . $url);
            exit;
        }
        $Meting = new \Metowolf\Meting($site);
        $i = ($site !== "netease") ? 1 : -1;
        $cookies = $this->settings('netease_cookies');
        if (!empty($cookies) && $site === "netease") {
            $Meting->cookie($cookies);
        }
        while (substr($url, 0, 10) === 'http://m7' && ($i++ < 2)) {
            $url = json_decode($Meting->format()->url($music_id, $this->settings('quality')), true);
            $url = $url['url'];
            if (empty($url)) {
                //if ($this->settings('within_China')) {
            Header("Location: " . 'https://api.lwl12.com/music/netease/song?id=607441');
                //} else {
                //    Header("Location: " . "https://api.lwl12.com/music/$site/song?id=" . $music_id);
                //}
                exit;
            }
        }
        if ($i > 0 && $site === "netease") {
            Header("X-Hermit-Retrys: $i");
        }

        if ($site === "netease") {
            $url = str_replace('http://m7', 'http://m8', $url);
            $url = str_replace('http://m8', 'https://m8', $url);
        }
        if ($site === "xiami") {
            $url = str_replace('http://', 'https://', $url);
        }

        $this->set_cache($cacheKey, $url, 0.25);
        Header("Location: " . $url);
        exit;
    }

    public function pic_url($site, $id, $pic)
    {
        $cacheKey = "/$site/pic_url/$pic";
        $url = $this->get_cache($cacheKey);
        if ($url) {
            Header("X-Hermit-Cached: From Cache");
            Header("Location: " . $url);
            return 0;
        }
        $Meting = new \Metowolf\Meting($site);

        $pic = json_decode($Meting->pic($pic, 100), true);
        if (empty($pic["url"])) {
            $return = array(
                'code' => 501,
                'msg' => 'Invalid song or Music site server error'
            );
            exit(json_encode($return));
        }
        $this->set_cache($cacheKey, $pic["url"], 168);
        Header("Location: " . $pic["url"]);
        exit;
    }

    public function id_parse($site, $src)
    {
        foreach ($src as $key => $value) {
            $cacheKey = "/$site/idparse/$value";
            $cache    = $this->get_cache($cacheKey);
            if ($cache) {
                $ids[] = $cache;
                continue;
            }

            switch ($site) {
                case 'tencent':
                $request = array(
                    'url'    => $value,
                    'referer'   => 'http://y.qq.com/portal/player.html',
                    'cookie'    => 'qqmusic_uin=12345678; qqmusic_key=12345678; qqmusic_fromtag=30; ts_last=y.qq.com/portal/player.html;',
                    'useragent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.30 Safari/537.36',
                );
                    break;

                case 'xiami':
                $request = array(
                    'url'    => $value,
                    'referer'   => 'http://h.xiami.com/',
                    'cookie'    => 'user_from=2;XMPLAYER_addSongsToggler=0;XMPLAYER_isOpen=0;_xiamitoken=123456789;',
                    'useragent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.30 Safari/537.36',
                );
                    break;
            }
            $response = $this->curl($request);
            switch ($site) {
                case 'xiami':
                    $re       = '/<link rel="canonical" href="http:\/\/www\.xiami\.com\/(collect|album|song)\/(?<id>\d+)" \/>/';
                    break;
                case 'tencent':
                    $re = '/g_SongData.*"songmid":"(?<id>[A-Za-z0-9]+)".*"songtype"/';
                    break;
                default:
                    return false;
                    break;
            }

            preg_match($re, $response, $matches);
            $ids[] = $matches['id'];
            $this->set_cache($cacheKey, $matches['id'], 744);
        }
        return $ids;
    }

    public function song($site, $music_id)
    {
        $Meting = new \Metowolf\Meting($site);
        $cache_key = "/$site/song/$music_id";

        $cache = $this->get_cache($cache_key);
        if ($cache) {
            return $this->addNonce($cache, $site, true);
        }

        $response = json_decode($Meting->format()->song($music_id), true);

        if (!empty($response[0]["id"])) {
            //处理音乐信息
            $mp3_url    = admin_url() . "admin-ajax.php" . "?action=hermit&scope=" . $site . "_song_url&id=" . $response[0]['url_id'];
            $music_name = $response[0]['name'];
            if ($site == 'baidu') {
                $pic = json_decode($Meting->pic($response[0]['pic_id']), true);
                if (empty($pic["url"])) {
                    $cover = null;
                } else {
                    $cover = $pic["url"];
                }
            } else {
                $cover      = admin_url() . "admin-ajax.php" . "?action=hermit&scope=" . $site . "_pic_url&picid=" . $response[0]['pic_id'] . '&id=' . $music_id;
            }
            $artists    = $response[0]['artist'];
            $artists = implode(",", $artists);

            $result = array(
                "id" => $response[0]["id"],
                "title" => $music_name,
                "author" => $artists,
                "url" => $mp3_url,
                "pic" => $cover,
                "lrc" => "https://api.lwl12.com/music/$site/lyric?raw=true&id=" . $music_id
            );

            $this->set_cache($cache_key, $result, 23);

            return $this->addNonce($result, $site, true);
        }

        return false;
    }

    public function songlist($site, $song_list)
    {
        if (!$song_list) {
            return false;
        }

        $songs_array = explode(",", $song_list);
        $songs_array = array_unique($songs_array);

        if (!empty($songs_array)) {
            $result = array();
            foreach ($songs_array as $song_id) {
                $result['songs'][] = $this->song($site, $song_id);
            }
            return $result;
        }

        return false;
    }

    public function album($site, $album_id)
    {
        $Meting = new \Metowolf\Meting($site);
        $cache_key = "/$site/album/$album_id";

        $cache = $this->get_cache($cache_key);
        if ($cache) {
            return $this->addNonce($cache, $site);
        }

        $response = json_decode($Meting->format()->album($album_id), true);

        if (!empty($response[0])) {
            //处理音乐信息
            $result = $response;
            $count  = count($result);

            if ($count < 1) {
                return false;
            }

            $album = array(
                "album_id" => $album_id,
                "album_type" => "albums",
                "album_count" => $count
            );


            foreach ($result as $k => $value) {
                $mp3_url          = admin_url() . "admin-ajax.php" . "?action=hermit&scope=" . $site . "_song_url&id=" . $value["url_id"];
                if ($site == 'baidu') {
                    $pic = json_decode($Meting->pic($value['pic_id']), true);
                    if (empty($pic["url"])) {
                        $cover = null;
                    } else {
                        $cover = $pic["url"];
                    }
                } else {
                    $cover = admin_url() . "admin-ajax.php" . "?action=hermit&scope=" . $site . "_pic_url&picid=" . $value['pic_id'] . '&id=' . $value['id'];
                }
                $album["songs"][] = array(
                    "id" => $value["id"],
                    "title" => $value["name"],
                    "url" => $mp3_url,
                    "author" => $album_author = implode(",", $value['artist']),
                    "pic" => $cover,
                    "lrc" => "https://api.lwl12.com/music/$site/lyric?raw=true&id=" . $value["id"]
                );
            }

            $this->set_cache($key, $album, 24);
            return $this->addNonce($album, $site);
        }

        return false;
    }

    public function playlist($site, $playlist_id)
    {
        $Meting = new \Metowolf\Meting($site);
        $cache_key = "/$site/playlist/$playlist_id";

        $cache = $this->get_cache($cache_key);
        if ($cache) {
            return $this->addNonce($cache, $site);
        }

        $response = json_decode($Meting->format()->playlist($playlist_id), true);

        if (!empty($response[0])) {
            //处理音乐信息
            $result = $response;
            $count  = count($result);

            if ($count < 1) {
                return false;
            }

            $playlist = array(
                "playlist_id" => $playlist_id,
                "playlist_type" => "playlists",
                "playlist_count" => $count
            );

            foreach ($result as $k => $value) {
                $mp3_url = admin_url() . "admin-ajax.php" . "?action=hermit&scope=" . $site . "_song_url&id=" . $value["url_id"];
                $artists = $value["artist"];

                $artists = implode(",", $artists);

                if ($site == 'baidu') {
                    $pic = json_decode($Meting->pic($value['pic_id']), true);
                    if (empty($pic["url"])) {
                        $cover = null;
                    } else {
                        $cover = $pic["url"];
                    }
                } else {
                    $cover = admin_url() . "admin-ajax.php" . "?action=hermit&scope=" . $site . "_pic_url&picid=" . $value['pic_id'] . '&id=' . $value['id'];
                }

                $playlist["songs"][] = array(
                    "id" => $value["id"],
                    "title" => $value["name"],
                    "url" => $mp3_url,
                    "author" => $artists,
                    "pic" => $cover,
                    "lrc" => "https://api.lwl12.com/music/$site/lyric?raw=true&id=" . $value["id"]
                );
            }

            $this->set_cache($cache_key, $playlist, 24);
            return $this->addNonce($playlist, $site);
        }

        return false;
    }

    private function addNonce($data, $site, $single = false)
    {
        if ($single) {
            $data['url'] = $data['url'] . "&_nonce=".wp_create_nonce($site . "_song_url#:".$data['id']);
            $data['pic'] = $data['pic'] . "&_nonce=".wp_create_nonce($site . "_pic_url#:".$data['id']);
        } else {
            foreach ($data["songs"] as $key => $value) {
                $data["songs"][$key]['url'] = $value['url'] . "&_nonce=".wp_create_nonce($site . "_song_url#:".$value['id']);
                $data["songs"][$key]['pic'] = $value['pic'] . "&_nonce=".wp_create_nonce($site . "_pic_url#:".$value['id']);
            }
        }
        return $data;
    }

    public function curl($API)
    {
        $curl=curl_init();
        if (isset($API['body'])) {
            $API['url']=$API['url'].'?'.http_build_query($API['body']);
        }
        curl_setopt($curl, CURLOPT_URL, $API['url']);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_COOKIE, $API['cookie']);
        curl_setopt($curl, CURLOPT_REFERER, $API['referer']);
        curl_setopt($curl, CURLOPT_USERAGENT, $API['useragent']);

        $result=curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    public function get_cache($key)
    {
        if ($this->settings('advanced_cache')) {
            $cache = wp_cache_get($key, 'hermit');
        } else {
            $cache = get_transient($key);
        }

        return $cache === false ? false : json_decode($cache, true);
    }

    public function set_cache($key, $value, $hour = 0.1)
    {
        $value = json_encode($value);

        if ($this->settings('advanced_cache')) {
            wp_cache_set($key, $value, 'hermit', 60 * 60 * $hour);
        } else {
            set_transient($key, $value, 60 * 60 * $hour);
        }
    }

    public function clear_cache($key)
    {
        //delete_transient($key);
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
            'tips' => '点击播放或暂停',
            'strategy' => 1,
            'color' => 'default',
            'playlist_max_height' => '349',
            'quality' => '320',
            'jsplace' => 0,
            'prePage' => 20,
            'remainTime' => 10,
            'roles' => array(
                'administrator'
            ),
            'albumSource' => 0,
            'debug' => 0,
            'advanced_cache' => 0,
            'netease_cookies'=> '',
        );

        $settings = $this->_settings;
        $settings = wp_parse_args($settings, $defaults);

        return $settings[$key];
    }
}
