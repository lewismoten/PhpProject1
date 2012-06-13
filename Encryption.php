<?php
class Encryption {
    public $encrypted;
    public $iv;
    
    public static function encrypt($message)
    {
        $data = new Encryption();
        $iv_size = mcrypt_get_iv_size(ENCRYPTION_CIPHER, ENCRYPTION_MODE);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $encrypted = mcrypt_encrypt(ENCRYPTION_CIPHER, ENCRYPTION_KEY, $message, ENCRYPTION_MODE, $iv);
        //$block = mcrypt_get_block_size(ENCRYPTION_CIPHER, ENCRYPTION_MODE);
        //$pad = $block - (strlen($encrypted) % $block);
        //$encrypted .= str_repeat("\0", $pad);
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
    
    public static function stretchKey($password)
    {
        $key = '';
        for ($index = 0; $index < 65536; $index++) {
            $key = hash('md5', "$key$password");
        }
        return $key;
    }
    
    public static function removeSalt($key)
    {
        $i = strrpos($key, ':');
        if($i === FALSE) return NULL;
        return substr($key, 0, $i);
    }
}

?>
