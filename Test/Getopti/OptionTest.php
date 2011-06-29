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
}

/* End of file OptionTest.php */
/* Location: ./Test/Getopti/OptionTest.php */
