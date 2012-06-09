<?php
require_once dirname(__FILE__) . '/Response.php';
require_once dirname(__FILE__) . '/ErrorNumbers.php';

/**
 * Description of LogOn
 *
 * @author developer
 */
class LogOn {
    public function getVersion()
    {
        return 1;
    }
    
    public function authenticate($username, $cnonce, $hash)
    {
        if($username == '' 
                || $cnonce == '' 
                || $hash == ''
                || preg_match('/^[a-z0-9]+$/', $username) === 0
                || preg_match('/^[a-f0-9]{32}$/', $cnonce) === 0
                || preg_match('/^[a-f0-9]{32}$/', $hash) === 0)
        {
            return Response::AsException(MESSAGE_CREDENTIALS_INVALID, "Invalid credentials provided");
        }
        //todo: look at db for username/password
        if($username == 'badtest')
        {
            return Response::AsException(MESSAGE_CREDENTIALS_INVALID, "Invalid credentials provided");
        }
        $content = hash('md5', rand(0, getrandmax()).time().'6oCGlwleKsRWlwnhcWEL');
        return Response::AsContent($content);
    }

    public function initiate()
    {
        return hash('md5', rand(0, getrandmax()).time().'edqdiOCDes2b1vGO7L2Y');
    }
}

?>
