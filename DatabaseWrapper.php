<?php
require_once dirname(__FILE__) . '/configuration.php';

/**
 * Description of DataProvider
 *
 * @author developer
 */
class DatabaseWrapper {

    private $connection;
    private $affectedRows;
    
    public function getRows($query)
    {
       $result = $this->query($query);
       //$rows = $result->fetch_all();
       $rows = array();
       while($row = $result->fetch_array())
       {
           $rows[] = $row;
       }
       $result->free();
       return $rows;
    }
    
    public function getRow($query)
    {
       $result = $this->query($query);
       $row = $result->fetch_assoc();
       $result->free();
       return $row;
    }
    
    public function escape($string_to_escape)
    {
         return $this->getConnection()->escape_string($string_to_escape);
    }
    
    public function getValue($query)
    {
        $row = $this->getRow($query);
        if(is_array($row))
        {
            foreach($row as $value)
            {
                return $value;
            }
        }
        return NULL;
    }
    
    public function getFirstRow($query)
    {
       $this->result = $this->query($query);
       $row = $this->result->fetch_assoc();
       return $row;
        
    }
    
    public function getNextRow()
    {
       $row = $this->result->fetch_assoc();
       if($row === NULL)
       {
            $this->result->free();
            $this->result = NULL;
       }
       return $row;
        
    }
    
    public function execute($query)
    {
       $result = $this->query($query);
       $affected = $this->affectedRows;
       if($result && is_object($result))
       {
            $result->free();
       }
       return $affected;
    }
    
    private function query($query)
    {
        $db = $this->getConnection();       
        $result = $db->query($query);
       
        if($this->hasError())
        {
            return NULL;
        }
        $this->affectedRows = $this->connection->affected_rows;
        
       return $result;
    }
    private function hasError()
    {
        if($this->connection->error)
        {
            die($this->connection->error);
            return TRUE;
        }
        return FALSE;

    }
    
    private function getConnection()
    {
        if($this->connection == NULL)
        {
            $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, 
                    DB_NAME, DB_PORT, DB_SOCKET)
            or die ('Could not connect to the database server');
        }
        
        return $this->connection;
    }

    function __destruct()
    {
        if($this->connection !== NULL)
        {
            $this->connection->close();
            $this->connection = NULL;
        }
    }
}

?>
