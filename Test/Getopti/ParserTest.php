<?php

use Getopti\Parser;

class ParserTest extends PHPUnit_Framework_TestCase {
  
  // --------------------------------------------------------------------
  
  public function longoptProvider()
  {
    return array(
      array(
        '--a0',   // the longopt
        TRUE      // expected validity
      ),
      array('--a0=',    TRUE),
      array('--a-0',    TRUE),
      array('--a-0=',   TRUE),
      array('-a',       FALSE),
      array('-ab',      FALSE),
      array('--a',      FALSE),
      array('--a=',     FALSE),
      array('-',        FALSE),
      array('--',       FALSE),
      array('---',      FALSE),
      array('non-opt',  FALSE),
    );
  }
  
  /**
   * @test
   * @dataProvider longoptProvider
   * 
   * @covers  Getopti\Parser::is_longopt
   */
  public function is_longopt($long, $validity)
  {
    $this->assertEquals($validity, Parser::is_longopt($long));
  }
  
  // --------------------------------------------------------------------

  public function shortoptProvider()
  {
    return array(
      array(
        '-a',   // the shortopt
        TRUE,   // expected validity
      ),
      array('-0',       TRUE),
      array('-ab',      TRUE),
      array('-a1',      TRUE),
      array('-0b',      TRUE),
      array('-01',      TRUE),
      array('--a',      FALSE),
      array('--a=',     FALSE),
      array('-',        FALSE),
      array('--',       FALSE),
      array('---',      FALSE),
      array('non-opt',  FALSE),
    );
  }
  
  /**
   * @test
   * @dataProvider shortoptProvider
   * 
   * @covers  Getopti\Parser::is_shortopt
   */
  public function is_shortopt($short, $validity)
  {
    $this->assertEquals($validity, Parser::is_shortopt($short));
  }
  
  // --------------------------------------------------------------------
  
  public function validShortoptArgumentProvider()
  {
    // $rules[0] returns the actual rule (a::)
    // $rules[1] returns expected from Parser::get_shortopts
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
   */
  public function sets_up_shortopts_correctly($args, $rules)
  {
    $results = Parser::get_shortopts($rules[0]);
    $this->assertSame($rules[1], $results);
  }
    
  /**
   * @test
   * @dataProvider validShortoptArgumentProvider
   * 
   * @covers  Getopti\Parser::parse
   * @covers  Getopti\Parser::_parse_shortopt
   */
  public function parses_valid_short_arguments_correctly($args, $rules, $expected)
  {
    list($opts, $nonopts) = Parser::parse($args, $rules[0], array());
    $this->assertSame($expected, $opts);
  }
  
  // --------------------------------------------------------------------
  
  public function validLongoptArgumentProvider()
  {
    // $rules[0] returns the actual rule (long==)
    // $rules[1] returns as if Parser::get_longopts was called
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
   */
  public function sets_up_longopts_correctly($args, $rules)
  {
    $results = Parser::get_longopts($rules[0]);
    $this->assertSame($rules[1], $results);
  }
    
  /**
   * @test
   * @dataProvider validLongoptArgumentProvider
   * 
   * @covers  Getopti\Parser::parse
   * @covers  Getopti\Parser::_parse_longopt
   */
  public function parses_valid_longopts_correctly($args, $rules, $expected)
  {
    list($opts, $nonopts) = Parser::parse($args, '', $rules[0]);
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
   */
  public function parses_consecutive_shortopts_correctly($args, $expected)
  {
    list($opts, $nonopts) = Parser::parse($args, 'a:b:', array());
    $this->assertSame($expected, $opts);
  }
  
  // --------------------------------------------------------------------
  
  public function commandWithBreakProvider()
  {
    return array(
      array(
        array('-a','nonoption', '--', '--long'),
        array('a', array('long')),
        array(
          array(array('a', NULL)),  // option results
          array('nonoption'),       // nonopt results
          array('--long')           // breakopt results
        )
          
      ),
      array(
        array('--long', '--', 'breakopt', '-a'),
        array('a', array('long=')),
        array(
          array(array('long', NULL)),
          array(),
          array('breakopt', '-a')
        )
      ),
    );
  }
  
  /**
   * @test
   * @dataProvider  commandWithBreakProvider
   * 
   * @covers  Getopti\Parser::parse
   * @covers  Getopti\Parser::is_option
   */
  public function stops_parsing_options_at_break($args, $rules, $expected)
  {
    list($opts, $nons, $breaks) = Parser::parse($args, $rules[0], $rules[1]);
    $this->assertSame($expected[0], $opts);
    $this->assertSame($expected[1], $nons);
    $this->assertSame($expected[2], $breaks);
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
   */
  public function illegal_option_raises_exception($args, $opts)
  {
    Parser::parse($args, $opts[0], $opts[1]);
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
   */
  public function option_missing_parameter_raises_exception($args, $opts)
  {
    Parser::parse($args, $opts[0], $opts[1]);
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
   */
  public function does_not_set_options_as_values($args, $expected)
  {
    list($opts, $nonopts) = Parser::parse($args, 'a:b:', array('long=', 'other='));
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
   */
  public function parses_mixed_arguments_correctly($args, $rules, $expected)
  {
    list($opts, $nonopts) = Parser::parse($args, $rules[0], $rules[1]);
    $this->assertSame($expected, $opts);
  }
  
  /**
   * @test
   * @dataProvider  mixedArgumentProvider
   * 
   * @covers  Getopti\Parser::parse
   * @covers  Getopti\Parser::_parse_shortopt
   * @covers  Getopti\Parser::_parse_longopt
   */
  public function returns_correct_nonoptions($args, $rules, $ignore, $expected)
  {
    list($opts, $nonopts) = Parser::parse($args, $rules[0], $rules[1]);
    $this->assertSame($expected, $nonopts);
  }
}

/* End of file ParserTest.php */
/* Location: ./test/Getopti/ParserTest.php */