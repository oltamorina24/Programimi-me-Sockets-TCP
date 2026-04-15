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
        }else {
                $role = $is_admin ? "[ADMIN]" : "[USER]";
                $response = "$role Mesazhi u mor.";
            }
            $final_response = $response . "\n";
            @socket_write($client['socket'], $final_response, strlen($final_response));
        }
    }
    checkTimeouts();
}