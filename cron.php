<?PHP

class Cron
{
	private static $instance = null;
	public static function GetInstance()
	{
		if (self::$instance == null)
			self::$instance = new Cron();
		return self::$instance;
	}

	private $schedules;
	private $airdrop_second = true;
	private $announce = 0;
	private $igtime = 0;

	private function __construct()
	{
		$this->schedules = Data::ReadCron();
		if ($this->schedules == null)
			$this->schedules = array(
				"cron_hours" => strtotime("next full hour"),
				"cron_airdrop" => time() + 30 * 60,
				"cron_announce" => time() + 5 * 60,
				"cron_playercount" => time() + 2 * 60,
				"cron_joinquit" => time() + 30,
				"cron_igtime" => time() + 60,
				"cron_vote" => time() + 10,
				"cron_parachute" => time() + 1,
			);
	}

	public function Run()
	{
		$new_schedules = array();
		$ctime = time();

		foreach ($this->schedules as $callback => $time)
			if ($ctime >= $time)
				$new_schedules[$callback] = $this->$callback();

		if (count($new_schedules) > 0)
		{
			$this->schedules = array_merge($this->schedules, $new_schedules);
			Data::WriteCrons($this->schedules);
		}
	}

	/**
	 * Full hour announce
	 */
	private function cron_hours()
	{
		echo "Announcing a passed hour.\n";
		RustRcon::GetInstance()->Send('say "' . Language::Text('hour', array('[color #FF0000]' . intval(date('G')) . ':00')) .'"');
		return strtotime('+1 hour');
	}

	/*
	 * Airdrop call
	 */
	private function cron_airdrop()
	{
		WaitForID(RustRcon::GetInstance()->Send('status'), 'cron_airdrop_response', $this);
		return time() + (30 * 60) + rand(0, 15 * 60);
	}

	public function cron_airdrop_response($response)
	{
		$players = Manager::GetInstance()->ParsePlayers($response);

		$drop = 0;
		if (count($players) > 1)
		{
			if (count($players) <= 4)
			{
				if ($this->airdrop_second)
					$drop = 1;
				$this->airdrop_second = !$this->airdrop_second;
			}
			elseif (count($players) < 20)
				$drop = 1;
			elseif (count($players) >= 20)
				$drop = 2;
		}

		for ($i = 0; $i < $drop; $i++)
			RustRcon::GetInstance()->Send('airdrop.drop');

		if ($drop > 0)
		{
			echo "Announcing airdrop.\n";
			RustRcon::GetInstance()->Send('say "' . Language::Text('airdrop') . '"');
		}
	}

	/**
	 * Cycling announces
	 */
	private function cron_announce()
	{
		$announces = Data::ReadAnnounces();
		if (++$this->announce >= count($announces))
			$this->announce = 0;

		$announce_content = $announces[$this->announce];

		echo "Announcing static messages.\n";
		foreach ($announce_content as $announce_text)
			RustRcon::GetInstance()->Send('say "[color #BBBBBB]' . $announce_text . '"');

		return time() + (1.5 * 60);
	}

	/**
	 * Player count announce
	 */
	private function cron_playercount()
	{
		WaitForID(RustRcon::GetInstance()->Send('status'), 'cmd_count_response', Commands::GetInstance());
		return time() + (2.25 * 60);
	}

	/**
	 * Join & Quit messages
	 */
	private function cron_joinquit()
	{
		Manager::GetInstance()->VipColors();
		Commands::GetInstance()->Timeout();
		WaitForID(RustRcon::GetInstance()->Send('status'), 'cron_joinquit_response', $this);
		$this->all_players = Data::ReadPlayers();
		return time() + 10;
	}

	private $prev_players = array();
	private $all_players = array();
	public function cron_joinquit_response($response)
	{
		$all_players = $this->all_players;
		$players = Manager::GetInstance()->ParsePlayers($response);
		$playerdata = Data::ReadPlayers();

		$test_for = (count($this->prev_players) == 0 ? Data::ReadPlayers() : $this->prev_players);

		$left = array_diff($this->prev_players, array_keys($players));

		$prev_players = $this->prev_players;
		if (count($prev_players) == 0)
			foreach ($all_players as $player => $data)
				if ($data['online'])
					$prev_players[] = $player;

		foreach ($players as $player => $data)
			if (isset($all_players[$player]))
				$all_players[$player]['last'] = time();
		Data::WritePlayers($all_players);

		$joined = array_diff(array_keys($players), $prev_players);
		$new = array_diff($joined, array_keys($all_players));
		$old = array_intersect($joined, array_keys($all_players));

/*		foreach ($new as $id => $maybe)
		{
			$test = Data::ReadPlayer($players[$maybe]['id']);
			if ($test != null && $test->first < time() - 60)
			{
				unset($new[$id]);
				$old[] = $maybe;
			}
		}
*/

		foreach ($players as $player => $data)
			if (!isset($playerdata[$player]))
				$playerdata[$player] = $data;

		$rcon = RustRcon::GetInstance();

/*		if (count($players) < 38 && true)
		{
		foreach ($left as $left_player)
		{
			echo "$left_player left.\n";
			IRC::WriteToIRC("#QUIT", $left_player);
			Data::WriteChatLine('#QUIT', $left_player);
			$rcon->Send('say "' . Language::Text('left', array('[color ' . $playerdata[$left_player]['color'] . ']' . $left_player)) . '"');
		}
		}
 */
		foreach ($joined as $joined_player)
		{
			Data::WriteVACCheck($players[$joined_player]['id']);
			$country = geoip_country_name_by_name($players[$joined_player]['ip']);

/*			if (count($players) < 38 && true)
			{
				echo "$joined_player joined from $country.\n";
				IRC::WriteToIRC("#JOIN", $joined_player . " from " . $country . " (" . (in_array($joined_player, $new) ? 'new' : 'old') . ")");
				Data::WriteChatLine('#JOIN', $joined_player);
				$rcon->Send('say "' . Language::Text('join', array('[color ' . (isset($playerdata[$joined_player]) ? $playerdata[$joined_player]['color'] : '#FF66FF') . ']' . $joined_player, '[color #FF0000]' . $country)) . '"');
			}
 */		

			if ($country == "Russian Federation")
			{
				$rcon->Send('banid "' . $players[$joined_player]['id'] . '" "No Russians allowed."');
				$rcon->Send('kick "' . $joined_player . '"');
				$rcon->Send('say "' . Language::Text('rusfag', array($joined_player)) . '"');
				Data::WritePlayer($players[$joined_player]);
			}

			if (in_array($country, array("Saudi Arabia", "United States", "Israel", "China", "Turkey", "Canada")))
			{
				echo "$country is flagged, will be kicked.\n";
				$rcon->Send('say "Kicking ' . $joined_player . ' for an insanely high ping."');
				IRC::WriteToIRC("#KICK", $joined_player . " (ping too high)");
				$rcon->Send('kick "' . $joined_player . '"');
			}
		}

/*		if (count($old) > 0)
			if (count($players) < 38)
			$rcon->Send('say "' . Language::Text('back', array(implode(' und ', $old))) . '"');
 */
		$karma = Data::ReadKarma();
		foreach ($joined as $joined_player)
		{
			$messages = Data::ReadMessages($joined_player);
			if (count($messages) > 0)
			{
				foreach ($messages as $message)
				{
					$rcon->Send('say "' . Language::Text('msg', array($message[1], $joined_player)) . '"');
					$rcon->Send('say "[color #BBBBBB]' . $message[2] . '"');
				}
			}
			if (isset($karma[$players[$joined_player]['id']]))
				if ($karma[$players[$joined_player]['id']] <= -5)
					if (count($players) < 38)
					RustRcon::GetInstance()->Send('say "' . Language::Text('karmawarn', array('[color ' . $playerdata[$joined_player]['color'] . ']' . $joined_player)) . '"');
		}
/*
		if (count($new) > 0)
		{
			foreach ($new as $player)
			{
				Data::WritePlayer($players[$player]);
			}
			$rcon->Send('say "' . Language::Text('welcome', array(implode(' und ', $new))) . '"');
			$rcon->Send('say "' . Language::Text('first') . '"');
		}
 */
		$this->prev_players = array_keys($players);
	}
	
	private function cron_vote()
	{
		Commands::GetInstance()->process_vote();
		return time() + 10;
	}
	
	/**
	 * In-game time
	 */
	private function cron_igtime()
	{
		WaitForID(RustRcon::GetInstance()->Send('env.time'), 'cron_igtime_response', $this);
		return time() + 20;
	}

	public function cron_igtime_response($response)
	{
		$time = floor(floatval(substr($response->Response(), 14)));
		if ($time != $this->igtime)
		{
			if ($time == 0 || $time == 6 || $time == 12 || $time == 18)
			{
				echo "Announcing $time:00 in-game.\n";
				RustRcon::GetInstance()->Send('say "' . Language::Text('inhour', array('[color #00FF00]' . $time . ':00')) . '"');
			}
		}
		$this->igtime = $time;
	}

	public function cron_parachute()
	{
		Commands::GetInstance()->TimeoutFalldmg();
		return time() + 1;
	}
}
