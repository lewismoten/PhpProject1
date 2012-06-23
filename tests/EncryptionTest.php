<?php

require_once dirname(__FILE__) . '/../Encryption.php';

class EncryptionTest extends PHPUnit_Framework_TestCase {
// todo: look at google authenticator TOTP for php
    public function testEncryption() {
        $originalMessage = "hello world.";
        $encryption = Encryption::encrypt($originalMessage);
        $this->assertNotEquals($originalMessage, $encryption);
        $this->assertRegExp('/^[[:alnum:]+\/]*={0,3}$/i', $encryption->encrypted);
        $decryptedMessage = Encryption::decrypt($encryption);
        $this->assertEquals($originalMessage, $decryptedMessage);
    }

    public function testKeyStretching()
    {
        $key = "original key";
        $newKey = Encryption::stretchKey($key, 1);
        $this->assertRegExp('/^[a-f\d]{32}$/', $newKey);
    }
    
    public function testKeyStretchingWithoutIterations()
    {
        $key = "original key";
        $newKey = Encryption::stretchKey($key, 0);
        $this->assertEquals($key, $newKey);
    }
    
    public function testKeyStretchingConsistency()
    {
        $key = "original key";
        
        $newKey1 = Encryption::stretchKey($key, 1);
        $newKey2 = Encryption::stretchKey($key, 1);
        $this->assertEquals($newKey1, $newKey2);
    }

    public function testKeyStretchingReusesKey()
    {
        $key = "original key";
        $newKey1 = Encryption::stretchKey($key, 1);
        $newKey1 = Encryption::stretchKey($newKey1, 1);
        $newKey2 = Encryption::stretchKey($key, 2);
        $this->assertNotEquals($newKey1, $newKey2);
    }
    
    public function testNonce()
    {
        $nonce1 = Encryption::generateNonce();
        $nonce2 = Encryption::generateNonce();
        $this->assertRegExp('/^[a-f\d]{32}$/', $nonce1);
        $this->assertNotEquals($nonce1, $nonce2);
    }

    public function testDerivedKey()
    {
        $derivedKey = Encryption::getDerivedKey("hello","world", 1);
        $stretchedKey = Encryption::stretchKey("helloworld", 1);
        
        $this->assertEquals($stretchedKey, $derivedKey);
        $this->assertRegExp('/^[a-f\d]{32}$/', $derivedKey);
    }
    
    public function testDerivedKeyWithoutIterations()
    {
        $derivedKey = Encryption::getDerivedKey("hello","world", 0);
        $this->assertEquals("helloworld", $derivedKey);
    }
 
    public function testDerivedKeyIterations()
    {
        $derivedKey1 = Encryption::getDerivedKey("hello","world", 1);
        $derivedKey2 = Encryption::getDerivedKey("hello","world", 2);
        $this->assertNotEquals($derivedKey1, $derivedKey2);
    }

    public function testDerivedKeyConsistency()
    {
        $derivedKey1 = Encryption::getDerivedKey("hello","world", 1);
        $derivedKey2 = Encryption::getDerivedKey("hello","world", 1);
        
        $this->assertEquals($derivedKey1, $derivedKey2);
    }

    public function testDerivedKeyReusesKey()
    {
        $key = "hello";
        $newKey1 = Encryption::getDerivedKey($key, "", 1);
        $newKey1 = Encryption::getDerivedKey($newKey1, "", 1);
        $newKey2 = Encryption::getDerivedKey($key, "", 2);
        $this->assertNotEquals($newKey1, $newKey2);
    }

    public function testDerivedKeyReusesSalt()
    {
        $salt = "hello";
        $newKey1 = Encryption::getDerivedKey("", $salt, 1);
        $newKey1 = Encryption::getDerivedKey("", $newKey1, 1);
        $newKey2 = Encryption::getDerivedKey("", $salt, 2);
        $this->assertNotEquals($newKey1, $newKey2);
    }
    public function testStrengthenKey()
    {
        
        $strengthenedKey = Encryption::strengthenKey("hello","world", 1);
        $derivedKey = Encryption::getDerivedKey("hello", "world", 1);
        
        $this->assertEquals($strengthenedKey, $derivedKey);
        $this->assertRegExp('/^[a-f\d]{32}$/', $strengthenedKey);
    }
    
    public function testStrengthenKeyWithoutIterations()
    {
        $strengthenedKey = Encryption::strengthenKey("hello","world", 0);
        $this->assertEquals("helloworld", $strengthenedKey);
    }
 
    public function testStrengthenKeyIterations()
    {
        $strengthenedKey1 = Encryption::strengthenKey("hello","world", 1);
        $strengthenedKey2 = Encryption::strengthenKey("hello","world", 2);
        $this->assertNotEquals($strengthenedKey1, $strengthenedKey2);
    }

    public function testStrengthenKeyConsistency()
    {
        $strengthenedKey1 = Encryption::strengthenKey("hello","world", 1);
        $strengthenedKey2 = Encryption::strengthenKey("hello","world", 1);
        
        $this->assertEquals($strengthenedKey1, $strengthenedKey2);
    }

    public function testStrengthenKeyReusesKey()
    {
        $key = "hello";
        $newKey1 = Encryption::strengthenKey($key, "", 1);
        $newKey1 = Encryption::strengthenKey($newKey1, "", 1);
        $newKey2 = Encryption::strengthenKey($key, "", 2);
        $this->assertNotEquals($newKey1, $newKey2);
    }

    public function testStrengthenKeyReusesSalt()
    {
        $salt = "hello";
        $newKey1 = Encryption::strengthenKey("", $salt, 1);
        $newKey1 = Encryption::strengthenKey("", $newKey1, 1);
        $newKey2 = Encryption::strengthenKey("", $salt, 2);
        $this->assertNotEquals($newKey1, $newKey2);
    }
    
    public function testSalting()
    {
        $key = "hello";
        $salted = Encryption::ensalt($key);
        $this->assertNotEquals($key, $salted);
        $desalted = Encryption::desalt($salted);
        $this->assertEquals($key, $desalted);
    }
}

?>
