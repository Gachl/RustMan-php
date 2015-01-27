<?PHP

class Language
{
	private static $texts_de = array(
	'vipgrats1' => 'Gratulation \1! Du hast \2 Stunden hier verbracht.',
	'vipgrats2' => 'Du hast nun VIP \1 erreicht!',

	'adminauth' => 'Ein Administrator ist jetzt online. Bei Problemen, wende Dich an \1.',
	'modauth'   => 'Ein Moderator ist jetzt online. Bei Problemen, wende Dich an \1.',
	'noauth'	  => 'Das wünschst du dir...',
	'deauth'    => 'Abgemeldet.',

	'hour'			=> 'Eine Stunde ist vergangen. Es ist nun \1 Uhr in Zentraleuropa.',
	'airdrop'		=> '[color#FF0000]Ein Airdrop ist im Anflug!',

	'left'			=> '\1 hat uns verlassen.',
	'join'			=> '\1 besucht uns von \2.',
	'rusfag'		=> '\1 wurde gebannt weil er ein Russe ist.',
	'back'			=> 'Willkommen zurück, \1!',	
	'msg'				=> 'Nachricht von \1 an \2:',
	'karmawarn'	=> '[color#FF0000]ACHTUNG! \1 ist [color#FF0000]sehr gefährlich[color#BBBBBB]!',
	'welcome'		=> 'Willkommen, \1!',
	'first'			=> 'Du besuchst uns [color#FF0000]zum ersten Mal[color#BBBBBB]. Gebe [color#FF0000]/hilfe [color#BBBBBB]ein oder besuche [color#FF0000]bloodisgood.org/rust[color#BBBBBB].',

	'inhour'		=> 'Es ist \1 [color#BBBBBB]Uhr in dieser Welt.',
	
	'reportw'		=> 'Was möchtest Du melden?',
	'report'		=> 'Deine Meldung wurde an einen Admin gesandt.',

	'dtime'			=> 'Es ist \1 in Zentraleuropa und \2 in dieser Welt.',

	'suggestw'	=> 'Was möchtest Du vorschlagen?',
	'suggest'		=> 'Danke für Deinen Vorschlag, \1!',

	'help1'			=> '[color#FF0000]kit[color#BBBBBB]: Erhalte einen Stein, Fackel, zwei Bandagen und Schokolade.',
	'help2'			=> '[color#FF0000]lost[color#BBBBBB]: Teleportiere an eine bekannte Position wenn du dich verirrst oder feststeckst.',
	'help3'			=> '[color#FF0000]count[color#BBBBBB]: Zeige die Anzahl aller verbundenen Spieler an.',
	'help4'			=> '[color#FF0000]report[color#BBBBBB]: Sende einen Report an einen Admin (kürzeste Antwortzeit).',
	'help5'			=> '[color#BBBBBB]Für eine komplette Liste aller verfügbaren Befehle, besuche [color#FF0000]http://bloodisgood.org/rust',

	'kitw'			=> 'Konnte Kit \1 nicht finden.',
	'kitt'			=> 'Kit \1 an \2 ausgeliefert.',
	'kit'				=> 'Kit an \1 ausgeliefert.',

	'whow'			=> 'Konnte keinen Benutzer \"\1\" finden.',
	'ping'			=> '\1 hat einen Ping von \2 ms.',

	'noone'			=> 'Niemand ist im Moment online. Wie geht das?',
	'onlyone'		=> 'Nur du bist im Moment online.',
	'someone'		=> 'Es sind im Moment \1 Spieler online.',
	'someonel'	=> 'Folgende \1 Spieler sind im Moment online:',

	'admins'		=> 'Es sind gerade \1 admin online:',
	'adminsw'		=> 'Es ist gerade kein Admin online. Benutze [color#FF0000]/report [color#BBBBBB]um einen zu kontaktieren.',

	'vips'			=> 'Folgede VIPs sind jetzt online:',

	'who'				=> '\1 (\2) ist \3, war \4 Stunden hier und ist \5.',
	'member'		=> '\1 ist Mitglied von \2.',
	'last'			=> 'Zuletzt wurde \1 gesehen am \2 gesehen.',

	'rescued'		=> '\1 wurde gerettet.',

	'watch'			=> '\1 wird jetzt überwacht. Wir beobachten Dich!',


	'tpoff'			=> '\1 ist momentan [color#FF0000]offline[color#BBBBBB].',
	'tpask'			=> '\1, es möchte sich \2 zu Dir teleportieren. Akzeptiere mit [color#FF0000]/tpa[color#BBBBBB].',
	'tphow'			=> 'Es sieht so aus als wüsstest du nicht, wie das funktioniert. Schau bitte auf [color#FF0000]bloodisgood.org/rust [color#BBBBBB].',
	'tpnoone'		=> 'Niemand möchte sich zu dir teleportieren.',
	'tpok'			=> '\1 wurde zu \2 bewegt.',
	'tpwut'			=> 'Ich kann ja kaum erraten wo Du hin möchstest.',
	'tpadm'			=> '\1 wurde teleportiert.',

	'msgys'			=> 'Wieso sagst du das \1 nicht selbst?',
	'msgok'			=> '\1 wird Deine Nachricht beim nächsten Besuch erhalten.',

	'othervote'	=> 'Eine andere Abstimmung läuft bereits.',
	'lessvote1'	=> 'Es müssen mindestens sechs Spieler online sein um ein Kickvote zu starten.',
	'lessvote2'	=> 'Benutze stattdessen bitte den [color#FF0000]/report [color#BBBBBB]Befehl.',
	'offvote'		=> '\1 ist offline und kann nicht gekickt werden.',
	'kickvote1'	=> '[olor #FFFFFF]** [color#FF0000]KICK VOTE [color#FFFFFF]**',
	'kickvote2'	=> 'Ein Kickvote gegen \1 wurde gestartet.',
	'kickvote3'	=> 'Wenn du \1 kicken willst, schreibe [color#FF0000]/ja',
	'kickvote4'	=> 'Wenn du dagegen bist, schreibe [color#FF0000]/nein',
	'kickvote5'	=> '[color#FFFFFF]** ** ** **',
	'nosurvey'	=> 'Es ist keine Abstimmung aktiv.',
	'alreadyv'	=> 'Du hast bereits abgestimmt.',
	'acceptv'		=> 'Deine Stimme wurde akzeptiert.',
	'voteok'		=> 'Die Abstimmung war erfolgreich!',
	'votefail'	=> 'Die Abstimmung hat fehlgeschlagen.',
	'vkickm'		=> '\1 wurde gekickt, weil Ihr es so wolltet.',

	'suicide1'	=> 'Der Server kann dich nicht töten.',
	'suicide2'	=> 'Drücke [color#FF0000]F1 [color#BBBBBB], gebe [color#FF0000]suicide [color#BBBBBB]ein und bestätige mit Enter.',

	'remove1'		=> 'Foundations, Pillars und Ceilings können nicht entfernt werden.',
	'remove2'		=> 'Für alles Andere, frage einen Admin oder Moderator.',

	'enterpvp'	=> '\1 hat die Arena betreten.',

	'townw'			=> 'In welche Stadt möchtest du reisen?',
	'townhuh'		=> 'Ich habe keine Ahnung wo du hin möchtest.',
	'townnope'	=> 'Du kannst dort nicht hinreisen.',
	'townok'		=> '\1 ist nach \2 gereist.',
	'towngo'		=> 'Du möchtest nach \1 reisen. Gebe den Befehl zur Bestätigung erneut ein.',

	'service1'	=> 'mumble.bloodisgood.org',
	'service2'	=> 'teamspeak.bloodisgood.org',
	'service3'	=> '[color#FF0000]http://bloodisgood.org/rust',

	'location'	=> 'Du befindest dich bei [color#FF0000]X: [color#BBBBBB]\1 ([color#FF0000]Y: [color#BBBBBB]\2) [color#FF0000]Z: [color#BBBBBB]\3.',

	'algroup'		=> 'Du bist bereits Mitglied in einer Gruppe.',
	'groupname'	=> 'Eine Gruppe mit diesem Namen existiert bereits.',
	'groupcr1'	=> '\1 hat die Gruppe \2 gegründet.',
	'groupcr2'	=> 'Lade andere Spieler mit dem [color#FF0000]/ginvite [color#BBBBBB]Befehl ein.',
	'grouplead'	=> 'Du musst Gruppenleiter sein um Spieler einzuladen oder zu kicken.',
	'algroupex'	=> '\1 ist bereits Mitglied in einer Gruppe.',
	'groupoff'	=> '\1 ist momentan [color#FF0000]offline [color#BBBBBB]und kann nicht eingeladen werden.',
	'groupinv1'	=> '\1, du wurdest eingeladen \2 beizutreten.',
	'groupinv2'	=> 'Gebe [color#FF0000]/gaccept [color#BBBBBB]ein um zu akzeptieren.',
	'nogroupin'	=> 'Du hast keine ausstehenden Einladungen.',
	'groupok'		=> '\1 ist \2 beigetreten.',
	'nogroup'		=> 'Du bist in keiner Gruppe.',
	'groupbye'	=> 'Du hast \1 verlassen.',
	'grouplist'	=> '\1 hat folgende Mitglieder:',
	'groupon'		=> 'Folgende Gruppen sind existieren:',

	'selfkarma'	=> 'Kriech nicht in deinen eigenen Arsch! Du kannst dir selbst kein Karma geben!',
	'karmapush'	=> 'Du kannst das Karma dieses Spielers erst wieder in \1 Stunden ändern.',
	'karma'			=> '\1 hat ein Karma von \2.',

	'cmdtime'		=> '\1 kann diesen Befehl erst wieder in \2 \3 verwenden.',
	'wait'			=> 'Bitte warte noch \1 Sekunden und gebe den Befehl erneut ein.',
	'noperm'		=> 'Du hast nicht die benötigten Rechte um das zu tun.',
	'what'			=> 'Hm, was?',
	'antivip'		=> 'Sei nicht verrückt! VIPs brauchen das nicht!',
	'onlyvip'		=> 'Nur VIPs können das.',
	'silvervip'	=> 'Nur Silver oder höhrere VIPs können das.',
	'tpwarn'		=> '[color#FF0000]WARNUNG: [color#BBBBBB]Gebe den Befehl erneut ein wenn du dir 100% sicher bist, was du gerade tust.'
	);

	public static function Text($text, $args = array(), $locale = "Germany")
	{
		$texts = self::$texts_de;

		/*
		if ($locale != "Germany")
			$texts = self::$texts_en;
		 */

		if (!isset($texts[$text]))
			return "Uuuuuh da ist was schlimmes passiert was nicht hätte passieren dürfen.";
		$text = $texts[$text];
		
		for ($i = 0; $i < count($args); $i++)
		{
			$arg = $args[$i];
			if (preg_match('/\[color #......\]/', $arg))
				$arg = $arg . '[color#BBBBBB]';
			$text = str_replace("\\" . ($i + 1), $arg, $text);
		}
		if (!preg_match('/^\[color #......\]/', $text))
			$text = "[color#BBBBBB]$text";

		return $text;
	}
}
