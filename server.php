<?php

/**
 * Simple Network Scanner
 * (c) 2015 Clark Winkelmann
 * MIT licensed
 *
 * HOW TO USE
 *
 * Start this file in the PHP development server as root (required by arp-scan)
 *     sudo php -S localhost:8000 server.php
 */

// Launch an ARP-Scan on the local subnet
// Must be run as root
$arp_scan_raw = shell_exec('arp-scan --localnet');

// Get lines as an array
$arp_scan = explode("\n", $arp_scan_raw);

// Will contain matching fields in the regexp
$matches = [];

// Will contain all found interfaces in a mac-indexed array
$found_interfaces = [];

// Scan results
foreach($arp_scan as $scan) {
	$matches = []; // reset

	// Parse output lines
	if(preg_match('/^([0-9\.]+)[[:space:]]+([0-9a-f:]+)[[:space:]]+(.+)$/', $scan, $matches) !== 1) {
		// Ignore lines that don't contain results
		continue;
	}

	$ip = $matches[1];
	$mac = $matches[2];
	$desc = $matches[3];

	$found_interfaces[$mac] = [
		'ip' => $ip,
		'desc' => $desc,
		'known' => false, // Will be changed by the loop
	];
}

// Read network data from file
$network = json_decode(file_get_contents('network.json'), true);

// Loop trough all described interfaces to mark them up or down
foreach($network['devices'] as $devicekey => $device) {
	foreach($device['interfaces'] as $interfacekey => $interface) {
		// Is this interface up ?
		$up = false;
		// IP of the interface (will remain null if neither defined or up)
		$ipData = $network['devices'][$devicekey]['interfaces'][$interfacekey]['ip'];
		$ip = array_key_exists('value', $ipData) ? $ipData['value'] : null;
		$mac = $interface['mac'];

		// If the interface is up
		if(array_key_exists($mac, $found_interfaces)) {
			$up = true;
			$ip = $found_interfaces[$mac]['ip'];
			$found_interfaces[$mac]['known'] = true;
		}

		// Save values for display
		$network['devices'][$devicekey]['interfaces'][$interfacekey]['up'] = $up;
		$network['devices'][$devicekey]['interfaces'][$interfacekey]['ip']['value'] = $ip;
	}
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>Simple Network Scanner</title>

	<style>
		* {
			box-sizing: border-box;
		}

		body {
			font-family: Arial, sans-serif;
			color: #333;
		}

		.container {
			width: 960px;
			max-width: 100%;
			margin: 0 auto;
		}

		.device {
			background: #dedede;
			margin: 0.5em 0;
			padding: 0.5em 2em;
			display: inline-block;
			vertical-align: top;
			width: 33%;
			font-size: 0.9em;
		}

		.device.up {
			background: #66e658;
		}

		.device.down {
			background: #f7b4bd;
		}

		.device .name {
			font-weight: bold;
			font-size: 1.2em;
		}
	</style>
</head>
<body>
	<div class="container">
		<h1>Simple Network Scanner</h1>
		<h2>Unknown devices</h2>
		<div class="devices">
		<?php foreach($found_interfaces as $mac => $interface):
			if($interface['known']) {
				// Here we only display unknown interfaces
				continue;
			}
		?>
			<div class="device">
				<div class="name"><?= $interface['desc'] ?></div>
				<div class="interfaces">
					<div class="interface">
						<div class="mac"><?= $mac ?></div>
						<div class="ip"><?= $interface['ip'] ?></div>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
		</div>
		<h2>Network</h2>
		<div class="devices">
		<?php foreach($network['devices'] as $device):
			// Is this host up ?
			$up = false;
			foreach($device['interfaces'] as $interface) {
				if($interface['up']) {
					$up = true;
					break; // The host is considered up if at least one interface is up
				}
			}
		?>
			<div class="device <?= $up ? 'up' : 'down' ?>">
				<div class="name"><?= $device['name'] ?></div>
				<div class="type"><?= $device['type'] ?></div>
				<div class="interfaces">
				<?php foreach($device['interfaces'] as $interface): ?>
					<div class="interface">
						<div class="mac"><?= $interface['mac'] ?></div>
						<div class="ip"><?= $interface['ip']['value'] ?></div>
						<div class="state"><?= $interface['up'] ? 'Up' : 'Down' ?></div>
					</div>
				<?php endforeach; ?>
				</div>
			</div>
		<?php endforeach; ?>
		</div>
	</div>
</body>
</html>