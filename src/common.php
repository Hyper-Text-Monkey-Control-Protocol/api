<?php
require_once 'config.php';
require 'Nexmo.php';

function simple_phone($to){
	$verifier = new nexmo;
	$verifier->set_format('json');
	$brand = 'Fishackathon';
	$sender_id = 'HTMCP';
	$code_length = '6';
	$lg = 'en-us';
	$require_type = null;
	$pin_expiry = SMS_CODE_EXPIRE_TIME;
	$response = $verifier->verify_request($to, $brand, $sender_id, $code_length, $lg, $require_type, $pin_expiry);
	if (status == 0 || status == 10){
		global $db;
		$stmt = $db->prepare("INSERT INTO `phone_verify` (time, phone, request_id, status, ip) VALUES (NOW(), :phone, :request_id, :status, :ip)");
		$stmt->execute([ 'phone' => $to, 'request_id' => $response["request_id"], 'status' => $response["status"], 'ip' => get_ip() ]);
		$dbrid = $stmt->fetch(PDO::FETCH_ASSOC)["request_id"];
	}
	return Array($response["status"], $response["request_id"]);
}

function get_ip()
{
	return $_SERVER['HTTP_CF_CONNECTING_IP'] ?: $_SERVER['REMOTE_ADDR'];
}

function is_account_exist($phone){
	global $db;
	$stmt = $db->prepare("SELECT `phone` FROM `users` WHERE phone=:phone ORDER BY `id` DESC LIMIT 1");
	$stmt->execute(['phone' => $phone]);
	$res = $stmt->fetch(PDO::FETCH_ASSOC)["uid"];
	if ($res == ""){
		return 0;
	}else{
		return 1;
	}
}

function is_phone_verification_expire($phone){
	global $db;
	$stmt = $db->prepare("SELECT `time` FROM `phone_verify` WHERE `phone`=:phone ORDER BY `id` DESC LIMIT 1");
	$stmt->execute([ 'phone' => $phone ]);
	$last_time = $stmt->fetch(PDO::FETCH_ASSOC)["time"];
	$expire_time = new DateTime("$last_time", new DateTimeZone(DB_TIMEZONE));
	$expire_time->modify('+'.SMS_CODE_EXPIRE_TIME.'seconds');
	return (new DateTime() > $expire_time) || !$last_time;
}

function verify_phone_verification_code($phone, $code){
	global $db;
	$stmt = $db->prepare("SELECT `request_id` FROM `phone_verify` WHERE phone=:phone ORDER BY `id` DESC LIMIT 1");
	$stmt->execute(['phone' => $phone]);
	$request_id = $stmt->fetch(PDO::FETCH_ASSOC)["request_id"];
	$verifier = new nexmo;
	$verifier->set_format('json');
	$response = $verifier->verify_check($request_id, $code, get_ip());
	switch ($response["status"]){
	case "0":
		return 0; //成功驗證
		break;
	case "16":
		return 16; //驗證碼錯誤
		break;
	default:
		return -1; //其他錯誤
		break;
	}
}

function register($phone, $name, $password, $sex, $birth, $nationality, $position, $photo){
	global $db;
	$stmt = $db->prepare('INSERT INTO `users` (phone, name, password, sex, birth, nationality, position, photo, reg_time, reg_ip) VALUES (:phone, :name, :password, :sex, :birth, :nationality, :position, :photo, :reg_time, :reg_ip)');
	$stmt->execute(['phone' => $phone,
		'password' => $password,
		'name' => $name,
		'password' => $password,
		'sex' => $sex,
		'birth' => $birth,
		'nationality' => $nationality,
		'position' => $position,
		'photo' => $photo,
		'reg_ip' => get_ip(),
		'reg_time' => date("Y-m-d H:i:s")]);
}

function login($phone, $password){
	global $db;
	$stmt = $db->prepare('SELECT * FROM `users` WHERE `phone`=:phone AND `password`=:password');
	$stmt->execute(['phone' => $phone,
		'password' => $password]);
	$res = $stmt->fetch(PDO::FETCH_ASSOC);
	if ($res['phone'] == $phone){
		$token = hash('sha256', rand());
		$stmt = $db->prepare('INSERT INTO `session` (`uid`, `token`) VALUES (:uid, :token)');
		$stmt->execute(['uid' => $res['uid'],
			'token' => $token]);
		$res['token'] = $token;
		return $res;
	}else{
		return False;
	}
}

function verify_session($token){
	global $db;
	$stmt = $db->prepare('SELECT * FROM `sessions` WHERE `token`=:token');
	$stmt->excute(['token' => $token]);
	$res = $stmt->fetch(PDO::FETCH_ASSOC);
	if ($res['uid']){
		return $res['uid'];
	}else{
		$output = array('status' => 1, 'example_text' => 'Token Failed');
		die(json_encode($output));
	}
}

function start_log($token, $time){
	global $db;
	$uid = verify_session($token);
	$stmt = $db->prepare('SELECT `id` FROM `log` WHERE `uid`=:uid, `end_time`=NULL');
	$stmt->execute(['uid' => $uid]);
	$id = $stmt->fetch(PDO::FETCH_ASSOC)['id'];
	if ($id){
		return False;
	}else{
		$stmt = $db->prepare('INSERT INTO `log` (`uid`, `start_time`) VALUES (:uid, :start_time)');
		$stmt->execute(['uid' => $uid,
			$start_time => $time]);
		return True;
	}
}

function end_log($token, $time){
	global $db;
	$uid = verify_session($token);
	$stmt = $db->prepare('SELECT `id` FROM `log` WHERE `uid`=:uid, `end_time`=NULL');
	$stmt->execute(['uid' => $uid]);
	$id = $stmt->fetch(PDO::FETCH_ASSOC)['id'];
	if ($id){
		$stmt = $db->prepare('UPDATE `log` SET `end_time`=:end_time WHERE `id`=:id, `uid`=:uid');
		$stmt->execute(['id' => $id,
			'uid' => $uid,
			'end_time' => $time]);
		return True;
	}else{
		return False;
	}
}

function log_status(){
	global $db;
	$uid = verify_session($token);
	$stmt = $db->prepare('SELECT `id`, `start_time` FROM `log` WHERE `uid`=:uid, `end_time`=NULL');
	$stmt->execute(['uid' => $uid]);
	$res = $stmt->fetch(PDO::FETCH_ASSOC)['id'];
	if ($res['id']){
		return array('status' => 'sailing', 'time' => $start_time);
	}else{
		return array('status' => 'ground');
	}
}
?>
