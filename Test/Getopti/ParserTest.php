<?php

class ParserTest extends PHPUnit_Framework_TestCase {
  
  // --------------------------------------------------------------------
  
  public function validLongoptProvider()
  {
    return array(
      array('--a0'),
      array('--a0='),
      array('--a-0'),
      array('--a-0=')
    );
  }
  
  /**
   * @test
   * @dataProvider validLongoptProvider
   * 
   * @covers  Getopti\Parser::is_longopt
   * 
   * @author  bschaeffer
   */
  public function validLongoptsAreValid($long)
  {
    $this->assertTrue(Getopti\Parser::is_longopt($long));
  }
  
  // --------------------------------------------------------------------

  public function validShortoptProvider()
  {
    return array(
      array('-a'),
      array('-0'),
      array('-ab'),
      array('-a1'),
      array('-0b'),
      array('-01')
    );
  }
  
  /**
   * @test
   * @dataProvider validShortoptProvider
   * 
   * @covers  Getopti\Parser::is_shortopt
   * 
   * @author  bschaeffer
   */
  public function validShortoptsAreValid($short)
  {
    $this->assertTrue(Getopti\Parser::is_shortopt($short));
  }

  // --------------------------------------------------------------------

  public function invalidOptionProvider()
  {
    return array(
      array('-a='),
      array('--a'),
      array('--a='),
      array('-'),
      array('---'),
      array('non-opt'),
    );
  }
  
  /**
   * @test
   * @dataProvider invalidOptionProvider
   * 
   * @covers  Getopti\Parser::is_shortopt
   * @author  bschaeffer
   */
  public function invalidShortoptsAreInvalid($longopt)
  {
    $this->assertFalse(Getopti\Parser::is_shortopt($longopt));
  }
  
  /**
   * @test
   * @dataProvider invalidOptionProvider
   * 
   * @covers  Getopti\Parser::is_longopt
   * 
   * @author  bschaeffer
   */
  public function invalidLongoptsAreInvalid($long)
  {
    $this->assertFalse(Getopti\Parser::is_longopt($long));
  }
  
  // --------------------------------------------------------------------
  
  public function validShortoptArgumentProvider()
  {
    // $rules[0] returns the actual rule (a::)
    // $rules[1] returns expected from Getopti\Parser::get_shortopts
    $rules = function ($v, $r) {
      $rule = 'a';
      if($v) $rule .= ":";
      if($r) $rule .= ":";
      return array( $rule, array('a' => array($v, $r)) );
    };
    
    $return = function ($v) {
      return array( array('a', $v) );
    };
    
    return array(
      array( array(),   array(array(), array()), array()), // returns empty
      array( array('-a'),           $rules(FALSE, FALSE), $return(NULL) ),
      array( array('-a', 'value'),  $rules(TRUE, FALSE),  $return('value') ),
      array( array('-a', 'value'),  $rules(TRUE, TRUE),   $return('value') )
    );
  }
  
  /**
   * @test
   * @dataProvider validShortoptArgumentProvider
   * 
   * @covers  Getopti\Parser::get_shortopts
   * 
   * @author  bschaeffer
   */
  public function setsUpShortoptsCorrectly($args, $rules)
  {
    $results = Getopti\Parser::get_shortopts($rules[0]);
    $this->assertSame($rules[1], $results);
  }
    
  /**
   * @test
   * @dataProvider validShortoptArgumentProvider
   * 
   * @covers  Getopti\Parser::parse
   * @covers  Getopti\Parser::_parse_shortopt
   * 
   * @author  bschaeffer
   */
  public function parsesValidShortArgumentsCorrectly($args, $rules, $expected)
  {
    list($opts, $nonopts) = Getopti\Parser::parse($args, $rules[0], array());
    $this->assertSame($expected, $opts);
  }
  
  // --------------------------------------------------------------------
  
  public function validLongoptArgumentProvider()
  {
    // $rules[0] returns the actual rule (long==)
    // $rules[1] returns as if Getopti\Parser::get_longopts was called
    $rules = function ($v, $r) {
      $rule = 'long';
      if($v) $rule .= "=";
      if($r) $rule .= "=";
      return array( array($rule), array('long' => array($v, $r)) );
    };
    
    $return = function ($v) {
      return array( array('long', $v) );
    };
    
    return array(
      array( array(),   array(array(), array()), array()), // returns empty
      array( array('--long'),           $rules(FALSE, FALSE), $return(NULL) ),
      array( array('--long=value'),     $rules(TRUE, FALSE),  $return('value') ),
      array( array('--long', 'value'),  $rules(TRUE, FALSE),  $return('value') ),
      array( array('--long', 'value'),  $rules(TRUE, TRUE),   $return('value') )
    );
  }
  
  /**
   * @test
   * @dataProvider validLongoptArgumentProvider
   * 
   * @covers  Getopti\Parser::get_longopts
   * 
   * @author  bschaeffer
   */
  public function setsUpLongoptsCorrectly($args, $rules)
  {
    $results = Getopti\Parser::get_longopts($rules[0]);
    $this->assertSame($rules[1], $results);
  }
    
  /**
   * @test
   * @dataProvider validLongoptArgumentProvider
   * 
   * @covers  Getopti\Parser::parse
   * @covers  Getopti\Parser::_parse_longopt
   * 
   * @author  bschaeffer
   */
  public function parsesValidLongArgumentsCorrectly($args, $rules, $expected)
  {
    list($opts, $nonopts) = Getopti\Parser::parse($args, '', $rules[0]);
    $this->assertSame($expected, $opts);
  }
  
  // --------------------------------------------------------------------
  
  public function consecutiveShortoptProvider()
  {
    return array(
      array(
        array('-ab'),
        array(array('a', NULL), array('b', NULL))
      ),
      array(
        array('-ab', 'value'),
        array(array('a', NULL), array('b', 'value'))
      ),
      array(
        array('-ba', 'value'),
        array(array('b', NULL), array('a', 'value'))
      )
    );  
  }
  
  /**
   * @test
   * @dataProvider consecutiveShortoptProvider
   * 
   * @covers  Getopti\Parser::parse
   * @covers  Getopti\Parser::_parse_shortopt
   * 
   * @author  bschaeffer
   */
  public function parsesConsecutiveShortoptsCorrectly($args, $expected)
  {
    list($opts, $nonopts) = Getopti\Parser::parse($args, 'a:b:', array());
    $this->assertSame($expected, $opts);
  }
  
  // --------------------------------------------------------------------
  
  public function commandWithBreakProvider()
  {
    return array(
      array(
        array('-a', '--', '--long'),
        array('a', array('long')),
        array(array(array('a', NULL)), array('--long'))
      ),
      array(
        array('--long', '--', 'nonopt', '-a'),
        array('a', array('long=')),
        array(array(array('long', NULL)), array('nonopt', '-a'))
      ),
    );
  }
  
  /**
   * @test
   * @dataProvider  commandWithBreakProvider
   * 
   * @covers  Getopti\Parser::parse
   * @covers  Getopti\Parser::is_option
   * 
   * @author  bschaeffer
   */
  public function stopsParsingOptionsAtBreak($args, $rules, $expected)
  {
    list($opts, $nonopts) = Getopti\Parser::parse($args, $rules[0], $rules[1]);
    $this->assertSame($expected[0], $opts);
    $this->assertSame($expected[1], $nonopts);
  }
  
  // --------------------------------------------------------------------
  
  public function illegalOptionProvider()
  {
    return array(
      array( array('-a'),                   array('b', array()) ),
      array( array('-a', '-b'),             array('b', array()) ),
      array( array('-ab'),                  array('a', array()) ),
      array( array('-ab'),                  array('b', array()) ),
      array( array('--illegal'),            array('', array('long')) ),
      array( array('--long', '--illegal'),  array('', array('long')) ),
    );
  }
  
  /**
   * @test
   * @dataProvider  illegalOptionProvider
   * 
   * @expectedException Getopti\Exception
   * 
   * @covers  Getopti\Parser::parse
   * @covers  Getopti\Parser::_parse_shortopt
   * @covers  Getopti\Parser::_parse_longopt
   * 
   * @author  bschaeffer
   */
  public function illegalOptionRaisesException($args, $opts)
  {
    Getopti\Parser::parse($args, $opts[0], $opts[1]);
  }
   
  // --------------------------------------------------------------------

  public function optionMissingParameterProvider()
  {
    return array(
      array( array('-a'),                array('a::', array()) ),
      array( array('-a', '-b'),          array('a::b', array()) ),
      array( array('-ab'),               array('a::b', array()) ),
      array( array('-ab'),               array('ab::', array()) ),
      array( array('--long'),            array('', array('long==', 'other')) ),
      array( array('--long', '--other'), array('', array('long==', 'other')) ),
    );
  }

  /**
   * @test
   * @dataProvider  optionMissingParameterProvider
   * 
   * @expectedException Getopti\Exception
   *
   * @covers  Getopti\Parser::parse
   * @covers  Getopti\Parser::_parse_shortopt
   * @covers  Getopti\Parser::_parse_longopt
   * 
   * @author  bschaeffer
   */
  public function optionMissingParameterRaisesException($args, $opts)
  {
    Getopti\Parser::parse($args, $opts[0], $opts[1]);
  }
  
  // --------------------------------------------------------------------
  
  public function optionAsPossibleValueProvider()
  { 
    return array(
      array(
        array('-a', '-b'),
        array( array('a', NULL), array('b', NULL) )
      ),
      array(
        array('-a', '--long'),
        array( array('a', NULL), array('long', NULL) )
      ),
      array(
        array('-a', '--'),
        array( array('a', NULL) )
      ),
      array(
        array('--long', '-a'),
        array( array('long', NULL), array('a', NULL) )
      ),
      array(
        array('--long', '--other'),
        array( array('long', NULL), array('other', NULL) )
      ),
      array(
        array('--long', '--'),
        array( array('long', NULL) )
      ),
    );
  }
  
  /**
   * @test
   * @dataProvider  optionAsPossibleValueProvider
   * 
   * @covers  Getopti\Parser::parse
   * @covers  Getopti\Parser::_parse_shortopt
   * @covers  Getopti\Parser::_parse_longopt
   * 
   * @author  bschaeffer
   */
  public function doesNotSetOptionsAsValues($args, $expected)
  {
    list($opts, $nonopts) = Getopti\Parser::parse($args, 'a:b:', array('long=', 'other='));
    $this->assertSame($expected, $opts);
  }
  
  // --------------------------------------------------------------------
  
  public function mixedArgumentProvider()
  {
    return array(
      array(
        array('-a', '--long'),      // arguments
        array('a', array('long')),  // rules
        array(                      // option results
          array('a', NULL),
          array('long', NULL)
        ),
        array()                     // nonopt results
      ),
      array(
        array('--long', 'value', '-a', 'othervalue'),
        array('a', array('long')),
        array(
          array('long', NULL),
          array('a', NULL)
        ),
        array('value', 'othervalue')
      ),
      array(
        array('--long', 'value', '-a', 'othervalue'),
        array('a:', array('long=')),
        array(
          array('long', 'value'),
          array('a', 'othervalue'),
        ),
        array()
      ),
    );
  }
  
  /**
   * @test
   * @dataProvider  mixedArgumentProvider
   * 
   * @covers  Getopti\Parser::parse
   * @covers  Getopti\Parser::_parse_shortopt
   * @covers  Getopti\Parser::_parse_longopt
   * 
   * @author  bschaeffer
   */
  public function parsesMixedArgumentsCorrectly($args, $rules, $expected)
  {
    list($opts, $nonopts) = Getopti\Parser::parse($args, $rules[0], $rules[1]);
    $this->assertSame($expected, $opts);
  }
  
  /**
   * @test
   * @dataProvider  mixedArgumentProvider
   * 
   * @covers  Getopti\Parser::parse
   * @covers  Getopti\Parser::_parse_shortopt
   * @covers  Getopti\Parser::_parse_longopt
   * 
   * @author  bschaeffer
   */
  public function returnsCorrectNonoptions($args, $rules, $ignore, $expected)
  {
    list($opts, $nonopts) = Getopti\Parser::parse($args, $rules[0], $rules[1]);
    $this->assertSame($expected, $nonopts);
  }
}

/* End of file ParserTest.php */
/* Location: ./test/Getopti/ParserTest.php */