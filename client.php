<?php

$server_ip = '192.168.0.51'; 
$server_port = 9000;

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === false) { die("Deshtoi krijimi i socket-it.\n"); }

echo "Duke u lidhur me serverin [$server_ip:$server_port]...\n";
$result = @socket_connect($socket, $server_ip, $server_port);
if ($result === false) { die("Lidhja deshtoi. Sigurohu qe serveri eshte hapur!\n"); }

echo "U lidhet me sukses!\n";
echo "------------------------------------------------------------\n";

while (true) {
    echo "Ti: ";
    $line = trim(fgets(STDIN)); 

    if ($line == 'exit') break;
    if ($line == '') continue;

    $message = $line;
