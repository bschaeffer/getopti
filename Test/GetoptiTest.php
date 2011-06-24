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
   * @covers  Getopti::on
   */
  public function on_allows_an_Option_object_as_only_parameter()
  {
    $option = Getopti\Option::build('a');
    
    try
    {
      $this->opts->on($option);
    }
    catch(Exception $e)
    {
      $this->fail(
        'The Getopti::on method did not allow a Getopti\Option object as the only parameter.'
      );
    }
  }
  
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
    
    $this->assertSame(
      $results, $this->opts->results
    );
    
    $this->assertNotEmpty($this->opts->results,   'The results property should not be empty.');
    $this->assertNotEmpty($this->opts->options,   'The options property should not be empty.');
    $this->assertNotEmpty($this->opts->nonopts,   'The nonotps property should not be empty.');
    $this->assertNotEmpty($this->opts->breakopts, 'The breakopts property should not be empty.');
    
    // Test returns flattened options array
    $options = $this->opts->parse($args, TRUE);
    
    $this->assertEquals($options, $this->opts->options);
  }
}

/* End of file GetoptiTest.php */
/* Location: ./test/GetoptiTest.php */