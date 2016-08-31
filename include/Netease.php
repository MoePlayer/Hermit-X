<?php
/*!
 * Netease Cloud Music Api - mini
 * https://i-meto.com
 * Version 20160813
 *
 * Copyright 2016, METO
 * Released under the MIT license
 */




class MusicAPI{

    // General
    protected $_MODULUS='00e0b509f6259df8642dbc35662901477df22677ec152b5ff68ace615bb7b725152b3ab17a876aea8a5aa76d2e417629ec4ee341f56135fccf695280104e0312ecbda92557c93870114af6c9d05c4f7f0c3685b7a46bee255932575cce10b424d813cfe4875d3e82047b97ddef52741d546b8e289dc6935b3ece0462db0a22b8e7';
    protected $_NONCE='0CoJUm6Qyw8W8jud';
    protected $_PUBKEY='010001';
    protected $_VI='0102030405060708';
    protected $_USERAGENT='Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.157 Safari/537.36';
    protected $_COOKIE='os=pc; osver=Microsoft-Windows-10-Professional-build-10586-64bit; appver=2.0.3.131777; channel=netease; __remember_me=true';
    protected $_REFERER='http://music.163.com/';
    // use static secretKey, without RSA algorithm
    protected $_secretKey='TA3YiYCfY2dDJQgg';
    protected $_encSecKey='84ca47bca10bad09a6b04c5c927ef077d9b9f1e37098aa3eac6ea70eb59df0aa28b691b7e75e4f1f9831754919ea784c8f74fbfadf2898b0be17849fd656060162857830e241aba44991601f137624094c114ea8d17bce815b0cd4e5b8e2fbaba978c6d1d14dc3d1faf852bdd28818031ccdaaa13a6018e1024e2aae98844210';

    // encrypt mod
    protected function prepare($raw){
        $data['params']=$this->aes_encode(json_encode($raw),$this->_NONCE);
        $data['params']=$this->aes_encode($data['params'],$this->_secretKey);
        $data['encSecKey']=$this->_encSecKey;
        return $data;
    }
    protected function aes_encode($secretData,$secret){
        return openssl_encrypt($secretData,'aes-128-cbc',$secret,false,$this->_VI);
    }

    // CURL
    protected function curl($url,$data=null){
        $curl=curl_init();
        curl_setopt($curl,CURLOPT_URL,$url);
        if($data){
            if(is_array($data))$data=http_build_query($data);
            curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
            curl_setopt($curl,CURLOPT_POST,1);
        }
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl,CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl,CURLOPT_REFERER,$this->_REFERER);
        curl_setopt($curl,CURLOPT_COOKIE,$this->_COOKIE);
        curl_setopt($curl,CURLOPT_USERAGENT,$this->_USERAGENT);
        $result=curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    // main function
    public function search($s,$limit=30,$offset=0,$type=1){
        $url='http://music.163.com/weapi/v1/search/get?csrf_token=';
        $data=array(
            's'=>$s,
            'type'=>$type,
            'limit'=>$limit,
            'total'=>'true',
            'offset'=>$offset,
            'csrf_token'=>'',
        );
        return $this->curl($url,$this->prepare($data));
    }

    public function artist($artist_id){
        $url='http://music.163.com/weapi/v1/artist/'.$artist_id.'?csrf_token=';
        $data=array(
            'csrf_token'=>'',
        );
        return $this->curl($url,$this->prepare($data));
    }

    public function album($album_id){
        $url='http://music.163.com/weapi/v1/album/'.$album_id.'?csrf_token=';
        $data=array(
            'csrf_token'=>'',
        );
        return $this->curl($url,$this->prepare($data));
    }

    public function detail($song_id){
        $url='http://music.163.com/weapi/v3/song/detail?csrf_token=';
        $data=array(
            'c'=>'['.json_encode(array('id'=>$song_id)).']',
            'csrf_token'=>'',
        );
        return $this->curl($url,$this->prepare($data));
    }
    
    public function DJdetail($dj_id){
        $url='http://music.163.com/weapi/dj/program/detail?csrf_token=';
        $data=array(
            'id'=>$dj_id,
            'csrf_token'=>'',
        );
        return $this->curl($url,$this->prepare($data));
    }
    
    public function DJ($dj_id){
        $url='http://music.163.com/weapi/dj/program/byradio?csrf_token=';
        $data=array(
            'radioId'=>$dj_id,
            'limit'=>1000,
            'csrf_token'=>'',
        );
        return $this->curl($url,$this->prepare($data));
    }

    public function url($song_id,$br=999000){
        $url='http://music.163.com/weapi/song/enhance/player/url?csrf_token=';
        if(!is_array($song_id))$song_id=array($song_id);
        $data=array(
            'ids'=>$song_id,
            'br'=>$br,
            'csrf_token'=>'',
        );
        return $this->curl($url,$this->prepare($data));
    }

    public function playlist($playlist_id){
        $url='http://music.163.com/weapi/v3/playlist/detail?csrf_token=';
        $data=array(
            'id'=>$playlist_id,
            'n'=>1000,
            'csrf_token'=>'',
        );
        return $this->curl($url,$this->prepare($data));
    }

    public function lyric($song_id){
        $url='http://music.163.com/weapi/song/lyric?csrf_token=';
        $data=array(
            'id'=>$song_id,
            'os'=>'pc',
            'lv'=>-1,
            'kv'=>-1,
            'tv'=>-1,
            'csrf_token'=>'',
        );
        return $this->curl($url,$this->prepare($data));
    }

    public function mv($mv_id){
        $url='http://music.163.com/weapi/mv/detail?csrf_token=';
        $data=array(
            'id'=>$mv_id,
            'csrf_token'=>'',
        );
        return $this->curl($url,$this->prepare($data));
    }

    /* static url encrypt, use for pic*/
    public function id2url($id){
        $byte1[]=$this->Str2Arr('3go8&$8*3*3h0k(2)2');
        $byte2[]=$this->Str2Arr($id);
        $magic=$byte1[0];
        $song_id=$byte2[0];
        for($i=0;$i<count($song_id);$i++)$song_id[$i]=$song_id[$i]^$magic[$i%count($magic)];
        $result=base64_encode(md5($this->Arr2Str($song_id),1));
        $result=str_replace('/','_',$result);
        $result=str_replace('+','-',$result);
        return 'http://p3.music.126.net/' . $result . '/' . $id . '.jpg';
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
}