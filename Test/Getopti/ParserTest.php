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
    }
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
  
  public function validShortoptArgumentProvider()
  {    
    return array(
      array(array('-a'),           Parser::OPTION_DEFAULT),
      array(array('-a', 'value'),  'value'),
      array(array('-a', 'value'),  'value')
    );
  }
    
  /**
   * @test
   * @covers  Getopti\Parser::parse
   * @covers  Getopti\Parser::_parse_shortopt
   * 
   * @dataProvider validShortoptArgumentProvider
   */
  public function parses_valid_short_arguments_correctly($args, $expected)
  {
    list($opts, $nonopts) = Parser::parse($args, $this->optional('a'), array());
    $this->assertSame($expected, $opts[0][1]);
  }
  
  // --------------------------------------------------------------------
  
  public function validLongoptArgumentProvider()
  {
    return array(
      array(array('--long'),           Parser::OPTION_DEFAULT),
      array(array('--long=value'),     'value'),
      array(array('--long', 'value'),  'value'),
      array(array('--long', 'value'),  'value')
    );
  }
    
  /**
   * @test
   * @covers  Getopti\Parser::parse
   * @covers  Getopti\Parser::_parse_longopt
   * 
   * @dataProvider validLongoptArgumentProvider
   */
  public function parses_valid_longopts_correctly($args, $expected)
  {
    list($opts, $nonopts) = Parser::parse($args, array(), $this->optional('long'));
    $this->assertSame($expected, $opts[0][1]);
  }
  
  // --------------------------------------------------------------------
  
  public function consecutiveShortoptProvider()
  { 
    return array(
      array(array('-ab'),           Parser::OPTION_DEFAULT),
      array(array('-ab', 'value'),  'value'),
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
    
    $this->assertEquals(
      Parser::OPTION_DEFAULT, $opts[0][1],
      'Non-final option in a consecutive short option string was not set to the default correctly.'
    );
    
    $this->assertSame(
      $expected, $opts[1][1],
      "Final option in a consecutive short option string did not have it's value set correctly."
    );
  }

  // --------------------------------------------------------------------

  /**
   * @test
   * @covers  Getopti\Parser::parse
   */
  public function sets_non_opts_correctly()
  {
    $args = array('-a', 'a-value', 'non-one', '--long', 'non-two');
    list($opts, $nonopts) = Parser::parse($args, $this->optional('a'), $this->none('long'));

    $this->assertNotContains(
      'a-value', $nonopts,
      'Parser extracted the "-a" option value "a-value" as a non-option.'
    );

    $this->assertContains(
      'non-one', $nonopts,
      'Parser failed to extract "non-one" non-option.'
    );

    $this->assertContains(
      'non-two', $nonopts,
      'Parser failed to extract "non-two" non-option.'
    );
  }
  

  // --------------------------------------------------------------------
  
  /**
   * @test
   * @covers  Getopti\Parser::parse
   * @covers  Getopti\Parser::is_option
   */
  public function stops_parsing_options_at_break()
  {
    $args = array('-a', 'non-option', '--', 'break-option');
    
    list($opts, $nonopts, $breakopts) = Parser::parse($args, $this->none('a'), array());
    
    $this->assertNotContains(
      'break-option', $nonopts,
      'Parser should not recognize an argument after a break (--) as a non-option.'
    );
    
    $this->assertContains(
      'break-option', $breakopts,
      'Parser did recognize an argument after a break (--) as a break-option.'
    );
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
   */
  public function illegal_option_raises_exception($args, $opts)
  {
    $this->setExpectedException('Getopti\\Exception');
    Parser::parse($args, $opts[0], $opts[1]);
  }
   
  // --------------------------------------------------------------------

  public function optionMissingParameterProvider()
  {
    return array(
      array(array('-a'),       $this->required('a'), array()),
      array(array('--long'),   array(),  $this->required('long')),
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
  public function option_missing_parameter_raises_exception($args, $short, $long)
  {
    $this->setExpectedException('Getopti\\Exception');
    Parser::parse($args, $short, $long);
  }
  
  // --------------------------------------------------------------------
  
  public function optionAsPossibleValueProvider()
  { 
    $opts = array('-a', '--long');
    $others = array('-b', '--other', '--');
    
    $return = array();
    
    foreach($opts as $opt)
    {
      foreach($others as $other)
      {
        $return[] = array($opt, $other);
      }
    }
    
    return $return;
  }
  
  /**
   * @test
   * @covers  Getopti\Parser::parse
   * @covers  Getopti\Parser::_parse_shortopt
   * @covers  Getopti\Parser::_parse_longopt
   * @covers  Getopti\Parser::is_option
   * 
   * @dataProvider optionAsPossibleValueProvider
   */
  public function does_not_set_options_as_values($arg_one, $arg_two)
  {
    $short = $this->optional('a', 'b');
    $long = $this->optional('long', 'other');
    
    list($opts, $nonopts) = Parser::parse(array($arg_one, $arg_two), $short, $long);
    
    $this->assertNotEquals(
      $arg_two, $opts[0][1],
      'Parser should not set one option as the value of another option.'
    );
  }
}

/* End of file ParserTest.php */
/* Location: ./test/Getopti/ParserTest.php */