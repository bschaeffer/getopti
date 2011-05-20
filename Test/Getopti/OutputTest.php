<?php

class OutputTest extends PHPUnit_Framework_TestCase 
{
  /**
   * Setup method for each test
   */
  public function setUp()
  {
    $this->output = new Getopti\Output();
  }
  
  // --------------------------------------------------------------------
   
  public function optionProvider()
  {
    $pad = str_repeat(" ", 2);
    
    return array(
      array(
        array(array('h'), '', 'show help'),
        str_pad($pad.'-h', 26, " ").'show help'.PHP_EOL
      ),
      array(
        array(array('f'), 'FILE', 'show file'),
        str_pad($pad.'-f FILE', 26, " ").'show file'.PHP_EOL
      ),
      array(
        array(array('h', 'help'), '', 'show help'),
        str_pad($pad.'-h, --help', 26, " ").'show help'.PHP_EOL
      ),
      array(
        array(array('f', 'file'), 'FILE', 'show file'),
        str_pad($pad.'-f, --file FILE', 26, " ").'show file'.PHP_EOL
      ),
      array(
        array(array('file'), 'FILE', 'show file'),
        str_pad(str_repeat(" ", 6).'--file FILE', 26, " ").'show file'.PHP_EOL
      ),
    );
  }
  
  /**
   * @test
   * @dataProvider  optionProvider
   * 
   * @covers  Getopti\Output::option
   * 
   * @author  Braden Schaeffer
   */
  public function adds_options_correctly($opts, $expected)
  {
    $this->output->option($opts[0], $opts[1], $opts[2]);
    $this->assertSame($expected, $this->output->help());
  }
  
  // --------------------------------------------------------------------
   
  public function commandProvider()
  {
    $pad = str_repeat(" ", 2);
    
    return array(
      array(
        array('command', 'command description'),
        str_pad($pad.'command', 26, " ").'command description'.PHP_EOL
      ),
      array(
        array('help', 'another description'),
        str_pad($pad.'help', 26, " ").'another description'.PHP_EOL
      ),
    );
  }
  
  /**
   * @test
   * @dataProvider  commandProvider
   * 
   * @covers  Getopti\Output::command
   * 
   * @author  Braden Schaeffer
   */
  public function adds_commands_correctly($opts, $expected)
  {
    $this->output->command($opts[0], $opts[1]);
    $this->assertSame($expected, $this->output->help());
  }
  
  // --------------------------------------------------------------------
  
  public function bannerProvider()
  {
    return array(
      array("global options:"),
      array("command options:"),
      array("this application is way cool!!")
    );
  }
  
  /**
   * @test
   * @dataProvider  bannerProvider
   * 
   * @covers  Getopti\Output::banner
   * 
   * @author  Braden Schaeffer
   */
  public function adds_banners_correctly($banner)
  {
    $this->output->banner($banner);
    $this->assertEquals($banner.PHP_EOL, $this->output->help());
  }
  
  // --------------------------------------------------------------------
  
  /**
   * @test
   * 
   * @covers  Getopti\Output::usage
   * 
   * @author  Braden Schaeffer
   */
  public function adds_usages_correctly()
  {
    $usage = "script cmd [-f --flags]";
    
    $this->output->usage($usage);
    $this->assertEquals(str_repeat(" ", 2).$usage.PHP_EOL, $this->output->help());
  }
  
  // --------------------------------------------------------------------
  
  public function textProvider()
  {
    return array(
      array(''),
      array(PHP_EOL),
      array('test line')
    );
  }
  
  /**
   * @test
   * @dataProvider textProvider
   * 
   * @covers  Getopti\Output::write
   * 
   * @author  Braden Schaeffer
   */
  public function appends_new_line_to_each_write_request($text)
  {
    $this->output->write($text);
    $this->assertEquals($text.PHP_EOL, $this->output->output);
  }
  
  /**
   * @test
   * @dataProvider  textProvider
   * 
   * @covers  Getopti\Output::help
   * 
   * @author  Braden Schaeffer
   */
  public function returns_correct_usage_information($text)
  {
    $this->output->output = $text;
    $this->assertEquals($text, $this->output->help());
  }
}

/* End of file OutputTest.php */
/* Location: ./Test/Getopti/OutputTest.php */