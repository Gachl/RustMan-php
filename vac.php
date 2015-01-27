<?PHP

require_once('data.php');
require_once('manager.php');
require_once('rust.rcon.php');
require_once('irc.php');

$players = Data::ReadPlayers();
$checks = Data::ReadVACCheck();

if (count($checks) == 0)
	return;
echo "Checking " . count($checks) . " users...\n";

$bans = array();
foreach ($checks as $id)
{
	$data = Data::ReadPlayer($id);
	$player = $data->name;
	
	$vac = VAC::BanTime($data->id);
	if ($vac > 0 && $vac < 366)
	{
		echo "Player $player ({$data->id}) has a VAC ban of $vac days.\n";
		IRC::WriteToIRC("#VAC-BAN", "Player $player ({$data->id}) has VAC bans on record $vac days ago!");
		$bans[$data->id] = $player;
	}
	else
	{
		echo "Player $player has $vac VAC days.\n";
	}
}

$rcon = RustRcon::GetInstance();
$rcon->Connect('server.ip.here', 27015, 'rcon_pw');
$rcon->SetTimeout(2, 0);

if (count($bans) > 0)
{
	foreach ($bans as $id => $name)
	{
		echo "Banning $name.\n";
		$rcon->Send('say "[color #BBBBBB]Banning player [color #FF0000]' . $name . '[color #BBBBBB] for VAC bans on record."');
		$rcon->Send('banid ' . $id . ' "VAC ban on record."');
		$rcon->Send('kick "' . $name . '"');
		echo 'Command: <kick "' . $name . '">' . "\n";
	}
}

while (true)
{
	$response = $rcon->internal_read();
	if ($response == null)
		return;
}

class VAC
{
	public static function BanTime($id)
	{
		$vac = @file_get_contents("http://steamcommunity.com/profiles/" . $id);
		if ($vac == null)
		{
			sleep(1);
			return self::BanTime($id);
		}
		if (preg_match_all('/([0-9]+) VAC ban\(s\) on record[^0-9]*([0-9]+) day\(s\) since last ban/su', $vac, $matches))
			return intval($matches[2][0]);
		return 0;
	}
}
