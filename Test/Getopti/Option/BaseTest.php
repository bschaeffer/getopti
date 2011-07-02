<?php

use Getopti\Option\Base;

class BaseTest extends PHPUnit_Framework_TestCase {
  
  /**
   * @test
   * @covers  Getopti\Option\Base::__construct
   */
  public function requires_at_least_a_short_or_long_option()
  {
    $this->setExpectedException('InvalidArgumentException');
    $void = new Base(NULL, NULL);
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
   * @covers  Getopti\Option\Base::__construct
   * 
   * @dataProvider  optionProvider
   */
  public function sets_options_correctly($short, $long)
  {
    $option = new Base($short, $long);
    $this->assertEquals($short, $option->short);
    $this->assertEquals($long, $option->long);
  }
  
  /**
   * @test
   * @covers  Getopti\Option\Base::__toString
   *
   * @dataProvider  optionProvider
   */
  public function toString_returns_correct_string_reference($short, $long)
  {
    $option = new Base($short, $long);
    
    $index = (empty($long)) ? $short : $long;
    $this->assertEquals($index, (string)$option);
  }
  
  // --------------------------------------------------------------------
  
  /**
   * @test
   * @covers  Getopti\Option\Base::__construct
   */
  public function invalid_callback_raises_an_error()
  {
    $this->setExpectedException('InvalidArgumentException');
    $void = new Base('a', NULL, NULL, 'not_a_valid_callback_function');
  }
  
  // --------------------------------------------------------------------
  
  public function parameterProvider()
  { 
    return array(
      array(
        NULL,     // the param option
        NULL,     // the param string
        FALSE,    // is it required?
        FALSE,    // can it be specified multiple times?
      ),
      array('VALUE',        'VALUE', TRUE),
      array('[VALUE]',      '[VALUE]', FALSE),
      array('VALUE[+]',     'VALUE[+]', TRUE, TRUE),
      array('[VALUE] [+]',  '[VALUE] [+]', FALSE, TRUE),
    );
  }
  
  /**
   * @test
   * @covers  Getopti\Option\Base::__construct
   * 
   * @dataProvider  parameterProvider
   */
  public function parses_given_parameters_correctly($opts, $string, $required, $multiple = FALSE)
  {
    $option = new Base('a', NULL, $opts);
    
    $this->assertSame(
      $string, $option->parameter,
      "The option's parameter string was not set correctly."
    );
    
    $this->assertSame(
      $required, $option->required,
      "The option's required property was not parsed correctly."
    );
    
    $this->assertSame(
      $multiple, $option->multiple,
      "The option's multiple property was not parsed correctly."
    );
    
    $rules = array( ! empty($string), $required);
    
    $this->assertSame(
      $rules, $option->rule,
      "The option's parsing rule (the rule property) was not set correctly." 
    );
  }
  
  // --------------------------------------------------------------------
  
  /**
   * @test
   * @covers  Getopti\Option\Base::run_callback
   */
  public function runs_callbacks_correctly()
  {
    $callback = $this->getMock('stdClass', array('callback'));
    $callback->expects($this->once())
             ->method('callback')
             ->with('some_value');
    
    $option = new Base('a', NULL, 'VALUE', array($callback, 'callback'));
    $option->run_callback('some_value');
  }
  
  /**
   * @test
   * @covers  Getopti\Option\Base::set_value
   */
  public function set_value_with_empty_value_sets_TRUE_with_no_parameter()
  {           
    $option = new Base('a', 'long', NULL);

    $switcher = $this->getMock('Getopti\\Switcher', array('set'));
    $switcher->expects($this->once())
             ->method('set')
             ->with(
               $this->equalTo($option),
               $this->equalTo(TRUE)
             );

    $option->set_value($switcher, NULL);
  }
  
  /**
   * @test
   * @covers  Getopti\Option\Base::set_value
   */
  public function set_value_for_multiple_pushes_value()
  {
    $option = new Base('a', 'long', 'PARAM [+]');
    
    $switcher = $this->getMock('Getopti\\Switcher', array('push'));
    $switcher->expects($this->once())
             ->method('push')
             ->with(
               $this->equalTo($option),
               $this->equalTo('some value')
             );

    $option->set_value($switcher, 'some value');
  }
}

/* End of file BaseTest.php */
/* Location: ./Test/Getopti/Option/BaseTest.php */