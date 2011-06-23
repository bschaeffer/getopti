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
    $option = new Option('a', 'long', 'VALUE');
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
    $first = new Option('a', 'long');
    $repeat = new Option($short, $long);
    
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
    $this->switcher->add(new Option('a', NULL));
    $this->switcher->add(new Option(NULL, 'long'));
    $this->switcher->add(new Option('b', 'other'));
    
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
    $option = new Option('a', NULL);
    
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
   * @covers  Getopti\Switcher::_run_option
   */
  public function sets_non_parameter_options_to_TRUE_when_specified()
  {
    $option = new Option('a', NULL);
    
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
  public function sets_optional_argument_with_no_parameter_to_option_default()
  {
    $option = new Option('a', NULL, '[VALUE]');
    
    $this->switcher->add($option);
    $this->switcher->parse(array('-a'));
    
    $this->assertEquals(
      array($option->default), $this->switcher->options['a'],
      'Options that accept optional values should be set to the option default if no value is preset.'
    );
  }
  
  /**
   * @test
   * @covers  Getopti\Switcher::_run_option
   */
  public function uses_longopts_to_set_option_values_when_available()
  {
    $option = new Option('a', 'long', '[VALUE]');
    
    $this->switcher->add($option);
    $this->switcher->parse(array('-a'));
    $this->assertTrue( ! isset($this->switcher->options['a']));
    $this->assertTrue(isset($this->switcher->options['long']));
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
    
    $option = new Option('a', NULL, '[VALUE]', array($callback, 'callback'));

    $this->switcher->add($option);
    $this->switcher->parse(array('-a', 'some_value'));
  }
}

/* End of file SwitcherTest.php */
/* Location: ./Test/Getopti/SwitcherTest.php */