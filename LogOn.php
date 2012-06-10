<?php
require_once dirname(__FILE__) . '/Response.php';
require_once dirname(__FILE__) . '/DatabaseWrapper.php';
require_once dirname(__FILE__) . '/ErrorNumbers.php';

/**
 * Description of LogOn
 *
 * @author developer
 */
class LogOn {
    
    private $databaseWrapper;
    
    public function __construct() {
        $this->databaseWrapper = new DatabaseWrapper;
    }
    
    public function setDatabaseWrapper($databaseWrapper)
    {
        $this->databaseWrapper = $databaseWrapper;
    }
    
    public function getVersion()
    {
        return 1;
    }
    
    public function authenticate($username, $cnonce, $hash)
    {
        if($username == '' 
                || $cnonce == '' 
                || $hash == ''
                || preg_match('/^[a-z0-9]{1,16}$/', $username) === 0
                || preg_match('/^[a-f0-9]{32}$/', $cnonce) === 0
                || preg_match('/^[a-f0-9]{32}$/', $hash) === 0)
        {
            return Response::AsException(MESSAGE_CREDENTIALS_INVALID, "Invalid credentials provided");
        }
        
        $fusername = $this->databaseWrapper->escape($username);
        $query = "select AccountName, Password from Account where AccountName = '$fusername'";
        $row = $this->databaseWrapper->getRow($query);
        if($row === NULL)
        {
            return Response::AsException(MESSAGE_CREDENTIALS_INVALID, "Invalid credentials provided");
        }
        $nonce = $row["Nonce"];
        $password = $row["Password"];
        
        $expectedHash = hash('md5', "$nonce:$cnonce:$password");
        if($expectedHash != $hash)
        {
            return Response::AsException(MESSAGE_CREDENTIALS_INVALID, "Invalid credentials provided");
        }
        
        $content = hash('md5', rand(0, getrandmax()).time().'6oCGlwleKsRWlwnhcWEL');
        return Response::AsContent($content);
    }

    public function initiate($username)
    {
        $nonce = hash('md5', rand(0, getrandmax()).time().'edqdiOCDes2b1vGO7L2Y');
        
        if($username == '' 
                || preg_match('/^[a-z0-9]{1,16}$/', $username) === 0)
        {
            return $nonce;
        }
        
        $fusername = $this->databaseWrapper->escape($username);
        $fnonce = $this->databaseWrapper->escape($nonce);
        $query = "update `Account` set `AccountNonce` = '$fnonce' where `AccountName` = '$fusername'";
        $this->databaseWrapper->execute($query);
        return $nonce;
    }
}

?>
