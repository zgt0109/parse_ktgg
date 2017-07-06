<?php
header('Content-Type: text/html; charset=UTF-8');
date_default_timezone_set('Asia/Shanghai');
set_time_limit(0);

include_once('anyou.php');
// 定义错误返回方法
function exit_via_error($code,$message){
  $return_array = ["success"=>false,"code"=>$code,"msg"=>$message];
  var_dump($return_array);exit;
}

// 找到具体的案由
function findanyou($content){
  global $anyou_arr;
  $anyou = implode("|", $anyou_arr);
  $pattern = "/(".$anyou.")/u";
  if (preg_match($pattern, $content, $match)) {
    $result = $match[1];
    return $result;
  }
}


// 获取命令行参数
if (isset($argv[1])) {
  $content = $argv[1].$argv[2];
  // var_dump($argv);
  $result = findanyou($content);
  var_dump($result);
  $pattern = "/公开审理(.*?)[和与诉](.*?)".$result."/u";
  // 公开审理赵朋朋诉中国人寿财产保险股份有限公司新乡市支公司财产保险合同纠纷一案
  // var_dump($result);exit;
  if (preg_match($pattern, $content, $match)) {
    array_shift($match);
    // print_r($match);die;
    $delete_deng = [];
    // 将等去掉
    foreach ($match as $value) {
      if (preg_match("/等$/u", $value)) {
        $value = preg_replace("/等$/u","",$value);
      }
      array_push($delete_deng, $value);
    }
    print_r($delete_deng);

    $final_array = [];
    $personal_array = [];
    $gongsi_array = [];
    // 判断是否是公司，人名不要
    foreach ($delete_deng as $value) {
      $gongsi_reg = "/.{3,}(院|政府|部|委员会|[军部]队|所|局|海关|[银分支商]行|队|处|站|室|机构|中心|[学驾]校|[小中大]学|台|馆|宫|社|会|社[区团]|联[社盟]|公司|集团|企业|园|坊|屋|吧|厂|铺|店|场|库|村|组|人)$/u";
      $strlength = strlen($value);
      if (preg_match($gongsi_reg, $value, $match) && $strlength >= 5) {
        array_push($gongsi_array, $value);
      }else {
        continue;
      }
    }
    print_r($gongsi_array);
    // 解析公司名和该公司是原告还是被告
    foreach ($gongsi_array as $value) {
      $pattern = "/^[原被]告/u";
      if (preg_match($pattern, $value, $match)) {
        // print_r($match);exit;
        $role = $match[0];
        $name = str_replace($role,"",$value);
        // 最终返回的辩护意见
        $personal_array = [
            'role'   => $role,
            'name' => $name
        ];
        array_push($final_array, $personal_array);
      }else {
        $role = "未识别";
        $name = $value;
        // 最终返回的辩护意见
        $personal_array = [
            'role'   => $role,
            'name' => $name
        ];
        array_push($final_array, $personal_array);
      }
    }
    print_r($final_array);
  }
} else {
  exit_via_error(600,'参数不能为空');
}

?>
