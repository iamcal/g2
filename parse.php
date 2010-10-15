<?
	$cfg = array(
		'ip'		=> "127.0.0.1",
		'port'		=> 8652,
		'timeout'	=> 3.0,
		'interactive'	=> true,
		'read_buffer'	=> 16384,
	);

	function fetch($url){

		$fp = fsockopen($GLOBALS['cfg']['ip'], $GLOBALS['cfg']['port'], $errno, $errstr, $GLOBALS['cfg']['timeout']);
		if (!$fp){
			return array(
				'ok'	=> 0,
				'error'	=> 'cant_connect',
			);
		}

		if ($GLOBALS['cfg']['interactive']){
			$rc = fwrite($fp, "$url\n");
			if ($rc < strlen($url)+1){
				return array(
					'ok'	=> 0,
					'error'	=> 'cant_write',
				);
			}
		}

		$buffer = '';
		while (!feof($fp)){
			$data = fread($fp, $GLOBALS['cfg']['read_buffer']);
			$buffer .= $data;
		}

		fclose($fp);

		return array(
			'ok'	=> 1,
			'xml'	=> $buffer,
		);
	}


	echo "fetching...";
	$ret = fetch('/www/www1');
	echo "ok\n";
	#print_r($ret);

	echo $ret['xml'];
?>
