<?PHP

Class User
{
	public $id = 0;
	public $name = "- Unnamed -";
	public $time = 0;
	public $ip = "0.0.0.0";
	public $country = "Nowhere";
	public $online = false;
	public $last = 0;
	public $member = 0;
	public $karma = 0;
	public $vac = false;
	public $color = "#FF66FF";
	public $first = 0;
	public $votes = 0;

	public function __construct($id, $name, $time, $ip, $online, $last, $member, $karma, $vac, $first, $votes)
	{
		$this->id = $id;
		$this->name = $name;
		$this->time = $time;
		$this->ip = $ip;
		$this->online = $online ? true : false;
		$this->last = $last;
		$this->member = $member;
		$this->karma = $karma;
		$this->vac = $vac ? true : false;
		$this->first = $first;
		$this->votes = $votes;

		$vip_times = array('bronze' => 36000, 'silver' => 72000, 'gold' => 180000, 'platinum' => 360000);
		$vip_colors = array('bronze' => '#CD7F32', 'silver' => '#BCC6CC', 'gold' => '#FDD017', 'platinum' => '#E5E4E2');

		$state = null;
		foreach ($vip_times as $name => $time)
			if ($this->time >= $time)
				$state = $name;
		if ($state == null)
			return;

		$this->color = $vip_colors[$state];
	}
}
