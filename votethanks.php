<?PHP

require_once('rust.rcon.php');
require_once('manager.php');
require_once('data.php');

$rcon = RustRcon::GetInstance();
$rcon->Connect('server.ip.here', 27015, 'rcon_pw');
$rcon->SetTimeout(2, 0);

$votes = @file_get_contents('http://toprustservers.com/vote/????');
if ($votes == null || $votes == "")
	return;
preg_match_all('/<tr>.*?<td>(.*?)<td>([0-9]+)<\/td>.*?<td>[0-9]+ [a-zA-Z]+ ago /su', $votes, $matches);

$okay = array();

for ($i = 0; $i < count($matches[1]); $i++)
{
	$name = substr($matches[1][$i], 0, -1);
	$count = $matches[2][$i];

	$player = Data::ReadPlayer($name);

	if ($player == null || !$player->online)
		continue;

	if ($player->votes < $count)
	{
		echo "Received vote #$count from {$player->name}.\n";
		$rcon->Send('say "[color #BBBBBB]Vielen Dank, [color ' . $player->color . ']' . $player->name . '[color #BBBBBB], dass du für uns [color #FF0000]' . $count . '[color #BBBBBB] mal gevotet hast!"');
		if ($count == 1 || $count == 3 || $count == 5)
		{
			$signals = 1;
			if ($count == 3) $signals++;
			if ($count == 5) $signals += 2;
			$rcon->Send('say "[color #BBBBBB]Als Dankeschön bekommst du [color #FF0000]' . $signals . ' [color #BBBBBB]Supply Signals."');
			$okay[$rcon->Send('inv.giveplayer "' . $player->name . '" "Supply Signal" ' . $signals)] = array($player->id, $count);
			echo "Giving {$player->name} $signals signals for voting $count times.\n";
		}
		else
			$okay[$rcon->Send('env.time')] = array($player->id, $count);
	}
}

while (true)
{
	$response = $rcon->internal_read();
	if ($response == null)
		return;
	if (!isset($okay[$response->Id()]))
		continue;
	$id = $okay[$response->Id()][0];
	$count = $okay[$response->Id()][1];
	$player = Data::ReadPlayer($id);

	if (substr($response->Response(), 0, 5) != 'Gave ' && substr($response->Response(), 0, 13) != 'Current Time:')
	{
		var_dump($response->Response());
		echo "Failed to deliver Signal to {$player->name}.\n";
		continue;
	}

	echo "Updating {$player->name} votes from {$player->votes} to $count.\n";
	$player->votes = $count;
	Data::WritePlayer($player);
}
