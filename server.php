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
            case 'list':
                $files=array_diff(scandir("."),array('.','..'));
                $response="Files ne server: ".implode(", ",$files);
                break;
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
    
}
?>