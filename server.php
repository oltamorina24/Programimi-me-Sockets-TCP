<?php
$host = "0.0.0.0"; 
$tcp_port = 9000;
$http_port = 9090; 
$max_clients = 6;
$timeout_seconds = 200; 

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
                usleep(100000);
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
            echo "Klienti ({$client['ip']}) kerkoi: $input\n";
            $messages_log[] = ["ip" => $client['ip'], "msg" => substr($input, 0, 50), "time" => date("H:i:s")];

            $response = "";
            if(strpos($input, '/')===0){
              $parts = explode(' ',$input, 3);
              $command = $parts[0];
              $arg1 = isset($parts[1]) ? trim ($parts[1]) : '';
              $arg2 = isset($parts[2]) ? $parts[2] : '';
              $admin_only_commands=['/delete','/upload','/execute'];

            if(!$is_admin && in_array($command,$admin_only_commands)){
                 $response="Error: Ju keni vetem read() permission!";
            } else{
               switch($command){
                   case '/list':
                        $files=array_diff(scandir("."),array('.','..'));
                        $response="Files ne server: ".implode(", ",$files);
                        break;
                  case '/read':
                        if(file_exists($arg1) && !is_dir($arg1)){
                        $response =file_get_contents($arg1);
                     }else{$response="Error: File nuk ekziston."; }
                     break;
                  case '/info':
                       if (file_exists($arg1)) {
                       $response = "INFO: $arg1 | " . filesize($arg1) . " bytes | " . date("Y-m-d H:i", filemtime($arg1));
                     } else { $response = "Error: Nuk u gjet."; }
                     break;
                  case '/search':
                       $files = scandir(".");
                       $found = array_filter($files, function($f) use ($arg1) { return strpos($f, $arg1) !== false; });
                       $response = "Rezultatet: " . implode(", ", $found);
                         break;
                  case '/delete':
                       if (file_exists($arg1)) {
                       unlink($arg1);
                       $response = "File '$arg1' u fshi.";
                    } else { $response = "Error: File nuk u gjet.";
                      }break;
                  case '/upload': 
                      if ($arg1 !== '' && $arg2 !== '') {
                      file_put_contents($arg1, $arg2); 
                      $response = "File '$arg1' u ngarkua me sukses.";
                    } else { 
                      $response = "Error: Mungon emri ose permbajtja."; 
                      }break;
                  case '/download':
                      if (file_exists($arg1) && !is_dir($arg1)) {
                      $response = "FILE_DATA:" . $arg1 . ":" . file_get_contents($arg1);
                    } else { 
                      $response = "Error: File nuk ekziston."; 
                      }break;
                 default:
                     $response = "Komande e panjohur.";
                   }
                }
        } else {
                $role = $is_admin ? "[ADMIN]" : "[USER]";
                $response = "$role Mesazhi u mor.";
            }
            $final_response = $response . " | (Kerkesa nr: " . $clients[$id]['requests'] . ")\n";
            @socket_write($client['socket'], $final_response, strlen($final_response));
        }
    }
    checkTimeouts();
}
function checkTimeouts() {
    global $clients, $timeout_seconds;
    foreach ($clients as $id => $client) {
        if (time() - $client['last_seen'] > $timeout_seconds) {
            closeConnection($id);
        }
    }
}

function closeConnection($id) {
    global $clients, $admin_client;
    if (isset($clients[$id])) { 
        $ip = $clients[$id]['ip'];
        @socket_close($clients[$id]['socket']);
        unset($clients[$id]);
        if ($admin_client === $id) {
            $admin_client = null;
        echo "--- [INFO] Admini ($ip) u shkeput. ---\n";
        } else {
            echo "--- [INFO] Klienti ($ip) u shkeput. ---\n";
        }
    }
}
function handleHttpRequest($http_socket) { 
    global $clients, $messages_log; 
    
    $conn = @socket_accept($http_socket);
    if ($conn === false) return;
    
    $req = @socket_read($conn, 1024);
    
    $stats = [
        "status" => "Online",
        "klientet_aktiv" => count($clients),
        "mesazhet_total" => count($messages_log),
        "lista_e_ip_adresave" => array_column($clients, 'ip'), 
        "historiku_i_mesazheve" => $messages_log           
    ];
     $body = json_encode($stats, JSON_PRETTY_PRINT);
    
    $response = "HTTP/1.1 200 OK\r\n";
    $response .= "Content-Type: application/json\r\n";
    $response .= "Content-Length: " . strlen($body) . "\r\n";
    $response .= "Connection: close\r\n\r\n";
    $response .= $body;
    
    @socket_write($conn, $response, strlen($response));
    @socket_close($conn);
}
?>