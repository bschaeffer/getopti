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
  
  /**
   * Provides two sets of padding options
   */
  public function paddingProvider()
  {
    return array(
      array(
        1,  // Getopti::$left_padding
        26  // Getopti::$option_padding
      ),
      array(2, 30), // Two sets of padding should do
    );
  }
  
  // --------------------------------------------------------------------
  
  /**
   * @test
   *
   * @covers  Getopti::__toString
   * 
   * @author  Braden Schaeffer
   */
  public function getopti_obj_to_string_outputs_usage_information()
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
   * @dataProvider  paddingProvider
   * 
   * @covers  Getopti::usage
   * 
   * @author  Braden Schaeffer
   */
  public function usage($padding)
  {
    Getopti::$left_padding = $padding;
    
    $message = "test usage";
    $expected = str_repeat(" ", $padding).$message.PHP_EOL;
    
    $this->opts->usage($message);
    $this->assertEquals($expected, $this->opts->help());
  }
  
  /**
   * @test
   * 
   * @dataProvider  paddingProvider
   *
   * @covers  Getopti::on
   * 
   * @author  Braden Schaeffer
   */
  public function on_output($left_pad, $opt_pad)
  {
    Getopti::$left_padding   = $left_pad;
    Getopti::$option_padding = $opt_pad;
    
    $opts = str_repeat(" ", $left_pad).'-t, --test [OPTION]';
    $expected = str_pad($opts, $opt_pad, " ").'description'.PHP_EOL;
    
    $this->opts->on(array('t', 'test'), '[OPTION]', 'description');
    $this->assertEquals($expected, $this->opts->help());
  }
  
  /**
   * @test
   * 
   * @dataProvider  paddingProvider
   *
   * @covers  Getopti::command
   * 
   * @author  Braden Schaeffer
   */
  public function command_output($left_pad, $opt_pad)
  {
    Getopti::$left_padding = $left_pad;
    Getopti::$option_padding = $opt_pad;
    
    $command = str_repeat(" ", $left_pad).'command';
    $expected = str_pad($command, $opt_pad, " ").'description'.PHP_EOL;
    
    $this->opts->command('command', 'description');
    $this->assertEquals($expected, $this->opts->help());
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
    $this->assertEquals("test banner".PHP_EOL, $this->opts->help());
  }
}

/* End of file GetoptiTest.php */
/* Location: ./test/GetoptiTest.php */