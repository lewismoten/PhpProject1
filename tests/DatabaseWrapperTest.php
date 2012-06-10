<?php

require_once dirname(__FILE__) . '/../DatabaseWrapper.php';

class DatabaseWrapperTest extends PHPUnit_Framework_TestCase {

    protected $target;

    protected function setUp() {
        $this->target = new DatabaseWrapper;
        
        $count = $this->target->execute("drop table if exists `Example`");
        $count = $this->target->execute("create table `Example` (`ExampleId` INT NOT NULL, `ExampleValue` VARCHAR(32) NULL, PRIMARY KEY (`ExampleId`))");
        $count = $this->target->execute("insert into `Example` values (1, 'a')");
        $this->assertEquals(1, $count);
        $count = $this->target->execute("insert into `Example` values (2, 'b')");
        $this->assertEquals(1, $count);
        $count = $this->target->execute("insert into `Example` values (3, 'c')");
        $this->assertEquals(1, $count);
        $count = $this->target->execute("insert into `Example` values (4, 'd')");
        $this->assertEquals(1, $count);
        
    }

    protected function tearDown() {
        $this->target->execute("drop table if exists `Example`");
    }

    public function testGetRows() {

        $rows = $this->target->GetRows("select * from Example order by ExampleId desc");
        
        $i = 4;
        $this->assertEquals(4, count($rows));
        foreach($rows as $row)
        {
            $this->assertEquals($i, $row["ExampleId"]);
            $this->assertEquals(substr(" abcd", $i, 1), $row["ExampleValue"]);
            $i--;
        }
        
    }

    public function testGetRow() {
        $row = $this->target->getRow("select * from Example where ExampleId = 1");
        $this->assertEquals(1, $row['ExampleId']);
        $this->assertEquals('a', $row['ExampleValue']);
    }

    public function testGetRowWithManyMatches() {
        $row = $this->target->getRow("select * from Example order by ExampleId desc");
        $this->assertEquals(4, $row['ExampleId']);
        $this->assertEquals('d', $row['ExampleValue']);
    }
    
    public function testGetRowNotFound() {
        $row = $this->target->getRow("select * from Example where 1 = 0");
        $this->assertNull($row);
    }
    
    public function testGetValue() {
        $value = $this->target->getValue("select ExampleValue from Example where ExampleId = 1");
        $this->assertEquals('a', $value);
    }
    
    public function testGetValueWithManyMatches() {
        $value = $this->target->getValue("select ExampleValue from Example order by ExampleId desc");
        $this->assertEquals('d', $value);
    }
    
    public function testGetValueWithManyFields() {
        $value = $this->target->getValue("select ExampleId, ExampleValue from Example where ExampleId = 2");
        $this->assertEquals(2, $value);
    }
    
    public function testGetValueNotFound() {
        $value = $this->target->getValue("select ExampleValue from Example where 1 = 0");
        $this->assertNull($value);
    }

    public function testGetFirstRow() {
        $row = $this->target->getFirstRow("select * from Example where ExampleId = 3");
        $this->assertEquals(3, $row['ExampleId']);
        $this->assertEquals('c', $row['ExampleValue']);

        $row = $this->target->getNextRow();
        $this->assertNull($row);
    }
    
    public function testGetFirstRowNotFound() {
        $row = $this->target->getFirstRow("select * from Example where 1 = 0");
        $this->assertNull($row);

        $row = $this->target->GetNextRow();
        $this->assertNull($row);
    }
    
    public function testGetFirstRowWithManyMatches() {
        $row = $this->target->getFirstRow("select * from Example order by ExampleId asc");
        $this->assertEquals(1, $row['ExampleId']);
        $this->assertEquals('a', $row['ExampleValue']);
        
        $row = $this->target->getNextRow();
        $this->assertEquals(2, $row['ExampleId']);
        $this->assertEquals('b', $row['ExampleValue']);

        $row = $this->target->getNextRow();
        $this->assertEquals(3, $row['ExampleId']);
        $this->assertEquals('c', $row['ExampleValue']);
       }
    
    public function testExecute() {
        $count = $this->target->execute("delete from `Example`");
        $this->assertEquals(4, $count);
    }
}

?>
