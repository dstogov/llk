<?php

function parse_test($test, &$name, &$code, &$expect, &$opt) {
	$text = @file_get_contents($test);
	if (!$text) {
		return false;
	}
	$p1 = strpos($text, '--TEST--');
	$p2 = strpos($text, '--ARGS--');
	$p3 = strpos($text, '--CODE--');
	$p4 = strpos($text, '--EXPECT--');
	if ($p1 === false || $p3 === false || $p4 === false || $p1 > $p3 || $p3 > $p4) {
		return false;
	}
	if ($p2 === false) {
		$p2 = $p3;
		$opt = "";
	} else if ($p1 > $p2 || $p2 > $p3) {
		return false;
	} else {
		$opt = trim(substr($text, $p2 + strlen('--ARGS--'), $p3 - $p2 - strlen('--ARGS--')));
	}		
	$name = trim(substr($text, $p1 + strlen('--TEST--'), $p2 - $p1 - strlen('--TEST--')));
	$code = trim(substr($text, $p3 + strlen('--CODE--'), $p4 - $p3 - strlen('--CODE--')));
	$expect = trim(substr($text, $p4 + strlen('--EXPECT--')));
	$expect = str_replace("\r", "", $expect);
	return true;
}

function run_test($test, $name, $code, $expect, $opt) {
	$base   = substr($test, 0, -3);
	$gramma = $base . ".g";
	$output = $base . ".out";
	@unlink($grammar);
	@unlink($output);
	@unlink("$base.exp");
	@unlink("$base.diff");
	if (!@file_put_contents($gramma, $code)) {
		return false;
	}
	if (@system("php llk.php $opt $gramma > $output") != 0) {
		return false;
	}
	$out = @file_get_contents($output);
	if ($out === false) {
		return false;
	}
	$out = trim($out);	
	$out = str_replace("\r", "", $out);
	if ($out !== $expect) {
		if (!@file_put_contents("$base.exp", "$expect\n")) {
			return false;
		}
		if (@system("diff $output $base.exp > $base.diff") != 0) {
			return false;
		}
		return false;
	}
	@unlink($gramma);
	@unlink($output);
	return true;
}

function run_tests() {
	$tests = glob("tests/*.gt");
	$bad = array();
	$failed = array();
	foreach($tests as $test) {
		if (parse_test($test, $name, $code, $expect, $opt)) {
			if (!run_test($test, $name, $code, $expect, $opt)) {
				$failed[$test] = $name;
			}
		} else {
			$bad[] = $test;
		}
	}
	echo "-------------------------------\n";
	echo "Test Summary\n";
	echo "-------------------------------\n";
	if (count($bad) > 0) {
		echo "Bad tests:  " . count($bad) . "\n";
		echo "-------------------------------\n";
		foreach ($bad as $test) {
			echo "$test\n";
		}
		echo "-------------------------------\n";
	}
	echo "Total: " . count($tests) . "\n";
	echo "Passed: " . (count($tests) - count($failed)) . "\n";
	echo "Failed: " . count($failed) . "\n";
	if (count($failed) > 0) {
		echo "-------------------------------\n";
		foreach ($failed as $test => $name) {
			echo "$name [$test]\n";
		}
	}
	echo "-------------------------------\n";
	@unlink('parser.php');
}

run_tests();
