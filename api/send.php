<?php
require '../src/common.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $to = $_GET["to"];
  if ($to){
    $res = simple_phone($to);
    switch ($res[0]){
    case "0":
      $output = array('status' => 0, 'type' => 'phone', 'example_text' => 'SMS驗證碼成功發送', 'id' => $res[1]);
      break;
    case "10":
      $output = array('status' => 10, 'type' => 'phone', 'example_text' => '已發送過驗證碼，請稍等30分鐘後再試', 'id' => $res[1]);
      break;
    default:
      $output = array('status' => -1, 'type' => 'phone', 'example_text' => '其他錯誤，請聯絡開發人員');
      break;
    }
  }else{
    $output = array('status' => -1, 'type' => 'phone', 'example_text' => '缺少欄位');
  }
echo json_encode($output);
}
