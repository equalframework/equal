<?php
namespace qinoa\php;

use qinoa\organic\Singleton;
use easyobject\orm\DataAdapter;

class Test extends Singleton {
    
    protected function __construct(DataAdapter $data) {
        $this->data = $data;
    }
    
    public function test() {
        print_r($this->data);
    }
    
    public function __toString() {
        return 'Test instance';
    }
}