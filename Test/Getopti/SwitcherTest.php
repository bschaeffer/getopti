<?php

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
  public function add_requires_an_Option_object()
  {
    $this->setExpectedException('PHPUnit_Framework_Error');
    $this->switcher->add(new stdClass);
  }
}

/* End of file SwitcherTest.php */
/* Location: ./Test/Getopti/SwitcherTest.php */