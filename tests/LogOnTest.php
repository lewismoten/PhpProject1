<?php
require_once dirname(__FILE__) . '/../LogOn.php';
//require_once 'PHPUnit/Framework.php';

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
        $mockDatabaseWrapper = $this->getMock('DatabaseWrapper', array('execute'));
        $mockDatabaseWrapper->expects($this->once())
                ->method('execute')
                ->will($this->returnValue(1));
        $target = new LogOn;
        $target->setDatabaseWrapper($mockDatabaseWrapper);
        $nonce = $target->initiate($this->username);
        $this->assertEquals(32, strlen($nonce));
    }
    
    public function testInitiateBadUsername()
    {
        $mockDatabaseWrapper = $this->getMock('DatabaseWrapper', array('execute'));
        $mockDatabaseWrapper->expects($this->once())
                ->method('execute')
                ->will($this->returnValue(0));
        $target = new LogOn;
        $target->setDatabaseWrapper($mockDatabaseWrapper);
        $nonce = $target->initiate($this->badUsername);
        $this->assertEquals(32, strlen($nonce));
    }
    
    public function testInitiateIsUnique()
    {
        $mockDatabaseWrapper = $this->getMock('DatabaseWrapper', array('execute'));
        $mockDatabaseWrapper->expects($this->exactly(2))
                ->method('execute')
                ->will($this->returnValue(1));
        $target = new LogOn;
        $target->setDatabaseWrapper($mockDatabaseWrapper);
        $firstNonce = $target->initiate($this->username);
        $secondNonce = $target->initiate($this->username);
        $this->assertNotEquals($firstNonce, $secondNonce);
    }
    
    public function testAuthenticateWithoutUsername()
    {
        $response = $this->target->authenticate('', '11111111111111111111111111111111', '11111111111111111111111111111111');
        $this->assertFalse($response->success);
        $this->assertEquals(MESSAGE_CREDENTIALS_INVALID, $response->errorNumber);
        $this->assertEquals('Invalid credentials provided', $response->errorMessage);
        $response = $this->target->authenticate(null, '11111111111111111111111111111111', '11111111111111111111111111111111');
        $this->assertFalse($response->success);
        $this->assertEquals(MESSAGE_CREDENTIALS_INVALID, $response->errorNumber);
        $this->assertEquals('Invalid credentials provided', $response->errorMessage);        
    }
    
    public function testAuthenticateUsernameWithBadFormat()
    {
        $response = $this->target->authenticate(" $this->username ", '11111111111111111111111111111111', '11111111111111111111111111111111');
        $this->assertFalse($response->success);
        $this->assertEquals(MESSAGE_CREDENTIALS_INVALID, $response->errorNumber);
        $this->assertEquals('Invalid credentials provided', $response->errorMessage);
    }

    public function testAuthenticateCNonceWithBadFormat()
    {
        $response = $this->target->authenticate($this->username, 'x', '11111111111111111111111111111111');
        $this->assertFalse($response->success);
        $this->assertEquals(MESSAGE_CREDENTIALS_INVALID, $response->errorNumber);
        $this->assertEquals('Invalid credentials provided', $response->errorMessage);
    }
    
    public function testAuthenticateHashWithBadFormat()
    {
        $response = $this->target->authenticate($this->username, '11111111111111111111111111111111', 'x');
        $this->assertFalse($response->success);
        $this->assertEquals(MESSAGE_CREDENTIALS_INVALID, $response->errorNumber);
        $this->assertEquals('Invalid credentials provided', $response->errorMessage);
    }

    public function testAuthenticateWithoutCNonce()
    {
        $response = $this->target->authenticate($this->username, '', '11111111111111111111111111111111');
        $this->assertFalse($response->success);
        $this->assertEquals(MESSAGE_CREDENTIALS_INVALID, $response->errorNumber);
        $this->assertEquals('Invalid credentials provided', $response->errorMessage);
        $response = $this->target->authenticate($this->username, null, '11111111111111111111111111111111');
        $this->assertFalse($response->success);
        $this->assertEquals(MESSAGE_CREDENTIALS_INVALID, $response->errorNumber);
        $this->assertEquals('Invalid credentials provided', $response->errorMessage);
    }

    public function testAuthenticateWithoutHash()
    {
        $cnonce = Encryption::generateNonce();
        $response = $this->target->authenticate($this->username, $cnonce, '');
        $this->assertFalse($response->success);
        $this->assertEquals(MESSAGE_CREDENTIALS_INVALID, $response->errorNumber);
        $this->assertEquals('Invalid credentials provided', $response->errorMessage);
        
        $cnonce = Encryption::generateNonce();
        $response = $this->target->authenticate($this->username, $cnonce, null);
        $this->assertFalse($response->success);
        $this->assertEquals(MESSAGE_CREDENTIALS_INVALID, $response->errorNumber);
        $this->assertEquals('Invalid credentials provided', $response->errorMessage);
    }

    public function testAuthenticateWithBadUsername()
    {
        $mockDatabaseWrapper = $this->getMock('DatabaseWrapper', array('getRow'));
        $mockDatabaseWrapper->expects($this->once())
                ->method('getRow')
                ->will($this->returnValue(NULL));
        $target = new LogOn;
        $target->setDatabaseWrapper($mockDatabaseWrapper);
        
        $nonce = Encryption::generateNonce();
        $cnonce = Encryption::generateNonce();
        $hash = Encryption::strengthenKey($this->password, "$cnonce$nonce", KEY_STRENGTH);
        
        $response = $target->authenticate($this->badUsername, $cnonce, $hash);
        $this->assertFalse($response->success);
        $this->assertEquals(MESSAGE_CREDENTIALS_INVALID, $response->errorNumber);
        $this->assertEquals('Invalid credentials provided', $response->errorMessage);
    }

    public function testAuthenticateWithBadNonce()
    {
        $row = $this->getRowResponse();
        $mockDatabaseWrapper = $this->getMock('DatabaseWrapper', array('getRow'));
        $mockDatabaseWrapper->expects($this->once())
                ->method('getRow')
                ->will($this->returnValue($row));
        $target = new LogOn;
        $target->setDatabaseWrapper($mockDatabaseWrapper);

        $cnonce = Encryption::generateNonce();
        $nonce = Encryption::generateNonce();
        $hash = Encryption::strengthenKey($this->password, "$cnonce$nonce", KEY_STRENGTH);
        
        $response = $target->authenticate($this->username, $cnonce, $hash);
        $this->assertFalse($response->success);
        $this->assertEquals(MESSAGE_CREDENTIALS_INVALID, $response->errorNumber);
        $this->assertEquals('Invalid credentials provided', $response->errorMessage);
    }

    public function testAuthenticateWithDuplicateNonce()
    {
        $row = $this->getRowResponse();
        $nonce = $row['Nonce'];

        $mockDatabaseWrapper = $this->getMock('DatabaseWrapper', array('getRow'));
        $mockDatabaseWrapper->expects($this->exactly(2))
                ->method('getRow')
                ->will($this->returnValue($row));
        $target = new LogOn;
        $target->setDatabaseWrapper($mockDatabaseWrapper);

        $cnonce = Encryption::generateNonce();
        $hash = Encryption::strengthenKey($this->password, "$cnonce$nonce", KEY_STRENGTH);
        
        $response = $target->authenticate($this->username, $cnonce, $hash);
        $this->assertTrue($response->success);

        $cnonce2 = Encryption::generateNonce();
        $hash2 = Encryption::strengthenKey($this->password, "$cnonce$nonce", KEY_STRENGTH);

        $response = $target->authenticate($this->username, $cnonce2, $hash2);
        $this->assertFalse($response->success);
        $this->assertEquals(MESSAGE_CREDENTIALS_INVALID, $response->errorNumber);
        $this->assertEquals('Invalid credentials provided', $response->errorMessage);
    }

    public function testAuthenticateWithBadPassword()
    {
        $row = $this->getRowResponse();
        $nonce = $row["Nonce"];
       
        $mockDatabaseWrapper = $this->getMock('DatabaseWrapper', array('getRow'));
        $mockDatabaseWrapper->expects($this->once())
                ->method('getRow')
                ->will($this->returnValue($row));
        $target = new LogOn;
        $target->setDatabaseWrapper($mockDatabaseWrapper);
        
        $cnonce = hash('md5', rand(0, getrandmax()).'MyOwnValue');
        $hash = Encryption::strengthenKey("bad$this->password", "$cnonce$nonce", KEY_STRENGTH);
       
        $response = $target->authenticate($this->username, $cnonce, $hash);
        $this->assertFalse($response->success);
        $this->assertEquals(MESSAGE_CREDENTIALS_INVALID, $response->errorNumber);
        $this->assertEquals('Invalid credentials provided', $response->errorMessage);
    }

    public function testEncryption()
    {
        $encryption = Encryption::encrypt($this->password);
        $decrypted = Encryption::decrypt($encryption);
        $this->assertEquals($this->password, $decrypted);
    }
    private function getRowResponse()
    {
        $row = array();
        $row["AccountName"] = $this->username;
        
        $row["Nonce"] = Encryption::generateNonce();
        $salted = Encryption::ensalt($this->password);
        $encryption = Encryption::encrypt($salted);
        $row["Password"] = $encryption->encrypted;
        $row["iv"] = $encryption->iv;
        
        return $row;
    }
    public function testAuthenticate()
    {
        $row = $this->getRowResponse();
        $mockDatabaseWrapper = $this->getMock('DatabaseWrapper', array('getRow'));
        $mockDatabaseWrapper->expects($this->once())
                ->method('getRow')
                ->will($this->returnValue($row));
        $target = new LogOn;
        $target->setDatabaseWrapper($mockDatabaseWrapper);

        $nonce = $row["Nonce"];
        $cnonce = Encryption::generateNonce();
        $hash = Encryption::strengthenKey($this->password, "$cnonce$nonce", KEY_STRENGTH);
        $response = $target->authenticate($this->username, $cnonce, $hash);
        $this->assertTrue($response->success, $response->errorMessage);
        $this->assertRegExp('/^[a-z0-9]{32}$/', $response->content);
    }
    
 }

?>
