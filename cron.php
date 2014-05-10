<?php

chdir(dirname(__FILE__));

require "lib/config.php";

$now = time();
$now_formatted = date("Y-m-d H:i:s", $now);

foreach ($monitorz->listAllKeys() as $key)
{
	$monitor_config = $monitorz->getMonitorConfig($key);
	
	$last_datapoint = $monitorz->getLastDataPoint($key);
	$is_down = $now - $last_datapoint->timestamp > $monitor_config->expected_update_interval;

	$case_opened = $monitorz->caseForKey($key);

	if ($case_opened)
	{
		$case_opened_formatted = date("Y-m-d H:i:s", $case_opened);

		if (!$is_down)
		{
			$monitorz->closeCase($key);
			print "case closed for $key<br>";

			if ($monitorz->notify($monitor_config, "Case closed for $key", "The case for $key opened $case_opened_formatted has now been closed as of $now_formatted.\n\nThank you"))
			{
				print "did notify for $key<br>";
			}
		}
	}
	else
	{
		if ($is_down)
		{
			$monitorz->openCase($key);
			print "case opened for $key<br>";

			if ($monitorz->notify($monitor_config, "Urgent! $key reported down", "$key has been down since $now_formatted.\n\nThank you"))
			{
				print "did notify for $key<br>";
			}
		}
	}
}