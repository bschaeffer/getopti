<?php

class OutputTest extends PHPUnit_Framework_TestCase 
{
  /**
   * @test
   * 
   * @covers  Getopti\Output::__construct
   * 
   * @author  Braden Schaeffer <braden.schaeffer@gmail.com>
   */
  public function setsStaticOuputVariableToEmptyStringOnNew()
  {
    Getopti\Output::$output = "non empty string";
    $out = new Getopti\Output();
    $this->assertSame('', $out::$output);
  }
  
  // --------------------------------------------------------------------
   
  public function optionProvider()
  {
    return array(
      array(
        array(array('h'), '', 'show help'),
        str_pad(' -h', 26, " ").'show help'.PHP_EOL
      ),
      array(
        array(array('f'), 'FILE', 'show file'),
        str_pad(' -f FILE', 26, " ").'show file'.PHP_EOL
      ),
      array(
        array(array('h', 'help'), '', 'show help'),
        str_pad(' -h, --help', 26, " ").'show help'.PHP_EOL
      ),
      array(
        array(array('f', 'file'), 'FILE', 'show file'),
        str_pad(' -f, --file FILE', 26, " ").'show file'.PHP_EOL
      ),
      array(
        array(array('file'), 'FILE', 'show file'),
        str_pad('     --file FILE', 26, " ").'show file'.PHP_EOL
      ),
    );
  }
  
  /**
   * @test
   * @dataProvider  optionProvider
   * 
   * @covers  Getopti\Output::option
   * 
   * @author  Braden Schaeffer <braden.schaeffer@gmail.com>
   */
  public function addsOptionsCorrectly($opts, $expected)
  {
    $out = new Getopti\Output();
    $out->option($opts[0], $opts[1], $opts[2]);
    $this->assertSame($expected, $out->help());
  }
  
  // --------------------------------------------------------------------
   
  public function commandProvider()
  {
    return array(
      array(
        array('command', 'command description'),
        str_pad(' command', 26, " ").'command description'.PHP_EOL
      ),
      array(
        array('help', 'another description'),
        str_pad(' help', 26, " ").'another description'.PHP_EOL
      ),
    );
  }
  
  /**
   * @test
   * @dataProvider  commandProvider
   * 
   * @covers  Getopti\Output::command
   * 
   * @author  Braden Schaeffer <braden.schaeffer@gmail.com>
   */
  public function addsCommandsCorrectly($opts, $expected)
  {
    $out = new Getopti\Output();
    $out->command($opts[0], $opts[1]);
    $this->assertSame($expected, $out->help());
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
   * @author  Braden Schaeffer <braden.schaeffer@gmail.com>
   */
  public function addsBannersCorrectly($banner)
  {
    $out = new Getopti\Output();
    $out->banner($banner);
    $this->assertEquals($banner.PHP_EOL, $out->help());
  }
  
  // --------------------------------------------------------------------
  
  /**
   * @test
   * 
   * @covers  Getopti\Output::usage
   * 
   * @author  Braden Schaeffer <braden.schaeffer@gmail.com>
   */
  public function addsUsagesCorrectly()
  {
    $usage = "script cmd [-f --flags]";
    
    $out = new Getopti\Output();
    $out->usage($usage);
    $this->assertEquals("Usage:".PHP_EOL."  ".$usage.PHP_EOL, $out->help());
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
   * @author  Braden Schaeffer <braden.schaeffer@gmail.com>
   */
  public function writeAddsNewLine($text)
  {
    $out = new Getopti\Output();
    $out->write($text);
    $this->assertEquals($text.PHP_EOL, $out::$output);
  }
  
  /**
   * @test
   * @dataProvider  textProvider
   * 
   * @covers  Getopti\Output::help
   * 
   * @author  Braden Schaeffer <braden.schaeffer@gmail.com>
   */
  public function helpReturnsCorrectOutput($text)
  {
    $out = new Getopti\Output();
    $out::$output = $text;
    $this->assertEquals($text, $out->help());
  }
}

/* End of file OutputTest.php */
/* Location: ./Test/Getopti/OutputTest.php */