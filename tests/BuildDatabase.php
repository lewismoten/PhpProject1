<?php
require_once dirname(__FILE__) . '/../configuration.php';

class BuildDatabase extends PHPUnit_Framework_TestCase {
    
    private $connection;
    protected function setUp() {
       $this->connection = new mysqli(DB_HOST, 'root');
       $this->assertEquals('', $this->connection->connect_error);
    }
    protected function tearDown() {
        $this->connection->close();
    }
    
    public function testBuildNewDatabase()
    {
        $db = DB_NAME;
        $account = '\''.DB_USER.'\'@\''.DB_HOST.'\'';
        $password = DB_PASSWORD;
        
       $this->connection->query("DROP DATABASE IF EXISTS $db");
       $this->assertEquals('', $this->connection->error);

       $this->connection->query("CREATE DATABASE IF NOT EXISTS $db");
       $this->assertEquals('', $this->connection->error);
       
       $this->connection->query("GRANT ALL ON $db.* TO $account IDENTIFIED BY '$password'");
       $this->assertEquals('', $this->connection->error);
//       
//       $this->connection->query("GRANT SELECT ON $db.* TO $account");
//       $this->assertEquals('', $this->connection->error);
//
       $this->connection->query("GRANT USAGE ON $db.* TO $account");
       $this->assertEquals('', $this->connection->error);
       
       $this->connection->change_user(DB_USER, DB_PASSWORD, DB_NAME);
       $this->assertEquals('', $this->connection->error);
       
       
       $path = dirname(__FILE__) . '/../Database/';
       
       $files = $this->getSqlFiles($path);
       
       foreach($files as $file)
       {
            $script = file_get_contents("$path$file");
            $this->assertNotEquals('', $script, "The file was $file");
            $result = $this->connection->query($script);
            $this->assertTrue($result, $file .' :: '.$this->connection->error);
            $this->assertEquals('', $this->connection->error, $file);
            print $script;
            if(is_object($result))
            {
                $result->close();
            }
       }
    }
    
    function getSqlFiles($path)
    {
       $files = array();
       $fileLink = opendir($path);
       while($file = readdir($fileLink))
       {
           if(substr($file, -4) == '.sql')
           {
                $files[] = $file;
           }
       }
       
       closedir($fileLink);
       
       sort($files);

       return $files;
    }
}
?>
