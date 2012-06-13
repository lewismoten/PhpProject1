<?php
require_once dirname(__FILE__) . '/Response.php';
require_once dirname(__FILE__) . '/DatabaseWrapper.php';
require_once dirname(__FILE__) . '/ErrorNumbers.php';
require_once dirname(__FILE__) . '/Encryption.php';

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
            $this->removeNonce($username);
            return Response::AsException(MESSAGE_CREDENTIALS_INVALID, "Invalid credentials provided");
        }
        
        $fusername = $this->databaseWrapper->escape($username);
        $query = "select AccountName, Password, Nonce, iv from Account where AccountName = '$fusername' and NonceCreeated > DATE_ADD(NOW(), INTERVAL -1 MINUTE)  ";
        $row = $this->databaseWrapper->getRow($query);
        if($row === NULL)
        {
            $this->removeNonce($username);
            return Response::AsException(MESSAGE_CREDENTIALS_INVALID, "Invalid credentials provided");
        }
        $this->removeNonce($username);
        
        $nonce = $row["Nonce"];
        $encrypted = new Encryption();
        $encrypted->encrypted = $row["Password"];
        $encrypted->iv = $row["iv"];
        $password = Encryption::decrypt($encrypted);
        $password = Encryption::removeSalt($password);
        
        $expectedHash = Encryption::stretchKey("$password$cnonce$nonce");
        
        
        if($expectedHash != $hash)
        {
            return Response::AsException(MESSAGE_CREDENTIALS_INVALID, "Invalid credentials provided");
        }
        
        $content = hash('md5', rand(0, getrandmax()).time().'6oCGlwleKsRWlwnhcWEL');
        return Response::AsContent($content);
    }
    
    private function removeNonce($username)
    {
        $fusername = $this->databaseWrapper->escape($username);
        $query = "update `Account` set `Nonce` = '' where `AccountName` = '$fusername'";
        $row = $this->databaseWrapper->execute($query);
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
