<?php

use Getopti\Utils;

class UtilsTest extends PHPUnit_Framework_TestCase {
  
  /**
   * @test
   * @covers  Getopti\Utils::read_args
   */
  public function read_args()
  {
    // Set up the arguments
    global $argv;
    $argv = $_SERVER['argv'] = array('arg1', 'arg2', 'arg3');
    
    // Test it reads the global $argv
    $this->assertSame($argv, Utils::read_args(),
      'Getopti::read_args() should return global $argv.'
    );
    
    // Test it trims arguments correctly
    $this->assertSame(array('arg2', 'arg3'), Utils::read_args(1),
      'Getopti::read_args(1) should trim the first argument from the global $argv.'
    );
    
    // Test it reads $_SERVER['argv'] if no global $argv is present
    $argv = NULL;
    $this->assertSame($_SERVER['argv'], Utils::read_args(),
      'Getopti::read_args() should return $_SERVER[\'argv\'] for empty $argv.'
    );
    
    // Test it returns a empty array() if no 'argv' values are present
    $_SERVER['argv'] = NULL;
    $this->assertSame(array(), Utils::read_args(),
      'Getopti::read_args() should return an empty array when no \'argv\' values are present.'
    );
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
   * @covers  Getopti\Utils::get_columns
   *
   * @dataProvider columnsProvider
   */
  public function get_columns($set, $expected)
  {
    Getopti::$columns = $set;
    $this->assertEquals($expected, Utils::get_columns());
  }
}

/* End of file Utils.php */
/* Location: ./Test/Getopti/Utils.php */