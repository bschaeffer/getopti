<?php

class SwitcherTest extends PHPUnit_Framework_TestCase {
  
  // --------------------------------------------------------------------
  
  public function shortoptProvider()
  {
    return array(
      array('a', NULL,      'a'),
      array('a', '[ITEM]',  'a:'),
      array('a', 'ITEM',    'a::')
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
   * @author  bschaeffer
   */
  public function addsShortoptsCorrectly($opt, $param, $expected)
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
      array('long', 'ITEM',   'long==')
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
   * @author  bschaeffer
   */
  public function addsLongoptsCorrectly($opt, $param, $expected)
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
   * @author  bschaeffer
   */
  public function setsShort2longArrayCorrectly($opts, $ignore, $expected)
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
   * @author  bschaeffer
   */
  public function intializesOptionValuesWithFalse($opts, $index)
  {
    $switcher = new Getopti\Switcher;
    $switcher->add($opts);
    $this->assertFalse($switcher->options[$index]);
  }
}

/* End of file SwitcherTest.php */
/* Location: ./Test/Getopti/SwitcherTest.php */