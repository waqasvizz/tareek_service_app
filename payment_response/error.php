<?php
	$data = file_get_contents("php://input");
    $fp = fopen('errortxt.txt', 'a') or exit("Unable to open file!");
    // fwrite($fp, $data."\r\n\r\n========================================================\r\n\r\n");
    fwrite($fp, "------->".date('Y-m-d h:i:s')."\n".$data."\r\n\r\n========================================================\r\n\r\n");
    fclose($fp);
    
    echo "Line no @"."<br>";
    echo "<pre>";
    print_r($data);
    echo "</pre>";
    exit("@@@@");
?>