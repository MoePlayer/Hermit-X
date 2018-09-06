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

        $cookies = $this->settings('netease_cookies');
        if (!empty($cookies) && $site === "netease") $Meting->cookie($cookies);

        $proxy = $this->settings('proxy');
        if (!empty($proxy)) $Meting->proxy($proxy);

        $url = json_decode($Meting->format()->url($music_id, $this->settings('quality')), true);
        $url = $url['url'];

        // if (empty($url)) {
        //     Header("Location: " . 'https://api.lwl12.com/music/netease/song?id=607441');
        //     exit;
        // }

        if ($site === "netease") {
            $url = str_replace('http://m9.', 'http://m7.', $url);
            $url = str_replace('http://m7c.', 'http://m7.', $url);
            $url = str_replace('http://m8c.', 'http://m7.', $url);
            $url = str_replace('http://m8.', 'http://m7.', $url);
            $url = str_replace('http://m7.', 'https://m7.', $url);
            $url = str_replace('http://m10', 'https://m10', $url);
        }
        if ($site === "xiami" || $site === 'tencent') {
            $url = str_replace('http://', 'https://', $url);
        }
        if ($site === 'baidu') {
            $url = str_replace('http://zhangmenshiting.qianqian.com', 'https://gss3.baidu.com/y0s1hSulBw92lNKgpU_Z2jR7b2w6buu', $url);
        }

        $this->set_cache($cacheKey, $url, 0.25);
        Header("Location: " . $url);
        exit;
    }

    private function pic_url($site, $id, $pic)
    {
        $cacheKey = "/$site/pic_url/$pic";
        $url = $this->get_cache($cacheKey);
        if (!empty($url)) return $url;

        $Meting = new \Metowolf\Meting($site);
        $proxy = $this->settings('proxy');
        if (!empty($proxy)) $Meting->proxy($proxy);
        $cookies = $this->settings('netease_cookies');
        if (!empty($cookies) && $site === "netease") $Meting->cookie($cookies);

        $pic = json_decode($Meting->pic($pic, ($site === 'tencent') ? 90 : 100), true);
        if (empty($pic["url"])) {
            return null;
        }
        if ($site === 'netease' || $site === "xiami" || $site === 'tencent') {
            $pic['url'] = str_replace('http://', 'https://', $pic['url']);
        }
        $this->set_cache($cacheKey, $pic["url"], 168);
        return $pic["url"];
    }

    public function lyric($site, $id)
    {
        $cacheKey = "/$site/lyric/$id";
        $value = $this->get_cache($cacheKey);
        if ($value) {
            Header("X-Hermit-Cached: From Cache");
            return $value;
        }
        $Meting = new \Metowolf\Meting($site);
        $proxy = $this->settings('proxy');
        if (!empty($proxy)) $Meting->proxy($proxy);
        $cookies = $this->settings('netease_cookies');
        if (!empty($cookies) && $site === "netease") $Meting->cookie($cookies);

        $value = json_decode($Meting->format(true)->lyric($id), true);
        $value = $this->lrctran($value['lyric'],$value['tlyric']);
        if (empty($value)) {
            $value = "[00:00.000]此歌曲暂无歌词，请您欣赏";
        }

        if ($site === 'tencent') {
          $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5);
        }
        
        $this->set_cache($cacheKey, $value, 24);
        return $value;
    }

    private function lrctrim($lyrics)
    {
        $result = "";
        $lyrics = explode("\n", $lyrics);
        $data = array();
        foreach($lyrics as $key => $lyric){
            preg_match('/\[(\d{2}):(\d{2}[\.:]?\d*)]/', $lyric, $lrcTimes);
            $lrcText = preg_replace('/\[(\d{2}):(\d{2}[\.:]?\d*)]/', '', $lyric);
            if (empty($lrcTimes)) {
                continue;
            }
            $lrcTimes = intval($lrcTimes[1]) * 60000 + intval(floatval($lrcTimes[2]) * 1000);
            $lrcText = preg_replace('/\s\s+/', ' ', $lrcText);
            $lrcText = trim($lrcText);
            $data[] = array($lrcTimes, $key, $lrcText);
        }
        sort($data);
        return $data;
    }

    private function lrctran($lyric,$tlyric)
    {
        $lyric = $this->lrctrim($lyric);
        $tlyric = $this->lrctrim($tlyric);
        $len1 = count($lyric);
        $len2 = count($tlyric);
        $result = "";
        for($i=0,$j=0; $i<$len1&&$j<$len2; $i++){
            while ($lyric[$i][0]>$tlyric[$j][0]&&$j+1<$len2) {
                $j++;
            }
            if ($lyric[$i][0] == $tlyric[$j][0]) {
                $tlyric[$j][2] = str_replace('/', '', $tlyric[$j][2]);
                if(!empty($tlyric[$j][2])){
                    $lyric[$i][2] .= " ({$tlyric[$j][2]})";
                }
                $j++;
            }
        }
        for($i=0; $i<$len1; $i++){
            $t = $lyric[$i][0];
            $result .= sprintf("[%02d:%02d.%03d]%s\n", $t/60000, $t%60000/1000, $t%1000, $lyric[$i][2]);
        }
        return $result;
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
                // case 'tencent':
                // $request = array(
                //     'url'    => $value,
                //     'referer'   => 'https://y.qq.com/portal/player.html',
                //     'cookie'    => 'pgv_pvi=3832878080; pgv_si=s4066364416; pgv_pvid=3938077488; yplayer_open=1; qqmusic_fromtag=66; ts_last=y.qq.com/portal/player.html; ts_uid=5141451452; player_exist=1; yq_index=1',
                //     'useragent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36',
                // );
                //     break;

                case 'xiami':
                $request = array(
                    'url'    => $value,
                    'referer'   => 'http://h.xiami.com/',
                    'cookie'    => 'user_from=2;XMPLAYER_addSongsToggler=0;XMPLAYER_isOpen=0;_xiamitoken=c7c00f36f7f1acc679c3ad4ee5aabebd;',
                    'useragent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36',
                );
                    break;
            }
            $response = $this->curl($request);
            switch ($site) {
                case 'xiami':
                    $re       = '/<link rel="canonical" href="http:\/\/www\.xiami\.com\/(collect|album|song)\/(?<id>\d+)" \/>/';
                    break;
                // case 'tencent':
                //     $re = '/g_SongData.*"songmid":"(?<id>[A-Za-z0-9]+)".*"songtype"/';
                //     break;
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

        $cookies = $this->settings('netease_cookies');
        if (!empty($cookies) && $site === "netease") $Meting->cookie($cookies);

        $proxy = $this->settings('proxy');
        if (!empty($proxy)) $Meting->proxy($proxy);

        $response = json_decode($Meting->format()->song($music_id), true);

        if (!empty($response[0]["id"])) {
            //处理音乐信息
            $mp3_url    = admin_url() . "admin-ajax.php" . "?action=hermit&scope=" . $site . "_song_url&id=" . $response[0]['url_id'];
            $music_name = $response[0]['name'];
            $cover      = $this->pic_url($site, $music_id, $response[0]['pic_id']);
            $lyric      = admin_url() . "admin-ajax.php" . "?action=hermit&scope=" . $site . "_lyric&id=" . $response[0]['lyric_id'];
            $artists    = $response[0]['artist'];
            $artists = implode(",", $artists);

            $result = array(
                "id" => $response[0]["id"],
                "title" => $music_name,
                "author" => $artists,
                "url" => $mp3_url,
                "pic" => $cover,
                "lrc" => $lyric
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

        $proxy = $this->settings('proxy');
        if (!empty($proxy)) $Meting->proxy($proxy);

        $cookies = $this->settings('netease_cookies');
        if (!empty($cookies) && $site === "netease") $Meting->cookie($cookies);

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
                $mp3_url = admin_url() . "admin-ajax.php" . "?action=hermit&scope=" . $site . "_song_url&id=" . $value["url_id"];
                $cover   = $this->pic_url($site, $value['id'], $value['pic_id']);
                $lyric   = admin_url() . "admin-ajax.php" . "?action=hermit&scope=" . $site . "_lyric&id=" . $value['lyric_id'];
                $album["songs"][] = array(
                    "id" => $value["id"],
                    "title" => $value["name"],
                    "url" => $mp3_url,
                    "author" => $album_author = implode(",", $value['artist']),
                    "pic" => $cover,
                    "lrc" => $lyric
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

        $proxy = $this->settings('proxy');
        if (!empty($proxy)) $Meting->proxy($proxy);
        
        $cookies = $this->settings('netease_cookies');
        if (!empty($cookies) && $site === "netease") $Meting->cookie($cookies);

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
                $cover   = $this->pic_url($site, $value['id'], $value['pic_id']);
                $lyric   = admin_url() . "admin-ajax.php" . "?action=hermit&scope=" . $site . "_lyric&id=" . $value['lyric_id'];
                $playlist["songs"][] = array(
                    "id" => $value["id"],
                    "title" => $value["name"],
                    "url" => $mp3_url,
                    "author" => $artists,
                    "pic" => $cover,
                    "lrc" => $lyric
                );
            }

            $this->set_cache($cache_key, $playlist, 24);
            return $this->addNonce($playlist, $site);
        }

        return false;
    }

    private function addNonce($data, $site, $single = false)
    {
        if(!$this->settings('low_security')){
            if ($single) {
                $data['url'] = $data['url'] . "&_nonce=".wp_create_nonce($site . "_song_url#:".$data['id']);
                //$data['pic'] = $data['pic'] . "&_nonce=".wp_create_nonce($site . "_pic_url#:".$data['id']);
                $data['lrc'] = $data['lrc'] . "&_nonce=".wp_create_nonce($site . "_lyric#:".$data['id']);
            } else {
                foreach ($data["songs"] as $key => $value) {
                    $data["songs"][$key]['url'] = $value['url'] . "&_nonce=".wp_create_nonce($site . "_song_url#:".$value['id']);
                    //$data["songs"][$key]['pic'] = $value['pic'] . "&_nonce=".wp_create_nonce($site . "_pic_url#:".$value['id']);
                    $data["songs"][$key]['lrc'] = $value['lrc'] . "&_nonce=".wp_create_nonce($site . "_lyric#:".$value['id']);
                }
            }
        } else {
            if ($single) {
                $data['url'] = $data['url'] . "&_nonce=".md5(NONCE_KEY.$site . "_song_url#:".$data['id'].NONCE_KEY);
                //$data['pic'] = $data['pic'] . "&_nonce=".md5(NONCE_KEY.$site . "_pic_url#:".$data['id'].NONCE_KEY);
                $data['lrc'] = $data['lrc'] . "&_nonce=".md5(NONCE_KEY.$site . "_lyric#:".$data['id'].NONCE_KEY);
            } else {
                foreach ($data["songs"] as $key => $value) {
                    $data["songs"][$key]['url'] = $value['url'] . "&_nonce=".md5(NONCE_KEY.$site . "_song_url#:".$value['id'].NONCE_KEY);
                    //$data["songs"][$key]['pic'] = $value['pic'] . "&_nonce=".md5(NONCE_KEY.$site . "_pic_url#:".$value['id'].NONCE_KEY);
                    $data["songs"][$key]['lrc'] = $value['lrc'] . "&_nonce=".md5(NONCE_KEY.$site . "_lyric#:".$value['id'].NONCE_KEY);
                }
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
        if ($this->settings('proxy')) {
            curl_setopt($curl, CURLOPT_PROXY, $this->settings('proxy'));
        }

        $result=curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    public function get_cache($key)
    {
        $cache = get_transient($key);
        return $cache === false ? false : json_decode($cache, true);
    }

    public function set_cache($key, $value, $hour = 0.1)
    {
        $value = json_encode($value);
        set_transient($key, $value, 60 * 60 * $hour);
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
        global $HMT;
        return $HMT->settings($key);
    }

}
