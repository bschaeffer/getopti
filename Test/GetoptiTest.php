<?php

class GetoptiTest extends PHPUnit_Framework_TestCase {
  
  /**
   * Setup method for each test
   */
  public function setUp()
  {
    Getopti::$columns = 0;
    $this->opts = new Getopti();
  }

  // --------------------------------------------------------------------
  
  /**
   * @test
   * @covers  Getopti::__construct
   */
  public function construct()
  {
    Getopti::$columns = 0;
    
    $obj = new Getopti();
    
    $this->assertInstanceOf('Getopti\\Switcher', $obj->switcher);
    $this->assertInstanceOf('Getopti\\Output', $obj->output);
  }
  
  /**
   * @test
   * @covers  Getopti::__toString
   */
  public function obj_toString()
  {
    $this->opts->banner("test banner");
    $this->assertEquals((string)$this->opts, $this->opts->help());
  }
    
  /**
   * @test
   * @covers  Getopti::banner
   */
  public function banner()
  {
    $this->opts->banner("test banner");
    $this->assertEquals("test banner".PHP_EOL, $this->opts->help());
  }
  
  /**
   * @test
   * @covers  Getopti::usage
   */
  public function usage()
  { 
    $this->opts->usage('message');
    $this->assertNotEmpty($this->opts->help());
  }
  
  // --------------------------------------------------------------------
  
  public function onProvider()
  {
    return array(
      array(
        'a',    // the options
        TRUE,   // is it short?
        FALSE   // is it long?
      ),
      array('long', FALSE, TRUE),
      array(array('a'), TRUE),
      array(array('long'), FALSE, TRUE),
      array(array('a', 'long'), TRUE, TRUE)
    );
  }
  
  /**
   * @test
   * @covers  Getopti::on
   * 
   * @dataProvider onProvider
   */
  public function on($option, $short = FALSE, $long = FALSE)
  { 
    $this->opts->on($option);
    
    // Test that the options were added to the switcher correctly
    if($short) $this->assertNotEmpty($this->opts->switcher->_shortopts);
    if($long) $this->assertNotEmpty($this->opts->switcher->_longopts);
    
    // Test that there is output
    $this->assertNotEmpty($this->opts->help());
  }
  
  // --------------------------------------------------------------------
  
  /**
   * @test
   * @covers  Getopti::command
   */
  public function command()
  { 
    $this->opts->command('command', 'description');
    $this->assertNotEmpty($this->opts->help());
  }
  
  /**
   * @test
   * @covers  Getopti::help
   */
  public function help()
  {
    $this->opts->banner("test banner");
    $this->assertNotEmpty($this->opts->help());
  }
  
  /*-------------------------------------------------------
  * Static Methods
  -------------------------------------------------------*/
  
  /**
   * @test
   * @covers  Getopti::read_args
   */
  public function read_args()
  {
    // Set up the arguments
    global $argv;
    $argv = $_SERVER['argv'] = array('arg1', 'arg2', 'arg3');
    
    // Test it reads the global $argv
    $this->assertSame($argv, Getopti::read_args(),
      'Getopti::read_args() should return global $argv.'
    );
    
    // Test it trims arguments correctly
    $this->assertSame(array('arg2', 'arg3'), Getopti::read_args(1),
      'Getopti::read_args(1) should trim the first argument from the global $argv.'
    );
    
    // Test it reads $_SERVER['argv'] if no global $argv is present
    $argv = NULL;
    $this->assertSame($_SERVER['argv'], Getopti::read_args(),
      'Getopti::read_args() should return $_SERVER[\'argv\'] for empty $argv.'
    );
    
    // Test it returns a empty array() if no 'argv' values are present
    $_SERVER['argv'] = NULL;
    $this->assertSame(array(), Getopti::read_args(),
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
   * @covers  Getopti::get_columns
   *
   * @dataProvider columnsProvider
   */
  public function get_columns($set, $expected)
  {
    Getopti::$columns = $set;
    $this->assertEquals($expected, Getopti::get_columns());
  }
}

/* End of file GetoptiTest.php */
/* Location: ./test/GetoptiTest.php */