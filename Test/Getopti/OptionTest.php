<?php

use Getopti\Option;

class OptionTest extends PHPUnit_Framework_TestCase {
  
  /**
   * @test
   * @covers  Getopti\Option::build
   */
  public function build()
  {
    $this->assertInstanceOf('Getopti\\Option', Option::build('a'));
  }
  
  // --------------------------------------------------------------------
  
  public function buildProvider()
  {
    return array(
      array(
        'a',    // the option(s)
        TRUE,   // expect short opt to be set
        FALSE   // expect long opt to be set
      ),
      array('long', FALSE, TRUE),
      array(array('a'), TRUE, FALSE),
      array(array('long'), FALSE, TRUE),
      array(array(NULL, 'long'), FALSE, TRUE),
      array(array('a', 'long'), TRUE, TRUE)
    );
  }
  
  /**
   * @test
   * @covers  Getopti\Option::build
   * 
   * @dataProvider  buildProvider
   */
  public function build_passes_options_correctly($opts, $is_short = FALSE, $is_long = FALSE)
  {
    $option = Option::build($opts);
    
    $short = ($is_short) ? 'a' : NULL;
    $this->assertEquals($short, $option->short);
    
    $long = ($is_long) ? 'long' : NULL;
    $this->assertEquals($long, $option->long);
  }
  
  // --------------------------------------------------------------------
  
  /**
   * @test
   * @covers  Getopti\Option::__construct
   */
  public function requires_at_least_a_short_or_long_option()
  {
    $this->setExpectedException('InvalidArgumentException');
    $void = new Option(NULL, NULL);
  }
  
  // --------------------------------------------------------------------
  
  public function optionProvider()
  {
    return array(
      array('a', NULL),
      array(NULL, 'long'),
      array('a', 'long'),
    );
  }
  
  /**
   * @test
   * @covers  Getopti\Option::__construct
   * 
   * @dataProvider  optionProvider
   */
  public function sets_options_correctly($short, $long)
  {
    $option = new Option($short, $long);
    $this->assertEquals($short, $option->short);
    $this->assertEquals($long, $option->long);
  }
  
  /**
   * @test
   * @covers  Getopti\Option::__toString
   *
   * @dataProvider  optionProvider
   */
  public function toString_returns_correct_string_reference($short, $long)
  {
    $option = new Option($short, $long);
    
    $index = (empty($long)) ? $short : $long;
    $this->assertEquals($index, (string)$option);
  }
  
  // --------------------------------------------------------------------
  
  /**
   * @test
   * @covers  Getopti\Option::__construct
   */
  public function invalid_callback_raises_an_error()
  {
    $this->setExpectedException('InvalidArgumentException');
    $void = new Option('a', NULL, NULL, 'not_a_valid_callback_function');
  }
  
  // --------------------------------------------------------------------
  
  public function parameterProvider()
  {
    $default = Option::OPTION_DEFAULT;
    
    return array(
      array(
        NULL,     // the param option
        NULL,     // the param string
        FALSE,    // is it required?
        $default, // the default value
      ),
      array('VALUE', 'VALUE', TRUE),
      array('[VALUE]', '[VALUE]', FALSE),
      array(array(NULL, NULL), NULL, FALSE),
      array(array('VALUE', NULL), 'VALUE', TRUE),
      array(array('[VALUE]', NULL), '[VALUE]', FALSE),
      array(array('[VALUE]', 'some_value'), '[VALUE]', FALSE, 'some_value'),
    );
  }
  
  /**
   * @test
   * @covers  Getopti\Option::__construct
   * @covers  Getopti\Option::_parse_parameter
   * 
   * @dataProvider  parameterProvider
   */
  public function parses_given_parameters_correctly($opts, $string, $required, $default = FALSE)
  {
    $option = new Option('a', NULL, $opts);
    
    $this->assertSame($string, $option->parameter);
    $this->assertSame($default, $option->default);
    $this->assertSame($required, $option->required);
  }
  
  /**
   * @test
   * @covers  Getopti\Option::long_string
   * @covers  Getopti\Option::_opt_string
   * 
   * @dataProvider  parameterProvider
   */
  public function generates_long_opt_strings_correctly($opts, $string, $required)
  {
    $option = Option::build('long', $opts);
    $pad = (empty($string)) ? 0 : ( ! $required) ? 1 : 2;
    
    $this->assertEquals(
      'long'.str_repeat(Option::INDICATOR_LONG, $pad),
      $option->long_string()
    );
  }
  
  /**
   * @test
   * @covers  Getopti\Option::short_string
   * @covers  Getopti\Option::_opt_string
   * 
   * @dataProvider  parameterProvider
   */
  public function generates_short_opt_strings_correctly($opts, $string, $required)
  {
    $option = Option::build('a', $opts);
    $pad = (empty($string)) ? 0 : ( ! $required) ? 1 : 2;
    
    $this->assertEquals(
      'a'.str_repeat(Option::INDICATOR_SHORT, $pad),
      $option->short_string()
    );
  }
  
  // --------------------------------------------------------------------
  
  /**
   * @test
   * @covers  Getopti\Option::run_callback
   */
  public function runs_callbacks_correctly()
  {
    $callback = $this->getMock('stdClass', array('callback'));
    $callback->expects($this->once())
             ->method('callback')
             ->with('some_value');
    
    $option = Option::build('a', 'VALUE', array($callback, 'callback'));
    $option->run_callback('some_value');
  }
}

/* End of file OptionTest.php */
/* Location: ./Test/Getopti/OptionTest.php */
