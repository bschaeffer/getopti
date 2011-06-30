<?php

use Getopti\Option;

class OptionTest extends PHPUnit_Framework_TestCase {
  
  /**
   * @test
   * @covers  Getopti\Option::build
   */
  public function build()
  {
    $this->assertInstanceOf('Getopti\\Option\\Base', Option::build('a'));
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

  public function optionTypeProvider()
  {
    return array(
      array(Option::TYPE_DEFAULT, 'Getopti\\Option\\Base'),
      array(OPTION::TYPE_BOOL,    'Getopti\\Option\\Bool')
    );
  }

  /**
   * @test
   * @covers  Getopti\Option::build
   *
   * @dataProvider  optionTypeProvider
   */
  public function build_returns_correct_option_type($type, $expected_class)
  {
    $option = Option::build('a', array('PARAM', $type));
    $this->assertInstanceOf($expected_class, $option);
  }

  /**
   * @test
   * @covers  Getopti\Option::build
   */
  public function build_throws_exception_for_invalid_option_type()
  {
    $this->setExpectedException('InvalidArgumentException');
    $option = Option::build('a', array(NULL, 'invalid_type'));
  }

  // --------------------------------------------------------------------

  public function optsProvider()
  {
    return array(
      array(
        'a',                  // opts argument
        'a',                  // expected short opt
        NULL,                 // expected long opt
      ),
      array('long',               NULL, 'long'),
      array(array('a'),           'a', NULL),
      array(array('long'),        NULL, 'long'),
      array(array('a', NULL),     'a', NULL),
      array(array(NULL, 'long'),  NULL, 'long'),
      array(array('a', 'long'),   'a', 'long'),

      // We don't throw errors here, so empty options should work
      array(NULL,                 NULL, NULL),
      array(array(NULL),          NULL, NULL),
      array(array(NULL, NULL),    NULL, NULL)
    );
  }

  /**
   * @test
   * @covers  Getopti\Option::parse_opts
   *
   * @dataProvider  optsProvider
   */
  public function parses_opts_correctly($opts, $expected_short, $expected_long)
  {
    $results = Option::parse_opts($opts);

    $this->assertEquals(
      $expected_short, $results[0],
      'Option::parse_opts did not extract the SHORT option correctly.'
    );

    $this->assertEquals(
      $expected_long, $results[1],
      'Option::parse_opts did not extract the LONG option correctly.'
    );
  }
  
  /**
   * @test
   * @covers  Getopti\Option::parse_opts
   */
  public function parse_opts_returns_correct_number_of_results()
  {
    $results = Option::parse_opts('a');
    
    $this->assertEquals(
      2, count($results),
      'Option::parse_opts did not return exactly two (2) results.'
    );
  }  

  // --------------------------------------------------------------------

  public function paramProvider()
  {
    return array(
      array(
        'PARAM',              // param argument
        'PARAM',              // expected pared string
        Option::TYPE_DEFAULT, // expected parsed type
      ),
      array(array('PARAM'), 'PARAM', Option::TYPE_DEFAULT)
    );
  }

  /**
   * @test
   * @covers  Getopti\Option::parse_params
   *
   * @dataProvider  paramProvider
   */
  public function parses_params_correctly($params, $expected_string, $expected_type)
  {
    $results = Option::parse_params($params);

    $this->assertEquals(
      $expected_string, $results[0],
      'Option::parse_param did not extract the parameter STRING correctly.'
    );

    $this->assertEquals(
      $expected_type, $results[1],
      'Option::parse_param did not extract the parameter TYPE correctly.'
    );
  }

  /**
   * @test
   * @covers  Getopti\Option::parse_params
   */
  public function parse_params_returns_correct_number_of_results()
  {
    $results = Option::parse_params('PARAM');
    
    $this->assertEquals(
      2, count($results),
      'Option::parse_param did not return exactly two (2) results.'
    );
  }
}

/* End of file OptionTest.php */
/* Location: ./Test/Getopti/OptionTest.php */
