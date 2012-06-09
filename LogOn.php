<?php
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
        if($username == '') {
            return '';
        }
        
        return hash('md5', rand(0, getrandmax()).time().'6oCGlwleKsRWlwnhcWEL');
    }

    public function initiate()
    {
        return hash('md5', rand(0, getrandmax()).time().'edqdiOCDes2b1vGO7L2Y');
    }
}

?>
