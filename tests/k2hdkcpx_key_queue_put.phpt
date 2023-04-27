--TEST--
k2hdkcpx_key_queue_put
--CREDITS--
K2HDKC PHP Extension library

Copyright 2023 Yahoo Japan Corporation.

K2HDKC is k2hash based distributed KVS cluster.
K2HDKC uses K2HASH, CHMPX, FULLOCK libraries. K2HDKC supports
distributed KVS cluster server program and client libraries.

For the full copyright and license information, please view
the license file that was distributed with this source code.
--EXTENSIONS--
k2hdkcpx
--FILE--
<?php

if(!file_exists("./tests/slave.ini")){
	echo "NG";
}else{
	// run chmpx server
	exec("chmpx -conf ./tests/server.ini -d silent > /tmp/server.log 2>&1 &");
	time_nanosleep(0, 500 * 1000 * 1000);
	$procid = exec("ps ax | grep chmpx | grep tests/server.ini | grep -v grep | awk '{print $1}'");
	
	// run chmpx slave
	exec("chmpx -conf ./tests/slave.ini -d silent > /tmp/slave.log 2>&1 &");
	time_nanosleep(0, 500 * 1000 * 1000);
	$procid_slave = exec("ps ax | grep chmpx | grep tests/slave.ini | grep -v grep | awk '{print $1}'");

	// run k2hdkc
	exec("k2hdkc -conf ./tests/server.ini -d silent > /tmp/k2hdkc.log 2>&1 &");
	time_nanosleep(0, 500 * 1000 * 1000);
	$procid_k2hdkc = exec("ps ax | grep k2hdkc | grep tests/server.ini | grep -v grep | awk '{print $1}'");

	// echo $procid_k2hdkc; 
	$handle = k2hdkcpx_open("./tests/slave.ini");

	if(false !== $handle){
		$prefix = "p1";
		$key = "key";
		$val = "val";
		k2hdkcpx_key_queue_put($handle, $prefix, $key, $val);
		time_nanosleep(0, 250 * 1000 * 1000);

		$arr = array($key => $val);
		if ($arr === k2hdkcpx_key_queue_get($handle, $prefix)) {
			echo "OK";
		}else{
			echo "NG";
		}
		k2hdkcpx_close($handle);
	}else{
		echo "NG";
	}
	// kill k2hdkc
	if($procid_k2hdkc == sprintf("%d", intval($procid_k2hdkc))){
		$killcmd = "kill -HUP " . $procid_k2hdkc . " >> /tmp/test_k2hdkc.log 2>&1";
		exec($killcmd);
	}
	// kill chmpx slave
	if($procid_slave == sprintf("%d", intval($procid_slave))){
		$killcmd = "kill -HUP " . $procid_slave . " >> /tmp/test_chmpx_slave.log 2>&1";
		exec($killcmd);
	}
	// kill chmpx server
	if($procid == sprintf("%d", intval($procid))){
		$killcmd = "kill -HUP " . $procid . " >> /tmp/test_chmpx_server.log 2>&1";
		exec($killcmd);
	}
}

?>
--EXPECT--
OK
--CLEAN--
<?php
$output = array();
$result_code = 0;

$k2hdkc_procid = exec("ps ax | grep k2hdkc | grep tests/server.ini | grep -v grep | awk '{print $1}'");
$svr_procid = exec("ps ax | grep chmpx | grep tests/server.ini | grep -v grep | awk '{print $1}'");
$slv_procid = exec("ps ax | grep chmpx | grep tests/slave.ini | grep -v grep | awk '{print $1}'");

if(!empty($k2hdkc_procid) || !empty($svr_procid) || !empty($slv_procid)){
	$kill_cmd = "kill -HUP " . $k2hdkc_procid . " " . $svr_procid . " " . $slv_procid;
	exec($kill_cmd, $output, $result_code);
	time_nanosleep(0, 500 * 1000 * 1000);

	$kill_cmd = "kill -KILL " . $k2hdkc_procid . " " . $svr_procid . " " . $slv_procid;
	exec($kill_cmd, $output, $result_code);
	time_nanosleep(0, 500 * 1000 * 1000);

	$k2hdkc_procid = exec("ps ax | grep k2hdkc | grep tests/server.ini | grep -v grep | awk '{print $1}'");
	$svr_procid = exec("ps ax | grep chmpx | grep tests/server.ini | grep -v grep | awk '{print $1}'");
	$slv_procid = exec("ps ax | grep chmpx | grep tests/slave.ini | grep -v grep | awk '{print $1}'");

	if(!empty($k2hdkc_procid) || !empty($svr_procid) || !empty($slv_procid)){
		die("failed to stop some k2hdkc/chmpx processes");
	}
}
?>
--DESCRIPTION--
/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noexpandtab sw=4 ts=4 fdm=marker
 * vim<600: noexpandtab sw=4 ts=4
 */
