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