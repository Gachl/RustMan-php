<?PHP
$wait = array();

if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off')
{
	header('Location: https://whereitsat.com/rust/moderate.php');
	die();
}

chdir('..');
session_start();
header('Content-Type: text/html; charset=utf-8');

if (isset($_POST['auth']) && $_POST['auth'] == 'super secret and ultra secure password... not.')
	$_SESSION['auth'] = 1;

if (!isset($_SESSION['auth']) || $_SESSION['auth'] != 1)
{
?>
<form action="?" method="POST"><input type="password" name="auth"><input type="submit" value="Login"></form>
<?PHP
	session_write_close();
	return;	
}
session_write_close();
require_once('manager.php');
require_once('data.php');
require_once('ftp.php');
require_once('rust.rcon.seq.php');

RustRcon::GetInstance()->Connect('server.ip.here', 27015, 'rcon_pw');
RustRcon::GetInstance()->SetTimeout(2, 0);

if (isset($_GET['location']))
{
	$location = $_GET['location'];
	$data = Data::ReadPlayer($location);
	$coord = Manager::GetInstance()->GetCoordinates($data->name);
	header('Location: http://rustnuts.com/?x=' . $coord[0] . '&y=' . $coord[1] . '&z=' . $coord[2]);
	die();
}

$rid = RustRcon::GetInstance()->Send('banlistex');
$banlist = "";
while (true)
{
	$response = RustRcon::GetInstance()->Read($rid);
	if ($response != null && $response->ID() == $rid)
	{
		$banlist = $response->Response();
		break;
	}
}

if (isset($_POST['action']) && $_POST['action'] == "teleport")
{
	$id = $_POST['who'];
	$player = null;
	$player = Data::ReadPlayer($id);

	$to = $_POST['to'];
	if ($to < 0)
	{
		switch ($to)
		{
		case -1:
			$target = '6063 383 -3697';
			break;
		case -2:
			$target = '5219 365 -4890';
			break;
		case -3:
			$target = '0 390 0';
			break;
		case -4:
			$target = '-5794 390 4911';
			break;
		case -5:
			$target = '-6328.85 442.71 -7359.26';
			break;
		case -6:
			$target = '6628.27 354.7 -3754.13';
			break;
		case -7:
			$target = '6646 348.9 -4333';
			break;
		case -8:
			$target = '6328.21 358.91 -4665.33';
			break;
		case -9:
			$target = '6117.79 381.56 -4348.65';
			break;
		}
		$wait[] = RustRcon::GetInstance()->Send('teleport.topos "' . $player->name . '" ' . $target);
	}
	else
	{
		$target = Data::ReadPlayer($to);
		$wait[] = RustRcon::GetInstance()->Send('teleport.toplayer "' . $player->name . '" "' . $target->name . '"');
	}
}

if (isset($_POST['action']) && $_POST['action'] == "kickban")
{
	foreach ($_POST as $do => $v)
	{
		if (strpos($do, "_") === false)
			continue;
		$action = substr($do, 0, strpos($do, "_"));
		$id = substr($do, strpos($do, "_") + 1);
		$player = Data::ReadPlayer($id);
		if ($action == "kick")
		{
			$wait[] = RustRcon::GetInstance()->Send('kick "' . $player->name . '"');
			$wait[] = RustRcon::GetInstance()->Send('say "[color #BBBBBB]Kicked player [color #FF0000]' . $player->name . '[color #BBBBBB]."');
		}
		elseif ($action == "ban")
		{
			$wait[] = RustRcon::GetInstance()->Send('banid ' . $id . ' "Banned by moderator"');
			$wait[] = RustRcon::GetInstance()->Send('say "[color #BBBBBB]Banned player [color #FF0000]' . $player->name . '[color #BBBBBB]."');
		}
	}
}

if (isset($_POST['action']) && $_POST['action'] == "armor")
{
	$wait[] = RustRcon::GetInstance()->Send('inv.giveplayer Tankton "Invisible Helmet"');
	$wait[] = RustRcon::GetInstance()->Send('inv.giveplayer Tankton "Invisible Vest"');
	$wait[] = RustRcon::GetInstance()->Send('inv.giveplayer Tankton "Invisible Pants"');
	$wait[] = RustRcon::GetInstance()->Send('inv.giveplayer Tankton "Invisible Boots"');
}

if (count($wait) > 0)
	foreach ($wait as $id)
		while (true)
			if (RustRcon::GetInstance()->Read($id) != null)
				break;

?>

<html>
<head>
<title>Rust Moderator</title>
<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
<style>
a, a * { text-decoration: none; }
table.two tr:nth-child(even) { background: #ddd; }
</style>
</head>
<body>
<form action="?" method="POST">
<input type="hidden" name="action" value="kickban">
<table width="100%" border="1" class="two">
	<tr>
		<td>#</td><td>Player</td><td>Flags</td><td>IP</td><td>Location</td><td>Actions</td>
	</tr>
<?PHP
$num = 1;
$online_players = Data::GetOnlinePlayers();

function sort_online_players($a, $b)
{
	if (strtolower($a['name']) == strtolower($b['name']))
		return 0;
	return strtolower($a['name']) < strtolower($b['name']) ? -1 : 1;
}

uasort($online_players, 'sort_online_players');

foreach ($online_players as $player => $data)
{
?>
	<tr>
		<td><?= $num++ ?></td><td><a href="http://steamcommunity.com/profiles/<?= $data['id'] ?>" target="_blank"><?= htmlspecialchars($data['name']) ?></a></td><td><?= Manager::GetInstance()->IsVIP($data['name']) ?></td><td><?= $data['ip'] ?> (<?= geoip_country_name_by_name($data['ip']) ?>)</td><td><a href="moderate.php?location=<?= $data['id'] ?>" target="_blank">Location</a></td><td><input type="submit" name="kick_<?= $data['id'] ?>" value="Kick"><input type="submit" name="ban_<?= $data['id'] ?>" value="Ban"></td>
	</tr>
<?PHP
}
?>
</table>
</form>
<form action="?" method="POST">
	Teleport <select name="who" value="<?= $_POST['who'] ?>"><?PHP foreach ($online_players as $player => $data) echo '<option value="' . $data['id'] . '"' . ($_POST['who'] == $data['id'] ? ' selected="selected"' : '') . '>' . $player . '</option>' . "\n"; ?></select> to <select name="to" value="<?= $_POST['to'] ?>"><option value="-1"<?= ($_POST['to'] == -1 ? ' selected="selected"' : '') ?>>Small Rad</option><option value="-2"<?= ($_POST['to'] == -2 ? ' selected="selected"' : '') ?>>Big Rad</option><option value="-6"<?= ($_POST['to'] == -6 ? ' selected="selected"' : '') ?>>Tanks</option><option value="-7"<?= ($_POST['to'] == -7 ? ' selected="selected"' : '') ?>>Hangar</option><option value="-8"<?= ($_POST['to'] == -8 ? ' selected="selected"' : '') ?>>Factory</option><option value="-9"<?= ($_POST['to'] == -9 ? ' selected="selected"' : '') ?>>Civ Road</option><option value="-3"<?= ($_POST['to'] == -3 ? ' selected="selected"' : '') ?>>Medi</option><option value="-4"<?= ($_POST['to'] == -4 ? ' selected="selected"' : '') ?>>Orient</option><option value="-5"<?= ($_POST['to'] == -5 ? ' selected="selected"' : '') ?>>Admin</option><?PHP foreach ($online_players as $player => $data) if ($data['online']) echo '<option value="' . $data['id'] . '"' . ($_POST['to'] == $data['id'] ? ' selected="selected"' : '') . '>' . $player . '</option>' . "\n"; ?></select>
<input type="submit" value="Go!"><input type="hidden" name="action" value="teleport">
</form>
<form action="?" method="GET">
<input type="submit" value="Refresh">
</form>
<form action="?" method="POST">
<input type="hidden" name="action" value="armor">
<input type="submit" value="Invisibility for Tankton">
</form>
<pre><?php $banlist = explode("\n", $banlist); foreach ($banlist as $ban)
	{
		$bani = explode(" ", $ban);
		$player = Data::ReadPlayer($bani[1]);
		if ($player == null)
			echo $ban . ' <a href="http://steamcommunity.com/profiles/'.$bani[1].'" target="_blank">N/A</a>' . "\n";
		else
			echo $ban . ' <a href="http://steamcommunity.com/profiles/'.$bani[1].'" target="_blank">' . htmlspecialchars($player->name == "" ? "N/A" : $player->name) . "</a>\n";
	}?></pre>
