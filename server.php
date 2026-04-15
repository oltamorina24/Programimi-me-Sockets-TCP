<?php

$host = "0.0.0.0"; 
$tcp_port = 9000;
$http_port = 9090; 
$max_clients = 6;
$timeout_seconds = 300; 


$clients = [];
$messages_log = [];
$admin_client = null;

$main_socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_option($main_socket, SOL_SOCKET, SO_REUSEADDR, 1);
socket_bind($main_socket, $host, $tcp_port);
socket_listen($main_socket, $max_clients);
socket_set_nonblock($main_socket);


$http_socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_option($http_socket, SOL_SOCKET, SO_REUSEADDR, 1);
socket_bind($http_socket, $host, $http_port);
socket_listen($http_socket, 5);
socket_set_nonblock($http_socket);

echo "TCP Server nisi ne portin $tcp_port...\n";
echo "HTTP Monitoring nisi ne portin $http_port...\n";

while (true) {
    $read = [];
    $read[] = $main_socket;
    $read[] = $http_socket;
    
    foreach ($clients as $c) {
        $read[] = $c['socket'];
    }

    $write = null;
    $except = null;
    
    if (socket_select($read, $write, $except, 1) < 1) {
        checkTimeouts();
        continue;
    }

    
    if (in_array($main_socket, $read)) {
        $new_socket = @socket_accept($main_socket);
        if ($new_socket) {
            if (count($clients) >= $max_clients) {
                $msg = "Serveri plot. Provo me vone.\n";
                socket_write($new_socket, $msg, strlen($msg));
                socket_close($new_socket);
            } else {
                socket_getpeername($new_socket, $ip);
                $socket_id = (int)$new_socket;
                $clients[$socket_id] = [
                    'socket' => $new_socket,
                    'ip' => $ip,
                    'last_seen' => time(),
                    'requests' => 0
                ];
                if ($admin_client === null) {
                    $admin_client = $socket_id;
                    echo "Admin i ri lidhur: $ip\n";
                } else {
                    echo "Klient i ri lidhur: $ip\n";
                }
            }
        }
    }

    if (in_array($http_socket, $read)) {
        handleHttpRequest($http_socket);
    }

   
    foreach ($clients as $id => $client) {
        if (in_array($client['socket'], $read)) {
            
           
            $is_admin = ($id === $admin_client);
            if (!$is_admin) {
                usleep(300000); 
            }

            $input = @socket_read($client['socket'], 1024 * 1024);
            
            if ($input === false || $input === "") {
                closeConnection($id);
                continue;
            }

            $input = trim($input);
            $clients[$id]['last_seen'] = time();
            $clients[$id]['requests']++; 
            $messages_log[] = ["ip" => $client['ip'], "msg" => substr($input, 0, 50), "time" => date("H:i:s")];

            $response = "";