<?php

class BaseTest extends PHPUnit_Framework_TestCase {
  
  public function setUp()
  {
    global $argv;
    
    $this->argv = array('arg1', 'arg2', 'arg3'); 
    $this->argv_trimmed = array('arg2', 'arg3');
    
    $argv = $_SERVER['argv'] = $this->argv;
  }
  
  /**
   * @test
   * 
   * @covers  Getopti\Exception
   * 
   * @author  Braden Schaeffer <braden.schaeffer@gmail.com>
   */
  public function contains_getopti_exception()
  { 
    $this->assertInstanceOf('Exception', new Getopti\Exception);
  }
  
  /**
   * @test
   * 
   * @covers  Getopti::read_args
   * 
   * @author  Braden Schaeffer <braden.schaeffer@gmail.com>
   */
  public function trims_first_argument_from_global_args()
  {
    $this->assertSame($this->argv_trimmed, Getopti\Base::read_args(1));
  }
  
  /**
   * @test
   * 
   * @covers  Getopti::read_args
   * 
   * @author  Braden Schaeffer <braden.schaeffer@gmail.com>
   */
  public function reads_argv()
  { 
    $this->assertSame($this->argv, Getopti\Base::read_args());
  }
  
  /**
   * @test
   * 
   * @covers  Getopti::read_args
   * 
   * @author  Braden Schaeffer <braden.schaeffer@gmail.com>
   */
  public function reads_server_argv()
  { 
    global $argv;
    $argv = NULL;
    $this->assertSame($this->argv, Getopti\Base::read_args());
  }
  
  /**
   * @test
   * 
   * @covers  Getopti::read_args
   * 
   * @author  Braden Schaeffer <braden.schaeffer@gmail.com>
   */
  public function returns_empty_array_for_no_argv()
  { 
    global $argv;
    $argv = $_SERVER['argv'] = NULL;
    $this->assertSame(array(), Getopti\Base::read_args());
  }
}

/* End of file BaseTest.php */
/* Location: ./Test/Getopti/BaseTest.php */