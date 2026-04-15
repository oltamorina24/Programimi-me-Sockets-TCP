<?php

$server_ip = '192.168.0.51'; 
$server_port = 9000;

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === false) { die("Deshtoi krijimi i socket-it.\n"); }

echo "Duke u lidhur me serverin [$server_ip:$server_port]...\n";
