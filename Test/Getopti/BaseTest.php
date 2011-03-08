<?php

class BaseTest extends PHPUnit_Framework_TestCase {
  
  public function setUp()
  {
    global $argv;
    
    $this->argv = array('arg1', 'arg2', 'arg3'); 
    $this->argv_trimmed = array('arg2', 'arg3');
    
    $argv = $_SERVER['argv'] = $GLOBALS['HTTP_SERVER_VARS']['argv'] = $this->argv;
  }
  
  /**
   * @test
   * 
   * @covers  Getopti::read_args
   * 
   * @author  bschaeffer
   */
  public function readsGlobalArgv()
  { 
    $this->assertSame($this->argv, Getopti\Base::read_args());
  }
  
  /**
   * @test
   * 
   * @covers  Getopti::read_args
   * 
   * @author  bschaeffer
   */
  public function trimsFirstArgumentFromGlobalArgs()
  {
    $this->assertSame($this->argv_trimmed, Getopti\Base::read_args(1));
  }
}

/* End of file BaseTest.php */
/* Location: ./Test/Getopti/BaseTest.php */