<?php

/**
 * @backupStaticAttributes enabled
 */
class GetoptiTest extends PHPUnit_Framework_TestCase {
  
  /**
   * Setup method for each test
   */
  public function setUp()
  {
    $this->opts = new Getopti();
  }
  
  // --------------------------------------------------------------------
  
  /**
   * @test
   *
   * @covers  Getopti::__toString
   * 
   * @author  Braden Schaeffer
   */
  public function obj_toString()
  {
    $this->opts->banner("test banner");
    $this->assertEquals((string)$this->opts, $this->opts->help());
  }
    
  /**
   * @test
   *
   * @covers  Getopti::banner
   * 
   * @author  Braden Schaeffer
   */
  public function banner()
  {
    $this->opts->banner("test banner");
    $this->assertEquals("test banner".PHP_EOL, $this->opts->help());
  }
  
  /**
   * @test
   * 
   * @covers  Getopti::usage
   * 
   * @author  Braden Schaeffer
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
   * 
   * @dataProvider  onProvider
   *
   * @covers  Getopti::on
   * 
   * @author  Braden Schaeffer
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
   *
   * @covers  Getopti::command
   * 
   * @author  Braden Schaeffer
   */
  public function command()
  { 
    $this->opts->command('command', 'description');
    $this->assertNotEmpty($this->opts->help());
  }
  
  /**
   * @test
   *
   * @covers  Getopti::help
   * 
   * @author  Braden Schaeffer
   */
  public function help()
  {
    $this->opts->banner("test banner");
    $this->assertNotEmpty($this->opts->help());
  }
}

/* End of file GetoptiTest.php */
/* Location: ./test/GetoptiTest.php */