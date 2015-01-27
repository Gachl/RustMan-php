<?PHP
$page = isset($_GET['page']) ? $_GET['page'] : 'index';
header('Content-Type: text/html; charset=utf-8');
chdir('..');
require_once('data.php');
require_once('manager.php');
$players = Data::ReadPlayers();
$colors = array("#FF6600",
	"#66FF33",
	"#3399FF",
	"#FFFF00",
	"#FF3300",
	"#CC66FF",
	"#66FFFF");
$vipcol = array('bronze' => '#CD7F32', 'silver' => '#C0C0C0', 'gold' => '#EAC117', 'platinum' => '#E5E4E2');
$c = 0;
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Rust Stats</title>
		<meta charset="utf-8">
		<style type="text/css">
		<!--
		body
		{
			font-family: verdana, sans-serif;
			font-size: 8pt;
			background-color: #000;
			color: #ccc;
		}

		.content
		{
			text-align: left;
			background-color: #444;
			width: 1300px;
			padding: 10px;
		}

		h1
		{
			color: #DF2E23;
		}

		a.nick, a.nickc
		{
			text-decoration: none;
		}

		a, a:visited
		{
			text-shadow: 1px 1px 2px #000;
			color: #DF2E23;
		}

		a:hover
		{
			text-shadow: 0px 0px 2px #000;
		}
	
		a:hover, a:active
		{
			color: #F66 !important;
		}
		
		a.nickc
		{
			color: #FFF;
		}

		table tr td
		{
			max-width: 500px;
		}

		ol.top li
		{
			width: 33%;
			float: left;
		}

		a.btn
		{
			border: 1px solid #000;
			background-color: #f00;
			color: #000;
			padding: 6px;
			text-decoration: none;
		}

		-->
		</style>
	</head>
	<body>
		<center>
			<img src="rust.png" alt="Rust" width="550" height="208">
			<h1>net.connect ip:27000</h1>
			<a href="http://webchat.quakenet.org/?channels=BiG%7Crust%2CBiG%7C" target="_blank">Chat with admins</a><br>
			<a href="http://www.reddit.com/r/playrustservers/" target="_blank">Discuss this server on Reddit</a><br>
			<a href="http://facepunch.com/" target="_blank">Discuss this server on Facepunch</a><br>
			<span style="position: relative; left: 120px;"><a href="http://toprustservers.com/" target="_blank">Vote on Top Rust Servers</a></span>
			<div class="content">
			<center>
			<a class="btn" href="?page=index">Commands &amp; info</a> &nbsp; <a class="btn" href="?page=activity">Activity &amp; chat log</a> &nbsp; <a class="btn" href="chat.html" target="_blank">Chat statistics</a>
			</center><br><br>
<?PHP
if ($page == 'index')
{
?>
				<h1>Info</h1>
<?PHP
	$info = file_get_contents('info.html');
	if (!preg_match_all('/.*\<div class="md"\><p><strong>\[DE\/EU\] .*?<\/sup><\/p>(.*)<p>I am open for ideas and suggestions/su', $info, $matches))
	{
		echo "Couldn't fetch info :(";
		return;
	}
$info = $matches[1][0];


$info = preg_replace('/<p><strong><sup>(.*)<\/sup><\/strong><\/p>/u', '<h2>\1</h2>', $info);
$info = preg_replace('/<p><strong>(.*)<\/strong><\/p>/u', '<h1>\1</h1>', $info);
echo $info;
} elseif ($page == 'activity') {
?>
				<h1>Activity</h1>
				Active players from <?= date('d.m.Y H:i', strtotime("-8 hours")) ?> to <?= date('d.m.Y H:i', time()) ?>.<br>
				<img src="makegraph.php" alt="graph">
			<table width="100%">
				 <tr>
					 <td valign="top">
						 <h1>Chat log</h1>
	 <?= date('d.m.Y H:i', strtotime("-4 hours")) ?> to <?= date('d.m.Y H:i') ?>.
						 <pre>
<?php
	$log = Data::ReadChatLog(250);
	$log = array_reverse($log);
	//$log = file('data/log/chat_log.' . date('d-m-Y') . '.txt');
	foreach ($log as $line)
	{
		//$line = explode("\t", $line);
//		if (strtotime($line[0]) > strtotime("-4 hours"))
		{
			if (isset($players[$line['user']]))
			{
				if (!isset($players[$line['user']]['ccolor']))
				{
					if ($c >= count($colors))
						$c = 0;
					$players[$line['user']]['ccolor'] = $colors[$c++];
				}
				$line['text'] = preg_replace('/\[color #....?.?.?\]/', '', $line['text']);
				echo "[" . date("H:i:s", (intval($line['time']) - 621356040000000000) / 10000000) . "] " .
					'<a class="nickc" style="color: ' . $players[$line['user']]['ccolor'] . ';" href="http://steamcommunity.com/profiles/' . $players[$line['user']]['id'] . '" target="_blank">' . htmlspecialchars($line['user']) . "</a>: " . htmlspecialchars($line['text']) . "\n";
			}
			else
			{
				/*if ($line[1] == "#JOIN" || $line[1] == "#QUIT")
					$line[1] = $line[1] == "#JOIN" ? "&gt;" : "&lt;";
				else*/
					$line[1] = htmlspecialchars($line[1]);
				echo "[" . $line[0] . "] " . 
					$line[1] . ": " . htmlspecialchars($line[2]);
			}
		}
	}
?>
						 </pre>
					 </td>
					 <td valign="top">
						 <h1>Current players</h1>
						 <pre>
<?php $i = 1; foreach ($players as $data) if ($data['online']) echo $i++ . '. <a class="nick" href="http://steamcommunity.com/profiles/' . $data['id'] . '" target="_blank">' . htmlspecialchars($data['name']) . "</a>" . (Manager::GetInstance()->IsVIP($data['name']) !== false ? ' <span style="color: ' . $vipcol[Manager::GetInstance()->IsVIP($data['name'])] . ';">*VIP*</span>' : '') . "\n"; ?>
						 </pre>
					 </td>
				 </tr>
			 </table>
<?PHP
}
	$filter = 0;
	foreach ($players as $name => $data)
	{
		//		echo "{$data['name']}: " . date("d.m. H:i", $data['last']) . "<br>\n";
		if ((intval($data['last']) - 621356040000000000) / 10000000 < strtotime('-5 days'))
			$filter++;
	}
?>
				<h1>Top 100 players</h1>
				Not showing <?= (count($players) - 100) - $filter < 0 ? 0 : (count($players) - 100) - $filter ?> players on ranks 101 to <?= count($players) ?>. Also missing are <?= $filter ?> inactive players (not online since 5 days).
				<ol class="top">
<?php
	$order = $players;
	function sort_vip($a, $b)
	{
		if ($a['time'] == $b['time'])
			return 0;
		return $a['time'] > $b['time'] ? -1 : 1;
	}
	usort($order, "sort_vip");

	$i = 0;
	foreach ($order as $data)
	{
		if ((intval($data['last']) - 621356040000000000) / 10000000 < strtotime('-5 days'))
			continue;
		echo '<li><a class="nick" href="http://steamcommunity.com/profiles/' . $data['id'] . '" target="_blank">' . htmlspecialchars($data['name']) . "</a>" . (Manager::GetInstance()->IsVIP($data['name']) !== false ? ' <span style="color: ' . $vipcol[Manager::GetInstance()->IsVIP($data['name'])] . ';">*' . Ucfirst(Manager::GetInstance()->IsVIP($data['name'])) . ' VIP*</span>' : '') . ' <span style="color: #E3E4FA;">' . round($data['time'] / (60 * 60), 2) .  " hours</span></li>\n";
		if (++$i >= 100)
			break;
	}
?>
				</ol>
				<br clear="all">
			</div>
		</center>
	</body>
</html>
