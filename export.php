<?php
$config = require_once 'config.php';


$sqlconf = $config['mysql'];

global $mysqli;
$mysqli = new mysqli($sqlconf['host'], $sqlconf['username'], $sqlconf['password'], $sqlconf['db']);

/* check connection */
if (mysqli_connect_errno()) {
	printf("Connect failed: %s\n", mysqli_connect_error());
	exit();
}

/* change character set to utf8 */
if (!$mysqli->set_charset("utf8")) {
	printf("Error loading character set utf8: %s\n", $mysqli->error);
}

$query = "select addr, wxuser from eth";

$data = [];
if ($stmt = $mysqli->prepare($query)) {

	/* execute statement */
	$stmt->execute();

	/* bind result variables */
	$stmt->bind_result($addr, $wxuser);

	/* fetch values */
	while ($stmt->fetch()) {
//		echo $addr . $wxuser . PHP_EOL;
		// 处理addr
		if (strlen($addr) < 42){
			continue;
		}
		$addr = trim($addr);
		$addr = substr($addr, 0, 42);
		// 进群即发一个
		$data[$addr] = [
			'wxuser' => $wxuser,
			'count' => 1,
		];

	}
	/* close statement */
	$stmt->close();
}

$sql = "select count(1) as `count` from invites where invite_user = ?";
$stmt2 = $mysqli->prepare($sql);
foreach ($data as $key => $value){
	// 邀请人再发一个
	$stmt2->bind_param('s', $value['wxuser']);
	$stmt2->execute();
	$stmt2->bind_result($count);
	$stmt2->fetch();
	$data[$key]['count'] += $count;
	echo $key . ',' . $data[$key]['count']. PHP_EOL;
}
$stmt2->close();
