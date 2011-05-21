<?php

/**
 * @backupStaticAttributes enabled
 */
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
   * @author  Braden Schaeffer
   */
  public function exception()
  { 
    $this->assertInstanceOf('Exception', new Getopti\Exception);
  }
  
  /**
   * @test
   * 
   * @covers  Getopti::read_args
   * 
   * @author  Braden Schaeffer
   */
  public function read_args_with_trim()
  {
    $this->assertSame($this->argv_trimmed, Getopti\Base::read_args(1));
  }
  
  /**
   * @test
   * 
   * @covers  Getopti::read_args
   * 
   * @author  Braden Schaeffer
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
   * @author  Braden Schaeffer
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
   * @author  Braden Schaeffer
   */
  public function returns_empty_array_for_no_argv()
  { 
    global $argv;
    $argv = $_SERVER['argv'] = NULL;
    $this->assertSame(array(), Getopti\Base::read_args());
  }
  
  // --------------------------------------------------------------------
  
  public function columnsProvider()
  {
    $default_or_auto = Getopti::DEFAULT_COLUMNS;
    
    if( php_sapi_name() === 'cli' && 'darwin' === strtolower(PHP_OS))
    {
      $default_or_auto = (int)exec('tput cols');
    }
    
    return array(
      array(0,  $default_or_auto), // this expects either the default or auto-discovered
      array(80, 80),
      array(50, 50),
      array(10, 10)
    );
  }
  
  /**
   * @test
   * @dataProvider columnsProvider
   * 
   * @covers  Getopti\Base::get_columns
   * 
   * @author  Braden Schaeffer
   */
  public function get_columns($set, $expected)
  {
    Getopti::$columns = $set;
    $this->assertEquals($expected, Getopti::get_columns());
  }
}

/* End of file BaseTest.php */
/* Location: ./Test/Getopti/BaseTest.php */