<?PHP
/**
 * Created by Gachl
 * admin@bloodisgood.org
 * Please give credit where credit belongs.
 * 
 * Resources used:
 * * http://php.net/pack
 * * https://developer.valvesoftware.com/wiki/Source_RCON_Protocol
 * * My own Rust server
 *
 * License: http://creativecommons.org/licenses/by-nc-sa/4.0/
 */
class RustRcon
{
	private $debug = false; // activate this flag if you want to see verbose communication
	private $packet_id = 0;
	private $stream;
	private $rcon_password = "";
	private $server = "";
	private $port = 0;

	private static $instance = null;
	public static function GetInstance()
	{
		if (self::$instance == null)
			self::$instance = new RustRcon();
		return self::$instance;
	}

	private function __construct()
	{

	}

	public function SetTimeout($seconds, $microseconds)
	{
		stream_set_timeout($this->stream, $seconds, $microseconds); // don't take too long for requests
	}

	public function Connect($server, $port, $rcon_password)
	{
		$this->rcon_password = $rcon_password;
		$this->server = $server;
		$this->port = $port;

		$error = 0;
		$errorstring = "";

		$this->stream = @fsockopen("tcp://$server", intval($port), $error, $errorstring);

		if ($error !== 0 || $errorstring !== "")
			throw new Exception("Could not connect to rust server: Error($error) $errorstring.");

		stream_set_timeout($this->stream, 0, 25000); // don't take too long for requests
		$auth = $this->transmit($rcon_password, 3); // 3 = Auth flag
	}

	public function __destruct()
	{
		$this->Disconnect();
	}

	public function Disconnect()
	{
		fclose($this->stream);
	}

	public function Send($command)
	{
		return $this->transmit($command);
	}

	private function transmit($packet_body, $packet_type = 2)
	{
		$packet_id = ++$this->packet_id;
		$request = pack('VV', $packet_id, $packet_type) . $packet_body . "\x00\x00";
		$request = Pack('V', strlen($request)) . $request;

		if ($this->debug)
		{
			echo "> LEN " . strlen($request) . "\n" .
					 "> ID $packet_id\n" .
					 "> TYPE $packet_type\n" .
					 "> BODY $packet_body\n\n\n";
		}
		
		$result = fwrite($this->stream, $request);
		if ($result != strlen($request))
		{
			echo "Pipe broken, reconnecting...\n";
			$this->Disconnect();
			$this->Connect($this->server, $this->port, $this->rcon_password);
			return $this->transmit($packet_body, $packet_type);
		}

		return $packet_id;
	}
	
	private $idqueue = array();
	private $queue = array();

	public function Read($id = 0)
	{
		$read = $this->internal_read();
		while ($read != null)
		{
			if ($read->Id() > 1)
			{
				if (isset($this->idqueue[$read->Id()]))
				{
					//if ($this->idqueue[$read->Id()]->IsMultipart())
						$this->idqueue[$read->Id()] = new RustRconResponse($read->Id(), $this->idqueue[$read->Id()]->Response() . $read->Response());
					/*else
						$this->idqueue[$read->Id()] = new RustRconResponse($read->Id(), $read->Response() . $this->idqueue[$read->Id()]->Response());*/
				}
				else
					$this->idqueue[$read->Id()] = $read;
			}
			elseif ($read->Id() == 0)
				$this->queue[] = $read;
			$read = $this->internal_read();
		}

		if ($id != 0)
		{
			if (isset($this->idqueue[$id]))
			{
				if ($this->idqueue[$id]->IsMultipart())
				{
					$this->idqueue[$id] = new RustRconResponse($this->idqueue[$id]->Id(), $this->idqueue[$id]->Response());
					return null;
				}
				$package = $this->idqueue[$id];
				unset($this->idqueue[$id]);
				return $package;
			}
			return null;
		}
		
		$package = null;
		if (count($this->queue) > 0)
			$package = array_shift($this->queue);
		return $package;
	}

//	public function Read($queued = true)
	public function internal_read()
	{
		if (feof($this->stream))
			return null;
		
		// Let's do this
		stream_set_timeout($this->stream, 0, 25000); // don't take too long for requests
		$size = fread($this->stream, 4);
		if (strlen($size) !== 4)
			return null; // Invalid!

		$size = unpack('V', $size);
		$id = unpack('V', fread($this->stream, 4));
		$type = unpack('V', fread($this->stream, 4));
		if ($size[1] > 9000)
		{
			echo $size[1] . " is too big.\n";
			return null;
		}

		$body = fread($this->stream, $size[1] - 8);

		if ($this->debug)
		{
			echo "< LEN ${size[1]}\n" .
					 "< ID ${id[1]}" . ($id[1] == 0xffffffff ? ' (-1)' : '') . "\n" . 
					 "< TYPE ${type[1]}\n" .
					 "< BODY " . $body . "\n\n\n";
		}

		if ($id[1] == 0xffffffff)
			throw new Exception("Rcon password invalid.");

		return new RustRconResponse($id[1], $body);
	}
}

class RustRconResponse
{
	private $id = -1;
	private $body = null;
	private $multipart = false;

	public function __construct($id, $body)
	{
		$this->id = intval($id);
		$this->body = trim($body);
		if (substr($body, -2) == "\x00\x00")
			$this->multipart = true;
	}

	public function ID()
	{
		return $this->id;
	}

	public function Response()
	{
		return $this->body;
	}

	public function IsMultipart()
	{
		return $this->multipart;
	}
}

