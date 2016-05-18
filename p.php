<?php
include('Websom/Start.php');

$params = $_GET;

$process = $params['p'];
unset($params['p']);

fireProcessEvent($process, $params);

include('Websom/End.php');
?>