<?php

function curl_file_get_contents($durl){  
    global $wpdb, $hermit_table_name;
    $ch = curl_init();  
    curl_setopt($ch, CURLOPT_URL, $durl);  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回    
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回    
    $r = curl_exec($ch);  
    curl_close($ch);  
    return $r;  
}  

$lrc = curl_file_get_contents('https://api.lwl12.com/music/netease/lyric?id='.$_GET['id']);
$json = json_decode($lrc);
//print_r($json);
echo $json->lyric;

?>