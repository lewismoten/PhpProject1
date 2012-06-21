<?php
require_once dirname(__FILE__) . '/configuration.php';

class Encryption {
    public $encrypted;
    public $iv;
    
    public static function encrypt($message)
    {
        $data = new Encryption();
        $iv_size = mcrypt_get_iv_size(ENCRYPTION_CIPHER, ENCRYPTION_MODE);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $encrypted = mcrypt_encrypt(ENCRYPTION_CIPHER, ENCRYPTION_KEY, $message, ENCRYPTION_MODE, $iv);
        $data->iv = base64_encode($iv);
        $data->encrypted = base64_encode($encrypted);
        return $data;
    }
    
    public static function decrypt($encryption)
    {
        $encrypted = base64_decode($encryption->encrypted);
        $iv = base64_decode($encryption->iv);
        $message = mcrypt_decrypt(ENCRYPTION_CIPHER, ENCRYPTION_KEY, $encrypted, ENCRYPTION_MODE, $iv);
        return rtrim($message, "\0");
    }
    
    public static function stretchKey($key, $iterations)
    {
        $enhancedKey = $key;
        for ($index = 0; $index < $iterations; $index++) {
            $enhancedKey = self::hash("$enhancedKey$key");
        }
        return $enhancedKey;
    }
    
    public static function generateNonce()
    {
        return self::hash(rand(0, getrandmax()).time().'edqdiOCDes2b1vGO7L2Y');
    }
    
    public static function hash($data)
    {
        return hash('md5', $data);
    }
    
    public static function strengthenKey($key, $nonce, $iterations)
    {
        return self::getDerivedKey($key, $nonce, $iterations);
    }
    
    public static function getDerivedKey($key, $salt, $iterations)
    {
        return self::stretchKey("$key$salt", $iterations);
    }
    
    public static function desalt($key)
    {
        if(strlen($key) > 32)
        {
            return substr($key, 0, -32);
        }
        
        return NULL;
    }
    
    public static function ensalt($key)
    {
        return $key.self::generateNonce();
    }
}

?>
