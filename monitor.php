<?PHP

require_once('language.php');
require_once('rust.rcon.seq.php');
require_once('cron.php');
require_once('commands.php');
require_once('data.php');
require_once('manager.php');
require_once('irc.php');
require_once('ftp.php');
require_once('user.php');

$waiting_replies = array();
function WaitForID($id, $callback, $target = null, $param = null)
{
	global $waiting_replies;
	$waiting_replies[$id] = array('target' => $target, 'callback' => $callback, 'param' => $param);
}

$cron = Cron::GetInstance();
$commands = Commands::GetInstance();
$rcon = RustRcon::GetInstance();
$manager = Manager::GetInstance();

echo "Connecting\n";
$rcon->Connect('server_ip_here', 27015, 'rcon_pw_here');
echo "Connected\n";

while (true)
{
	$response = null;
	if (count($waiting_replies) > 0)
	{
		foreach ($waiting_replies as $id => $target)
		{
			$response = $rcon->Read($id);
			if ($response != null)
				break;
		}
	}
	if ($response == null)
		$response = $rcon->Read();
	if ($response != null && substr($response->Response(), 0, 8) != 'hostname' && substr($response->Response(), 0, 2) != '1 ')
		Data::WriteLog($response);

	if ($response != null && $response->ID() !== 1)
	{
		// Handle asynchronous requests
		if (array_key_exists($response->ID(), $waiting_replies))
		{
			$callback = $waiting_replies[$response->ID()]['callback'];
			// Call to callback
			if ($waiting_replies[$response->ID()]['target'] == null)
			{
				if ($waiting_replies[$response->ID()]['param'] == null)
					$callback($response);
				else
					$callback($response, $waiting_replies[$response->ID()]['param']);
			}
			else
			{
				if ($waiting_replies[$response->ID()]['param'] == null)
					call_user_func(array($waiting_replies[$response->ID()]['target'], $callback), $response);
				else
					call_user_func(array($waiting_replies[$response->ID()]['target'], $callback), $response, $waiting_replies[$response->ID()]['param']);
			}
			unset($waiting_replies[$response->ID()]);
			/*if (substr($response->Response(), 0, 8) == 'hostname' && $response->Id() > 1)
				$manager->ParsePlayers($response); // Update player stats*/
		}

		$commands->Run($response);

		if (substr($response->Response(), -13) == ' has suicided')
		{
			$suicide = Data::ReadPlayer(substr($response->Response(), 0, -13));
			RustRcon::GetInstance()->Send('say "[color ' . $suicide->color . ']' . $suicide->name . ' [color #BBBBBB]hat sich selbst umgebracht."');
		}
	}

	$cron->Run();
	foreach (IRC::ReadFromIRC() as $irc_line)
	{
		Data::WriteChatLine("[IRC] " . $irc_line['user'], $irc_line['text']);
		$rcon->Send('say "[color #FF0000][IRC] ' . $irc_line['user'] . '[color #BBBBBB]: ' . str_replace('"', '\\"', $irc_line['text']) . '"');
	}
}
