<?php
require '../src/common.php';
$_POST = json_decode(file_get_contents('php://input'), true);
$fields = ["phone", "phone_code", "name", "sex", "password", "birth", "nationality", "position", "photo"];

foreach($fields as $field) {
	if (empty($_POST[$field])) {
		$output = array('status' => 1, 'example_text' => '缺少欄位');
		die(json_encode($output));
	}
}

if (is_account_exist($_POST["phone"])){
	$output = array('status' => 2, 'example_text' => '帳號已存在');
}else if (is_phone_verification_expire($_POST["phone"])){
	$output = array('status' => 3, 'example_text' => '手機驗證碼過期或不存在');
}else if (verify_phone_verification_code($_POST["phone"], $_POST["phone_code"]) != 0){
	$output = array('status' => 4, 'example_text' => '手機驗證碼錯誤');
}else{
	register($_POST['phone'],
		$_POST['name'],
		$_POST['password'],
		$_POST['sex'],
		$_POST['birth'],
		$_POST['nationality'],
		$_POST['position'],
		$_POST['photo']);
	$output = array('status' => 0, 'example_text' => '註冊成功');
}
echo json_encode($output);

