<?php
class Parameter {
    public $name;
    public $type;
    public $value;
    
    public function __construct($name, $type, $value) {
        $this->name = $name;
        $this->type = $type;
        $this->value = $value;
    }    
}

?>
