<?PHP

class RFTP
{
	private static $instance = null;
	public static function GetInstance()
	{
		if (self::$instance == null)
			self::$instance = new RFTP();
		return self::$instance;
	}

	private $connection = null;

	public function __construct()
	{

	}

	private function connect($depth = 0)
	{
		if ($this->connection == null ||$this->connection == false)
		{
			$this->connection = ftp_connect('server_ftp_ip', 21, 2);
			if ($this->connection === false)
			{
				if ($depth > 3)
				{
					echo "Nope, no ftp. Fuck it!\n";
					return;
				}
				$this->connect($depth+1);
				return;
			}
			ftp_login($this->connection, 'username', 'password');
			ftp_pasv($this->connection, true);
		}
	}

	public function GetAvatar($userid, $nested = false)
	{
		$path = '/path/to/avatars/';
		$this->connect();
		$result = ftp_get($this->connection, $path . $userid . '.bin', '/path/to/serverdata/userdata/' . $userid . '/avatar.bin', FTP_BINARY) or $result = false;
		ftp_close($this->connection);
		if (!$result && !$nested)
		{
			$this->connect();
			return $this->GetAvatar($userid, true);
		}

		if (!$result)
			return false;
		return true;
	}
	
	public function GetBans()
	{
		$this->connect();
		$result = ftp_get($this->connection, '/path/to/local/bans.txt', '/path/to/serverdata/cfg/bans.cfg', FTP_BINARY) or $result = false;
		ftp_close($this->connection);
		
		if (!$result)
			return false;
		return true;
	}
}
