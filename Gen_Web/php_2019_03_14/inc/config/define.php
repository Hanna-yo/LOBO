<?php
#CQ9
define('DomainName','http://api.cqgame.games/dev');				#對接網址(整合站)
#define('DomainName','https://api.cqgame.cc');					#對接網址(正式站)
define('CQ9RollIn','rel-rollin-');						#CQ9 RollIn 單號格式(整合站)
#define('CQ9RollIn','pro-rollin-');						#CQ9 RollIn 單號格式(正式站)

define('CQ9PW','CQ9PW123');							#CQ9對接帳號固定密碼
define('CQ9At_Code','9');							#CQ9對接代碼
define('CQ9BackName','@cq9');							#CQ9後綴
define('CQ9NiuNiuGC','AQ01');							#百人牛牛 CQ9遊戲代碼
define('CQ9BackUrl','http://www.cq9gaming.com/');				#CQ9導回網址
#define('CQ9_KEY','T6C7il5BzKg2M9kS');						#CQ9登入API KEY
define('INTERNAL_URL','http://gm1.cdnedge.bluemix.net/Gen_Web/game/');			#內部測試導回網址
define('INTERNAL_JUMP_URL','http://gm1.cdnedge.bluemix.net/Gen_Web/game/guide.php');	#內部測試跳轉頁面(導向對應遊戲)
define('GEN_HALL_URL','http://gm1.cdnedge.bluemix.net/Gen_Web/game/hall/');			#遊戲大廳

#基本匯率
define('BasicExchange',		100);

#log 權限
define('LogPermission',	2);
define('IPLogPermission',	2);
/*
log 說明
Roll_Out => 2:寫入全log
	 => 1:只寫錯誤
Roll_In  => 2:寫入全log
	 => 1:只寫錯誤
各個inc_index  	=> 2:Auth Log
	 	=> 1:不寫 Auth Log
*/

#遊戲登入判斷 ajax 回應 code
$aGameAjaxResCode = array(
	'success'		=> 1,	#成功
	'maintain'		=> 2, #維護中
	'no_permission'	=> 3, #無權限進行遊戲
	'api_no_response'	=> 4, #API無回應
	'token_invalid'	=> 5, #token失效
	'key_invalid'	=> 6, #遊戲 key 失效
);

#RollOut RollIn,單狀態
$aRollStatus = array(
	'-1'	=> '失败',
	'1'	=> '未完成',
	'2'	=> '已完成',
	'3'	=> '重更新成功',
	'4'	=> '重更新失败',
	'5'	=> '重送成功',
	'6'	=> '重送失败',
);

define('now_time',		time());
define('sys_code',		'2018S01Y02S');

define('now_page', 		$_SERVER['SCRIPT_NAME']);

define('user_ip', 		$sUser_IP);
define('tab_lock', 		'FOR UPDATE');

define('adm_key',		'DS_V2_20171022');

define('adm_path',		(dirname(dirname(dirname(__file__)))).'/img');

#全站cookie
define('cookie_close', 		now_time - 3600);
define('cookie_max_time', 	now_time - (60*60));
define('cookie_remember', 	now_time + 60*60*24*15);

#遊戲明細 狀態
$aProcessType = array(
	'0'	=> '待审核',
	'1'	=> '处理完成',
	'2'	=> '拒绝'
);

$status_type = array(
	1 	=> "百家乐",
	3 	=> "百人牛牛",
);

#CQ9 細帳
$aCQ9_Detail_Type = array(
	1 	=> false,
	3 	=> true,
);

#有AI遊戲
$aYesAI = array(
	1 	=> true,	#百家樂
	3 	=> true,	#百人牛牛
);

#對戰遊戲
$aYesBattle = array(
	4 	=> true,	#德州撲克
);

# API Err回傳
$aErrorResult = array(
	'Success'				=> 0,	#成功
	
	'NullParameter'			=> 100, #無參數
	'NullAgentCode'			=> 101, #無 AgentCode
	'NullActType'			=> 102, #無 ActType
	'NullParams'			=> 103, #無 Params
	'NullSign'				=> 104, #無 Sign
	'DataIncomplete'			=> 105, #資料不完整

	'ActTypeError'			=> 200, # ActType錯誤
	'NoJson'				=> 201, #不是JSON
	'SignError'				=> 202, #驗證失敗

	'RepeatAgentCode'			=> 300, # AgentCode重複
	'RepeatAgentString'		=> 301, #後綴重複
	'NoIp'				=> 302, # IP不符
	'AgentCodeFormalError'		=> 303, # AgentCode格式錯誤
	'AgentStringError'		=> 304, #後綴格式錯誤
	'AgentNameFormalError'		=> 305, #對接方名稱字數超過15
	'InsAgentFailure'			=> 306, #新增對接方資料失敗
	'InsufficientPermissions'	=> 307, #權限不足
	'AgentCodeError'			=> 308, # AgentCode有誤
	'AgentTimeError'			=> 309, #時間參數有誤
	
	'AccountExist'			=> 400, #帳號存在
	'AccountNotExist'			=> 401, #帳號不存在
	'InsAccountFailure'		=> 402, #新增帳號失敗
	'AccountFormalError'		=> 403, #帳號格式錯誤

	'PointFormalZero'			=> 500, #轉點點數小於0
	'PointFormalError'		=> 501, #轉點點數非整數
	'InsufficientBalance'		=> 502, #剩餘點數不足
	'noLogOut'				=> 503, #尚未登出
	'NoGameCode'			=> 504, #無此遊戲
	'PlayingGame'			=> 505, #玩家遊戲中
	'TokenNotExist'			=> 506, #無此Token
	'TokenInvalid'			=> 507, #Token失效
	'LoggedOut'				=> 508, #已登出

	'NoGameData'			=> 600, #此遊戲帳無資料
	'TimeFormalError'			=> 601, #時間格式有誤
	'TimeError'				=> 602, #超過可查詢時間

	'NoCurrency'			=> 700, #無此幣別
);
?>