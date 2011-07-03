<?php

use Getopti\Command;

class CommmandTest extends PHPUnit_Framework_TestCase {
  
  /**
   * Setup method for each test
   */
  public function setUp()
  {
    Getopti::$columns = 0;
    $this->opts = new Command();
  }

  // --------------------------------------------------------------------
  
  /**
   * @test
   * @covers  Getopti\Command::__construct
   */
  public function construct()
  {
    Getopti::$columns = 0;
    
    $this->assertInstanceOf('Getopti\\Switcher', $this->opts->switcher);
    $this->assertInstanceOf('Getopti\\Output', $this->opts->output);
  }
  
  /**
   * @test
   * @covers  Getopti\Command::__toString
   */
  public function obj_toString()
  {
    $this->opts->banner("test banner");
    $this->assertEquals((string)$this->opts, $this->opts->help());
  }
    
  /**
   * @test
   * @covers  Getopti\Command::banner
   */
  public function banner()
  {
    $this->opts->banner("test banner");
    $this->assertEquals("test banner".PHP_EOL, $this->opts->help());
  }
  
  /**
   * @test
   * @covers  Getopti\Command::usage
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
   * @covers  Getopti\Command::on
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
   * @covers  Getopti\Command::on
   */
  public function on_allows_an_Option_object_as_parameter()
  {
    $option = \Getopti\Option::build('a');
    
    try
    {
      $this->opts->on($option, 'a simple description');
    }
    catch(Exception $e)
    {
      $this->fail(
        'The Getopti::on method did not allow a Getopti\Option object as the only parameter.'
      );
    }
    
    $this->assertContains(
      'a simple description', $this->opts->help(),
      'When passing a Getopti\\Option object as the first parameter, ' .
      '$opts->on did not use the 2nd parameter as the description.'
    );
  }
  
  /**
   * @test
   * @covers  Getopti\Command::command
   */
  public function command()
  { 
    $this->opts->command('command', 'description');
    $this->assertNotEmpty($this->opts->help());
  }
  
  /**
   * @test
   * @covers  Getopti\Command::help
   */
  public function help()
  {
    $this->opts->banner("test banner");
    $this->assertNotEmpty($this->opts->help());
  }
  
  // --------------------------------------------------------------------
  
  /**
   * @test
   * @covers  Getopti\Command::parse
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

/* End of file CommandTest.php */
/* Location: ./test/CommandTest.php */