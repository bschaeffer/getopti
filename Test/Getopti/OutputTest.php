<?php

use Getopti\Output;

class OutputTest extends PHPUnit_Framework_TestCase 
{
  /**
   * Setup method for each test
   */
  public function setUp()
  {
    $this->output = new Output();
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
  public function option($opts, $expected)
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
  public function command($opts, $expected)
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
  public function banner($banner)
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
  public function usage()
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
  public function write($text)
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
  public function help($text)
  {
    $this->output->output = $text;
    $this->assertEquals($text, $this->output->help());
  }
  
  // --------------------------------------------------------------------
  
  /**
   * @test
   *
   * @covers  Getopti\Output::pad
   * 
   * @author  Braden Schaeffer
   */
  public function pad()
  { 
    // Simple left padding
    $expected = str_repeat(" " , Getopti::$left_padding).'test';
    $this->assertEquals($expected, Output::pad('test', FALSE));
    
    // Left pading and option padding
    $expected = str_pad($expected, Getopti::$option_padding, " ");
    $this->assertEquals($expected, Output::pad('test'));
  }
  
  /**
   * @test
   *
   * @covers  Getopti\Output::br
   * 
   * @author  Braden Schaeffer
   */
  public function br()
  {    
    // Typical $option_padding break
    $expected = PHP_EOL.str_repeat(" ", Getopti::$option_padding);
    $this->assertEquals($expected, Output::br());
    
    // Custom $option_padding
    $expected = PHP_EOL.str_repeat(" ", 2);
    $this->assertEquals($expected, Output::br(2));
  }
  
  // --------------------------------------------------------------------
  
  public function formatStringProvider()
  {
    return array(
      array(
        'command',      // the option/command
        'description',  // the description
      ),
      array(
        'command longer than the default option padding of 26',
        'description',
      ),
    );
  }
  
  /**
   * @test
   * @dataProvider  formatStringProvider
   *
   * @covers  Getopti\Output::format_string
   * 
   * @author  Braden Schaeffer
   */
  public function format_string($opt, $description)
  {
    $option = Output::pad($opt);
    
    if(strlen($option) > Getopti::$option_padding)
    {
      $option .= Output::br();
    }
    
    $expected = Output::wrap($description, Output::br(), $option);
    $this->assertEquals($expected, Output::format_string($opt, $description));
  }
  
  // --------------------------------------------------------------------
  
  public function wrapProvider()
  {
    return array(
      array(
        // Expected
        "when Getopti wraps this\nthis should be a new line",
        // String to be wrapped
        "when Getopti wraps this this should be a new line"
      ),
      array(
        // Test text with new lines at points that wouldn't
        // otherwise be wrapped
        "this text\nshould start wrapping on\nthe second line",
        "this text\nshould start wrapping on the second line"
      ),
      array(
        // Expected
        "\nwhen Getopti wraps this\nthis should be a new line",
        // Test that a new line at the beggining of the string remains
        "\nwhen Getopti wraps this this should be a new line"
      ),
      array(
        // Expected
        "  wrapping a haiku\n" .
        "  will not be as easy as\n" .
        "  you think it would be",
        // String to be wrapped
        "wrapping a haiku\nwill not be as easy as you think it would be",
        // The break string (3rd param)
        Output::br(2)
      ),
      array(
        // Expected
        "=> when Getopti wraps this\n" .
        "   this should be new",
        // String to be wrapped
        "when Getopti wraps this this should be new",
        // The break string (3rd param)
        Output::br(3),
        '=>'
      ),
      
    );
  }
  
  /**
   * @test
   * @dataProvider wrapProvider
   *
   * @covers  Getopti\Output::wrap
   * 
   * @author  Braden Schaeffer
   */
  public function wrap($expected, $string, $break = PHP_EOL, $append = '')
  {
    Getopti::$columns = 30; // Let's do 30 so I don't have to type an s-ton of text
    $this->assertSame($expected, Output::wrap($string, $break, $append));
  }
}

/* End of file OutputTest.php */
/* Location: ./Test/Getopti/OutputTest.php */