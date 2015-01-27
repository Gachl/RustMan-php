<?PHP

class IRC
{
	private static $in_path = '/tmp/rust_irc_in';
	private static $out_path = '/tmp/rust_irc_out';

	public static function ReadFromIRC()
	{
		$irc_source = file(self::$out_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		file_put_contents(self::$out_path, "");

		// Parse IRC text
		$irc = array();
		foreach ($irc_source as $irc_line)
		{
			$irc_line = explode("\t", $irc_line);
			$irc[] = array('user' => $irc_line[0], 'text' => $irc_line[1]);
		}

		return $irc;
	}

	public static function WriteToIRC($user, $message)
	{
		file_put_contents(self::$in_path, "$user\t$message\n", FILE_APPEND);
	}
}
