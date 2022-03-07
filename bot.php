<?php
    date_default_timezone_set("Asia/Jakarta");
    //Data From Webhook
    $content = file_get_contents("php://input");
    $update = json_decode($content, true);
    $chat_id = $_ENV['CHAT_ID'];
    $apiToken = $_ENV['API_TOKEN'];  
    $admin_id = $_ENV['ADMIN_ID'];  
    $message = $update["message"]["text"];
    $message_chat_id = $update["message"]["chat"]["id"];
    $message_id = $update["message"]["message_id"];
    $id = $update["message"]["from"]["id"];
    $username = $update["message"]["from"]["username"];
    $firstname = $update["message"]["from"]["first_name"];

if ($message_chat_id == $chat_id) {
    if (strpos($message, "/init") === 0) {
          $json = file_get_contents("https://api.telegram.org/bot$apiToken/getMyCommands");
        $data = json_decode($json, true);
        
        $ok = $data['ok'];
        if ($ok == true and strtoupper($username) === strtoupper($admin_id)) {
            file_get_contents("https://api.telegram.org/bot$apiToken/deleteMessage?chat_id=$chat_id&message_id=$message_id");
            file_get_contents("https://api.telegram.org/bot$apiToken/setMyCommands?commands=[{%22command%22:%22/bin%22,%22description%22:%22/bin%20卡头%20查询信用卡信息%22},{%22command%22:%22/rate%22,%22description%22:%22/rate%20货币1%20货币2%20数量%20查询货币汇率%22}]");
        }
    }
    //Bin Lookup
    if (strpos($message, "/bin") === 0) {
        $bin = substr($message, 5);
          $json = file_get_contents("https://binsu-api.vercel.app/api/{$bin}");
        $data = json_decode($json, true);
        $bank = $data['data']['bank'];
        $country = $data['data']['country'];
        $brand = $data['data']['vendor'];
        $level = $data['data']['level'];
        $type = $data['data']['type'];
        $flag = $data['data']['countryInfo']['emoji'];
        $result = $data['result'];
    
        if ($result == true) {
            send_message($apiToken,$chat_id, $message_id, "***卡头: $bin
种类: $brand
级别: $level
银行: $bank
国家: $country $flag
类型: $type***
查询人 @$username");
        } else {
            send_message($apiToken,$chat_id, $message_id, "***卡头解析错误*** 正确格式：/bin 6-8位卡头");
        }
    }
    //-rate CNY TRY 100
    if (strpos($message, "/rate") === 0) {
        $apikey = "c1bf309f1ed58b0e54c8";
        $from_Currency = urlencode(strtoupper(substr($message, 6, 3)));
        $to_Currency = urlencode(strtoupper(substr($message, 10, 3)));
          $query =  "{$from_Currency}_{$to_Currency}";
        
          $json = file_get_contents("https://free.currconv.com/api/v7/convert?q={$query}&compact=ultra&apiKey={$apikey}");
          $obj = json_decode($json, true);
        
        if (empty(substr($message, 14)) == false) {
            $amount = floatval(substr($message, 14));
        } else {
            $amount = 100;
        }
        
        if (empty($obj) == false) {
            if (empty($obj["$query"]) == false) {
                $val = floatval($obj["$query"]);
                $total = round($val * $amount,2);
                send_message($apiToken,$chat_id, $message_id, "***$from_Currency : $to_Currency = $amount : $total ***
查询人 @$username ");
            } else {
                send_message($apiToken,$chat_id,$message_id, "***汇率解析错误*** 正确格式：/rate 货币1 货币2 数量");
            }
        } else {
            send_message($apiToken,$chat_id,$message_id, "***汇率解析错误*** 正确格式：/rate 货币1 货币2 数量");
        }
    }
}

function send_message($apiToken,$chat_id,$message_id, $message){
    $text = urlencode($message);
    file_get_contents("https://api.telegram.org/bot$apiToken/deleteMessage?chat_id=$chat_id&message_id=$message_id");
    file_get_contents("https://api.telegram.org/bot$apiToken/sendMessage?chat_id=$chat_id&text=$text&parse_mode=Markdown");
}
?>
