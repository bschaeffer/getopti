<?php

use Getopti\Option\Bool;

class BoolTest extends \PHPUnit_Framework_TestCase {
  
  /**
   * @test
   * @covers  Getopti\Option\Bool::__construct
   */
  public function sets_boolean_related_properties_correctly()
  {
    $bool = new Bool('a');
    $this->assertInstanceOf(
      'Getopti\\Option\\Base', $bool,
      'Getopti\\Option\\Boolean is not an instance of Getopti\\Option\\Base.'
    );
    
    $this->assertNotContains(
      TRUE, $bool->rule,
      "Bool option rules contain a TRUE value, indicating it accepts a parameter when it should not." 
    );
    
    $this->assertEmpty(
      $bool->parameter,
      "Bool->parameter is not empty."
    );
    
    $this->assertFalse(
      $bool->multiple,
      'Bool->multiple is not FALSE.'
    );
  }
  
  // --------------------------------------------------------------------
  
  public function nonBooleanTrueValuesProvider()
  {
    return array(
      array(0),
      array('0'),
      array(1.1),
      array(-1),
      array('1.1'),
      array('string'),
      array(''),
      array(FALSE),
      array(NULL),
    );
  }
  
  /**
   * @test
   * @covers  Getopti\Option\Bool::set_value
   * 
   * @dataProvider  nonBooleanTrueValuesProvider
   */
  public function sets_non_boolean_TRUE_values_to_FALSE($non_true_value)
  {
    $bool = new Bool('a');

    $switcher = $this->getMock('Getopti\\Switcher', array('set'));
    $switcher->expects($this->once())
             ->method('set')
             ->with(
               $this->equalTo($bool),
               $this->equalTo(FALSE)
             );

    $bool->set_value($switcher, $non_true_value);
  }
  
  // --------------------------------------------------------------------
  
  /**
   * @test
   * @covers  Getopti\Option\Bool::set_value
   */
  public function sets_boolean_TRUE_value_to_TRUE()
  {
    $bool = new Bool('a');

    $switcher = $this->getMock('Getopti\\Switcher', array('set'));
    $switcher->expects($this->once())
             ->method('set')
             ->with(
               $this->equalTo($bool),
               $this->equalTo(TRUE)
             );

    $bool->set_value($switcher, TRUE);
  }
}

/* End of file BoolTest.php */
/* Location: ./Test/Getopti/Option/BoolTest.php */