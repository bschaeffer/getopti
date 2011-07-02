<?php

use Getopti\Option;

class SwitcherTest extends PHPUnit_Framework_TestCase {
  
  /**
   * Sets up a Getopti\Switcher object
   */
  public function setUp()
  {
    $this->switcher = new Getopti\Switcher();
  }
  
  /**
   * @test
   * @covers  Getopti\Switcher::add
   */
  public function add_method_requires_an_Option_object()
  {
    // This 'exception' actually represents a PHP error becuase
    // the argument is type cast to require a Getopti\Option object
    
    $this->setExpectedException('PHPUnit_Framework_Error');
    $this->switcher->add(new stdClass);
  }
  
  /**
   * @test
   * @covers  Getopti\Switcher::add
   */
  public function adds_option()
  {
    $option = Option::build(array('a', 'long'), 'VALUE');
    $this->switcher->add($option);
    
    $this->assertEquals($option->rule, $this->switcher->_shortopts['a']);
    $this->assertEquals($option->rule, $this->switcher->_longopts['long']);
    
    $this->assertEquals(
      $option, $this->switcher->_opts_cache['long'],
      'The option object should be cached based on the short2long association.'
    );
  }
  
  // --------------------------------------------------------------------
  
  public function repeatOptionsProvider()
  {
    return array(
      array('a', NULL),
      array(NULL, 'long')
    );
  }
  
  /**
   * @test
   * @covers  Getopti\Switcher::add
   * 
   * @dataProvider  repeatOptionsProvider
   */
  public function options_can_only_be_registered_once($short, $long)
  {
    $this->setExpectedException('InvalidArgumentException');
    $first = Option::build(array('a', 'long'));
    $repeat = Option::build(array($short, $long));
    
    $this->switcher->add($first);
    $this->switcher->add($repeat);
  }
  
  // --------------------------------------------------------------------
  
  /**
   * @test
   * @covers  Getopti\Switcher::add
   */
  public function sets_short2long_association()
  {
    $this->switcher->add(Option::build(array('a', NULL)));
    $this->switcher->add(Option::build(array(NULL, 'long')));
    $this->switcher->add(Option::build(array('b', 'other')));
    
    $this->assertFalse(
      isset($this->switcher->_short2long['a']),
      'A short2long association should not be set for a single short option.'
    );
    $this->assertFalse(
      isset($this->switcher->_short2long['long']),
      'A short2long association should not be set for a single long option.'
    );
    
    $this->assertTrue(
      isset($this->switcher->_short2long['b']),
      'A short2long association should be set when both are present.'
    );
  }
  
  /**
   * @test
   * @covers  Getopti\Switcher::parse
   */
  public function sets_parse_results_properties()
  {
    $args = array('-a', 'value', 'nonopt', '--', 'breakopts');
    $option = Option::build(array('a', NULL));
    
    $expected = Getopti\Parser::parse($args, array('a' => $option->rule));
    
    $this->switcher->add($option);
    
    $this->assertEquals(
      $expected, $this->switcher->parse($args),
      'Switcher::parse should return the results of Parser::parse.'
    );
    
    $this->assertEquals(
      $expected, $this->switcher->results,
      'After parsing, the results propery should be set correctly.'
    );
    
    $this->assertEquals(
      $expected[1], $this->switcher->nonopts,
      'After parsing, the nonopts propery should be set correctly.'
    );
    $this->assertEquals(
      $expected[2], $this->switcher->breakopts,
      'After parsing, the breakopts propery should be set correctly.'
    );
  }
  
  /**
   * @test
   * @covers  Getopti\Switcher::add
   */
  public function sets_option_to_empty_value_by_default()
  {
    $option = Option::build('a');
    $this->switcher->add($option);
    
    $this->assertFalse(
      $this->switcher->options['a'],
      'The option value was not set to FALSE by default.'
    );
  }
  
  /**
   * @test
   * @covers  Getopti\Switcher::_run_option
   */
  public function sets_non_parameter_options_to_TRUE_when_specified()
  {
    $option = Option::build(array('a', NULL));
    
    $this->switcher->add($option);
    $this->switcher->parse(array('-a'));
    
    $this->assertTrue(
      $this->switcher->options['a'],
      'When specified, options that do not accepts values should be set to TRUE.'
    );
  }
  
  /**
   * @test
   * @covers  Getopti\Switcher::_run_option
   */
  public function non_multiple_allowed_option_values_are_overridden_on_consecutive_calls()
  {
    $option = Option::build(array('a', NULL), '[VALUE]');
    
    $this->switcher->add($option);
    $this->switcher->parse(array('-a', 'first_call', '-a', 'second_call'));
    
    $this->assertEquals(
      'second_call', $this->switcher->options['a'],
      'The non-multiple-allowed option was not overridden with the second specified value.'
    );
  }
  
  /**
   * @test
   * @covers  Getopti\Switcher::_run_option
   */
  public function multiple_allowed_options_values_are_pushed_to_an_array()
  {
    $option = Option::build(array('a', NULL), '[VALUE] [+]');
    
    $this->switcher->add($option);
    $this->switcher->parse(array('-a', 'first_call', '-a', 'second_call'));
    
    $this->assertInternalType(
      'array', $this->switcher->options['a'],
      'A multiple-allowed option value should be an array.'
    );
    
    $this->assertContains(
      'first_call', $this->switcher->options['a'],
      'The multiple-allowed option value does not contain the first specified value.'
    );
    
    $this->assertContains(
      'second_call', $this->switcher->options['a'],
      'The multiple-allowed option value does not contain the second specified value.'
    );
  }
  
  /**
   * @test
   * @covers  Getopti\Switcher::_run_option
   */
  public function uses_longopts_to_set_option_values_when_available()
  {
    $option = Option::build(array('a', 'long'), '[VALUE]');
    
    $this->switcher->add($option);

    $this->assertArrayNotHasKey(
      'a', $this->switcher->options,
      'The options property contains an index for the short opt when the long opt is present.'
    );

    $this->assertArrayHasKey(
      'long', $this->switcher->options,
      'The options property does not contain an index for the long opt even though one is present.'
    );
  }
  
  /**
   * @test
   * @covers  Getopti\Switcher::_run_option
   */
  public function runs_callbacks()
  {
    $callback = $this->getMock('stdClass', array('callback'));
    $callback->expects($this->once())
             ->method('callback')
             ->with('some_value');
    
    $option = Option::build(array('a', NULL), '[VALUE]', array($callback, 'callback'));

    $this->switcher->add($option);
    $this->switcher->parse(array('-a', 'some_value'));
  }
  
  /**
   * @test
   * @covers  Getopti\Switcher::set
   */
  public function set_sets_value_correctly()
  {
    $option = Option::build('a');
    $this->switcher->add($option);
    $this->switcher->set($option, 'value');
    
    $this->assertEquals(
      'value', $this->switcher->options['a'],
      "Switcher::set did not set the option value correctly."
    );
  }
  
  /**
   * @test
   * @covers  Getopti\Switcher::set
   */
  public function set_called_twice_overrides_previous_value()
  {
    $option = Option::build('a');
    $this->switcher->add($option);
    $this->switcher->set($option, 'value');
    $this->switcher->set($option, 'another_value');
    
    $this->assertEquals(
      'another_value', $this->switcher->options['a'],
      "Switcher::set did not override the option value correctly."
    );
  }
  
  /**
   * @test
   * @covers  Getopti\Switcher::push
   */
  public function push_adds_value_correctly()
  {
    $option = Option::build('a');
    $this->switcher->add($option);
    $this->switcher->push($option, 'value');
    $this->switcher->push($option, 'another_value');
    
    $this->assertInternalType(
      'array', $this->switcher->options['a'],
      "Switcher::push did not set the the option value to an array."
    );
    
    $this->assertContains(
      'value', $this->switcher->options['a'],
      "Switcher::push did add the option value correctly to the array."
    );
    
    $this->assertContains(
      'another_value', $this->switcher->options['a'],
      "Switcher::push did add the option value correctly to the array."
    );
  }
}

/* End of file SwitcherTest.php */
/* Location: ./Test/Getopti/SwitcherTest.php */