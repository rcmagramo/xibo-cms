<?php
/*
 * Xibo - Digitial Signage - http://www.xibo.org.uk
 * Copyright (C) 2009-13 Daniel Garner
 *
 * This file is part of Xibo.
 *
 * Xibo is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version. 
 *
 * Xibo is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Xibo.  If not, see <http://www.gnu.org/licenses/>.
 */ 
defined('XIBO') or die("Sorry, you are not allowed to directly access this page.<br /> Please press the back button in your browser.");

class faultDAO
{
	private $db;
	private $user;
	
	function __construct(database $db, user $user) 
	{
		$this->db 	=& $db;
		$this->user =& $user;
	}
	
	function displayPage() 
	{
		$db =& $this->db;
		$user =& $this->user;
		$config = new Config($db);

		// Configure the theme
        Theme::Set('environment_check', $config->CheckEnvironment());
        Theme::Set('collect_data_url', 'index.php?p=fault&q=CollectData');

        // Render the Theme and output
        Theme::Render('fault_page');
	}
	
	function CollectData()
	{
		$db =& $this->db;
		
		// We want to output a load of stuff to the browser as a text file.
		header('Content-Type: text/plain');
		header('Content-Disposition: attachment; filename="troubleshoot.txt"');
		header("Content-Transfer-Encoding: binary");
		header('Accept-Ranges: bytes');
		 
		 		
		$config = new Config($db);
		echo "--------------------------------------\n";
		echo 'Environment Checks' . "\n";
		echo "--------------------------------------\n";
		echo $config->CheckEnvironment();
		
		echo "\n";
		echo "--------------------------------------\n";
		echo 'PHP INFO' . "\n";
		echo "--------------------------------------\n";
		$this->phpinfo_array();
		
		echo "\n";
		echo "--------------------------------------\n";
		echo 'LOG Dump' . "\n";
		echo "--------------------------------------\n";
		
		// Get the last 10 minutes of log messages
		$SQL  = "SELECT logdate, page, function, message FROM log ";
		$SQL .= sprintf("WHERE logdate > '%s' ", date("Y-m-d H:i:s", time() - (60*10)));
		$SQL .= "ORDER BY logdate DESC, logid ";

		if(!$results = $db->query($SQL))  
		{
			trigger_error($db->error());
			trigger_error("Can not query the log", E_USER_ERROR);
		}
		
		while ($row = $db->get_row($results)) 
		{
			$logdate 	= Kit::ValidateParam($row[0], _STRING);
			$page 		= Kit::ValidateParam($row[1], _STRING);
			$function 	= Kit::ValidateParam($row[2], _STRING);
			$message 	= Kit::ValidateParam($row[3], _HTMLSTRING);
			
			$output = <<<END
Date: $logdate
Page: $page
Function: $function
Message: $message
\n
END;
			echo $output;
		}
		
		echo "\n";
		echo "--------------------------------------\n";
		echo 'Display Dump' . "\n";
		echo "--------------------------------------\n";
		
		$SQL = <<<SQL
		SELECT  display.displayid, 
				display.display, 
				layout.layout, 
				display.loggedin, 
				display.lastaccessed, 
				display.inc_schedule ,
				display.licensed
		FROM display
		LEFT OUTER JOIN layout ON layout.layoutid = display.defaultlayoutid
		ORDER BY display.displayid
SQL;

		if(!($results = $db->query($SQL))) 
		{
			trigger_error($db->error());
			trigger_error("Can not list displays", E_USER_ERROR);
		}

		while($aRow = $db->get_row($results)) 
		{
			$displayid 		= $aRow[0];
			$display 		= $aRow[1];
			$defaultlayoutid = $aRow[2];
			$loggedin 		= $aRow[3];
			$lastaccessed 	= $aRow[4];
			$inc_schedule 	= $aRow[5];
			$licensed 		= $aRow[6];
			
			$output = <<<END
DisplayID: $displayid
Display: $display
Default Layout: $defaultlayoutid
Logged In: $loggedin
Last Accessed: $lastaccessed
Interleave: $inc_schedule
Licensed: $licensed
\n
END;
			echo $output;
		}

		echo "\n";
		echo "--------------------------------------\n";
		echo 'Settings Dump' . "\n";
		echo "--------------------------------------\n";
		
		$SQL = <<<SQL
		SELECT  *
		FROM setting
		WHERE setting NOT IN ('SERVER_KEY','PHONE_HOME_KEY')
SQL;

		if(!($results = $db->query($SQL))) 
		{
			trigger_error($db->error());
			trigger_error("Can not list Settings", E_USER_ERROR);
		}

		while($row = $db->get_assoc_row($results)) 
		{
			$setting	= $row['setting'];
			$value		= $row['value'];
			
			$output = <<<END
Setting: $setting - Value:   $value
\n
END;
			echo $output;
		}

		echo "\n";
		echo "--------------------------------------\n";
		echo 'Sessions Dump' . "\n";
		echo "--------------------------------------\n";
		
		$SQL = <<<SQL
		SELECT  *
		FROM session
		WHERE IsExpired = 0
SQL;

		if(!($results = $db->query($SQL))) 
		{
			trigger_error($db->error());
			trigger_error("Can not list sessions", E_USER_ERROR);
		}

		while($row = $db->get_assoc_row($results)) 
		{
			$userAgent		= $row['UserAgent'];
			$remoteAddress	= $row['RemoteAddr'];
			$sessionData 	= $row['session_data'];
			
			$output = <<<END
UserAgent: $userAgent
RemoteAddress: $remoteAddress
Session Data
$sessionData
----
\n
END;
			echo $output;
		}

		exit;
	}
	
	/**
	 * Outputs PHP info as an array rather than HTML
	 * Taken from: http://uk2.php.net/phpinfo
	 * @return 
	 * @param $return Object[optional]
	 */
	function phpinfo_array($return=false) 
	{
		ob_start();
		phpinfo(-1);
		
		$pi = preg_replace(
		array('#^.*<body>(.*)</body>.*$#ms', '#<h2>PHP License</h2>.*$#ms',
		'#<h1>Configuration</h1>#',  "#\r?\n#", "#</(h1|h2|h3|tr)>#", '# +<#',
		"#[ \t]+#", '#&nbsp;#', '#  +#', '# class=".*?"#', '%&#039;%',
		'#<tr>(?:.*?)" src="(?:.*?)=(.*?)" alt="PHP Logo" /></a>'
		.'<h1>PHP Version (.*?)</h1>(?:\n+?)</td></tr>#',
		'#<h1><a href="(?:.*?)\?=(.*?)">PHP Credits</a></h1>#',
		'#<tr>(?:.*?)" src="(?:.*?)=(.*?)"(?:.*?)Zend Engine (.*?),(?:.*?)</tr>#',
		"# +#", '#<tr>#', '#</tr>#'),
		array('$1', '', '', '', '</$1>' . "\n", '<', ' ', ' ', ' ', '', ' ',
		'<h2>PHP Configuration</h2>'."\n".'<tr><td>PHP Version</td><td>$2</td></tr>'.
		"\n".'<tr><td>PHP Egg</td><td>$1</td></tr>',
		'<tr><td>PHP Credits Egg</td><td>$1</td></tr>',
		'<tr><td>Zend Engine</td><td>$2</td></tr>' . "\n" .
		'<tr><td>Zend Egg</td><td>$1</td></tr>', ' ', '%S%', '%E%'),
		ob_get_clean());
		
		$sections = explode('<h2>', strip_tags($pi, '<h2><th><td>'));
		unset($sections[0]);
		
		$pi = array();
		foreach($sections as $section)
		{
			$n = substr($section, 0, strpos($section, '</h2>'));
			preg_match_all(
			'#%S%(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?%E%#',
			$section, $askapache, PREG_SET_ORDER);
			foreach($askapache as $m)
			$pi[$n][$m[1]]=(!isset($m[3])||$m[2]==$m[3])?$m[2]:array_slice($m,2);
		}
		
		return ($return === false) ? print_r($pi) : $pi;
	}
}
?>
