<?php
date_default_timezone_set('Asia/Taipei');
header('Content-Type: text/html; charset=utf-8');

ini_set('display_errors', 1);

// 取得使用者來源 IP
$sUser_IP = !empty($_SERVER['REMOTE_ADDR']) ? htmlspecialchars($_SERVER['REMOTE_ADDR']) : '';

$aDb = array(
	'dbHost'	=> '10.10.130.30',
	'dbName' 	=> 'mt_db',
	'dbUser'	=> 'gary',
	'dbPassword' 	=> 'jc0926++',
);

require_once('db_ctrl.php');
require_once('set_ctrl.php');
require_once('define.php');

// 建立資料庫連線
try {
	$PdoOptions = array(
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
	);
	$DSN = 'mysql:host='.$aDb['dbHost'].';dbname='.$aDb['dbName'];
	$pdo = new PDO($DSN, $aDb['dbUser'], $aDb['dbPassword'], $PdoOptions);
} catch ( PDOException $e ) {
	echo 'DB Connection failed: ' . $e->getMessage() . PHP_EOL;
	exit;
}

unset($aDb);
?>