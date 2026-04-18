<?php

$server_ip = '192.168.0.10'; 
$server_port = 9000;

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === false) { die("Deshtoi krijimi i socket-it.\n"); }

echo "Duke u lidhur me serverin [$server_ip:$server_port]...\n";
$result = @socket_connect($socket, $server_ip, $server_port);
if ($result === false) { die("Lidhja deshtoi. Sigurohu qe serveri eshte hapur!\n"); }

socket_set_nonblock($socket);
usleep(200000);
$initial_response = @socket_read($socket, 1024);
if ($initial_response && strpos($initial_response, 'Serveri plot') !== false) {
    echo trim($initial_response) . " Lidhja deshtoi!\n";
    socket_close($socket);
    exit; 
}
socket_set_block($socket);
echo "U lidhet me sukses!\n";
echo "------------------------------------------------------------\n";
if ($initial_response) {
    echo trim($initial_response) . "\n";
    
   
    if (strpos($initial_response, 'Serveri plot') !== false) {
        echo "Lidhja do të mbyllet sepse nuk ka vende të lira.\n";
        socket_close($socket);
        exit; 
    }
}
socket_set_block($socket);
while (true) {
    echo "Ti: ";
    $line = trim(fgets(STDIN)); 

    if ($line == 'exit') break;
    if ($line == '') continue;

    $message = $line;
if (strpos($line, '/upload') === 0) {
        $parts = explode(' ', $line, 2);
        if (isset($parts[1])) {
            $filename = trim($parts[1]);
            
            if (file_exists($filename)) {
                $content = file_get_contents($filename);
               
                $message = "/upload " . $filename . " " . $content;
                echo "SISTEMI: Skedari u gjet dhe po dergohet...\n";
            } else {
                echo "SISTEMI: Gabim! Skedari '$filename' NUK ekziston ne kete folder.\n";
            }
        }
    }

    socket_write($socket, $message, strlen($message));

    $response = socket_read($socket, 1024 * 1024);
    if ($response === false || $response === "") {
        echo "Lidhja me serverin u ndërpre (Timeout ose Serveri u mbyll).\n";
        break;
    }

    if (strpos($response, 'FILE_DATA:') === 0) {
        $data_parts = explode(':', $response, 3);
        file_put_contents("shkarkuar_" . $data_parts[1], $data_parts[2]);
        echo "SISTEMI: Skedari u shkarkua.\n";
    } else {
        echo "Serveri:\n" . $response . "\n";
    }
    echo "------------------------------------------------------------\n";
}

socket_close($socket);
?>