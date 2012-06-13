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
        $nonce = hash('md5', 'testAuthenticateWithBadUsername');
        $mockDatabaseWrapper = $this->getMock('DatabaseWrapper', array('getRow'));
        $mockDatabaseWrapper->expects($this->once())
                ->method('getRow')
                ->will($this->returnValue(NULL));
        $target = new LogOn;
        $target->setDatabaseWrapper($mockDatabaseWrapper);
        
        $cnonce = hash('md5', rand(0, getrandmax()).'MyOwnValue');
        $hash = hash('md5', "bad$nonce:$cnonce:$this->password");
        
        $response = $target->authenticate($this->badUsername, $cnonce, $hash);
        $this->assertFalse($response->success);
        $this->assertEquals(MESSAGE_CREDENTIALS_INVALID, $response->errorNumber);
        $this->assertEquals('Invalid credentials provided', $response->errorMessage);
    }

    public function testAuthenticateWithBadNonce()
    {
        $row = $this->getRowResponse();
        $nonce = hash('md5', 'An unexpected value');

        $mockDatabaseWrapper = $this->getMock('DatabaseWrapper', array('getRow'));
        $mockDatabaseWrapper->expects($this->once())
                ->method('getRow')
                ->will($this->returnValue($row));
        $target = new LogOn;
        $target->setDatabaseWrapper($mockDatabaseWrapper);

        $cnonce = hash('md5', rand(0, getrandmax()).'MyOwnValue');
        $hash = hash('md5', "bad$nonce:$cnonce:$this->password");
        
        $encrypted = Encryption::encrypt($this->password);
        
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
        $mockDatabaseWrapper->expects($this->once())
                ->method('getRow')
                ->will($this->returnValue($row));
        $target = new LogOn;
        $target->setDatabaseWrapper($mockDatabaseWrapper);

        $cnonce = hash('md5', rand(0, getrandmax()).'MyOwnValue');
        $hash = Encryption::stretchKey("$this->password$cnonce$nonce");
        
        $response = $target->authenticate($this->username, $cnonce, $hash);
        $this->assertTrue($response->success);

        $cnonce2 = hash('md5', rand(0, getrandmax()).'MyOtherValue');
        $hash2 = Encryption::stretchKey("$this->password$cnonce2$nonce");

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
        $hash = Encryption::stretchKey("bad$this->password$cnonce$nonce");
       
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
        
        $row["Nonce"] = hash('md5', 'Just an example');
        
        $encryption = Encryption::encrypt("$this->password:my salt value");
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
        $cnonce = hash('md5', rand(0, getrandmax()).'MyOwnValue');
        $hash = Encryption::stretchKey("$this->password$cnonce$nonce");
        $response = $target->authenticate($this->username, $cnonce, $hash);
        $this->assertTrue($response->success, $response->errorMessage);
        $this->assertRegExp('/^[a-z0-9]{32}$/', $response->content);
    }
    
    }

?>
