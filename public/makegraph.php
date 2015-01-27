<?PHP
require_once ('jpgraph-3.5.0b1/src/jpgraph.php');
require_once ('jpgraph-3.5.0b1/src/jpgraph_line.php');
require_once ('jpgraph-3.5.0b1/src/jpgraph_date.php');

$db = new PDO('mysql:host=localhost;dbname=dbname;charset=utf8', "dbuser", 'dbpw');

$hquery = $db->prepare("SELECT `user`, `action`, `time` FROM `visitors` WHERE `time` > :hold ORDER BY `time`;");
$hquery->execute(array(":hold" => (time() - 8 * 60 * 60) * 10000000 + 621356040000000000));

/*$data = file('data/history.txt', FILE_IGNORE_NEW_LINES + FILE_SKIP_EMPTY_LINES);
$history = array();
foreach ($data as $line)
{
	$line = explode("\t", $line);
	$history[intval($line[0])] = intval($line[1]);
}*/
$history = array();
$prev_key = -1;
while ($row = $hquery->fetch())
{
	if ($row['action'] == 'key')
		$prev_key = intval($row['user']);
	elseif ($row['action'] == 'join' && $prev_key != -1)
			$prev_key++;
	elseif ($row['action'] == 'quit' && $prev_key != -1)
			$prev_key--;

	if ($prev_key != -1)
		$history[(intval($row['time']) - 621356040000000000) / 10000000] = $prev_key;;
}

$data = "";

$min = strtotime("-8 hours");
$max = time();

$relevant = array();
$high = 0;
$last = array(-1, -1);
foreach ($history as $time => $count)
{
	if ($time <= $max && $time >= $min)
	{
		if ($count > $high)
			$high = $count;

		if ($count != $last[1] && $last[1] >= 0)
			$relevant[$time-1] = $last[1];

		$relevant[$time] = $count;
		$last = array($time, $count);
	}
}
$relevant[time()-1] = $last[1];
$relevant[time()] = array_pop($relevant);
$high = $high < 10 ? 10 : $high;
$history = $relevant;

$range = $max - $min;

$graph = new Graph(1200,550);
$graph->SetScale("datlin");

$theme_class=new UniversalTheme;

$graph->SetTheme($theme_class);
$graph->img->SetAntiAliasing(false);
$graph->title->Set('Player Count');
$graph->title->SetColor('#CCC');
$graph->SetBox(false);

$bgcolor = "#444";
$graph->SetBackgroundGradient($bgcolor, $bgcolor, GRAD_HOR, BGRAD_MARGIN);

$graph->yaxis->HideZeroLabel();
$graph->yaxis->HideLine(false);
$graph->yaxis->HideTicks(false,false);
$graph->yaxis->SetColor("#CCC");
$graph->xaxis->SetColor("#CCC");

$graph->ygrid->Show();
$graph->ygrid->SetLineStyle("solid");
$graph->ygrid->SetColor('#0F2F0F');
$graph->ygrid->SetFill(true, "#000", "#000");

$graph->xgrid->Show();
$graph->xgrid->SetLineStyle("solid");
//$graph->xaxis->SetTickLabels(array_keys($history));
$graph->xaxis->SetLabelAngle(90);
$graph->xgrid->SetColor('#2F0F0F');

$graph->xaxis->scale->SetTimeAlign(MINADJ_15);
$graph->xaxis->scale->ticks->Set(60*15);
$graph->xaxis->scale->SetDateFormat('H:i');

$graph->yaxis->scale->SetAutoMax($high);
$graph->yaxis->scale->SetAutoMin(0);

// Create the first line
$p1 = new LinePlot(array_values($history), array_keys($history));
$graph->Add($p1);
$p1->SetColor("#DF2E23");
$p1->SetLegend('Player Count');
$p1->SetWeight(1);
/*$p1->mark->SetType(MARK_FILLEDCIRCLE,'',0.1);
$p1->mark->SetColor('#55bbdd');
$p1->mark->SetFillColor('#55bbdd');
$p1->SetCenter();*/

$graph->legend->SetFrameWeight(1);

// Output line
$graph->Stroke();
