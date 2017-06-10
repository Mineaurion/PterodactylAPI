<?php
require 'PterodactylAPI.php';

$api = new PterodactylAPI('https://yourpanel.exemple.com','publicKey','privateKey');


//List all servers
print_r($api->listServers());
//For info on single server
print_r($api->singleServer('uuid'));
//To start/stop/restart/kill the server
print_r($api->powerToggles('uuid', '{"command":"start"}'));
//To execute command like /tps on the server
print_r($api->sendCommand('uuid','{"command":"tps"}'));