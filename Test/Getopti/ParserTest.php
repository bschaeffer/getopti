<?php

use Getopti\Parser;

class ParserTest extends PHPUnit_Framework_TestCase {
  
  public $l_none     = array(FALSE, FALSE);
  public $l_optional = array(TRUE, FALSE);
  public $l_required = array(TRUE, TRUE);
  
  /**
   * Parameter rule generation functions.
   */
  protected function none()
  {
    return $this->_generate_rule(func_get_args(), $this->l_none);
  }
  
  protected function optional()
  {
    return $this->_generate_rule(func_get_args(), $this->l_optional);
  }
  
  protected function required()
  {
    return $this->_generate_rule(func_get_args(), $this->l_required);
  }

  protected function _generate_rule($opts, $rule)
  {
    $rules = array();
    foreach ($opts as $opt) $rules[$opt] = $rule;
    return $rules;
  }
  
  // --------------------------------------------------------------------
  
  public function emptyArgumentProvider()
  {
    return array(
      array(''),
      array(0),
      array(0.0),
      array(NULL),
      array(FALSE),
      array(array())
    );
  }
  
  /**
   * @test
   * @covers  Getopti\Parser::parse
   * 
   * @dataProvider  emptyArgumentProvider
   */
  public function parse_is_liberal_with_empty_data($type)
  {
    try
    {
      $void = Parser::parse($type, $this->none('a'), $this->none('long'));
      $void = Parser::parse(array('-a'), $this->none('a'), $type);
      $void = Parser::parse(array('--long'), $type, $this->none('long'));
    }
    catch(Getopti\Exception $e)
    {
      $this->fail(
        'Setting an argument to any type of empty value should not raise a Getopt\Exception. ' .
        'Parser ERROR: '.$e->getMessage()
      );
      return;
    }
  }
  
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
   * @covers  Getopti\Parser::is_longopt
   * 
   * @dataProvider longoptProvider
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
   * @covers  Getopti\Parser::is_shortopt
   * 
   * @dataProvider shortoptProvider
   */
  public function is_shortopt($short, $validity)
  {
    $this->assertEquals($validity, Parser::is_shortopt($short));
  }
  
  // --------------------------------------------------------------------
  
  public function validShortoptArgumentProvider()
  {
    $rules = function ($v, $r) {
      return array('a' => array($v, $r));
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
   * @covers  Getopti\Parser::parse
   * @covers  Getopti\Parser::_parse_shortopt
   * 
   * @dataProvider validShortoptArgumentProvider
   */
  public function parses_valid_short_arguments_correctly($args, $rules, $expected)
  {
    list($opts, $nonopts) = Parser::parse($args, $rules, array());
    $this->assertSame($expected, $opts);
  }
  
  // --------------------------------------------------------------------
  
  public function validLongoptArgumentProvider()
  {    
    $rules = function ($v, $r) {
      return array('long' => array($v, $r));
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
   * @covers  Getopti\Parser::parse
   * @covers  Getopti\Parser::_parse_longopt
   * 
   * @dataProvider validLongoptArgumentProvider
   */
  public function parses_valid_longopts_correctly($args, $rules, $expected)
  {
    list($opts, $nonopts) = Parser::parse($args, array(), $rules);
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
   * @covers  Getopti\Parser::parse
   * @covers  Getopti\Parser::_parse_shortopt
   * 
   * @dataProvider consecutiveShortoptProvider
   */
  public function parses_consecutive_shortopts_correctly($args, $expected)
  {
    list($opts, $nonopts) = Parser::parse($args, $this->optional('a', 'b'), array());
    $this->assertSame($expected, $opts);
  }
  
  // --------------------------------------------------------------------
  
  public function commandWithBreakProvider()
  {
    return array(
      array(
        array('-a','nonoption', '--', '--long'),      // the arguments
        array($this->none('a'), $this->none('long')), // the param rules
        array(
          array(array('a', NULL)),  // option results
          array('nonoption'),       // nonopt results
          array('--long')           // breakopt results
        )
      ),
      array(
        array('--long', '--', 'breakopt', '-a'),
        array($this->none('a'), $this->optional('long')),
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
   * @covers  Getopti\Parser::parse
   * @covers  Getopti\Parser::is_option
   * 
   * @dataProvider commandWithBreakProvider
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
      array( array('-a'),                   array($this->none('b'), array()) ),
      array( array('-a', '-b'),             array($this->none('a'), array()) ),
      array( array('-ab'),                  array($this->none('b'), array()) ),
      array( array('-ab'),                  array($this->none('b'), array()) ),
      array( array('--illegal'),            array(array(), $this->none('long')) ),
      array( array('--long', '--illegal'),  array(array(), $this->none('long')) ),
    );
  }
  
  /**
   * @test
   * @covers  Getopti\Parser::parse
   * @covers  Getopti\Parser::_parse_shortopt
   * @covers  Getopti\Parser::_parse_longopt
   * 
   * @dataProvider illegalOptionProvider
   * @expectedException Getopti\Exception
   */
  public function illegal_option_raises_exception($args, $opts)
  {
    Parser::parse($args, $opts[0], $opts[1]);
  }
   
  // --------------------------------------------------------------------

  public function optionMissingParameterProvider()
  {
    return array(
      array( array('-a'),                 array($this->required('a'), array()) ),
      array( array('-a', '-b'),           array($this->required('a') + $this->none('b'), array()) ),
      array( array('-ab'),                array($this->required('a') + $this->none('b'), array()) ),
      array( array('-ab'),                array($this->none('a') + $this->required('b'), array()) ),
      array( array('--long'),             array(array(), $this->required('long')) ),
      array( array('--long='),            array(array(), $this->required('long')) ),
      array( array('--long', '--other'),  array(array(), $this->required('long') + $this->none('other')) ),
      array( array('--long=', '--other'), array(array(), $this->required('long') + $this->none('other')) ),
    );
  }

  /**
   * @test
   * @covers  Getopti\Parser::parse
   * @covers  Getopti\Parser::_parse_shortopt
   * @covers  Getopti\Parser::_parse_longopt
   * 
   * @dataProvider optionMissingParameterProvider
   */
  public function option_missing_parameter_raises_exception($args, $opts)
  {
    $this->setExpectedException('Getopti\\Exception');
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
   * @covers  Getopti\Parser::parse
   * @covers  Getopti\Parser::_parse_shortopt
   * @covers  Getopti\Parser::_parse_longopt
   * 
   * @dataProvider optionAsPossibleValueProvider
   */
  public function does_not_set_options_as_values($args, $expected)
  {    
    list($opts, $nonopts) = Parser::parse($args, $this->optional('a', 'b'), $this->optional('long', 'other'));
    $this->assertSame($expected, $opts);
  }
  
  // --------------------------------------------------------------------
  
  public function mixedArgumentProvider()
  {
    return array(
      array(
        array('-a', '--long'),      // arguments
        array($this->none('a'), $this->none('long')),  // rules
        array(                      // option results
          array('a', NULL),
          array('long', NULL)
        ),
        array()                     // nonopt results
      ),
      array(
        array('--long', 'value', '-a', 'othervalue'),
        array($this->none('a'), $this->none('long')),
        array(
          array('long', NULL),
          array('a', NULL)
        ),
        array('value', 'othervalue')
      ),
      array(
        array('--long', 'value', '-a', 'othervalue'),
        array($this->optional('a'), $this->optional('long')),
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
   * @covers  Getopti\Parser::parse
   * @covers  Getopti\Parser::_parse_shortopt
   * @covers  Getopti\Parser::_parse_longopt
   * 
   * @dataProvider  mixedArgumentProvider
   */
  public function parses_mixed_arguments_correctly($args, $rules, $expected)
  {
    list($opts, $nonopts) = Parser::parse($args, $rules[0], $rules[1]);
    $this->assertSame($expected, $opts);
  }
  
  /**
   * @test
   * @covers  Getopti\Parser::parse
   * @covers  Getopti\Parser::_parse_shortopt
   * @covers  Getopti\Parser::_parse_longopt
   * 
   * @dataProvider mixedArgumentProvider
   */
  public function returns_correct_nonoptions($args, $rules, $ignore, $expected)
  {
    list($opts, $nonopts) = Parser::parse($args, $rules[0], $rules[1]);
    $this->assertSame($expected, $nonopts);
  }
}

/* End of file ParserTest.php */
/* Location: ./test/Getopti/ParserTest.php */