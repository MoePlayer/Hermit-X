<?php
 //ini_set('display_errors',1);            //错误信息
 //ini_set('display_startup_errors',1);    //php启动错误信息
 //error_reporting(-1);                    //打印出所有的 错误信息
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
        
		global $Netease;
        @require_once('include/Netease.php');
        $Netease = new MusicAPI();
    }
    
    public function song($song_id)
    {
        $cache_key = "/xiami/song/" . $song_id;
        
        $cache = $this->get_cache($cache_key);
        if ($cache)
            return $cache;
        
        $response = $this->xiami_http(0, $song_id);
        
        if ($response && $response["state"] == 0 && $response['data']) {
            $result = $response["data"]["song"];
            
            $song = array(
                "song_id" => $result["song_id"],
                "song_title" => $result["song_name"],
                "song_author" => $result["singers"],
                "song_src" => $result["listen_file"],
                "song_cover" => $result['logo']
            );
            
            $this->set_cache($cache_key, $song);
            
            return $song;
        }
        
        return false;
    }
    
    public function song_list($song_list)
    {
        if (!$song_list)
            return false;
        
        $this->get_token();
        
        $songs_array = explode(",", $song_list);
        $songs_array = array_unique($songs_array);
        
        if (!empty($songs_array)) {
            $result = array();
            foreach ($songs_array as $song_id) {
                $result['songs'][] = $this->song($song_id);
            }
            //$this->set_cache($key, $result);
            return $result;
        }
        
        return false;
    }
    
    public function album($album_id)
    {
		
        $cache_key = '/xiami/album/' . $album_id;
        
        $cache = $this->get_cache($cache_key);
        if ($cache)
            return $cache;
        
        $this->get_token();
        $response = $this->xiami_http(1, $album_id);
        
        if ($response && $response["state"] == 0 && $response["data"]) {
            $result = $response["data"];
            $count  = $result['song_count'];
            
            if ($count < 1)
                return false;
            
            $album = array(
                "album_id" => $album_id,
                "album_title" => $result['album_name'],
                "album_author" => $result['artist_name'],
                "album_cover" => $result['album_logo'],
                "album_count" => $count,
                "album_type" => "albums"
            );
            
            foreach ($result['songs'] as $key => $val) {
                $song_id          = $val['song_id'];
                $album["songs"][] = $this->song($song_id);
            }
            
            $this->set_cache($cache_key, $album);
            return $album;
        }
        
        return false;
    }
    
    public function collect($collect_id)
    {
        $cache_key = '/xiami/collect/' . $collect_id;
        
        $cache = $this->get_cache($cache_key);
        if ($cache)
            return $cache;
        
        $this->get_token();
        $response = $this->xiami_http(2, $collect_id);
        
        if ($response && $response["state"] == 0 && $response["data"]) {
            $result = $response["data"];
            $count  = $result['songs_count'];
            
            if ($count < 1)
                return false;
            
            $collect = array(
                "collect_id" => $collect_id,
                "collect_title" => $result['collect_name'],
                "collect_author" => $result['user_name'],
                "collect_cover" => $result['logo'],
                "collect_type" => "collects",
                "collect_count" => $count
            );
            
            foreach ($result['songs'] as $key => $value) {
                $collect["songs"][] = array(
                    "song_id" => $value["song_id"],
                    "song_title" => $value["song_name"],
                    "song_length" => $value["length"],
                    "song_src" => $value["listen_file"],
                    "song_author" => $value["singers"],
                    "song_cover" => $value['album_logo']
                );
            }
            
            $this->set_cache($cache_key, $collect);
            
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
        
        $response = json_decode($Netease->detail($music_id), true);
        
        if ($response["code"] == 200 && $response["songs"]) {
            //处理音乐信息
            $mp3_url    = admin_url() . "admin-ajax.php" . '?action=hermit&scope=netease_song_url&id=' . $music_id;
            $music_name = $response["songs"][0]["name"];
            $cover      = admin_url() . "admin-ajax.php" . '?action=hermit&scope=netease_pic_url&id=' . $response['songs'][0]['al']['pic_str'];
            $artists    = array();
            
            foreach ($response["songs"][0]["ar"] as $artist) {
                $artists[] = $artist["name"];
            }
            
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
        $url = json_decode($Netease->url($music_id), true);
        $url = $url["data"][0]["url"];
        if (empty($url)) {
            Header("Location: " . 'http://tts.baidu.com/text2audio?lan=zh&pid=101&ie=UTF-8&spd=6&text=%E9%9D%9E%E5%B8%B8%E6%8A%B1%E6%AD%89%EF%BC%8C%E6%AD%A4%E6%AD%8C%E6%9B%B2%E5%9B%A0%E7%89%88%E6%9D%83%E5%8E%9F%E5%9B%A0%E6%88%96%E5%9B%A0%E7%BD%91%E6%98%93%E4%BA%91%E6%8A%BD%E9%A3%8E%E6%97%A0%E6%B3%95%E6%92%AD%E6%94%BE');
            exit;
        }
        Header("Location: " . $url);
        exit;
    }
    public function netease_pic_url($pic_str)
    {
		global $Netease;
        Header("Location: " . $Netease->id2url($pic_str));
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
        
        $response = json_decode($Netease->album($album_id), true);
        
        if ($response["code"] == 200 && $response["songs"]) {
            //处理音乐信息
            $result = $response["songs"];
            $count  = count($result);
            
            if ($count < 1)
                return false;
            
            $album_name   = $response["album"]["name"];
            $album_author = $response["album"]["artist"]["name"];
            $cover        = admin_url() . "admin-ajax.php" . '?action=hermit&scope=netease_pic_url&id=' . (string)$value["album"]['pic'];
            
            $album = array(
                "album_id" => $album_id,
                "album_title" => $album_name,
                "album_author" => $album_author,
                "album_type" => "albums",
                "album_count" => $count
            );
            
            foreach ($result as $k => $value) {
                $mp3_url          = admin_url() . "admin-ajax.php" . '?action=hermit&scope=netease_song_url&id=' . $value["id"];
                $album["songs"][] = array(
                    "song_id" => $value["id"],
                    "song_title" => $value["name"],
                    "song_length" => ceil($value['duration'] / 1000),
                    "song_src" => $mp3_url,
                    "song_author" => $album_author,
                    "song_cover" => $cover
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
        
        $response = json_decode($Netease->playlist($playlist_id), true);
        
        if ($response["code"] == 200 && $response["playlist"]) {
            //处理音乐信息
            $result = $response["playlist"]["tracks"];
            $count  = count($result);
            
            if ($count < 1)
                return false;
            
            $collect_name = $response["playlist"]["name"];
            
            $collect = array(
                "collect_id" => $playlist_id,
                "collect_title" => $collect_name,
                "collect_author" => '',
                "collect_type" => "collects",
                "collect_count" => $count
            );
            
            foreach ($result as $k => $value) {
                
				$mp3_url = admin_url() . "admin-ajax.php" . '?action=hermit&scope=netease_song_url&id=' . $value["id"];
                $artists = array();
                foreach ($value["ar"] as $artist) {
                    $artists[] = $artist["name"];
                }
                
                $artists = implode(",", $artists);
                
                $collect["songs"][] = array(
                    "title" => $value["name"],
                    "url" => $mp3_url,
                    "author" => $artists,
                    "pic" => admin_url() . "admin-ajax.php" . '?action=hermit&scope=netease_pic_url&id=' . $value['al']['pic_str'],
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
		global $Netease;
        $key = "/netease/radios/$radio_id";
        
        $cache = $this->get_cache($key);
        if ($cache)
            return $cache;
        
        $response = $this->netease_http(4, $radio_id);
        
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
                    "song_id" => $val['mainSong']['id'],
                    "song_title" => $val['mainSong']['name'],
                    "song_length" => (int) $val['mainSong']['duration'] / 1000,
                    "song_src" => $val['mainSong']['mp3Url'],
                    "song_author" => $val['radio']['name'],
                    "song_cover" => $val['mainSong']['album']['picUrl']
                );
            }
            
            $collect['collect_title'] = $collect["songs"][0]["song_author"];
            
            $this->set_cache($key, $collect, 24);
            return $collect;
        }
        
        return false;
    }
    
    private function netease_http($type, $id)
    {
        $header = array(
            "Accept:*/*",
            "Accept-Language:zh-CN,zh;q=0.8",
            "Cache-Control:no-cache",
            "Connection:keep-alive",
            "Content-Type:application/x-www-form-urlencoded;charset=UTF-8",
            "Cookie:visited=true;",
            "DNT:1",
            "Host:music.163.com",
            "Pragma:no-cache",
            "Referer:http://music.163.com/outchain/player?type={$type}&id={$id}&auto=1&height=430&bg=e8e8e8",
            "User-Agent:Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.155 Safari/537.36"
        );
        
        $prefix = 'http://music.163.com/api/';
        
        switch ($type) {
            //歌单
            case 0:
                $url = "playlist/detail?id={$id}&ids=%5B%22{$id}%22%5D&limit=10000&offset=0";
                break;
            
            //专辑
            case 1:
                $url = "album/{$id}?id={$id}&id={$id}&ids=%5B%22{$id}%22%5D&limit=10000&offset=0";
                break;
            
            //单曲
            case 2:
                $url = "song/detail?id={$id}&id={$id}&ids=%5B%22{$id}%22%5D&limit=10000&offset=0";
                break;
            
            //单播客
            case 3:
                $url = "dj/program/detail?id={$id}&id={$id}&ids=%5B%22{$id}%22%5D&limit=10000&offset=0";
                break;
            
            //播客全集
            case 4:
                $url = "dj/program/byradio?radioId={$id}&id={$id}&ids=%5B%22{$id}%22%5D&limit=10000&offset=0";
                break;
        }
        
        $url = $prefix . $url;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $cexecute = curl_exec($ch);
        @curl_close($ch);
        
        if ($cexecute) {
            $result = json_decode($cexecute, TRUE);
            return $result;
        } else {
            return false;
        }
    }
    
    private function xiami_http($type, $id)
    {
        
        switch ($type) {
            case 0:
                $url = "http://api.xiami.com/web?v=2.0&app_key=1&id={$id}&r=song/detail";
                break;
            
            case 1:
                $url = "http://api.xiami.com/web?v=2.0&app_key=1&id={$id}&r=album/detail";
                break;
            
            case 2:
                $url = "http://api.xiami.com/web?v=2.0&app_key=1&id={$id}&type=collectId&r=collect/detail";
                break;
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_REFERER, "http://m.xiami.com/");
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (iPhone; CPU iPhone OS 7_1_2 like Mac OS X) AppleWebKit/537.51.2 (KHTML, like Gecko) Version/7.0 Mobile/11D257 Safari/9537.53');
        curl_setopt($ch, CURLOPT_COOKIE, "user_from=2; _xiamitoken={$this->token}");
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $cexecute = curl_exec($ch);
        @curl_close($ch);
        
        if ($cexecute) {
            $result = json_decode($cexecute, TRUE);
            return $result;
        } else {
            return false;
        }
    }
    
    private function get_token()
    {
        $token = get_transient('_xiamitoken');
        
        if ($token) {
            $this->token = $token;
        } else {
            $XM_head = wp_remote_head('http://m.xiami.com', array(
                'headers' => array(
                    'Host: m.xiami.com',
                    'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 8_0 like Mac OS X) AppleWebKit/600.1.3 (KHTML, like Gecko) Version/8.0 Mobile/12A4345d Safari/600.1.4',
                    'Proxy-Connection:keep-alive',
                    'X-FORWARDED-FOR:42.156.140.238',
                    'CLIENT-IP:42.156.140.238'
                )
            ));
            
            if (!is_wp_error($XM_head)) {
                $cookies = $XM_head['cookies'];
                
                foreach ($cookies as $key => $cookie) {
                    if ($cookie->name == '_xiamitoken') {
                        $this->token = $cookie->value;
                        
                        set_transient('_xiamitoken', $this->token, 60 * 60 * 100);
                    }
                }
            }
        }
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