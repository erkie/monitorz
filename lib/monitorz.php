<?php

class Monitorz
{
	public $redis;
	public $config;

	public function __construct($config_name)
	{
		$this->config = json_decode(file_get_contents($config_name));
		$this->redis = new Predis\Client((array)$this->config->redis);
	}

	public function monitor($name, $value)
	{
		$value_array = array(
			'value' => $value,
			'timestamp' => time()
		);

		$this->redis->lpush(self::prepareKeyName($name), self::encodeValue($value_array));
		$this->redis->ltrim(self::prepareKeyName($name), 0, $this->config->max_data_points);
	}

	public function listAllKeys()
	{
		$names = array_map("self::unprepareKeyName", $this->redis->keys("stats.*"));
		sort($names);
		return $names;
	}

	public function statsForKey($name)
	{
		$raw_values = $this->redis->lrange(self::prepareKeyName($name), 0, -1);
		return array_reverse(array_map("self::decodeValue", $raw_values));
	}

	public function deleteMonitor($name)
	{
		$this->redis->del(self::prepareKeyName($name));
		$this->redis->del(self::prepareMonitorConfigName($name));
		$this->redis->del(self::prepareCaseName($name));
	}

	public function getLastDataPoint($name)
	{
		$point = $this->redis->lrange(self::prepareKeyName($name), 0, 1);
		return self::decodeValue($point[0]);
	}

	public function getMonitorConfig($name)
	{
		$settings = $this->redis->get(self::prepareMonitorConfigName($name));
		$monitor_config = new MonitorzConfig;

		if (!$settings)
		{
			return $monitor_config;
		}
		else
		{
			$values = json_decode($settings);
			foreach ($values as $key => $value)
			{
				$monitor_config->$key = $value;
			}

			return $monitor_config;
		}
	}

	public function setMonitorConfig($name, $config)
	{
		$this->redis->set(self::prepareMonitorConfigName($name), json_encode($config));
	}

	public function caseOpened($name)
	{
		return !! $this->caseForKey($name);
	}

	public function caseForKey($name)
	{
		return $this->redis->get(self::prepareCaseName($name));
	}

	public function openCase($name)
	{
		$this->redis->set(self::prepareCaseName($name), time());
	}

	public function closeCase($name)
	{
		$this->redis->del(self::prepareCaseName($name));
	}

	public function notify(MonitorzConfig $monitor_config, $subject, $body)
	{
		if (!$monitor_config->notify_on_failure)
			return false;

		$mail = new Nette\Mail\Message;
		$mail->setFrom($this->config->notify->from);
		$mail->addTo($this->config->notify->to);
		$mail->setSubject($subject);
		$mail->setBody($body);;

		$mailer = new Nette\Mail\SmtpMailer((array)$this->config->mail);
		$mailer->send($mail);

		return true;
	}

	private static function prepareMonitorConfigName($name)
	{
		return "stats-config.$name";
	}

	private static function prepareCaseName($name)
	{
		return "stats-case.$name";
	}

	private static function prepareKeyName($name)
	{
		return "stats.$name";
	}

	private static function unprepareKeyName($name)
	{
		return preg_replace('#^stats\.#', "", $name);
	}

	private static function decodeValue($value_array)
	{
		return json_decode($value_array);
	}

	private static function encodeValue($value_array)
	{
		return json_encode($value_array);
	}
}