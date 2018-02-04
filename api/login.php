<?php
require '../src/common.php';
$_POST = json_decode(file_get_contents('php://input'), true);
$fields = ["phone", "password"];

foreach($fields as $field) {
	if (empty($_POST[$field])) {
		$output = array('status' => 1, 'example_text' => '缺少欄位');
		die(json_encode($output));
	}
}

$res = login($_POST['phone'], $_POST['password']);
if ($res){
	$output = array('status' => 0, 'res' => $res);
}else{
	$output = array('status' => 1);
}

echo json_encode($output);

