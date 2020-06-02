<?php
	//$now = DateTime::createFromFormat('Y-m-d H:i:s', '2560-08-15 04:42:48');
	$now = new DateTime();
	$tz = $now->getTimezone();
	echo "[" . $tz->getName() . "] " . $now->format('Y-m-d H:i:s') . "<BR>";
	$now->setTimezone(new DateTimeZone('Asia/Bangkok'));
	$tz = $now->getTimezone();
	echo "[" . $tz->getName() . "] " . $now->format('Y-m-d H:i:s') . "<BR>";
	echo phpinfo();
?>