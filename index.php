<?php

require "lib/config.php";

if (isset($_REQUEST['name'], $_REQUEST['value']))
{
	$monitorz->monitor($_REQUEST['name'], $_REQUEST['value']);
	echo json_encode(array('ok' => true));
	exit;
}

if (isset($_REQUEST['config'], $_REQUEST['monitor_config']))
{
	$monitorz->setMonitorConfig($_REQUEST['config'], $_REQUEST['monitor_config']);

	if (isset($_REQUEST['redirect']))
	{
		header("Location: ?edit");
	}
	else
	{
		echo json_encode(array('ok' => true));
	}
	exit;
}

if (isset($_REQUEST['delete'], $_REQUEST['config']))
{
	$monitorz->deleteMonitor($_REQUEST['config']);

	if (isset($_REQUEST['redirect']))
	{
		header("Location: ?edit");
	}
	else
	{
		echo json_encode(array('ok' => true));
	}
	exit;
}

$stats = array();
foreach ($monitorz->listAllKeys() as $key)
{
	$stats[$key] = $monitorz->statsForKey($key);
}

require 'views/index.html.php';