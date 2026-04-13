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