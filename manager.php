<?PHP

class Manager
{
	private static $instance = null;
	public static function GetInstance()
	{
		if (self::$instance == null)
			self::$instance = new Manager();
		return self::$instance;
	}


	private $previous_matches = array();
	private function __construct()
	{

	}

	private $nts_def = array(
		0 => 'null',
		1 => 'ein',
		2 => 'zwei',
		3 => 'drei',
		4 => 'vier',
		5 => 'fünf',
		6 => 'sechs',
		7 => 'sieben',
		8 => 'acht',
		9 => 'neun',
		10 => 'zehn',
		11 => 'elf',
		12 => 'zwölf'
	);

	private $vip_time = 36000; // 10 hours
	private $vip_times = array('bronze' => 36000, 'silver' => 72000, 'gold' => 180000, 'platinum' => 360000);
	private $vip_colors = array('bronze' => '#CD7F32', 'silver' => '#BCC6CC', 'gold' => '#FDD017', 'platinum' => '#E5E4E2');

	public function ParsePlayers($response)
	{
		$parse = array();
		if (substr($response->Response(), 0, 8) != 'hostname')
			return $parse;

		preg_match('/players : ([0-9]+) /', $response->Response(), $matches);
		$player_count_real = intval($matches[1]);

		if (preg_match_all('/([0-9]{6,17}) +"(.*)" +([0-9]{1,4}) +([0-9]{1,7}s) +([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/', $response->Response(), $matches))
		{
			if (count($matches[0]) != $player_count_real)
				echo "Invalid player count: " . ($player_count_real - count($matches[0])) . ".\n";
			/*$queue = array();
			if (count($matches[0]) != $player_count_real)
			{
				$more = RustRcon::GetInstance()->Read();
				if ($more == null)
				{
					preg_match_all('/([0-9]{6,17}) +"(.*)" +([0-9]{1,4}) +([0-9]{1,7}s) +([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/', $response->Response(), $matches);
					echo "I give up, using " . count($matches[0]) . " players.\n";
					//break;
				}
				else
				{
				if (substr($more->Response(), 0, 8) == 'hostname')
					$queue[] = $more;
				else
				{
					preg_match_all('/([0-9]{6,17}) +"(.*)" +([0-9]{1,4}) +([0-9]{1,7}s) +([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/', $response->Response() . $more->Response(), $matches);
					if ($more->Id() != $response->Id())
					{
						$matches = $this->previous_matches;
						preg_match_all('/([0-9]{6,17}) +"(.*)" +([0-9]{1,4}) +([0-9]{1,7}s) +([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/', $response->Response(), $matches);
					}
					else
					{
						$this->previous_matches = $matches;
					}
				}
				/*	
					if (count($matches[0]) <= $player_count_real - 3)
					{
						echo "Close enough, some people must have tabbed out.\n";
						break;
					}
				 *//*
					//if (count($matches[0]) != $player_count_real)
					//	$queue[] = $more;
				}
			}
			 */

			/*foreach ($queue as $q)
				RustRcon::GetInstance()->Queue($q);*/

			for ($i = 0; $i < count($matches[0]); $i++)
			{
				//$parse[str_replace('"', '\\"', $matches[2][$i])] = array('id' => $matches[1][$i], 'name' => str_replace('"', '\\"', $matches[2][$i]), 'ping' => $matches[3][$i], 'time' => $matches[4][$i], 'ip' => $matches[5][$i], 'online' => true, 'color' => '#FF66FF');
				$parse[$matches[2][$i]] = array('id' => $matches[1][$i], 'name' => $matches[2][$i], 'ping' => $matches[3][$i], 'time' => $matches[4][$i], 'ip' => $matches[5][$i], 'online' => true, 'color' => '#FF66FF');
			}
		}

		$parse = $this->PlayerStats($parse);

		Data::WriteHistory(count($parse));

		return $parse;
	}

	public function NumToStr($num)
	{
		if (array_key_exists(intval($num), $this->nts_def))
			return $this->nts_def[intval($num)];
		return intval($num);
	}

	private $prev_players;
	private function PlayerStats($players)
	{
		// In: raw player times
		// Out: total player times

		$all_players = Data::ReadPlayers();
		
		foreach ($all_players as $player => $data)
		{
		/*	foreach ($players as $player2 => $data2)
				if ($data['id'] == $data2['id'] && $data['name'] != $data2['name'])
					if ($player != $player2)
					{
						echo "Resetting player $player to $player2.\n";
						//$all_players[$player2] = $all_players[$player];
						unset($all_players[$player]);
						$all_players[$player2] = $data;
						$all_players[$player2]['name'] = $player2;
						$player = $player2;
					}*/
			$all_players[$player]['online'] = false;
		}

		$real_players = array();
		foreach ($players as $player => $data)
		{
			$add_time = 0;

			if (isset($this->prev_players[$player]))
			{
				if ($this->prev_players[$player]['time'] < $data['time']) // eg. 3 seconds --> 10 seconds
					$add_time = $data['time'] - $this->prev_players[$player]['time']; // eg. 7 seconds

			//	elseif ($this->prev_players[$player]['time'] > $data['time']) // eg. 2000 seconds -> 4 seconds
			//		$add_time = $data['time']; // eg. 4 seconds
			}

			if (!isset($all_players[$player]))
				$all_players[$player] = $players[$player];

			foreach ($this->vip_times as $name => $time)
			{	
				if ($all_players[$player]['time'] < $time && $all_players[$player]['time'] + $add_time > $time)
				{
					RustRcon::GetInstance()->Send('say "' . Language::Text('vipgrats1', array('[color #FF0000]' . $player, '[color #FF0000]' . ceil($time / (60 * 60)))) . '"');
					RustRcon::GetInstance()->Send('say "' . Language::Text('vipgrats2', array(Ucfirst($name))) . '"');
					if ($name == 'platinum')
						RustRcon::GetInstance()->Send('inv.giveplayer "' . $player . '" "Supply Signal"');
				}
			}

			$all_players[$player]['time'] += $add_time;
			$all_players[$player]['ip'] = $data['ip'];
			$all_players[$player]['online'] = $data['online'];
			$all_players[$player]['ping'] = $data['ping'];
			$players[$player] = $all_players[$player];
			$players[$player]['time'] = $data['time'];

			$real_players[$player] = $all_players[$player];
		}

		Data::WritePlayers($all_players);
		$this->prev_players = $players;

		return $real_players;
	}

	private $command_times = null;
	public function CommandTime($user, $command, $time, $reset = false)
	{
		if ($this->command_times == null)
			$this->command_times = Data::ReadCommandTimes();

		if (!isset($this->command_times[$command]))
			$this->command_times[$command] = array();

		if (!isset($this->command_times[$command][$user]))
			$this->command_times[$command][$user] = time();

		if (time() < $this->command_times[$command][$user] && !$reset)
			return $this->command_times[$command][$user] - time();

		$this->command_times[$command][$user] = time() + $time;
		
		Data::WriteCommandTimes($this->command_times);
		return 0;
	}

	public function ResetCommandTime($user, $command)
	{
		if (!isset($this->command_times[$command]))
			$this->command_times[$command] = array();

		unset($this->command_times[$command][$user]);
	}

	private $authed = array();
	public function IsAuthed($user)
	{
		if (!isset($this->authed[$user]))
			return false;
		return $this->authed[$user];
	}

	public function Auth($user)
	{
		echo "Authing $user.\n";
		$target = Data::ReadPlayer($user);
		if ($user == 'BiG|Raven' && $this->prev_players['BiG|Raven']['id'] == '76561197976732818')
		{
			echo "Authed.\n";
			$this->authed['BiG|Raven'] = 'admin';

			RustRcon::GetInstance()->Send('say "' . Language::Text('adminauth', array('[color #FF0000]BiG|Raven')) . '"');
		}
		elseif ($target->id == '76561198025524624' || $target->id == '76561198029774102' || $target->id == '76561198069332364')
		{
			echo "Authed as mod.\n";
			$this->authed[$user] = 'mod';

			RustRcon::GetInstance()->Send('say "' . Language::Text('modauth', array('[color #FF0000]' . $user)) . '"');
		}
		else
			RustRcon::GetInstance()->Send('say "' . Language::Text('noauth') . '"');
	}

	public function Deauth($user)
	{
		echo "Deauthing $user.\n";
		if ($this->IsAuthed($user) === false)
			return;

		unset($this->authed[$user]);
		RustRcon::GetInstance()->Send('say "' . Language::Text('deauth') . '"');
	}

	public function VipColors()
	{
		return;
		$players = Data::ReadPlayers();
		$colors = array();
		foreach ($players as $player => $data)
		{
			$vip = $this->IsVIP($player);
			if ($vip === false)
				continue;
			$players[$player]['color'] = $this->vip_colors[$vip];
			if ($this->IsAuthed($player) !== false)
				$players[$player]['color'] = '#FF0000';
		}
		Data::WritePlayers($players);
	}

	public function IsVIP($user)
	{
		$player = Data::ReadPlayer($user);
		/*
		$players = Data::ReadPlayers();
		if (!isset($players[$user]))
			return false;
		 */
		if ($player == null)
			return false;

		$state = false;
		foreach ($this->vip_times as $name => $time)
			if ($player->time >= $time)
				$state = $name;

		return $state;
	}

	public function ClosestMatch($user)
	{
		if ($user == "")
			return null;

		$players = Data::ReadPlayers();

		$closest = null;
		$shortest = -1;
		foreach ($players as $player => $data)
		{
			$lev = levenshtein(strtolower($user), strtolower($player));
			if ($lev == 0)
				return $player;
			if (($lev <= $shortest || $shortest < 0) && strpos(strtolower($player), strtolower($user)) !== false)
			{
				$shortest = $lev;
				$closest = $player;
			}
		}

		return $closest;
	}

	public function GetCoordinates($user)
	{
		$players = Data::ReadPlayers();
		$player = $players[$user];

		if (!RFTP::GetInstance()->GetAvatar($player['id']))
			return null;

		if (!file_exists('data/avatars/' . $player['id'] . '.bin'))
			return null;

		$avatar = file_get_contents('data/avatars/' . $player['id'] . '.bin');
		unlink('data/avatars/' . $player['id'] . '.bin');
		$values = unpack("cMagic1/cMagic2/cMagic3/fX/cXLim/fY/cYLim/fZ/cZLim", $avatar);
		return array($values['X'], $values['Y'], $values['Z']);
	}
}
