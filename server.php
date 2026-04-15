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
<<<<<<< HEAD
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
    
    // Header-at HTTP (sigurohemi që Content-Type është JSON)
    $response = "HTTP/1.1 200 OK\r\n";
    $response .= "Content-Type: application/json\r\n";
    $response .= "Content-Length: " . strlen($body) . "\r\n";
    $response .= "Connection: close\r\n\r\n";
    $response .= $body;
    
    @socket_write($conn, $response, strlen($response));
    @socket_close($conn);
}
?>
=======
    checkTimeouts();
}
>>>>>>> main
