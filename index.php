<?php

require 'vendor/autoload.php';

$config = json_decode(file_get_contents('config.json'));

$redis = new Predis\Client((array)$config->redis);

if (isset($_REQUEST['name'], $_REQUEST['value']))
{
	$value_array = array(
		'value' => $_REQUEST['value'],
		'timestamp' => time()
	);

	$redis->lpush("stats.$_REQUEST[name]", json_encode($value_array));
	$redis->ltrim("stats.$_REQUEST[name]", 0, $config->max_data_points);

	echo json_encode(array('ok' => true));
	exit;
}

$key_names = $redis->keys('stats.*');

$stats = array();

foreach ($key_names as $key)
{
	$raw_stats = $redis->lrange($key, 0, -1);

	$stats_parsed = array();
	foreach ($raw_stats as $data_point)
	{
		$stats_parsed[] = json_decode($data_point);
	}

	$stats[preg_replace('/^stats\./', '', $key)] = array_reverse($stats_parsed);
}

require 'views/index.html.php';