<?php
require_once dirname(__FILE__) . '/../LogOn.php';
require_once 'PHPUnit/Framework.php';

/**
 * Description of LogOn
 *
 * @author developer
 */
class LogOnTest extends PHPUnit_Framework_TestCase {
    
    protected $target;
    private $username = 'test';
    private $badUsername = 'badtest';
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
        $response = $this->target->authenticate($this->username, $cnonce, $hash);
        $this->assertTrue($response->success);
        $this->assertRegExp('/^[a-z0-9]{32}$/', $response->content);
    }
    
    public function testAuthenticateWithoutUsername()
    {
        $response = $this->target->authenticate('', hash('md5', 'x'), hash('md5', 'x'));
        $this->assertFalse($response->success);
        $this->assertEquals(MESSAGE_CREDENTIALS_INVALID, $response->errorNumber);
        $this->assertEquals('Invalid credentials provided', $response->errorMessage);
        $response = $this->target->authenticate(null, hash('md5', 'x'), hash('md5', 'x'));
        $this->assertFalse($response->success);
        $this->assertEquals(MESSAGE_CREDENTIALS_INVALID, $response->errorNumber);
        $this->assertEquals('Invalid credentials provided', $response->errorMessage);        
    }
    
    public function testAuthenticateUsernameWithBadFormat()
    {
        $response = $this->target->authenticate(" $this->username ", hash('md5', 'x'), hash('md5', 'x'));
        $this->assertFalse($response->success);
        $this->assertEquals(MESSAGE_CREDENTIALS_INVALID, $response->errorNumber);
        $this->assertEquals('Invalid credentials provided', $response->errorMessage);
    }

    public function testAuthenticateCNonceWithBadFormat()
    {
        $response = $this->target->authenticate($this->username, 'x', hash('md5', 'x'));
        $this->assertFalse($response->success);
        $this->assertEquals(MESSAGE_CREDENTIALS_INVALID, $response->errorNumber);
        $this->assertEquals('Invalid credentials provided', $response->errorMessage);
    }
    
    public function testAuthenticateHashWithBadFormat()
    {
        $response = $this->target->authenticate($this->username, hash('md5', 'x'), 'x');
        $this->assertFalse($response->success);
        $this->assertEquals(MESSAGE_CREDENTIALS_INVALID, $response->errorNumber);
        $this->assertEquals('Invalid credentials provided', $response->errorMessage);
    }

    public function testAuthenticateWithoutCNonce()
    {
        $response = $this->target->authenticate($this->username, '', 'x');
        $this->assertFalse($response->success);
        $this->assertEquals(MESSAGE_CREDENTIALS_INVALID, $response->errorNumber);
        $this->assertEquals('Invalid credentials provided', $response->errorMessage);
        $response = $this->target->authenticate($this->username, null, 'x');
        $this->assertFalse($response->success);
        $this->assertEquals(MESSAGE_CREDENTIALS_INVALID, $response->errorNumber);
        $this->assertEquals('Invalid credentials provided', $response->errorMessage);
    }

    public function testAuthenticateWithoutHash()
    {
        $response = $this->target->authenticate($this->username, 'x', '');
        $this->assertFalse($response->success);
        $this->assertEquals(MESSAGE_CREDENTIALS_INVALID, $response->errorNumber);
        $this->assertEquals('Invalid credentials provided', $response->errorMessage);
        $response = $this->target->authenticate($this->username, 'x', null);
        $this->assertFalse($response->success);
        $this->assertEquals(MESSAGE_CREDENTIALS_INVALID, $response->errorNumber);
        $this->assertEquals('Invalid credentials provided', $response->errorMessage);
    }

    public function testAuthenticateWithBadUsername()
    {
        $db = $this->getMock('DatabaseWrapper', array('getRow'));
        $db->expects($this->once())
                ->method('getRow')
                ->with($this->equalTo(''));
        
        $target = new LogOn;
        $target->attach($db);
        
        $response = $target->authenticate($this->badUsername, hash('md5', 'x'), hash('md5', 'x'));
        $this->assertFalse($response->success);
        $this->assertEquals(MESSAGE_CREDENTIALS_INVALID, $response->errorNumber);
        $this->assertEquals('Invalid credentials provided', $response->errorMessage);
    }
}

?>
