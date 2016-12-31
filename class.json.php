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
		global $Netease, $Xiami, $Kugou, $Tencent;
        @require_once('include/Meting.php');
        $Netease = new Meting("netease");
        $Xiami = new Meting("xiami");
        $Kugou = new Meting("kugou");
        $Tencent = new Meting("tencent");
    }

    public function song($music_id)
        {
    		global $Xiami;
            $cache_key = "/xiami/song/$music_id";

            $cache = $this->get_cache($cache_key);
            if ($cache)
                return $cache;

            $response = json_decode($Xiami->format()->song($music_id), true);

            if (!empty($response["id"])) {
                //处理音乐信息
                $mp3_url    = admin_url() . "admin-ajax.php" . '?action=hermit&scope=xiami_song_url&id=' . $music_id;
                $music_name = $response["name"];
                $cover      = admin_url() . "admin-ajax.php" . '?action=hermit&scope=xiami_pic_url&picid=' . $response['pic_id'] . '&id=' . $music_id;
                $artists    = $response["artist"];

                $artists = implode(",", $artists);

                $result = array(
                    "title" => $music_name,
                    "author" => $artists,
                    "url" => $mp3_url,
                    "pic" => $cover,
                    "lrc" => 'https://api.lwl12.com/music/xiami/lyric?raw=true&id=' . $music_id
                );

                $this->set_cache($cache_key, $result, 24);

                return $result;
            }

            return false;
        }
        public function xiami_song_url($music_id)
        {
    		global $Xiami;
            if ($this->settings( 'XiamiMirror_status' ) == 1 && strpos($_SERVER['REQUEST_URI'], "admin-ajax.php") !== FALSE) {
                Header("Location: " . $this->settings('XiamiMirror') . "/xiami_song_url/id/" . $music_id);
                exit;
            }

            $url = json_decode($Xiami->format()->url($music_id, $this->settings( 'Xiami_Quality' )), true);
            $url = $url['url'];
            if (empty($url)) {
                Header("Location: " . 'http://tts.baidu.com/text2audio?lan=zh&pid=101&ie=UTF-8&spd=6&text=%E9%9D%9E%E5%B8%B8%E6%8A%B1%E6%AD%89%EF%BC%8C%E6%AD%A4%E6%AD%8C%E6%9B%B2%E5%9B%A0%E7%89%88%E6%9D%83%E5%8E%9F%E5%9B%A0%E6%88%96%E5%9B%A0%E7%BD%91%E6%98%93%E4%BA%91%E6%8A%BD%E9%A3%8E%E6%97%A0%E6%B3%95%E6%92%AD%E6%94%BE');
                exit;
            }
            Header("Location: " . $url);
            exit;
        }
        public function xiami_pic_url($id, $pic)
        {
    		global $Xiami;
            if ($this->settings( 'XiamiMirror_status' ) == 1 && strpos($_SERVER['REQUEST_URI'], "admin-ajax.php") !== FALSE) {
                Header("Location: " . $this->settings('XiamiMirror') . "/xiami_pic_url/id/" . $id . "/picid/" . $pic);
                exit;
            }
            $pic = json_decode($Xiami->pic($pic));
            Header("Location: " . $pic["url"]);
            exit;
        }
        public function songs($song_list)
        {
            if (!$song_list)
                return false;

            $songs_array = explode(",", $song_list);
            $songs_array = array_unique($songs_array);

            if (!empty($songs_array)) {
                $result = array();
                foreach ($songs_array as $song_id) {
                    $result['songs'][] = $this->xiami_song($song_id);
                }
                return $result;
            }

            return false;
        }

        public function album($album_id)
        {
    		global $Xiami;
            $key = "/xiami/album/$album_id";

            $cache = $this->get_cache($key);
            if ($cache)
                return $cache;

            $response = json_decode($Xiami->format()->album($album_id), true);

            if (!empty($response[0])) {
                //处理音乐信息
                $result = $response;
                $count  = count($result);

                if ($count < 1)
                    return false;

                $album = array(
                    "album_id" => $album_id,
                    "album_type" => "albums",
                    "album_count" => $count
                );

                foreach ($result as $k => $value) {
                    $mp3_url          = admin_url() . "admin-ajax.php" . '?action=hermit&scope=xiami_song_url&id=' . $value["id"];
                    $album["songs"][] = array(
                        "title" => $value["name"],
                        "url" => $mp3_url,
                        "author" => $album_author,
                        "pic" => $cover  . '&id=' . $value["id"],
    		    "lrc" => 'https://api.lwl12.com/music/xiami/lyric?raw=true&id=' . $value["id"]
                    );
                }

                $this->set_cache($key, $album, 24);
                return $album;
            }

            return false;
        }

        public function collect($playlist_id)
        {
    		global $Xiami;
            $key = "/xiami/playlist/$playlist_id";

            $cache = $this->get_cache($key);
            if ($cache)
                return $cache;

            $response = json_decode($Xiami->format()->playlist($playlist_id), true);

            if (!empty($response[0])) {
                //处理音乐信息
                $result = $response;
                $count  = count($result);

                if ($count < 1)
                    return false;

                $collect = array(
                    "collect_id" => $playlist_id,
                    "collect_type" => "collects",
                    "collect_count" => $count
                );

                foreach ($result as $k => $value) {

    				$mp3_url = admin_url() . "admin-ajax.php" . '?action=hermit&scope=xiami_song_url&id=' . $value["id"];
                    $artists = $value["artist"];

                    $artists = implode(",", $artists);

                    $collect["songs"][] = array(
                        "title" => $value["name"],
                        "url" => $mp3_url,
                        "author" => $artists,
                        "pic" => admin_url() . "admin-ajax.php" . '?action=hermit&scope=xiami_pic_url&picid=' . $value['al']['pic']  . '&id=' . $value["id"],
    					"lrc" => 'https://api.lwl12.com/music/xiami/lyric?raw=true&id=' . $value["id"]
                    );
                }

                $this->set_cache($key, $collect, 24);
                return $collect;
            }

            return false;
        }

    public function netease_song($music_id)
    {
		global $Netease;
        $cache_key = "/netease/song/$music_id";

        $cache = $this->get_cache($cache_key);
        if ($cache)
            return $cache;

        $response = json_decode($Netease->format()->song($music_id), true);

        if (!empty($response[0]["id"])) {
            //处理音乐信息
            $mp3_url    = admin_url() . "admin-ajax.php" . '?action=hermit&scope=netease_song_url&id=' . $music_id;
            $music_name = $response[0]['name'];
            $cover      = admin_url() . "admin-ajax.php" . '?action=hermit&scope=netease_pic_url&picid=' . $response['pic_id'] . '&id=' . $music_id;
            $artists    = $response[0]['artist'];

            $artists = implode(",", $artists);

            $result = array(
                "title" => $music_name,
                "author" => $artists,
                "url" => $mp3_url,
                "pic" => $cover,
                "lrc" => 'https://api.lwl12.com/music/netease/lyric?raw=true&id=' . $music_id
            );

            $this->set_cache($cache_key, $result, 24);

            return $result;
        }

        return false;
    }
    public function netease_song_url($music_id)
    {
		global $Netease;
        if ($this->settings( 'NeteaseMirror_status' ) == 1 && strpos($_SERVER['REQUEST_URI'], "admin-ajax.php") !== FALSE) {
            Header("Location: " . $this->settings('NeteaseMirror') . "/netease_song_url/id/" . $music_id);
            exit;
        }

        $url = json_decode($Netease->format()->url($music_id, $this->settings( 'Netease_Quality' )), true);
        $url = $url['url'];
        if (empty($url)) {
            Header("Location: " . 'http://tts.baidu.com/text2audio?lan=zh&pid=101&ie=UTF-8&spd=6&text=%E9%9D%9E%E5%B8%B8%E6%8A%B1%E6%AD%89%EF%BC%8C%E6%AD%A4%E6%AD%8C%E6%9B%B2%E5%9B%A0%E7%89%88%E6%9D%83%E5%8E%9F%E5%9B%A0%E6%88%96%E5%9B%A0%E7%BD%91%E6%98%93%E4%BA%91%E6%8A%BD%E9%A3%8E%E6%97%A0%E6%B3%95%E6%92%AD%E6%94%BE');
            exit;
        }
        Header("Location: " . $url);
        exit;
    }
    public function netease_pic_url($id, $pic)
    {
		global $Netease;
        if ($this->settings( 'NeteaseMirror_status' ) == 1 && strpos($_SERVER['REQUEST_URI'], "admin-ajax.php") !== FALSE) {
            Header("Location: " . $this->settings('NeteaseMirror') . "/netease_pic_url/id/" . $id . "/picid/" . $pic);
            exit;
        }
        $pic = json_decode($Netease->pic($pic));
        Header("Location: " . $pic["url"] . "?param=120y120");
        exit;
    }
    public function netease_songs($song_list)
    {
        if (!$song_list)
            return false;

        $songs_array = explode(",", $song_list);
        $songs_array = array_unique($songs_array);

        if (!empty($songs_array)) {
            $result = array();
            foreach ($songs_array as $song_id) {
                $result['songs'][] = $this->netease_song($song_id);
            }
            return $result;
        }

        return false;
    }

    public function netease_album($album_id)
    {
		global $Netease;
        $key = "/netease/album/$album_id";

        $cache = $this->get_cache($key);
        if ($cache)
            return $cache;

        $response = json_decode($Netease->format()->album($album_id), true);

        if (!empty($response[0])) {
            //处理音乐信息
            $result = $response;
            $count  = count($result);

            if ($count < 1)
                return false;

            $album = array(
                "album_id" => $album_id,
                "album_type" => "albums",
                "album_count" => $count
            );

            foreach ($result as $k => $value) {
                $mp3_url          = admin_url() . "admin-ajax.php" . '?action=hermit&scope=netease_song_url&id=' . $value["id"];
                $album["songs"][] = array(
                    "title" => $value["name"],
                    "url" => $mp3_url,
                    "author" => $album_author,
                    "pic" => $cover  . '&id=' . $value["id"],
		    "lrc" => 'https://api.lwl12.com/music/netease/lyric?raw=true&id=' . $value["id"]
                );
            }

            $this->set_cache($key, $album, 24);
            return $album;
        }

        return false;
    }

    public function netease_playlist($playlist_id)
    {
		global $Netease;
        $key = "/netease/playlist/$playlist_id";

        $cache = $this->get_cache($key);
        if ($cache)
            return $cache;

        $response = json_decode($Netease->format()->playlist($playlist_id), true);

        if (!empty($response[0])) {
            //处理音乐信息
            $result = $response;
            $count  = count($result);

            if ($count < 1)
                return false;

            $collect = array(
                "collect_id" => $playlist_id,
                "collect_type" => "collects",
                "collect_count" => $count
            );

            foreach ($result as $k => $value) {

				$mp3_url = admin_url() . "admin-ajax.php" . '?action=hermit&scope=netease_song_url&id=' . $value["id"];
                $artists = $value["artist"];

                $artists = implode(",", $artists);

                $collect["songs"][] = array(
                    "title" => $value["name"],
                    "url" => $mp3_url,
                    "author" => $artists,
                    "pic" => admin_url() . "admin-ajax.php" . '?action=hermit&scope=netease_pic_url&picid=' . $value['al']['pic']  . '&id=' . $value["id"],
					"lrc" => 'https://api.lwl12.com/music/netease/lyric?raw=true&id=' . $value["id"]
                );
            }

            $this->set_cache($key, $collect, 24);
            return $collect;
        }

        return false;
    }

    public function netease_radio($radio_id)
    {
        return false; //In Meting, this function has been takedown.
		global $Netease;
        $key = "/netease/radios/$radio_id";

        $cache = $this->get_cache($key);
        if ($cache)
            return $cache;

        $response = json_decode($Netease->DJ($radio_id), true);
        if ($response["code"] == 200 && $response["programs"]) {
            //处理音乐信息
            $result = $response["programs"];
            $count  = count($result);

            if ($count < 1)
                return false;

            $collect = array(
                "collect_id" => $radio_id,
                "collect_title" => '',
                "collect_author" => '多人',
                "collect_type" => "radios",
                "collect_count" => $count
            );

            foreach ($result as $k => $val) {
                $collect["songs"][] = array(
                    "title" => $val['mainSong']['name'],
                    "url" => $val['mainSong']['mp3Url'],
                    "author" => $val['mainSong']["album"]['artist']['name'],
                    "pic" => $val['mainSong']['album']['picUrl'],
                    "lyc" => ""
                );
            }

            $collect['collect_title'] = $collect["songs"][0]["song_author"];

            $this->set_cache($key, $collect, 24);
            return $collect;
        }

        return false;
    }

    public function get_cache($key)
    {
        if ($this->settings('advanced_cache')) {
            $cache = wp_cache_get($key, 'hermit');
        } else {
            $cache = get_transient($key);
        }

        return $cache === false ? false : json_decode($cache, TRUE);
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
            'Netease_Quality' => '320',
            'jsplace' => 0,
            'prePage' => 20,
            'remainTime' => 10,
            'roles' => array(
                'administrator'
            ),
            'albumSource' => 0,
            'debug' => 0,
            'advanced_cache' => 0
        );

        $settings = $this->_settings;
        $settings = wp_parse_args($settings, $defaults);

        return $settings[$key];
    }
}
