<!DOCTYPE html>
<html>
	<head>
		<title>Stats</title>

		<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/rickshaw/1.4.6/rickshaw.min.css">
		<style>
			body {
				font: 16px/20px Helvetica, Arial;
				padding: 120px 60px;
			}

			h1 {
				font: 42px Helvetica, Arial;
			}

			h2 {
				font: 20px/28px Helvetica, Arial;
				margin: 0;
			}

			.edit {
				position: absolute;
				top: 20px;
				right: 20px;
				color: #ddd;
			}

			article {
				box-sizing: border-box;
				width: 50%;
				float: left;
				padding-right: 25px;
				padding-bottom: 50px;
				position: relative;
			}

			.canvas {
				clear: both;
				width: 100%;
				height: 250px;
				position: relative;
			}

			.area {
				position: relative;
				left: 40px;
			}

			.case-opened h1 {
				color: red;
				float: left;
			}

			.case-message {
				float: left;
				padding-top: 40px;
				padding-left: 20px;
			}

			.manage {
				padding: 20px;
				margin-top: 20px;
			}

				.manage label {
					margin-right: 20px;
				}

			input[type=text] {
				border: 1px solid #ccc;
				padding: 5px;
				font: 16px/20px Helvetica, Arial;
			}

		</style>
	</head>

	<body>
		<?php if (isset($_GET['edit'])): ?>
			<a class="edit" href="?">done</a>
		<?php else: ?>
			<a class="edit" href="?edit">edit</a>
		<?php endif; ?>

		<?php foreach ($stats as $name => $data_points): ?>
			<article data="<?php echo htmlentities(json_encode($data_points)); ?>" class="<?php if ($monitorz->caseOpened($name)): ?>case-opened<?php endif; ?>">
				<h1><?php echo htmlentities($name); ?></h1>
				
				<?php if ($monitorz->caseOpened($name)): ?>
					<p class="case-message">Case open since: <?php echo date("Y-m-d H:i:s", $monitorz->caseForKey($name)); ?></p>
				<?php endif; ?>

				<div class="canvas">
					<div class="y"></div>
					<div class="x"></div>
					<div class="area"></div>
				</div>

				<?php if (isset($_GET['edit'])) require 'views/manage.html.php'; ?>
			</article>
		<?php endforeach; ?>

		<script src="http://d3js.org/d3.v3.min.js" charset="utf-8"></script>
		<script src="//cdnjs.cloudflare.com/ajax/libs/rickshaw/1.4.6/rickshaw.js"></script>

		<div id="chart"></div>

		<script>
			var zeroPad = d3.format("02d");

			function drawChart(element) {
				var canvas = element.querySelector(".canvas");

				var data = JSON.parse(element.getAttribute("data"));

				var labels = data.map(function(point) {
					return point.timestamp;//'';
				});

				var points = data.map(function(point) {
					return {x: point.timestamp, y: parseInt(point.value, 10)};
				});

				points.push({x: Date.now() / 1000, y: 0});

				var steps = 7;

				var graph = new Rickshaw.Graph({
					element: canvas.querySelector(".area"),
					width: canvas.offsetWidth - 40,
					height: canvas.offsetHeight,
					renderer: 'scatterplot',
					padding: {top: 0.02, left: 0.05, right: 0.1, bottom: 0.04},
					stroke: true,
					series: [{
						data: points,
						color: 'steelblue'
					}]
				});

				b = new Rickshaw.Graph.Axis.Y({
					graph: graph,
					orientation: 'right'
				});

				a = new Rickshaw.Graph.Axis.X({
					graph: graph,
					orientation: 'top',
					pixelsPerTick: 100,
					tickFormat: function(x) {
						return d3.time.format("%H:%M %m/%y")(new Date(x * 1000));
					}
				});

				graph.render();
			}

			document.addEventListener("DOMContentLoaded", function() {
				charts = document.querySelectorAll("article");
				[].forEach.call(charts, drawChart);
			}, false);
		</script>
	</body>
</html>