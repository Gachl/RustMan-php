<?PHP
require_once('user.php');

class Data
{
	private static $db = null;

	private static $base_path = '/path/to/data/dir/';
	private static $suggestions_file = 'suggestions.txt';
	private static $reports_file = 'reports.txt';
	private static $players_file = 'players.txt';
	private static $cron_file = 'cron.txt';
	private static $history_file = 'history.txt';
	private static $announces_file = 'announces.txt';
	private static $messages_file = 'messages.txt';
	private static $times_file = 'cmdtimes.txt';
	private static $log_file = 'log.txt';
	private static $groups_file = 'groups.txt';
	private static $karma_file = 'karma.txt';
	private static $vac_file = 'vac.txt';

	private static function connect()
	{
		try
		{
			if (self::$db == null)
				throw new Exception("Not connected");
			self::$db->prepare("SELECT 1")->execute();
		}
		catch (Exception $e)
		{
			global $__sshhh_name, $__sshhh_pass;
			self::$db = new PDO('mysql:host=localhost;dbname=daniel_rust_pvp;charset=utf8', "db_user", "db_pass");
		}
	}

	/**
	 * Suggestions
	 */
	public static function ReadSuggestions()
	{
		self::connect();
		return file(self::$base_path . self::$suggestions_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	}

	public static function WriteSuggestions($suggestions)
	{
		self::connect();
		file_put_contents(self::$base_path . self::$suggestions_file, "");
		foreach ($suggestions as $suggestion)
			self::WriteSuggestion($suggestion);
	}

	public static function WriteSuggestion($suggestion)
	{
		self::connect();
		file_put_contents(self::$base_path . self::$suggestions_file, "$suggestion\n", FILE_APPEND);
	}

	/**
	 * Reports
	 */
	public static function ReadReports()
	{
		self::connect();
		return file(self::$base_path . self::$reports_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	}

	public static function WriteReports($reports)
	{
		self::connect();
		file_put_contents(self::$base_path . self::$reports_file, "");
		foreach ($reports as $report)
			self::WriteReport($report);
	}

	public static function WriteReport($report)
	{
		self::connect();
		file_put_contents(self::$base_path . self::$reports_file, "$report\n", FILE_APPEND);
	}

	/**
	 * Players
	 */
	private static $players = null;

	public static function ReadPlayer($name_or_id)
	{
		self::connect();
		$record = null;
		$key = null;
		if (intval($name_or_id) > 1000000000000000)
		{
			$key = ':id';
			$record = self::$db->prepare('SELECT `id`, `name`, `time`, `ip`, `online`, `last`, `member`, `karma`, `vac`, `first`, `votes` FROM `users` WHERE `id` = :id;');
		}
		else
		{
			$key = ':name';
			$record = self::$db->prepare('SELECT `id`, `name`, `time`, `ip`, `online`, `last`, `member`, `karma`, `vac`, `first`, `votes` FROM `users` WHERE `name` = :name;');
		}

		if (!$record->execute(array($key => $name_or_id)))
			return null;

		if (!($row = $record->fetch()))
			return null;
		
		return new User(intval($row['id']), $row['name'], intval($row['time']), $row['ip'], $row['online'], intval($row['last']), $row['member'] == null ? null : intval($row['member']), intval($row['karma']), $row['vac'], $row['first'], $row['votes']);
	}

	public static function GetOnlinePlayers()
	{
		return self::ReadPlayers(true);
	}

	public static function ReadPlayers($online = false)
	{
		self::connect();

		$players = array();
		$statement = self::$db->prepare("SELECT `id` FROM `users`;");
		if ($online)
			$statement = self::$db->prepare("SELECT `id` FROM `users` WHERE `online` = 1;");
		$statement->execute();
		while ($idrow = $statement->fetch())
		{
			$player = self::ReadPlayer($idrow['id']);
			$players[$player->name] = array('id' => $player->id, 'name' => $player->name, 'time' => $player->time, 'ip' => $player->ip, 'online' => ($player->online ? true : false), 'color' => $player->color, 'last' => $player->last, 'member' => $player->member, 'karma' => $player->karma, 'vac' => ($player->vac ? true : false));
		}
		//$players[] = array();
		self::$players = $players;
		return $players;
 
 
		if (self::$players != null)
			return self::$players;

		$player_source = file(self::$base_path . self::$players_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

		// Parse players
		$players = array();
		foreach ($player_source as $player)
		{
			$player = explode("\000", $player);
			$players[$player[0]] = array('id' => $player[1], 'name' => $player[0], 'time' => $player[2], 'ip' => $player[3], 'online' => $player[4] == 'online', 'color' => '#FF66FF', 'last' => isset($player[5]) ? $player[5] : strtotime("yesterday"));
		}

		self::$players = $players;

		Manager::GetInstance()->VipColors();

		return self::$players;
	}

	public static function WritePlayers($players)
	{
		self::connect();
		if (self::$players != null && count($players) < count(self::$players))
		{
			echo "OH OH! WE ARE WRITING LESS PLAYERS THAN WE HAVE!\n";
			echo count($players) . " < " . count(self::$players) . "\n";
			
			return;
		}

		foreach ($players as $player => $data)
			if (isset(self::$players[$player]) && $data['time'] < self::$players[$player]['time'])
				$players[$player]['time'] = self::$players[$player]['time'];

		self::$players = array();

		file_put_contents(self::$base_path . self::$players_file, "");
		foreach ($players as $player)
			self::WritePlayer($player);
	}

	public static function WritePlayer($player)
	{
		self::connect();
return;
		$statement = self::$db->prepare('INSERT INTO `users` (`id`, `name`, `time`, `ip`, `online`, `last`, `member`, `karma`, `vac`, `first`, `votes`) VALUES (:id, :name, :time, :ip, :online, :last, :member, :karma, :vac, NOW(), 0) ON DUPLICATE KEY UPDATE `name` = :name, `time` = :time, `ip` = :ip, `online` = :online, `last` = :last, `member` = :member, `karma` = :karma, `vac` = :vac;');
		if (is_array($player))
			$statement->execute(array(':id' => $player['id'], ':name' => $player['name'], ':time' => $player['time'], ':ip' => $player['ip'], ':online' => $player['online'], ':last' => isset($player['last']) ? $player['last'] : time(), ':member' => NULL, ':karma' => 0, ':vac' => false));
		else
		{
			$statement = self::$db->prepare('INSERT INTO `users` (`id`, `name`, `time`, `ip`, `online`, `last`, `member`, `karma`, `vac`, `first`, `votes`) VALUES (:id, :name, :time, :ip, :online, :last, :member, :karma, :vac, NOW(), 0) ON DUPLICATE KEY UPDATE `name` = :name, `time` = :time, `ip` = :ip, `online` = :online, `last` = :last, `member` = :member, `karma` = :karma, `vac` = :vac, `votes` = :votes;');
			$statement->execute(array(':id' => $player->id, ':name' => $player->name, ':time' => $player->time, ':ip' => $player->ip, ':online' => $player->online, ':last' => $player->last, ':member' => $player->member, ':karma' => $player->karma, ':vac' => $player->vac, ':votes' => $player->votes));
			return;
		}


		self::$players[$player['name']] = $player;
		file_put_contents(self::$base_path . self::$players_file, $player['name'] . "\000" . $player['id'] . "\000" . $player['time'] . "\000" . (isset($player['ip']) ? $player['ip'] : '') . "\000" . ($player['online'] ? 'online' : 'offline') . "\000" . (isset($player['last']) ? $player['last'] : strtotime('yesterday')) . "\n", FILE_APPEND);
	}

	/**
	 * CRON
	 */
	public static function ReadCron()
	{
		self::connect();
		$cron_source = file(self::$base_path . self::$cron_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

		// Parse cron
		$cron = array();
		foreach ($cron_source as $cron_entry)
		{
			$cron_entry = explode("\t", $cron_entry);
			$cron[$cron_entry[0]] = intval($cron_entry[1]);
		}

		return $cron;
	}

	public static function WriteCrons($crons)
	{
		self::connect();
		file_put_contents(self::$base_path . self::$cron_file, "");
		foreach ($crons as $cron => $time)
			self::WriteCron($cron, $time);
	}

	public static function WriteCron($cron, $time)
	{
		self::connect();
		file_put_contents(self::$base_path . self::$cron_file, "$cron\t$time\n", FILE_APPEND);
	}

	/**
	 * Player history
	 */
	public static function ReadHistory()
	{
		self::connect();
		$history_source = file(self::$base_path . self::$history_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

		// Parse history
		$history = array();
		foreach ($history_source as $history_entry)
		{
			$history_entry = explode("\t", $history_entry);
			$history[intval($history_entry[0])] = intval($history_entry[1]);
		}

		return $history;
	}

	public static function WriteHistories($histories)
	{
		self::connect();
		file_put_contents(self::$base_path . self::$history_file, "");
		foreach ($histories as $time => $count)
			self::WriteHistory($count, $time);
	}

	public static function WriteHistory($count, $time = null)
	{
		self::connect();

		$line = explode("\t", self::ReadLastLine(self::$base_path . self::$history_file));
		if (intval($line[0]) == ($time == null ? time() : $time) || intval($line[1]) == $count)
			return;
		file_put_contents(self::$base_path . self::$history_file, ($time == null ? time() : $time) . "\t$count\n", FILE_APPEND);
	}

	private static function ReadLastLine($file)
	{
		self::connect();
		$line = '';

		$f = fopen($file, 'r');
		$cursor = -1;

		fseek($f, $cursor, SEEK_END);
		$char = fgetc($f);

		/**
		 * Trim trailing newline chars of the file
		 */
		while ($char === "\n" || $char === "\r") {
				fseek($f, $cursor--, SEEK_END);
				$char = fgetc($f);
		}

		/**
		 * Read until the start of file or first newline char
		 */
		while ($char !== false && $char !== "\n" && $char !== "\r") {
				/**
				 * Prepend the new char
				 */
				$line = $char . $line;
				fseek($f, $cursor--, SEEK_END);
				$char = fgetc($f);
		}

		return $line;
	}

	/**
	 * Announces
	 */
	public static function ReadAnnounces()
	{
		self::connect();
		$announces_source = file(self::$base_path . self::$announces_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

		// Parse announces
		$announces = array();
		foreach ($announces_source as $announce)
			$announces[] = explode("%", $announce);
		
		return $announces;
	}

	public static function WriteAnnounces($announces)
	{
		self::connect();
		file_put_contents(self::$base_path . self::$announces_file, "");
		foreach ($announces as $announce)
			self::WriteAnnounce($announce);
	}

	public static function WriteAnnounce($announce)
	{
		self::connect();
		file_put_contents(self::$base_path . self::$announces_file, implode("%", $announce) . "\n", FILE_APPEND);
	}

	/**
	 * Chat log
	 */
	public static function ReadChatLog($lines = -1)
	{
		self::connect();
		
		$statement = self::$db->prepare("SELECT `u`.`name` as `name`, `c`.`time` as `time`, `c`.`text` as `text` FROM `chatlog` as `c` LEFT JOIN `users` AS `u` ON `u`.`id` = `c`.`user` ORDER BY `c`.`time` DESC LIMIT ".$lines.";");
		$statement->execute();
		$chatlog = array();
		while ($row = $statement->fetch())
			$chatlog[] = array('time' => $row['time'], 'user' => $row['name'], 'text' => $row['text']);
		/*
		$chatlog_source = file(self::$base_path . "log/chat_log." . date('d-m-Y') . ".txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

		// Parse chat log
		$chatlog = array();
		foreach ($chatlog_source as $chatlog_line)
		{
			$chatlog_line = explode("\t", $chatlog_line);
			$chatlog[] = array('time' => $chatlog_line[0], 'user' => $chatlog_line[1], 'text' => $chatlog_line[2]);
		}

		if ($lines > 0)
		{
			$trim_chatlog = array();
			for ($i = count($chatlog) - $lines; $i < count($chatlog); $i++)
				if ($i >= 0)
					$trim_chatlog[] = $chatlog[$i];
			$chatlog = $trim_chatlog;
		}*/

		return $chatlog;
	}

	public static function WriteChatLog($log)
	{
		self::connect();
		file_put_contents(self::$base_path . "log/chat_log." . date('d-m-Y') . ".txt", "");
		foreach ($log as $line)
			self::WriteAnnounce($line['user'], $line['text'], $line['time']);
	}

	public static function WriteChatLine($user, $line, $time = null)
	{
		self::connect();
	return;
		$statement = self::$db->prepare('INSERT INTO `chatlog` (`user`, `time`, `text`) VALUES (:user, :time, :text);');
		$target = self::ReadPlayer($user);
		if ($target != null)
			$statement->execute(array(':user' => $target->id, ':time' => $time == null ? time() : $time, ':text' => $line));
		
		file_put_contents(self::$base_path . "log/chat_log." . date('d-m-Y') . ".txt", date('H:i:s', ($time == null ? time() : $time)) . "\t$user\t$line\n", FILE_APPEND);
	}

	public static function ReadMessages($user)
	{
		self::connect();
		$out = array();

		$messages = file(self::$base_path . self::$messages_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		foreach ($messages as $k => $message)
		{
			$message = explode("\t", $message);
			$messages[$k] = $message;
			if ($message[0] == $user)
			{
				$out[] = $message;
				unset($messages[$k]);
			}
		}

		self::WriteMessages($messages);

		return $out;
	}

	public static function WriteMessages($messages)
	{
		self::connect();
		file_put_contents(self::$base_path . self::$messages_file, "");
		foreach ($messages as $message)
			self::WriteMessage($message[0], $message[1], $message[2]);
	}

	public static function WriteMessage($to, $from, $message)
	{
		self::connect();
		file_put_contents(self::$base_path . self::$messages_file, "$to\t$from\t$message\n", FILE_APPEND);
	}

	public static function ReadCommandTimes()
	{
		self::connect();
		$times_read = file(self::$base_path . self::$times_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		$commandtimes = array();
		foreach ($times_read as $line)
		{
			$line = explode("\t", $line);
			$command = $line[0];
			$user = $line[1];
			$time = $line[2];
			if (!isset($commandtimes[$line[0]]))
				$commandtimes[$line[0]] = array();
			$commandtimes[$line[0]][$line[1]] = intval($line[2]);
		}
		return $commandtimes;
	}

	public static function WriteCommandTimes($times)
	{
		self::connect();
		file_put_contents(self::$base_path . self::$times_file, "");
		foreach ($times as $command => $data)
			foreach ($data as $user => $time)
				if ($time > time())
					self::WriteCommandTime($command, $user, $time);
	}

	public static function WriteCommandTime($command, $user, $time)
	{
		self::connect();
		file_put_contents(self::$base_path . self::$times_file, "$command\t$user\t$time\n", FILE_APPEND);
	}
	
	public static function WriteLog($response)
	{
		self::connect();
		$lastLine = self::ReadLastLine(self::$base_path . self::$log_file);
		$idLine =  '[' . date("d.m.Y H:i:s") . '] ' . $response->Id() . ': ' . $response->Response();
		$nullLine =  '[' . date("d.m.Y H:i:s") . '] ' . 0 . ': ' . $response->Response();
		if ($lastLine == $idLine || $lastLine == $nullLine)
			return;
		file_put_contents(self::$base_path . self::$log_file, $idLine . "\n", FILE_APPEND);
		if (strpos($idLine, 'violation') !== false)
			file_put_contents(self::$base_path . 'violations.txt', $idLine . "\n", FILE_APPEND);
	}

	public static function ReadGroups()
	{
		self::connect();
		$groups_read = file(self::$base_path . self::$groups_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		$groups = array();
		foreach ($groups_read as $line)
		{
			$line = explode("\t", $line);
			$groups[$line[0]] = $line;
			unset($groups[$line[0]][0]);
		}
		return $groups;
	}

	public static function WriteGroups($groups)
	{
		self::connect();
		file_put_contents(self::$base_path . self::$groups_file, "");
		foreach ($groups as $group => $members)
			file_put_contents(self::$base_path . self::$groups_file, "$group\t" . implode("\t", $members) . "\n", FILE_APPEND);
	}

	public static function ReadKarma()
	{
		self::connect();
		$karma_read = file(self::$base_path . self::$karma_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		$karma = array();
		foreach ($karma_read as $line)
		{
			$line = explode("\t", $line);
			$karma[$line[0]] = intval($line[1]);
		}
		return $karma;
	}

	public static function WriteKarma($karma)
	{
		self::connect();
		file_put_contents(self::$base_path . self::$karma_file, "");
		foreach ($karma as $id => $amount)
			file_put_contents(self::$base_path . self::$karma_file, "$id\t$amount\n", FILE_APPEND);
	}

	public static function WriteVACCheck($id)
	{
		self::connect();
		file_put_contents(self::$base_path . self::$vac_file, "$id\n", FILE_APPEND);
	}

	public static function ReadVACCheck()
	{
		self::connect();
		$read = file(self::$base_path . self::$vac_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		if ($read == "")
			return "";
		file_put_contents(self::$base_path . self::$vac_file, "");
		return $read;
	}
}
