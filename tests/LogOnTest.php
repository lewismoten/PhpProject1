<?php
require_once dirname(__FILE__) . '/../LogOn.php';

/**
 * Description of LogOn
 *
 * @author developer
 */
class LogOnTest extends PHPUnit_Framework_TestCase {
    
    protected $target;
    private $username = 'test';
    private $badUsername = '';
    private $password = 'password';

    protected function setUp() {
        $this->target = new LogOn;
    }
    
    protected function tearDown() {
        
    }
    
    public function testExpectedVersion() {
        $version = $this->target->getVersion();
        $this->assertEquals(1.0, $version);
    }
    
    public function testInitiate()
    {
        
        $nonce = $this->target->initiate();
        $this->assertEquals(32, strlen($nonce));
    }
    
    public function testInitiateIsUnique()
    {
        $firstNonce = $this->target->initiate();
        $secondNonce = $this->target->initiate();
        $this->assertNotEquals($firstNonce, $secondNonce);
    }
    
    public function testAuthenticate()
    {
        $nonce = $this->target->initiate();
        $cnonce = hash('md5', rand(0, getrandmax()).'MyOwnValue');
        $hash = hash('md5', "$nonce:$cnonce:$this->password");
        $token = $this->target->authenticate($this->username, $cnonce, $hash);
        $this->assertEquals(32, strlen($token));
    }

    public function testAuthenticateWithBadUsername()
    {
        $nonce = $this->target->initiate();
        $cnonce = hash('md5', rand(0, getrandmax()).'MyOwnValue');
        $hash = hash('md5', "$nonce:$cnonce:$this->password");
        $token = $this->target->authenticate($this->badUsername, $cnonce, $hash);
        $this->assertEquals('', $token);
    }
}

?>
