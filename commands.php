<?PHP

class Commands
{
	private static $instance = null;
	public static function GetInstance()
	{
		if (self::$instance == null)
			self::$instance = new Commands();
		return self::$instance;
	}

	private $commands;

	private function __construct()
	{
		$this->commands = array(
			'report' => 'cmd_report',
			'melde' => 'cmd_report',
			'admin' => 'cmd_report',
			'abuse' => 'cmd_abuse',

			'time' => 'cmd_time',
			'zeit' => 'cmd_time',
			
			'log' => 'cmd_log',
			'chat' => 'cmd_log',
			'chatlog' => 'cmd_log',
			'history' => 'cmd_log',
			
			'suggest' => 'cmd_suggest',
			'vorschlag' => 'cmd_suggest',

			'help' => 'cmd_help',
			'command' => 'cmd_help',
			'commands' => 'cmd_help',
			'hilfe' => 'cmd_help',
			'befehl' => 'cmd_help',
			'befehle' => 'cmd_help',

			'remove' => 'cmd_remove',
			'delete' => 'cmd_remove',
			
			'kit' => 'cmd_kit',
			'starter' => 'cmd_kit',
			'kits' => 'cmd_kit',
			
			'status' => 'cmd_status',
			'list' => 'cmd_status',
			'players' => 'cmd_status',
			'count' => 'cmd_status',
			'admins' => 'cmd_status',

			'auth' => 'cmd_auth',

			'watch' => 'cmd_watch',

			'who' => 'cmd_who',
			'wer' => 'cmd_who',
			'vip' => 'cmd_who',
			'swp' => 'cmd_who',
			'vips' => 'cmd_who',
			'find' => 'cmd_who',
			'finde' => 'cmd_who',
			'last' => 'cmd_who',
			'seen' => 'cmd_who',
			'gesehen' => 'cmd_who',

			'lost' => 'cmd_lost',
			'stuck' => 'cmd_lost',
			'verlaufen' => 'cmd_lost',

			'tp' => 'cmd_tp',
			'tpr' => 'cmd_tp_request',
			'tpa' => 'cmd_tp_accept',

			'ping' => 'cmd_status',

			'msg' => 'cmd_msg',
			'message' => 'cmd_msg',

			'suicide' => 'cmd_suicide',
			'kill' => 'cmd_suicide',

			'votekick' => 'cmd_votekick',
			'kick' => 'cmd_votekick',
			'ja' => 'cmd_yes',
			'nein' => 'cmd_no',
			'no' => 'cmd_no',
			'yes' => 'cmd_yes',

			'pvp' => 'cmd_pvp',
			'arena' => 'cmd_pvp',

			'state' => 'cmd_state',
			'staat' => 'cmd_state',
			'town' => 'cmd_state',

			'stats' => 'cmd_services',
			'teamspeak' => 'cmd_services',
			'mumble' => 'cmd_services',
			'homepage' => 'cmd_services',
			'hp' => 'cmd_services',
			'vote' => 'cmd_services',
			'link' => 'cmd_link',

			'location' => 'cmd_location',
			'position' => 'cmd_location',
			'coordinates' => 'cmd_location',
			'coords' => 'cmd_location',
			'coord' => 'cmd_location',

			'gcreate' => 'cmd_group_create',
			'ginvite' => 'cmd_group_invite',
			'groups' => 'cmd_groups',
			'group' => 'cmd_groups',
			'gruppe' => 'cmd_groups',
			'gruppen' => 'cmd_groups',
			'gquit' => 'cmd_group_quit',
			'gaccept' => 'cmd_group_accept',
			'gkick' => 'cmd_group_kick',

			'good' => 'cmd_karma',
			'happy' => 'cmd_karma',
			'bad' => 'cmd_karma',
			'made' => 'cmd_karma',
			'karma' => 'cmd_karma',

			'quit' => 'cmd_quit',

			'drop' => 'cmd_airdrop',
			'airdrop' => 'cmd_airdrop',

			'rules' => 'cmd_rules',
			'regeln' => 'cmd_rules'

/*			'para' => 'cmd_parachute',
			'chute' => 'cmd_parachute',
			'schirm' => 'cmd_parachute',
			'fallschirm' => 'cmd_parachute',
			'parachute' => 'cmd_parachute'
*/
		);
	}

	public function Run($command)
	{
		// Handle chat messages
		if (preg_match('/^\\[CHAT\\] "(.*)":"(.*)"$/', $command->Response(), $matches))
		{
			if (preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $command->Response()))
			{
				$user = $matches[1]; //str_replace('"', '\\"', $matches[1]);
				RustRcon::GetInstance()->Send('kick "' . $user . '"');
				RustRcon::GetInstance()->Send('say "[color #BBBBBB]Wir mögen hier keine Spammer."');
				return;
			}

			if (strpos(strtolower($command->Response()), "admin") !== false)
				RustRcon::GetInstance()->Send('say "[color #BBBBBB]Um einen Admin zu kontaktieren, verwende [color#FF0000]/report Deine Problembeschreibung hier[color#BBBBBB]."');

			IRC::WriteToIRC($matches[1], $matches[2]);
			
			// Command handling
			if (in_array($matches[2][0], array('/', '!', '?')))
			{
				$user = $matches[1]; //str_replace('"', '\\"', $matches[1]);

				$command = substr($matches[2], 1, strpos($matches[2], ' ') - 1);
				$params = array();
				$param = "";
				if (strpos($matches[2], ' ') === false)
					$command = substr($matches[2], 1); // No parameters, only command
				else
				{
					$param = substr($matches[2], strpos($matches[2], ' ') + 1);
					$params = explode(' ', $param); // Parameters
				}
				$command = strtolower($command);
				while (strlen($command) > 0 && in_array($command[0], array('/', '!', '?')))
					$command = substr($command, 1);

				if ($command == "")
					return;

				echo "Received command $command from player $user.\n";

				if (array_key_exists($command, $this->commands))
				{
					$fn = $this->commands[$command];
					$this->$fn($command, $user, $param, $params);
				}
			}
			else
				Data::WriteChatLine($matches[1], $matches[2]);
		}
	}

	private function cmd_report($command, $user, $param, $params)
	{
		if ($param == "")
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('reportw') . '"');
			RustRcon::GetInstance()->Send('say "[color #BBBBBB]Sprich mit Admins im Privaten: [color #FF0000]#BiG| [color #BBBBBB]auf [color #FF0000]http://webchat.quakenet.org"');
			return;
		}

		Data::WriteReport("$user: $param");
		RustRcon::GetInstance()->Send('say "' . Language::Text('report') . '"');
	}

	private function cmd_time($command, $user, $param, $params)
	{
		WaitForID(RustRcon::GetInstance()->Send('env.time'), 'cmd_time_response', $this);
	}

	public function cmd_time_response($response)
	{
		$time = floor(floatval(substr($response->Response(), 14)));
		RustRcon::GetInstance()->Send('say "' . Language::Text('dtime', array('[color #FF0000]' . date('H:i:s'), '[color #00FF00]' . $time . ':' . str_pad(floor((floatval(substr($response->Response(), 14)) - $time) * 60), 2, "0", STR_PAD_LEFT))) . '"');
	}

	private function cmd_log($command, $user, $param, $params)
	{
		if (Manager::GetInstance()->CommandTime("global", "log", 10) > 0)
			return;
		$rows = intval($param);
		if ($rows <= 0 || $rows > 6)
			$rows = 6;
		$players = Data::ReadPlayers();
		$log = Data::ReadChatLog($rows);
		foreach ($log as $line)
		{
			if ($line['user'] == '#JOIN' || $line['user'] == '#QUIT')
				continue;
			$color = "#FF0000";
			if (substr($line['user'], 0, 6) != '[IRC] ')
				$color = $players[$line['user']]['color'];
			RustRcon::GetInstance()->Send('say "[color ' . $color . ']' . $line['user'] . '[color #BBBBBB]: ' . $line['text'] . '"');
		}
	}

	private function cmd_suggest($command, $user, $param, $params)
	{
		if ($param == "")
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('suggestw') . '"');
			return;
		}

		$player = Data::ReadPlayer($user);
		RustRcon::GetInstance()->Send('say "' . Language::Text('suggest', array('[color ' . $player->color . ']' . $player->name)) . '"');
		Data::WriteSuggestion($param);
	}

	private function cmd_help($command, $user, $param, $params)
	{
		$rcon = RustRcon::GetInstance();

		if ($command == 'hilfe' || $command == 'befehl' || $command == 'befehle')
		{
			$rcon->Send('say "' . Language::Text('help1') . '"');
			$rcon->Send('say "' . Language::Text('help2') . '"');
			$rcon->Send('say "' . Language::Text('help3') . '"');
			$rcon->Send('say "' . Language::Text('help4') . '"');
			$rcon->Send('say "' . Language::Text('help5') . '"');
		}
		else
		{
			$rcon->Send('say "[color #FF0000]kit[color #BBBBBB]: Receive a Rock, Torch, two Bandages and Chocolate."');
			$rcon->Send('say "[color #FF0000]lost[color #BBBBBB]: Teleport to a known position when you get stuck or lost. May kill you."');
			$rcon->Send('say "[color #FF0000]count[color #BBBBBB]: Show the count of all connected players."');
			$rcon->Send('say "[color #FF0000]report[color #BBBBBB]: Send a report to an admin (shortest response time)."');
			$rcon->Send('say "[color #BBBBBB]For a complete list of all available commands, [color #FF0000]visit http://bloodisgood.org/rust"');
		}
	}

	private function cmd_kit($command, $user, $param, $params)
	{
		$rcon = RustRcon::GetInstance();
		$player = Data::ReadPlayer($user);

		$auth = Manager::GetInstance()->IsAuthed($user);
		if ($param != "" && $auth !== false)
		{
			switch (strtolower($params[0]))
			{
			case "armor":
				$rcon->Send('inv.giveplayer "' . $user . '" "Invisible Helmet"');
				$rcon->Send('inv.giveplayer "' . $user . '" "Invisible Vest"');
				$rcon->Send('inv.giveplayer "' . $user . '" "Invisible Pants"');
				$rcon->Send('inv.giveplayer "' . $user . '" "Invisible Boots"');
				break;
			case "signal":
				$rcon->Send('inv.giveplayer "' . $user . '" "Flare" ' . (isset($params[1]) ? $params[1] : 10));
				break;
			case "supply":
				$rcon->Send('inv.giveplayer "' . $user . '" "Supply Signal" ' . (isset($params[1]) ? $params[1] : 1));
				break;
			case "death":
				$rcon->Send('inv.giveplayer "' . $user . '" "Bolt Action Rifle" 1 1 5');
				$rcon->Send('inv.giveplayer "' . $user . '" "M4" 1 1 5');
				$rcon->Send('inv.giveplayer "' . $user . '" "Shotgun" 1 1 5');
				$rcon->Send('inv.giveplayer "' . $user . '" "556 Ammo" 250');
				$rcon->Send('inv.giveplayer "' . $user . '" "Shotgun Shells" 250');
				$rcon->Send('inv.giveplayer "' . $user . '" "Flashlight Mod" 3');
				$rcon->Send('inv.giveplayer "' . $user . '" "Silencer" 3');
				$rcon->Send('inv.giveplayer "' . $user . '" "Holo sight" 3');
				break;
			case "uber":
				$rcon->Send('inv.giveplayer "' . $user . '" "Uber Hatchet"');
				$rcon->Send('inv.giveplayer "' . $user . '" "Uber Hunting Bow"');
				$rcon->Send('inv.giveplayer "' . $user . '" "Arrow" 20"');
				break;
			case "boom":
				$rcon->Send('inv.giveplayer "' . $user . '" "Explosive Charge" ' . (isset($params[1]) ? $params[1] : 1));
				break;
			default:
				$rcon->Send('say "' . Language::Text('kitw', array('[color #FF0000]' . ucfirst($params[0]))) . '"');
				return;
			}
			$rcon->Send('say "' . Language::Text('kitt', array('[color #FF0000]' . ucfirst($params[0]), $user)) . '"');

			return;
		}

		$is_vip = Manager::GetInstance()->IsVIP($user);

		$cmd_time = Manager::GetInstance()->CommandTime($user, "kit", $is_vip !== false ? 600 : 10);
		if ($cmd_time > 0)
		{
			if ($is_vip)
				$cmd_time = ceil($cmd_time/60);
			$rcon->Send('say "' . Language::Text('cmdtime', array('[color ' . $player->color . ']' . $player->name, '[color #FF0000]' . $cmd_time, ($is_vip ? 'Minuten' : 'Sekunden'))) . '"');
			return;
		}

		if ($is_vip !== false)
		{
			$rcon->Send('inv.giveplayer "' . $user . '" "Chocolate Bar"');
		}
		else
		{
			$rcon->Send('inv.giveplayer "' . $user . '" "Rock"');
			$rcon->Send('inv.giveplayer "' . $user . '" "Torch"');
			$rcon->Send('inv.giveplayer "' . $user . '" "Bandage" 2');
			if (Manager::GetInstance()->CommandTime($user, "kit_med", 3 * 60) <= 0)
				$rcon->Send('inv.giveplayer "' . $user . '" "Small Medkit"');
			$rcon->Send('inv.giveplayer "' . $user . '" "Chocolate Bar"');
		}
		$rcon->Send('say "' . Language::Text('kit', array('[color ' . $player->color . ']' . $player->name)) . '"');
		echo "Delivered kit to $user.\n";
	}

	private function cmd_status($command, $user, $param, $params)
	{
		if (in_array($command, array("list", "players", "status")))
		{
			RustRcon::GetInstance()->Send('say "[color #BBBBBB]Wegen großer Nachfrage wurde diese Funktion [color #FF0000]deaktiviert[color #BBBBBB]."');
			return;
		}

		if ($command != "ping")
		if (Manager::GetInstance()->CommandTime("global\001timer", $command, 20) > 0)
			return;

		$callback = 'cmd_status_response';
		if ($command == 'count')
			$callback = 'cmd_count_response';
		elseif ($command == 'admins')
			$callback = 'cmd_admins_response';
		elseif ($command == 'ping')
			$callback = 'cmd_ping_response';
		WaitForID(RustRcon::GetInstance()->Send('status'), $callback, $this, $command == 'ping' ? array($user, $param) : $user);
	}

	public function cmd_ping_response($response, $param)
	{
		$players = Manager::GetInstance()->ParsePlayers($response);
		$who = $param[0];

		if ($param[1] != "")
			$who = $param[1];
		
		$player = Manager::GetInstance()->ClosestMatch($who);

		if (!isset($players[$player]))
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('whow', array($who)) . '"');
			return;
		}

		$player = Data::ReadPlayer($player);

		RustRcon::GetInstance()->Send('say "' . Language::Text('ping', array('[color ' . $player->color . ']' . $player->name, '[color #FF0000]' . $players[$player->name]['ping'])) . '"');
	}

	public function cmd_status_response($response)
	{
		$players = Manager::GetInstance()->ParsePlayers($response);

		if (count($players) == 0)
			return;

		echo "Explaining " . count($players) . " players.\n";

		if (count($players) == 0)
			RustRcon::GetInstance()->Send('say "' . Language::Text('noone') . '"');
		elseif (count($players) == 1)
			RustRcon::GetInstance()->Send('say "' . Language::Text('onlyone') . '"');
		else
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('someonel', array('[color #FF0000]' . Manager::GetInstance()->NumToStr(count($players)))) . '"');

			$playerdata = Data::ReadPlayers();
			$player_string = "";
			foreach (array_keys($players) as $player)
			{
				if (strlen($player_string . '[color ' . $playerdata[$player]['color'] . ']' . $player . "[color #BBBBBB],") > 200)
				{
					RustRcon::GetInstance()->Send('say "' . $player_string . '"');
					$player_string = '[color ' . $playerdata[$player]['color'] . ']' . $player . "[color #BBBBBB], ";
				}
				else
					$player_string .= '[color ' . $playerdata[$player]['color'] . ']' . $player . "[color #BBBBBB], ";
			}
			if (substr($player_string, -2) == ", ")
				RustRcon::GetInstance()->Send('say "' . substr($player_string, 0, -2) . '"');
		}
	}

	public function cmd_count_response($response)
	{
		$players = Manager::GetInstance()->ParsePlayers($response);

		if (count($players) == 0)
			return;

		echo "Explaining " . count($players) . " players.\n";

		if (count($players) == 0)
			RustRcon::GetInstance()->Send('say "' . Language::Text('noone') . '"');
		elseif (count($players) == 1)
			RustRcon::GetInstance()->Send('say "' . Language::Text('onlyone') . '"');
		else
			RustRcon::GetInstance()->Send('say "' . Language::Text('someone', array('[color #FF0000]' . Manager::GetInstance()->NumToStr(count($players)))) . '"');
	}

	public function cmd_admins_response($response)
	{
		$players = Manager::GetInstance()->ParsePlayers($response);

		if (count($players) == 0)
			return;

		if (in_array('BiG|Raven', array_keys($players)))
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('admins', array('[color #FF0000]ein')) . '"');
			RustRcon::GetInstance()->Send('say "[color #BBBBBB]BiG|Raven"');
		}
		else
			RustRcon::GetInstance()->Send('say "' . Language::Text('adminsw') . '"');
	}

	private function cmd_auth($command, $user, $param, $params)
	{
		if (Manager::GetInstance()->IsAuthed($user) !== false)
			Manager::GetInstance()->Deauth($user);
		else
			Manager::GetInstance()->Auth($user);
	}

	private function cmd_who($command, $user, $param, $params)
	{
		$players = Data::ReadPlayers();
		if ($command == "vips")
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('vips') . '"');

			$player_string = "";
			foreach ($players as $player => $data)
			{
				if ($data['online'])
					if (Manager::GetInstance()->IsVIP($player) !== false)
					{
						if (strlen($player_string . '[color ' . $data['color'] . ']' . $player . "[color #BBBBBB],") > 170)
						{
							RustRcon::GetInstance()->Send('say "' . $player_string . '"');
							$player_string = '[color ' . $data['color'] . ']' . $player . "[color #BBBBBB], ";
						}
						else
							$player_string .= '[color ' . $data['color'] . ']' . $player . "[color #BBBBBB], ";
					}
			}
			if (substr($player_string, -2) == ", ")
				RustRcon::GetInstance()->Send('say "' . substr($player_string, 0, -2) . '"');
			return;
		}

		$who = $user;

		if ($param != "")
			$who = $param;
		
		$player = Manager::GetInstance()->ClosestMatch($who);


		if (!isset($players[$player]))
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('whow', array($who)) . '"');
			return;
		}

		$groups = Data::ReadGroups();
		$player = $players[$player];
		$country = geoip_country_name_by_name($player['ip']);
		$is_vip = Manager::GetInstance()->IsVIP($player['name']);
		$group = null;
		foreach ($groups as $name => $members)
			if (in_array($player['id'], $members))
			{
				$group = $name;
				break;
			}
		RustRcon::GetInstance()->Send('say "' . Language::Text('who', array('[color ' . $player['color'] . ']' . $player['name'], '[color #FF0000]' . $country, ($player['online'] ? '[color #00FF00]' : '[color #FF0000]nicht ') . 'online', '[color #FF0000]' . round($player['time'] / (60 * 60), 2), ($is_vip !== false ? Ucfirst($is_vip) : 'kein') . ' [color ' . $player['color'] . ']VIP')) . '"');
		if ($group != null)
			RustRcon::GetInstance()->Send('say "' . Language::Text('member', array('[color ' . $player['color'] . ']' . $player['name'], '[color #FF0000]' . $group)) . '"');
		if (!$player['online'])
			RustRcon::GetInstance()->Send('say "' . Language::Text('last', array('[color ' . $player['color'] . ']' . $player['name'], '[color #FF0000]' . date('d.m. H:i', $player['last']))) . '"');
	}

	private function timeoutLost()
	{
		$nope = array();
		foreach ($this->lost_sure as $user => $time)
			if ($time < time())
				$nope[] = $user;
		foreach ($nope as $user)
		{
			Manager::GetInstance()->CommandTime($user, "lost", -10, true); // Reset lost timer
			unset($this->lost_sure[$user]);
		}
	}

	private $lost_sure = array();
	private function cmd_lost($command, $user, $param, $params)
	{
		$lost_time2 = Manager::GetInstance()->CommandTime($user, "lost2", 30);
		if ($lost_time2 > 0 && in_array($user, array_keys($this->lost_sure)))
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('wait', array('[color #FF0000]' . $lost_time2)) . '"');
			return;
		}

		$players = Data::ReadPlayers();
		if (in_array($user, array_keys($this->lost_sure)))
		{
			if (Manager::GetInstance()->IsVIP($user) !== false)
				RustRcon::GetInstance()->Send('teleport.topos "' . $user . '" "5219" "365" "-4890"'); // Teleport to Big Rad
			else
				RustRcon::GetInstance()->Send('teleport.topos "' . $user . '" 6628.27 354.7 -3754.13'); // Teleport to Tanks
				//RustRcon::GetInstance()->Send('teleport.topos "' . $user . '" "6063" "383" "-3697"'); // Teleport to Small Rad
			RustRcon::GetInstance()->Send('say "' . Language::Text('rescued', array('[color ' . $players[$user]['color'] . ']' . $user)) . '"');

			unset($this->lost_sure[$user]);

			return;
		}

		$lost_time1 = Manager::GetInstance()->CommandTime($user, "lost", 60 * 60);
		if ($lost_time1 > 0) // Once every hour
		{
			$lost_time1 = ceil($lost_time1/60);
			RustRcon::GetInstance()->Send('say "' . Language::Text('cmdtime', array('[color ' . $players[$user]['color'] . ']' . $user, '[color #FF0000]' . $lost_time1, "Minuten")) . '"');
			return;
		}

		$this->lost_sure[$user] = time() + 60;

		RustRcon::GetInstance()->Send('say "' . Language::Text('tpwarn') . '"');
	}

	private function cmd_watch($command, $user, $param, $params)
	{
		if (Manager::GetInstance()->IsAuthed($user) === false)
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('noperm') . '"');
			return;
		}

		if ($param == "")
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('what') . '"');
			return;
		}

		$player = Manager::GetInstance()->ClosestMatch($param);
		RustRcon::GetInstance()->Send('say "' . Language::Text('watch', array('[color #FF0000]' . $player)) . '."');
	}

	private $tpa_sure = array();
	private $tp_requests = array();
	private function cmd_tp_request($command, $user, $param, $params)
	{
		if (Manager::GetInstance()->IsVIP($user) !== false)
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('antivip') . '"');
			return;
		}

		if ($param == "")
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('tphow') . '"');
			return;
		}

		$player = Manager::GetInstance()->ClosestMatch($param);
		if ($player == null)
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('whow', array('[color #FF0000]' . $param)) . '"');
			return;
		}

		$players = Data::ReadPlayers();
		$from = $players[$user];
		$to = $players[$player];

		if ($from['id'] == $to['id'])
		{
			RustRcon::GetInstance()->Send('say "[color #BBBBBB]Du kannst dich nicht zu dir selbst teleportieren."');
			return;
		}

		if (!$to['online'])
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('tpoff', array('[color ' . $to['color'] . ']' . $to['name'])) . '"');
			return;
		}


		$this->tp_requests[$to['name']] = $from['name'];
		unset($this->tpa_sure[$to['name']]);
		RustRcon::GetInstance()->Send('say "' . Language::Text('tpask', array('[color ' . $to['color'] . ']' . $to['name'], '[color ' . $from['color'] . ']' . $from['name'])) . '"');
	}

	private function timeoutTPA()
	{
		$nope = array();
		foreach ($this->tpa_sure as $user => $time)
			if ($time < time())
				$nope[] = $user;
		foreach ($nope as $user)
		{
			Manager::GetInstance()->CommandTime($user, "tpa", -10, true);
			unset($this->tpa_sure[$user]);
			unset($this->tp_requests[$user]);
		}
	}

	private function cmd_tp_accept($command, $user, $param, $params)
	{
		if ($param != "")
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('tphow') . '"');
			return;
		}

		if (!isset($this->tp_requests[$user]))
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('tpnoone') . '"');
			return;
		}
		$target = $this->tp_requests[$user];
		
		$tpa_time2 = Manager::GetInstance()->CommandTime($user, "tpa2", 30);
		if ($tpa_time2 > 0 && in_array($user, array_keys($this->tpa_sure)))
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('wait', array('[color #FF0000]' . $tpa_time2)) . '"');
			return;
		}

		if (in_array($user, array_keys($this->tpa_sure)))
		{
			$players = Data::ReadPlayers();
			$tpa_time = Manager::GetInstance()->CommandTime($target, "tpa", 60 * 60 * 24);
			if ($tpa_time > 0)
			{
				unset($this->tpa_sure[$user]);
				unset($this->tp_requests[$user]);
				RustRcon::GetInstance()->Send('say "' . Language::Text('cmdtime', array('[color ' . $players[$target]['color'] . ']' . $target, '[color #FF0000]' . ceil($tpa_time / (60 * 60)),  'Stunden')) . '"');
				return;
			}

			RustRcon::GetInstance()->Send('teleport.toplayer "' . $target . '" "' . $user . '"');
			RustRcon::GetInstance()->Send('say "' . Language::Text('tpok', array('[color ' . $players[$target]['color'] . ']' . $target, '[color ' . $players[$user]['color'] . ']' . $user)) . '"');

			unset($this->tp_requests[$user]);
			unset($this->tpa_sure[$user]);

			return;
		}

		$this->tpa_sure[$user] = time() + 60;

		RustRcon::GetInstance()->Send('say "' . Language::Text('tpwarn') . '"');
	}

	private function cmd_tp($command, $user, $param, $params)
	{
		if (Manager::GetInstance()->IsAuthed($user) === false)
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('noperm') . '"');
			return;
		}

		if ($param == "")
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('tpwut') . '"');
			return;
		}

		$state = null;
		foreach ($this->states as $name => $data)
		{
			foreach ($data['trigger'] as $trigger)
				if (strpos($param, $trigger) !== false)
				{
					$state = $name;
					break;
				}
			if ($state !== null)
				break;
		}
		switch (strtolower($param))
		{
		case "big":
			RustRcon::GetInstance()->Send('teleport.topos "' . $user . '" "5219" "365" "-4890"');
			break;
		case "small":
			RustRcon::GetInstance()->Send('teleport.topos "' . $user . '" "6063" "383" "-3697"');
			break;
		case "admin":
			RustRcon::GetInstance()->Send('teleport.topos "' . $user . '" -6328.85 442.71 -7359.26');
			break;
		default:
			if ($state !== null)
			{
				RustRcon::GetInstance()->Send('teleport.topos "' . $user . ' ' . $this->states[$state]['coordinates']);
				break;
			}
			$player = Manager::GetInstance()->ClosestMatch($param);
			RustRcon::GetInstance()->Send('teleport.toplayer "' . $user . '" "' . $player . '"');
			break;
		}

		RustRcon::GetInstance()->Send('say "' . Language::Text('tpadm', array($user)) . '"');
	}

	private function cmd_msg($command, $user, $param, $params)
	{
		if (Manager::GetInstance()->IsVIP($user) === false)
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('onlyvip') . '"');
			return;
		}

		if (count($params) < 2)
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('tphow') . '"');
			return;
		}
	
		$players = Data::ReadPlayers();
		$target = Manager::GetInstance()->ClosestMatch($params[0]);
		$target = $players[$target];
		if ($target['online'])
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('msgys', array('[color ' . $target['color'] . ']' . $target['name'])) . '"');
			return;
		}

		$message = substr(implode(" ", $params), strlen($params[0]) + 1);
		Data::WriteMessage($target['name'], $user, $message);

		RustRcon::GetInstance()->Send('say "' . Language::Text('msgok', array('[color ' . $target['color'] . ']' . $target['name'])) . '"');
	}

	private $vote = null;
	private function cmd_votekick($command, $user, $param, $params)
	{
		if ($this->vote != null)
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('othervote') . '"');
			return;
		}

		if (Manager::GetInstance()->IsVIP($user) === false)
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('onlyvip') . '"');
			return;
		}
		
		$players = Data::ReadPlayers();
		$online = array();

		foreach ($players as $player => $data)
			if ($data['online'])
				$online[$player] = $data;

		if (count($online) < 6)
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('lessvote1') . '"');
			RustRcon::GetInstance()->Send('say "' . Language::Text('lessvote2') . '"');
			return;
		}

		$target = Manager::GetInstance()->ClosestMatch($param);

		if (!$players[$target]['online'])
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('offvote', array('[color ' . $players[$target]['color'] . ']' . $target)) . '"');
			return;
		}

		RustRcon::GetInstance()->Send('say "' . Language::Text('kickvote1') . '"');
		RustRcon::GetInstance()->Send('say "' . Language::Text('kickvote2', array('[color ' . $players[$target]['color'] . ']' . $target)) . '"');
		RustRcon::GetInstance()->Send('say "' . Language::Text('kickvote3', array('[color ' . $players[$target]['color'] . ']' .$target)) . '"');
		RustRcon::GetInstance()->Send('say "' . Language::Text('kickvote4') . '"');
		RustRcon::GetInstance()->Send('say "' . Language::Text('kickvote5') . '"');

		$this->vote = array('vote' => 'kick', 'target' => $target, 'begin_count' => count($online), 'yes' => array($user), 'no' => array($target), 'timeout' => time() + 120);
	}

	private function cmd_yes($command, $user, $param, $params)
	{
		if ($this->vote == null)
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('nosurvey') . '"');
			return;
		}

		if (in_array($user, $this->vote['yes']) || in_array($user, $this->vote['no']))
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('alreadyv') . '"');
			return;
		}
		
		$players = Data::ReadPlayers();
		$online = array();

		foreach ($players as $player => $data)
			if ($data['online'])
				$online[$player] = $data;

		$this->vote['yes'][] = $user;

		RustRcon::GetInstance()->Send('say "' . Language::Text('acceptv') . '"');
	}

	private function cmd_no($command, $user, $param, $params)
	{
		if ($this->vote == null)
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('nosurvey') . '"');
			return;
		}

		if (in_array($user, $this->vote['yes']) || in_array($user, $this->vote['no']))
		{
			RustRcon::GetInstance()->Send('say "'. Language::Text('alreadyv') . '"');
			return;
		}
		
		$this->vote['no'][] = $user;
	}

	public function process_vote()
	{
		if ($this->vote == null)
			return;

		if (count($this->vote['yes']) > 12 || count($this->vote['yes']) / $this->vote['begin_count'] >= 3/4)
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('voteok') . '"');
			$target = 'vote_' . $this->vote['vote'];
			$this->$target();
			$this->vote = null;
		}
		if (count($this->vote['no']) / $this->vote['begin_count'] > 1/4 || time() > $this->vote['timeout'])
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('votefail') . '"');
			$this->vote = null;
		}
	}

	private function vote_kick()
	{
		RustRcon::GetInstance()->Send('kick "' . $this->vote['target'] . '"');
		RustRcon::GetInstance()->Send('say "' . Language::Text('vkickm', array('[color #FF0000]' . $this->vote['target'])) . '"');
	}
	
	private function cmd_suicide($command, $user, $param, $params)
	{
		RustRcon::GetInstance()->Send('say "' . Language::Text('suicide1') . '"');
		RustRcon::GetInstance()->Send('say "' . Language::Text('suicide2') . '"');
	}
	
	private function cmd_remove($command, $user, $param, $params)
	{
		RustRcon::GetInstance()->Send('say "' . Language::Text('remove1') . '"');
		RustRcon::GetInstance()->Send('say "' . Language::Text('remove2') . '"');
	}

	public function Timeout()
	{
		$this->timeoutPVP();
		$this->timeoutLost();
		$this->timeoutState();
		$this->timeoutTPA();
	}

	private function timeoutPVP()
	{
		$nope = array();
		foreach ($this->pvp_sure as $user => $time)
			if ($time < time())
				$nope[] = $user;
		foreach ($nope as $user)
		{
			Manager::GetInstance()->CommandTime($user, "pvp", -10, true); // Reset lost timer
			unset($this->pvp_sure[$user]);
		}
	}

	private $pvp_sure = array();
	private $pvp_pos = 0;
	private function cmd_pvp($command, $user, $param, $params)
	{
		if (Manager::GetInstance()->IsVIP($user) === false)
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('onlyvip') . '"');
			return;
		}

		$pvp_time2 = Manager::GetInstance()->CommandTime($user, "pvp2", 30);
		if ($pvp_time2 > 0 && in_array($user, array_keys($this->pvp_sure)))
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('wait', array('[color #FF0000]' . $pvp_time2)) . '"');
			return;
		}

		$playerdata = Data::ReadPlayers();

		if (in_array($user, array_keys($this->pvp_sure)))
		{
			Manager::GetInstance()->CommandTime($user, "lost", 60 * 10, true);
			Manager::GetInstance()->CommandTime($user, "state", 60 * 10, true);
			$positions = array('"-6252" "370" "510"', '"-6452" "365" "510"', '"-6252" "370" "610"', '"-6652" "386" "610"');
			RustRcon::GetInstance()->Send('teleport.topos "' . $user . '" ' . $positions[$this->pvp_pos++]);
			RustRcon::GetInstance()->Send('say "' . Language::Text('enterpvp', array('[color ' . $playerdata[$user]['color'] . ']' . $user)) . '"');

			if ($this->pvp_pos >= count($positions))
				$this->pvp_pos = 0;

			unset($this->pvp_sure[$user]);

			return;
		}

		$pvp_time1 = Manager::GetInstance()->CommandTime($user, "pvp", 60 * 30);
		if ($pvp_time1 > 0) // Once every hour
		{
			$pvp_time1 = ceil($pvp_time1/60);
			RustRcon::GetInstance()->Send('say "' . Language::Text('cmdtime', array($user, '[color #FF0000]' . $pvp_time1, 'Minuten')) . '"');
			return;
		}

		$this->pvp_sure[$user] = time() + 60;

		RustRcon::GetInstance()->Send('say "' . Language::Text('tpwarn') . '"');
	}

	private function timeoutState()
	{
		$nope = array();
		foreach ($this->state_sure as $user => $time)
			if ($time < time())
				$nope[] = $user;
		foreach ($nope as $user)
		{
			Manager::GetInstance()->CommandTime($user, "state", -10, true); // Reset lost timer
			unset($this->state_sure[$user]);
		}
	}
	private $states = array(
		'Orientem Maris' => array(
			'vip' => 'silver',
			'trigger' => array('orient', 'maris'),
			'coordinates' => '"-5794" "390" "4911"'),
		'Mediterrasurgit' => array(
			'vip' => 'gold',
			'trigger' => array('medi', 'terra', 'surgi'),
			'coordinates' => '"0" "390" "0"')
		);
	private $state_sure = array();
	public function cmd_state($command, $user, $param, $params)
	{
		$playerdata = Data::ReadPlayer($user);
		$is_vip = Manager::GetInstance()->IsVIP($user);
		if ($is_vip === false || $is_vip == 'bronze')
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('silvervip') . '"');
			return;
		}

		$states = $this->states;
		foreach ($states as $name => $data)
		{
			$states[$name]['valid'] = false;
			if ($data['vip'] == 'silver')
				if ($is_vip == 'silver' || $is_vip == 'gold' || $is_vip == 'platinum')
					$states[$name]['valid'] = true;
			if ($data['vip'] == 'gold')
				if ($is_vip == 'gold' || $is_vip == 'platinum')
					$states[$name]['valid'] = true;
			if ($data['vip'] == 'platinum')
				if ($is_vip == 'platinum')
					$states[$name]['valid'] = true;
		}

		if ($param == "")
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('townw') . '"');
			foreach ($states as $name => $data)
			{
				if ($data['valid'])
					RustRcon::GetInstance()->Send('say "[color #BBBBBB]* ' . $name . ' "');
			}
			return;
		}

		$state = null;
		foreach ($states as $name => $data)
		{
			foreach ($data['trigger'] as $trigger)
				if (strpos($param, $trigger) !== false)
				{
					$state = $name;
					break;
				}
			if ($state !== null)
				break;
		}

		if ($state == null)
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('townhuh') . '"');
			return;
		}

		if (!$states[$state]['valid'])
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('townnope') . '"');
			return;
		}

		// Do the teleportation stuff
		//RustRcon::GetInstance()->Send('say "[color #BBBBBB]You would go to ' . $state . '."');
		$state_time2 = Manager::GetInstance()->CommandTime($user, "state2", 30);
		if ($state_time2 > 0 && in_array($user, array_keys($this->state_sure)))
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('wait', array('[color #FF0000]' . $state_time2)) . '"');
			return;
		}

		if (in_array($user, array_keys($this->state_sure)))
		{
			$players = Data::ReadPlayers();
			RustRcon::GetInstance()->Send('teleport.topos "' . $user . '" ' . $this->states[$state]['coordinates']);
			RustRcon::GetInstance()->Send('say "' . Language::Text('townok', array('[color ' . $players[$user]['color'] . ']' . $user, '[color #FF0000]' . $state)) . '"');

			Manager::GetInstance()->CommandTime($user, "lost", 10 * 60, true);

			unset($this->state_sure[$user]);

			return;
		}

		$state_time1 = Manager::GetInstance()->CommandTime($user, "state", 20 * 60);
		if ($state_time1 > 0)
		{
			$state_time1 = ceil($state_time1/60);
			RustRcon::GetInstance()->Send('say "' . Language::Text('cmdtime', array('[color ' . $playerdata->color . ']' . $user, '[color #FF0000]' . $state_time1, 'Minuten')) . '"');
			return;
		}

		$this->state_sure[$user] = time() + 60;

		RustRcon::GetInstance()->Send('say "' . Language::Text('towngo', array('[color #FF0000]' . $state)) . '"');
	}

	private function cmd_services($command, $user, $param, $params)
	{
		RustRcon::GetInstance()->Send('say "' . Language::Text('service1') . '"');
		RustRcon::GetInstance()->Send('say "' . Language::Text('service2') . '"');
		RustRcon::GetInstance()->Send('say "' . Language::Text('service3') . '"');
	}
	
	private function cmd_location($command, $user, $param, $params)
	{
		WaitForID(RustRcon::GetInstance()->Send('save.avatars'), 'cmd_location_response', $this, $user);
	}

	public function cmd_location_response($response, $user)
	{
		$coordinates = Manager::GetInstance()->GetCoordinates($user);
		if ($coordinates == null)
		{
			RustRcon::GetInstance()->Send('say "[color#BBBBBB]Es ist im Moment nicht möglich, deine Koordinaten zu finden. Versuche es später erneut."');
			return;
		}

		RustRcon::GetInstance()->Send('say "' . Language::Text('location', array(round($coordinates[0], 2), round($coordinates[1], 2), round($coordinates[2], 2))) . '"');
	}

	private function cmd_group_create($command, $user, $param, $params)
	{
		$groups = Data::ReadGroups();
		$players = Data::ReadPlayers();
		$player = $players[$user];

		if ($param == "")
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('tphow') . '"');
			return;
		}

		foreach ($groups as $group => $members)
		{
			if (in_array($player['id'], $members))
			{
				RustRcon::GetInstance()->Send('say "' . Language::Text('algroup') . '"');
				return;
			}
		}

		if (in_array($param, array_keys($groups)))
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('groupname') . '"');
			return;
		}

		$groups[$param] = array($player['id']);
		Data::WriteGroups($groups);
		RustRcon::GetInstance()->Send('say "' . Language::Text('groupcr1', array('[color ' . $player['color'] . ']' . $player['name'], '[color #FF0000]' . $param)) . '"');
		RustRcon::GetInstance()->Send('say "' . Language::Text('groupcr2') . '"');
	}

	private $group_joins = array();
	private function cmd_group_invite($command, $user, $param, $params)
	{
		$groups = Data::ReadGroups();
		$players = Data::ReadPlayers();
		$player = $players[$user];
		$group = null;

		foreach ($groups as $name => $members)
		{
			if ($members[1] == $player['id'])
			{
				$group = $name;
				break;
			}
		}

		if ($group == null)
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('grouplead') . '"');
			return;
		}

		$target = Manager::GetInstance()->ClosestMatch($param);
		if ($target == null)
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('whow', array($param)) . '"');
			return;
		}

		$target = $players[$target];

		$tgroup = null;
		foreach ($groups as $name => $members)
		{
			if (in_array($target['id'], $members))
			{
				RustRcon::GetInstance()->Send('say "' . Language::Text('algroupex', array('[color ' . $target['color'] . ']' . $target['name'])) . '"');
				return;
			}
		}

		if (!$target['online'])
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('groupoff', array('[color ' . $target['color'] . ']' . $target['name'])) . '"');
			return;
		}

		RustRcon::GetInstance()->Send('say "' . Language::Text('groupinv1', array('[color ' . $target['color'] . ']' . $target['name'], '[color #FF0000]' . $group)) . '"');
		RustRcon::GetInstance()->Send('say "' . Language::Text('groupinv2') . '"');
		$this->group_joins[$target['id']] = $group;
	}

	private function cmd_group_accept($command, $user, $param, $params)
	{
		$groups = Data::ReadGroups();
		$players = Data::ReadPlayers();
		$player = $players[$user];
		if (!isset($this->group_joins[$player['id']]))
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('nogroupin') . '"');
			return;
		}

		$group = $this->group_joins[$player['id']];
		unset($this->group_joins[$player['id']]);
		$groups[$group][] = $player['id'];
		Data::WriteGroups($groups);
		RustRcon::GetInstance()->Send('say "' . Language::Text('groupok', array('[color ' . $player['color'] . ']' . $player['name'], '[color #FF0000]' . $group)) . '"');
	}

	private function cmd_group_quit($command, $user, $param, $params)
	{
		$groups = Data::ReadGroups();
		$players = Data::ReadPlayers();
		$player = $players[$user];
		$group = null;

		$new_members = array();
		foreach ($groups as $name => $members)
		{
			if (in_array($player['id'], $members))
			{
				$group = $name;
				foreach ($members as $member)
					if ($member != $player['id'])
						$new_members[] = $member;
				break;
			}
		}

		if ($group == null)
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('nogroup') . '"');
			return;
		}

		$groups[$group] = $new_members;
		if (count($new_members) == 0)
			unset($groups[$group]);
		Data::WriteGroups($groups);
		RustRcon::GetInstance()->Send('say "' . Language::Text('groupbye', array('[color #FF0000]' . $group)) . '"');
	}

	private function cmd_group_kick($command, $user, $param, $params)
	{
		$groups = Data::ReadGroups();

		if ($param == "")
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('tphow') . '"');
			return;
		}

		$player = Data::ReadPlayer($user);
		$group = null;
		foreach ($groups as $groupname => $members)
			if ($player->id == $members[1])
			{
				$group = $groupname;
				break;
			}
		
		if ($group == null)
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('grouplead') . '"');
			return;
		}

		$who = Manager::GetInstance()->ClosestMatch($param);
		if ($who == null)
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('whow', array($param)) . '"');
			return;
		}
		$who = Data::ReadPlayer($who);

		if ($who->id == $player->id)
		{
			RustRcon::GetInstance()->Send('say "[color #BBBBBB]Du kannst dich nicht selbst kicken."');
			return;
		}

		$new_group_order = array();
		foreach ($groups[$group] as $member)
		{
			echo "Compare $member to {$who->id}.\n";
			if ($member != $who->id)
				$new_group_order[] = $member;
		}

		if (count(array_diff($groups[$group], $new_group_order)) == 0)
		{
			RustRcon::GetInstance()->Send('say "[color ' . $who->color . ']' . $who->name . ' [color #BBBBBB]ist nicht in dieser Gruppe."');
			return;
		}

		$groups[$group] = $new_group_order;
		Data::WriteGroups($groups);
		RustRcon::GetInstance()->Send('say "[color ' . $who->color . ']' . $who->name . ' [color #BBBBBB]wurde aus [color #FF0000]' . $group . ' [color #BBBBBB]gekickt."');
	}

	private function cmd_groups($command, $user, $param, $params)
	{
		$groups = Data::ReadGroups();
		$players = Data::ReadPlayers();

		$online = array();
		foreach ($players as $player => $data)
			if ($data['online'])
				$online[] = $data['id'];

		if ($param != "")
		{
			$gmembers = array();
			
			$closest = null;
			$shortest = -1;
			foreach ($groups as $group => $members)
			{
				$lev = levenshtein(strtolower($param), strtolower($group));
				if ($lev == 0)
				{
					$closest = $group;
					break;
				}
				if (($lev <= $shortest || $shortest < 0) && strpos(strtolower($group), strtolower($param)) !== false)
				{
					$shortest = $lev;
					$closest = $group;
				}
			}
			
			if ($closest == null)
			{
				RustRcon::GetInstance()->Send('say "[color #BBBBBB]Keine Gruppe mit [color #FF0000]' . $param . '[color #BBBBBB] im Namen gefunden."');
				return;
			}

			RustRcon::GetInstance()->Send('say "' . Language::Text('grouplist', array('[color #FF0000]' . $closest)) . '"');
			$members = $groups[$closest];
			foreach ($players as $player => $data)
				if (in_array($data['id'], $members))
					$gmembers[] = '[color ' . $data['color'] . ']' . $data['name'] . '[color #BBBBBB]';		
			
			$player_string = "";
			foreach ($gmembers as $member)
			{
				if (strlen($player_string . $member . ",") > 170)
				{
					RustRcon::GetInstance()->Send('say "' . $player_string . '"');
					$player_string = $member . ", ";
				}
				else
					$player_string .= $member . ", ";
			}
			
			if (substr($player_string, -2) == ", ")
				RustRcon::GetInstance()->Send('say "' . substr($player_string, 0, -2) . '"');
			
			return;
		}

		RustRcon::GetInstance()->Send('say "' . Language::Text('groupon') . '"');
		$group_string = "";
		foreach ($groups as $group => $members)
		{
			$group_online = count(array_intersect($members, $online)) != 0;

			if (strlen($group_string . $group . ",") > 160)
			{
				RustRcon::GetInstance()->Send('say "' . $group_string . '"');
				$group_string = '[color ' . ($group_online ? '#FF0000' : '#BBBBBB') . ']' . $group . ", ";
			}
			else
				$group_string .= '[color ' . ($group_online ? '#FF0000' : '#BBBBBB') . ']' . $group . ", ";
		}
		if (substr($group_string, -2) == ", ")
			RustRcon::GetInstance()->Send('say "' . substr($group_string, 0, -2) . '"');
	}

	private function cmd_karma($command, $user, $param, $params)
	{
		$karma = Data::ReadKarma();
		$target = $user;
		if ($param != "")
		{
			$target = Manager::GetInstance()->ClosestMatch($param);
			if ($target == null)
			{
				RustRcon::GetInstance()->Send('say "' . Language::Text('whow', array($param)) . '"');
				return;
			}
		}

		$add = 0;
		if ($command == "good" || $command == "happy")
		{
			$command = "good";
			$add = 1;
		}
		elseif ($command == "bad" || $command == "made")
		{
			$command = "bad";
			$add = -1;
		}

		if ($add != 0)
		{
			if ($target == $user)
			{
				if ($param == "")
					RustRcon::GetInstance()->Send('say "[color #BBBBBB]Wem möchtest du Karma geben?"');
				else
					RustRcon::GetInstance()->Send('say "' . Language::Text('selfkarma') . '"');
				return;
			}
			$time = Manager::GetInstance()->CommandTime($user, $command . "-" . $target, 60 * 60 * 4);
			if ($time > 0)
			{
				RustRcon::GetInstance()->Send('say "' . Language::Text('karmapush', array('[color #FF0000]' . round($time / (60 * 60), 2))) . '"');
				return;
			}
		}

		$player = Data::ReadPlayer($target);

		if (!isset($karma[$player->id]))
			$karma[$player->id] = 0;
		$karma[$player->id] += $add;
		Data::WriteKarma($karma);
		$karma = $karma[$player->id];
		$color = $karma < 0 ? '#FF0000' : '#00FF00';
		RustRcon::GetInstance()->Send('say "' . Language::Text('karma', array('[color ' . $player->color . ']' . $target, '[color ' . $color . ']' . $karma)) . '"');
	}

	private function cmd_quit($command, $user, $param, $params)
	{
		if (Manager::GetInstance()->IsVIP($user) !== false)
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('antivip') . '"');
			return;
		}

		RustRcon::GetInstance()->Send('sleepers.on false');
		RustRcon::GetInstance()->Send('kick "' . $user . '"');
		RustRcon::GetInstance()->Send('sleepers.on true');
		RustRcon::GetInstance()->Send('say "[color #BBBBBB]Ohne Sleeper ausgeloggt."');
	}

	private function cmd_airdrop($command, $user, $param, $params)
	{
		RustRcon::GetInstance()->Send('say "[color #BBBBBB]Airdrop [color #FF0000]jede 60-90 min [color #BBBBBB](2-4 sp.), [color #FF0000]30-45 min [color #BBBBBB](5-19 sp.) oder [color #FF0000]zwei alle 30-45 min [color #BBBBBB](20+ sp.)"');
	}

	private $falldmg = 0;

	public function TimeoutFalldmg()
	{
		if ($this->falldmg < time() && $this->falldmg != 0)
		{
			echo "RESET FALL DAMAGE! ######\n";
			RustRcon::GetInstance()->Send('falldamage.enabled true');
			$this->falldmg = 0;
		}
	}

	private function cmd_parachute($command, $user, $param, $params)
	{
		$time = Manager::GetInstance()->CommandTime($user, "parachute", 60 * 120);
		$user = Data::ReadPlayer($user);
		if ($time > 0)
		{
			RustRcon::GetInstance()->Send('say "' . Language::Text('cmdtime', array('[color ' . $user->color . ']' . $user->name, '[color #FF0000]' . round($time/60), "Minuten")) . '"');
			return;
		}
		$paratime = 7;
		RustRcon::GetInstance()->Send('falldamage.enabled false');
		RustRcon::GetInstance()->Send('say "[color #BBBBBB]Fallschirm für [color #FF0000]' . $paratime . ' [color #BBBBBB]Sekunden aktiviert."');
		$this->falldmg = time() + $paratime;
	}

	public function cmd_rules($command, $user, $param, $params)
	{
		if ($command == "regeln")
		{
			RustRcon::GetInstance()->Send('say "[color#FF0000]1. [color#BBBBBB]Kein Cheaten oder missbräuchliches Glitchen."');
			RustRcon::GetInstance()->Send('say "[color#FF0000]2. [color#BBBBBB]Keine Beleidungungen oder massloses Fluchen."');
			RustRcon::GetInstance()->Send('say "[color#FF0000]3. [color#BBBBBB]Kein Zubauen von fixen Loot Chests."');
			RustRcon::GetInstance()->Send('say "[color#FF0000]4. [color#BBBBBB]Keine links, URLs, IPs im Chat ohne Erlaubnis eines Admins."');
			RustRcon::GetInstance()->Send('say "[color#FF0000]Mehr Infos:[color#BBBBBB]http://bloodisgood.org/rust"');
		}
		else
		{
			RustRcon::GetInstance()->Send('say "[color#FF0000]1. [color#BBBBBB]No cheating or abusive glitching."');
			RustRcon::GetInstance()->Send('say "[color#FF0000]2. [color#BBBBBB]No insulting, no excessive swearing."');
			RustRcon::GetInstance()->Send('say "[color#FF0000]3. [color#BBBBBB]No blocking of loot chests with buildings."');
			RustRcon::GetInstance()->Send('say "[color#FF0000]4. [color#BBBBBB]No links, URLs, IPs in the chat without an admins consent."');
			RustRcon::GetInstance()->Send('say "[color#FF0000]More infos:[color#BBBBBB]http://bloodisgood.org/rust"');
		}
	}

	public function cmd_abuse($command, $user, $param, $params)
	{
		RustRcon::GetInstance()->Send('say "[color#BBBBBB]For information about [color#FF0000]ADMIN ABUSE [color#BBBBBB]visit [color#FF0000]http://goo.gl/W4SLTb"');
	}
}
