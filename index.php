<?
	#
	# the config
	#

	$hosts = array(
		'www1.1b.east.us' => array('geo' => 'us-east', 'dc' => 'us-east-1b', 'role' => 'www', 'env' => 'prod', 'size' => 'small', 'rack' => 48),
		'www2.1b.east.us' => array('geo' => 'us-east', 'dc' => 'us-east-1b', 'role' => 'www', 'env' => 'prod', 'size' => 'small', 'rack' => 48),
		'www3.1b.east.us' => array('geo' => 'us-east', 'dc' => 'us-east-1b', 'role' => 'www', 'env' => 'prod', 'size' => 'small', 'rack' => 49),

		'www1.1c.east.us' => array('geo' => 'us-east', 'dc' => 'us-east-1c', 'role' => 'www', 'env' => 'prod', 'size' => 'small', 'rack' => 50),
		'www2.1c.east.us' => array('geo' => 'us-east', 'dc' => 'us-east-1c', 'role' => 'www', 'env' => 'prod', 'size' => 'medium', 'rack' => 49),
		'www3.1c.east.us' => array('geo' => 'us-east', 'dc' => 'us-east-1c', 'role' => 'www', 'env' => 'prod', 'size' => 'small', 'rack' => 50),
	);

	$hide_tags = array(
		array('geo', 'dc', 'after'),	# hide 'geo' after we've chosen 'dc'
		array('rack', 'dc', 'before'),	# hide 'rack' before we've chosen 'dc'
	);


	#
	# get a list of all tags
	#

	$all_tags = array();
	foreach ($hosts as $host){
		foreach ($host as $k => $v){
			$all_tags[$k]++;
		}
	}


	#
	# get the current filter
	#

	$filter = array();

	foreach ($all_tags as $k => $v){
		if ($_GET[$k]){
			$filter[$k] = $_GET[$k];
		}
	}


	#
	# some filters aren't allow - e.g. remove the rack filter
	# unless there's a dc filter
	#

	clean_filter($filter);

	function clean_filter(&$filter){

		foreach ($GLOBALS[hide_tags] as $hide){
			if ($hide[2] == 'before'){
				if (!$filter[$hide[1]]){
					unset($filter[$hide[0]]);
				}
			}
		}
	}


	#
	# filter the host list
	#

	$filtered_hosts = $hosts;
	foreach ($filtered_hosts as $k => $host){
		foreach ($filter as $k2 => $v2){
			if ($host[$k2] != $v2){
				unset($filtered_hosts[$k]);
				break;
			}
		}
	}


	#
	# get the tags we're allowed to drill down into
	#

	$drill_tags = $all_tags;

	foreach ($filter as $k => $v){
		unset($drill_tags[$k]);
	}

	foreach ($hide_tags as $hide){
		if ($hide[2] == 'after' ) if ( $filter[$hide[1]]) unset($drill_tags[$hide[0]]);
		if ($hide[2] == 'before') if (!$filter[$hide[1]]) unset($drill_tags[$hide[0]]);
	}

	$drill_tags = array_keys($drill_tags);


	#
	# what drill down options are there for each tag in our filtered group?
	#

	$drill = array();
	foreach ($drill_tags as $tag){
		$drill[$tag] = array();
		foreach ($filtered_hosts as $host){
			$drill[$tag][$host[$tag]]++;
		}
	}


	#
	# format links
	#

	function get_filter_link($action, $tag, $value=null){

		$filter = $GLOBALS[filter];
		if ($action == 'add') $filter[$tag] = $value;
		if ($action == 'remove') unset($filter[$tag]);

		clean_filter($filter);

		$pairs = array();
		foreach ($filter as $k => $v){
			$pairs[] = urlencode($k).'='.urlencode($v);
		}
		if (!count($pairs)) return './';
		return './?'.implode('&', $pairs);
	}

?>

	<h1>Host drill-down</h1>

	<table border="1" cellpadding="10">
		<tr valign="top">
			<td>

	<b>Current filters</b><br />
	<br />
<?
	foreach ($filter as $k => $v){
		$link = get_filter_link('remove', $k);
		echo "$k = $v [<a href=\"$link\">x</a>]<br />";
	}
	if (!count($filter)){
		echo "<i>None</i><br />";
	}
?>	
	<br />
	<br />

	<b>Drill down</b><br />
	<br />
<?
	foreach ($drill as $tag => $choices){
		echo "$tag:<br />";
		foreach ($choices as $k => $v){
			$link = get_filter_link('add', $tag, $k);
			echo "&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"$link\">$k</a> ($v)<br />";
		}
	}
?>

			</td>
			<td width="400">

	<h2>Filtered Hosts</h2>
	<ul>
<? foreach ($filtered_hosts as $k => $v){ ?>
		<li> <?=$k?> </li>
<? } ?>
	</ul>

			</td>
		</tr>
	</table>

	<h3>Notes:</h3>
	<p>You can't drill down to rack until you choose a data center.</p>
	<p>You can't drill down to geo after you choose a data center.</p>
