<?php 
class hermit
{
        global $wpdb, $hermit_table_name;
        
        $result = array();
        /*CYP的代码*/
        $data   = $wpdb->get_results("SELECT cover_url FROM {$hermit_table_name}");
        /*CYP的代码*/
        //if ($data){  echo "已存在";}
    
        foreach ($data as $key => $value) {
            $result['cover_url'][] = array(
                "cover_url" => $value->cover_url
            );
        }
        echo $result;
}
?>
