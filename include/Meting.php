<?php
/*!
 * Meting music framework
 * http://i-meto.com/
 * Version 0.4.0 beta
 *
 * Copyright 2016, METO Sheel
 * Released under the MIT license
 *
 * Suppose search   song    album   playlist    lyric
 * netease *        *       *       *           *
 * tencent *        *       *       *           *
 * xiami   *        *       *       *           *
 * kugou   *        *       *       *           *
 */
class Meting{
    protected $_site;
    protected $_format=false;
    protected $_temp;

    function __construct($v){
        $this->_site=$v;
    }

    public function site($v){
        $this->_site=$v;
    }

    public function format($value=true){
        $this->_format=$value;
        return $this;
    }

    private function curl($API){
        if(isset($API['encode']))$API=call_user_func_array(array($this,$API['encode']),array($API));

        $BASE=$this->curlset();
        $curl=curl_init();
        if($API['methon']=='POST'){
            if(is_array($API['body']))$API['body']=http_build_query($API['body']);
            curl_setopt($curl,CURLOPT_URL,$API['url']);
            curl_setopt($curl,CURLOPT_POSTFIELDS,$API['body']);
            curl_setopt($curl,CURLOPT_POST,1);
        }
        if($API['methon']=='GET'){
            if(isset($API['body']))$API['url']=$API['url'].'?'.http_build_query($API['body']);
            curl_setopt($curl,CURLOPT_URL,$API['url']);
        }
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl,CURLOPT_CONNECTTIMEOUT,10);
        curl_setopt($curl,CURLOPT_COOKIE,$BASE['cookie']);
        curl_setopt($curl,CURLOPT_REFERER,$BASE['referer']);
        curl_setopt($curl,CURLOPT_USERAGENT,$BASE['useragent']);
        $result=curl_exec($curl);
        curl_close($curl);

        if(isset($API['decode']))$result=call_user_func_array(array($this,$API['decode']),array($result));

        if($this->_format&&isset($API['format'])){
            $result=json_decode($result,1);
            $result=$this->clean($result,$API['format']);
            $result=json_encode($result);
        }
        return $result;
    }

    private function pickup($array,$rule){
        $t=explode('/',$rule);
        foreach($t as $vo){
            if($array==null)break;
            $array=$array[$vo];
        }
        return $array;
    }

    private function clean($raw,$rule){
        $raw=$this->pickup($raw,$rule);
        if(!isset($raw[0]))$raw=array($raw);
        $result=array();
        foreach($raw as $vo){
            $result[]=call_user_func_array(array($this,'format_'.$this->_site),array($vo));
        }
        return $result;
    }

    public function search($keyword,$page=1,$limit=30){
        $API=array(
            'netease' => array(
                'methon' => 'POST',
                'url'    => 'http://music.163.com/weapi/cloudsearch/get/web',
                'body'   => array(
                    's'      => $keyword,
                    'type'   => 1,
                    'limit'  => $limit,
                    'total'  => 'true',
                    'offset' => $page-1,
                ),
                'encode' => 'netease_RSAAES',
                'format' => 'result/songs',
            ),
            'tencent'=>array(
                'methon' => 'GET',
                'url'    => 'http://c.y.qq.com/soso/fcgi-bin/search_cp',
                'body'   => array(
                    'p'       => $page,
                    'n'       => $limit,
                    'w'       => $keyword,
                    'aggr'    => 1,
                    'lossless'=> 1,
                    'cr'      => 1,
                ),
                'decode' => 'jsonp2json',
                'format' => 'data/song/list',
            ),
            'xiami'=>array(
                'methon' => 'GET',
                'url'    => 'http://api.xiami.com/web',
                'body'   => array(
                    'v'       => '2.0',
                    'app_key' => '1',
                    'key'     => $keyword,
                    'page'    => $page,
                    'limit'   => $limit,
                    'r'       => 'search/songs',
                ),
                'format' => 'data/songs',
            ),
            'kugou'=>array(
                'methon' => 'GET',
                'url'    => 'http://mobilecdn.kugou.com/api/v3/search/song',
                'body'   => array(
                    'iscorrect' => 1,
                    'pagesize'  => $limit,
                    'plat'      => 20,
                    'sver'      => 3,
                    'showtype'  => 14,
                    'page'      => $page,
                    'keyword'   => $keyword,
                ),
                'format' => 'data/info',
            ),
        );
        $raw=$this->curl($API[$this->_site]);
        return $raw;
    }

    public function song($id){
        $API=array(
            'netease'=>array(
                'methon' => 'POST',
                'url'    => 'http://music.163.com/weapi/v3/song/detail',
                'body'   => array(
                    'c' => '[{"id":'.$id.'}]'
                ),
                'encode' => 'netease_RSAAES',
                'format' => 'songs',
            ),
            'tencent'=>array(
                'methon' => 'GET',
                'url'    => 'http://c.y.qq.com/v8/fcg-bin/fcg_play_single_song.fcg',
                'body'   => array(
                    'songmid' => $id,
                    'format'  => 'json',
                ),
                'decode' => 'tencent_singlesong',
                'format' => 'data',
            ),
            'xiami'=>array(
                'methon' => 'GET',
                'url'    => 'http://api.xiami.com/web',
                'body'   => array(
                    'v'       => '2.0',
                    'app_key' => '1',
                    'id'      => $id,
                    'r'       => 'song/detail',
                ),
                'format' => 'data/song',
            ),
            'kugou'=>array(
                'methon' => 'POST',
                'url'    => 'http://media.store.kugou.com/v1/get_res_privilege',
                'body'   => array(
                    "relate"    => 1,
                    "userid"    => 0,
                    "vip"       => 0,
                    "appid"     => 1390,
                    "token"     => "",
                    "behavior"  => "download",
                    "clientver" => "1",
                    "resource"  => array(array(
                        "id"   => 0,
                        "type" => "audio",
                        "hash" => $id,
                    )),
                ),
                'encode' => 'kugou_json',
                'format' => 'data',
            ),
        );
        $raw=$this->curl($API[$this->_site]);
        return $raw;
    }

    public function album($id){
        $API=array(
            'netease'=>array(
                'methon' => 'POST',
                'url'    => 'http://music.163.com/weapi/v1/album/'.$id,
                'body'   => array(
                    'csrf_token' => '',
                ),
                'encode' => 'netease_RSAAES',
                'format' => 'songs',
            ),
            'tencent'=>array(
                'methon' => 'GET',
                'url'    => 'http://c.y.qq.com/v8/fcg-bin/fcg_v8_album_info_cp.fcg',
                'body'   => array(
                    'albummid' => $id,
                ),
                'format' => 'data/list',
            ),
            'xiami'=>array(
                'methon' => 'GET',
                'url'    => 'http://api.xiami.com/web',
                'body'   => array(
                    'v'       => '2.0',
                    'app_key' => '1',
                    'id'      => $id,
                    'r'       => 'album/detail',
                ),
                'format' => 'data/songs',
            ),
            'kugou'=>array(
                'methon' => 'GET',
                'url'    => 'http://mobilecdn.kugou.com/api/v3/album/song',
                'body'   => array(
                    'albumid' => $id,
                    'plat' => 2,
                    'page' => 1,
                    'pagesize' => -1,
                    'version' => 8400,
                ),
                'format' => 'data/info',
            ),
        );
        $raw=$this->curl($API[$this->_site]);
        return $raw;
    }

    public function playlist($id){
        $API=array(
            'netease'=>array(
                'methon' => 'POST',
                'url'    => 'http://music.163.com/weapi/v3/playlist/detail',
                'body'   => array(
                    'id' => $id,
                    'n'  => 1000,
                ),
                'encode' => 'netease_RSAAES',
                'format' => 'playlist/tracks',
            ),
            'tencent'=>array(
                'methon' => 'GET',
                'url'    => 'http://c.y.qq.com/qzone/fcg-bin/fcg_ucc_getcdinfo_byids_cp.fcg',
                'body'   => array(
                    'disstid' => $id,
                    'utf8'    => 1,
                    'type'    => 1,
                ),
                'decode' => 'jsonp2json',
                'format' => 'cdlist/0/songlist',
            ),
            'xiami'=>array(
                'methon' => 'GET',
                'url'    => 'http://api.xiami.com/web',
                'body'   => array(
                    'v'       => '2.0',
                    'app_key' => '1',
                    'id'      => $id,
                    'r'       => 'collect/detail',
                ),
                'format' => 'data/songs',
            ),
            'kugou'=>array(
                'methon' => 'GET',
                'url'    => 'http://mobilecdn.kugou.com/api/v3/special/song',
                'body'   => array(
                    'specialid' => $id,
                    'page' => 1,
                    'plat' => 2,
                    'pagesize' => -1,
                    'version' => 8400,
                ),
                'format' => 'data/info',
            ),
        );
        $raw=$this->curl($API[$this->_site]);
        return $raw;
    }

    public function url($id,$br=320){
        $API=array(
            'netease'=>array(
                'methon' => 'POST',
                'url'    => 'http://music.163.com/weapi/song/enhance/player/url',
                'body'   => array(
                    'ids' => array($id),
                    'br'  => $br*1000,
                ),
                'encode' => 'netease_RSAAES',
                'decode' => 'netease_url',
            ),
            'tencent'=>array(
                'methon' => 'GET',
                'url'    => 'http://c.y.qq.com/v8/fcg-bin/fcg_play_single_song.fcg',
                'body'   => array(
                    'songmid' => $id,
                    'format'  => 'json',
                ),
                'decode' => 'tencent_url',
            ),
            'xiami'=>array(
                'methon' => 'GET',
                'url'    => "http://www.xiami.com/song/playlist/id/{$id}/object_name/default/object_id/0/cat/json",
                'body'   => array(),
                'decode' => 'xiami_url',
            ),
            'kugou'=>array(
                'methon' => 'POST',
                'url'    => 'http://media.store.kugou.com/v1/get_res_privilege',
                'body'   => array(
                    "relate"=>1,
                    "userid"=>0,
                    "vip"=>0,
                    "appid"=>1390,
                    "token"=>"",
                    "behavior"=>"download",
                    "clientver"=>"1",
                    "resource"=>array(array(
                        "id"=>0,
                        "type"=>"audio",
                        "hash"=>$id,
                    )),
                ),
                'encode' => 'kugou_json',
                'decode' => 'kugou_url',
            ),
        );
        $this->_temp['br']=$br;
        $raw=$this->curl($API[$this->_site]);
        return $raw;
    }

    public function lyric($id){
        $API=array(
            'netease'=>array(
                'methon' => 'POST',
                'url'    => 'http://music.163.com/weapi/song/lyric',
                'body'   => array(
                    'id' => $id,
                    'os' => 'pc',
                    'lv' => -1,
                    'kv' => -1,
                    'tv' => -1,
                ),
                'encode' => 'netease_RSAAES',
            ),
            'tencent'=>array(
                'methon' => 'GET',
                'url'    => 'http://c.y.qq.com/lyric/fcgi-bin/fcg_query_lyric.fcg',
                'body'   => array(
                    'songmid'  => $id,
                    'nobase64' => 0,
                ),
                'decode' => 'jsonp2json',
            ),
            'xiami'=>array(
                'methon' => 'GET',
                'url'    => 'http://api.xiami.com/web',
                'body'   => array(
                    'v'       => '2.0',
                    'app_key' => '1',
                    'id'      => $id,
                    'r'       => 'song/detail',
                ),
                'decode' => 'xiami_lyric',
            ),
            'kugou'=>array(
                'methon' => 'GET',
                'url'    => 'http://m.kugou.com/app/i/krc.php',
                'body'   => array(
                    'keyword'    => '%20-%20',
                    'timelength' => 1000000,
                    'cmd'        => 100,
                    'hash'       => $id,
                ),
                'decode' => 'kugou_lyric'
            ),
        );
        $raw=$this->curl($API[$this->_site]);
        return $raw;
    }

    public function pic($id){
        switch($this->_site){
            case 'netease':
                $url='https://p4.music.126.net/'.$this->netease_pickey($id).'/'.$id.'.jpg?param=300y300';
                break;
            case 'tencent':
                $url='https://y.gtimg.cn/music/photo_new/T002R300x300M000'.$id.'.jpg?max_age=2592000';
                break;
            case 'xiami':
                $data=$this->format(false)->song($id);
                $url=json_decode($data,1)['data']['song']['logo'];
                $url=str_replace('_1.','_2.',$url);
                break;
            case 'kugou':
                $API=array(
                    'methon'=>'GET',
                    'url'   => 'http://tools.mobile.kugou.com/api/v1/singer_header/get_by_hash',
                    'body'  => array(
                        'hash' => $id,
                        'size'   => 400,
                        'format' => 'json',
                    ),
                );
                $data=$this->curl($API);
                $url=json_decode($data,1)['url'];
                break;
        }
        $arr=array('url'=>$url);
        return json_encode($arr);
    }

    private function curlset(){
        $BASE=array(
            'netease'=>array(
                'referer'   => 'http://music.163.com/',
                'cookie'    => 'os=pc; osver=Microsoft-Windows-10-Professional-build-10586-64bit; appver=2.0.3.131777; channel=netease; __remember_me=true',
                'useragent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.30 Safari/537.36',
            ),
            'tencent'=>array(
                'referer'   => 'http://y.qq.com/portal/player.html',
                'cookie'    => 'qqmusic_uin=12345678; qqmusic_key=12345678; qqmusic_fromtag=30; ts_last=y.qq.com/portal/player.html;',
                'useragent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.30 Safari/537.36',
            ),
            'xiami'=>array(
                'referer'   => 'http://h.xiami.com/',
                'cookie'    => 'user_from=2;XMPLAYER_addSongsToggler=0;XMPLAYER_isOpen=0;_xiamitoken=123456789;',
                'useragent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.30 Safari/537.36',
            ),
            'kugou'=>array(
                'referer'   => 'http://www.kugou.com/webkugouplayer/flash/webKugou.swf',
                'cookie'    => '_WCMID=123456789',
                'useragent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.30 Safari/537.36',
            ),
        );
        return $BASE[$this->_site];
    }

    // encode
    private function netease_RSAAES($API){
        $_NONCE='0CoJUm6Qyw8W8jud';
        $_PUBKEY='010001';
        $_VI='0102030405060708';
        $_secretKey='TA3YiYCfY2dDJQgg';
        $_encSecKey='84ca47bca10bad09a6b04c5c927ef077d9b9f1e37098aa3eac6ea70eb59df0aa28b691b7e75e4f1f9831754919ea784c8f74fbfadf2898b0be17849fd656060162857830e241aba44991601f137624094c114ea8d17bce815b0cd4e5b8e2fbaba978c6d1d14dc3d1faf852bdd28818031ccdaaa13a6018e1024e2aae98844210';

        $body=json_encode($API['body']);
        $body=openssl_encrypt($body,'aes-128-cbc',$_NONCE,false,$_VI);
        $body=openssl_encrypt($body,'aes-128-cbc',$_secretKey,false,$_VI);
        $API['body']=array(
            'params'=>$body,
            'encSecKey'=>$_encSecKey,
        );
        return $API;
    }
    private function kugou_json($API){
        $API['body']=json_encode($API['body']);
        return $API;
    }
    // decode
    private function jsonp2json($jsonp){
        if($jsonp[0] !== '[' && $jsonp[0] !== '{') {
            $jsonp = substr($jsonp, strpos($jsonp, '('));
        }
        return trim($jsonp,'();');
    }
    private function tencent_singlesong($result){
        $result=json_decode($result,1);
        $data=$result['data'][0];
        $t=array(
            'songmid' => $data['mid'],
            'songname' => $data['name'],
            'albummid' => $data['album']['mid'],
        );
        foreach($t as $key=>$vo)$result['data'][0][$key]=$vo;
        return json_encode($result);
    }
    private function xiami_lyric($result){
        $result=json_decode($result,1);
        $API=array(
            'methon' => 'GET',
            'url'    => $result['data']['song']['lyric'],
        );
        $data=$this->curl($API);
        $data=preg_replace('/<\d{1,8}>/','',$data);
        $arr=array(
            'lyric' => $data,
        );
        return json_encode($arr);
    }
    private function kugou_lyric($result){
        $arr=array(
            'lyric' => $result,
        );
        return json_encode($arr);
    }
    private function netease_pickey($id){
        $byte1[]=$this->Str2Arr('3go8&$8*3*3h0k(2)2');
        $byte2[]=$this->Str2Arr($id);
        $magic=$byte1[0];
        $song_id=$byte2[0];
        for($i=0;$i<count($song_id);$i++)$song_id[$i]=$song_id[$i]^$magic[$i%count($magic)];
        $result=base64_encode(md5($this->Arr2Str($song_id),1));
        $result=str_replace('/','_',$result);
        $result=str_replace('+','-',$result);
        return $result;
    }
    protected function Str2Arr($string){
        $bytes=array();
        for($i=0;$i<strlen($string);$i++)$bytes[]=ord($string[$i]);
        return $bytes;
    }
    protected function Arr2Str($bytes){
        $str='';
        for($i=0;$i<count($bytes);$i++)$str.=chr($bytes[$i]);
        return $str;
    }
    /**
     * URL - 歌曲地址转换函数
     * 用于返回指定 bitRate 以下的歌曲地址（默认规范化）
     */
    private function netease_url($result){
        $data=json_decode($result,1);
        $url=array(
            'url' => str_replace('http:','https:',$data['data'][0]['url']),
            'br'  => $data['data'][0]['br']/1000,
        );
        return json_encode($url);
    }
    private function tencent_url($result){
        $data=json_decode($result,1);
        $GUID=rand(1,2147483647)*(microtime()*1000)%10000000000;
        $API=array(
            'methon' => 'GET',
            'url'    => 'https://c.y.qq.com/base/fcgi-bin/fcg_musicexpress.fcg',
            'body'   => array(
                'json' => 3,
                'guid' => $GUID,
            ),
            'decode' => 'jsonp2json',
        );
        $t=$this->curl($API);
        $KEY=json_decode($t,1)['key'];

        $type=array(
            'size_320mp3'=>array(320,'M800','mp3'),
            'size_128mp3'=>array(128,'M500','mp3'),
            'size_96aac'=>array(96,'C400','m4a'),
            'size_48aac'=>array(48,'C200','m4a'),
        );
        foreach($type as $key=>$vo){
            if($data['data'][0]['file'][$key]&&$vo[0]<=$this->_temp['br']){
                $url=array(
                    'url' => 'http://dl.stream.qqmusic.qq.com/'.$vo[1].$data['data'][0]['file']['media_mid'].'.'.$vo[2].'?vkey='.$KEY.'&guid='.$GUID.'&fromtag=30',
                    'br'  => $vo[0],
                );
                break;
            }
        }
        return json_encode($url);
    }
    private function xiami_url($result){
        $data=json_decode($result,1);
        $max=0;
        foreach($data['data']['trackList'][0]['allAudios'] as $vo){
            if($vo['rate']<=$this->_temp['br']&&$vo['rate']>$max){
                $max=$vo['rate'];
                $url=array(
                    'url' => $vo['filePath'],
                    'br'  => $vo['rate'],
                );
            }
        }
        return json_encode($url);
    }
    private function kugou_url($result){
        $data=json_decode($result,1);

        $max=0;
        $url=array();
        foreach($data['data'][0]['relate_goods'] as $vo){
            if($vo['info']['bitrate']<=$this->_temp['br']&&$vo['info']['bitrate']>$max){
                $API=array(
                    'methon' => 'GET',
                    'url'    => 'http://trackercdn.kugou.com/i/v2/',
                    'body'   => array(
                        'hash'     => $vo['hash'],
                        'key'      => md5($vo['hash'].'kgcloudv2'),
                        'pid'      => 1,
                        'behavior' => 'play',
                        'cmd'      => '23',
                        'version'  => 8400,
                    ),
                );
                $t=json_decode($this->curl($API),1);
                if(isset($t['url'])){
                    $max=$t['bitRate']/1000;
                    $url=array(
                        'url' => $t['url'],
                        'br'  => $t['bitRate']/1000,
                    );
                }
            }
        }
        return json_encode($url);
    }
    /**
     * Format - 规范化函数
     * 用于统一返回的参数，可用 ->format() 开关开启
     */
    private function format_netease($data){
        $result=array(
            'id' => $data['id'],
            'name' => $data['name'],
            'artist' => array(),
            'pic_id' => strval($data['al']['pic']),
            'url_id' => $data['id'],
            'lyric_id' => $data['id'],
        );
        foreach($data['ar'] as $vo)$result['artist'][]=$vo['name'];
        return $result;
    }
    private function format_tencent($data){
        $result=array(
            'id' => $data['songmid'],
            'name' => $data['songname'],
            'artist' => array(),
            'pic_id' => $data['albummid'],
            'url_id' => $data['songmid'],
            'lyric_id' => $data['songmid'],
        );
        foreach($data['singer'] as $vo)$result['artist'][]=$vo['name'];
        return $result;
    }
    private function format_xiami($data){
        $result=array(
            'id' => $data['song_id'],
            'name' => $data['song_name'],
            'artist' => explode(';',$data['artist_name']),
            'pic_id' => $data['song_id'],
            'url_id' => $data['song_id'],
            'lyric_id' => $data['song_id'],
        );
        return $result;
    }
    private function format_kugou($data){
        $result=array(
            'id' => $data['hash'],
            'url_id' => $data['hash'],
            'pic_id' => $data['hash'],
            'lyric_id' => $data['hash'],
        );
        list($result['artist'],$result['name'])=explode(' - ',$data['filename']);
        $result['artist']=explode('、',$result['artist']);
        return $result;
    }
}
