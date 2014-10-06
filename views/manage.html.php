<?php
	$monitor_config = $monitorz->getMonitorConfig($name);
?>
<section class="manage">
	<h2>Manage</h2>

	<form action="?edit" method="post">
		<input type="hidden" name="config" value="<?php echo htmlentities($name); ?>" />
		<input type="hidden" name="redirect" value="true" />
		<label>
			<span>Notify on failure:</span>
			<input type="checkbox" name="monitor_config[notify_on_failure]" <?php if ($monitor_config->notify_on_failure) echo "checked" ?> />
		</label>
		<label>
			<span>Expected update interval:</span>
			<input type="text" name="monitor_config[expected_update_interval]" value="<?php echo htmlentities($monitor_config->expected_update_interval) ?>" />
		</label>
		<input type="submit" value="Save" />
	</form>

	<form action="?delete" method="post">
		<input type="hidden" name="config" value="<?php echo htmlentities($name); ?>" />
		<input type="hidden" name="redirect" value="true" />
		<input type="submit" value="DELETE" />
	</form>
</section>