<?php

use Getopti\Autoload;

class AutoloadTest extends PHPUnit_Framework_TestCase {
  
  /**
   * @test
   * @covers  Getopti\Autoload::load
   */
  public function load()
  {
    $this->assertFalse(Autoload::load('NotGetopti\\Fake\\Foo'));
    $this->assertFalse(Autoload::load('Getopti\\Fake\\Foo'));
    
    // Test: loads file (with require_once)
    $this->assertTrue(Autoload::load('Getopti\\Option\\Base'));
    
    // Test: returns TRUE for previously loaded file
    $this->assertTrue(Autoload::load('Getopti\\Option\\Base'));
  }
}

/* End of file AutoloadTest.php */
/* Location: ./Test/Getopti/AutoloadTest.php */