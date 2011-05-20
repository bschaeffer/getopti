<?php

class SwitcherTest extends PHPUnit_Framework_TestCase {
  
  // --------------------------------------------------------------------
  
  public function shortoptProvider()
  {
    return array(
      array('a', NULL,      'a'),
      array('a', '[ITEM]',  'a:'),
      array('a', 'ITEM',    'a::'),
      array('a', 0, 'a'),
      array('a', 1, 'a:'),
      array('a', 2, 'a::'),
    );
  }
  
  /**
   * @test
   * @dataProvider  shortoptProvider
   * 
   * @covers  Getopti\Switcher::add
   * @covers  Getopti\Switcher::_parse_opts
   * @covers  Getopti\Switcher::_parse_requirement_level
   * 
   * @author  Braden Schaeffer
   */
  public function adds_shortopts_correctly($opt, $param, $expected)
  {
    $switcher = new Getopti\Switcher;
    $switcher->add(array($opt, NULL), $param);
    $this->assertSame($expected, $switcher->_shortopts);
  }
  
  // --------------------------------------------------------------------
  
  public function longoptProvider()
  {
    return array(
      array('long', NULL,     'long'),
      array('long', '[ITEM]', 'long='),
      array('long', 'ITEM',   'long=='),
      array('long', 0, 'long'),
      array('long', 1, 'long='),
      array('long', 2, 'long==')
    );
  }
  
  /**
   * @test
   * @dataProvider  longoptProvider
   * 
   * @covers  Getopti\Switcher::add
   * @covers  Getopti\Switcher::_parse_opts
   * @covers  Getopti\Switcher::_parse_requirement_level
   * 
   * @author  Braden Schaeffer
   */
  public function adds_longopts_orrectly($opt, $param, $expected)
  {
    $switcher = new Getopti\Switcher;
    $switcher->add(array(NULL, $opt), $param);
    $this->assertSame($expected, $switcher->_longopts[0]);
  }
  
  // --------------------------------------------------------------------
  
  public function optionProvider()
  {
    return array(
      array(array('a'),           'a',    array()),
      array(array('long'),        'long', array()),
      array(array('a', 'long'),   'long', array('a' => 'long'))
    );
  }
  
  /**
   * @test
   * @dataProvider  optionProvider
   * 
   * @covers  Getopti\Switcher::add
   * @covers  Getopti\Switcher::_parse_opts
   * 
   * @author  Braden Schaeffer
   */
  public function sets_short2long_array_correctly($opts, $ignore, $expected)
  {
    $switcher = new Getopti\Switcher;
    $switcher->add($opts);
    $this->assertSame($expected, $switcher->_short2long);
  }
  
  /**
   * @test
   * @dataProvider  optionProvider
   * 
   * @covers  Getopti\Switcher::add
   * @covers  Getopti\Switcher::_parse_opts
   * 
   * @author  Braden Schaeffer
   */
  public function intializes_option_values_with_false($opts, $index)
  {
    $switcher = new Getopti\Switcher;
    $switcher->add($opts);
    $this->assertFalse($switcher->options[$index]);
  }
  
  // --------------------------------------------------------------------
  
  /**
   * @test
   *
   * @expectedException InvalidArgumentException
   * 
   * @covers  Getopti\Switcher::add
   * @covers  Getopti\Switcher::_parse_requirement_level
   * 
   * @author  Braden Schaeffer <hello@manasto.info>
   */
  public function invalid_parameter_requirement_level_raises_exception()
  {
    $switcher = new Getopti\Switcher();
    $switcher->add(array('a'), 4);
  }
}

/* End of file SwitcherTest.php */
/* Location: ./Test/Getopti/SwitcherTest.php */