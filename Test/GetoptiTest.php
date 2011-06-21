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
  
  // --------------------------------------------------------------------
  
  /**
   * @test
   * @covers  Getopti::parse
   */
  public function parse()
  {
    $args = array('-a', 'value', 'nonopt', '--', 'breakopt');
    
    // Add a simple option switch
    $this->opts->on('a', 'VALUE');
    
    // Test default behavior 
    $results = $this->opts->parse($args);
    
    $this->assertEquals($results, $this->opts->results);
    $this->assertEquals(array('a' => array('value')), $this->opts->options);
    $this->assertEquals(array('nonopt'), $this->opts->nonopts);
    $this->assertEquals(array('breakopt'), $this->opts->breakopts);
    
    // Test returns flattened options array
    $options = $this->opts->parse($args, TRUE);
    
    $this->assertEquals($options, $this->opts->options);
  }
}

/* End of file GetoptiTest.php */
/* Location: ./test/GetoptiTest.php */