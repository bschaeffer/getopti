<?php

class GetoptiTest extends PHPUnit_Framework_TestCase {
  
  /**
   * Setup method for each test
   */
  public function setUp()
  {
    $this->opts = new Getopti();
  }
  
  /**
   * @test
   *
   * @covers  Getopti::__toString
   * 
   * @author  Braden Schaeffer
   */
  public function getopti_obj_to_string_outputs_usage_information()
  {
    $this->opts->banner("test banner");
    $this->assertEquals((string)$this->opts, $this->opts->help());
  }
}

/* End of file GetoptiTest.php */
/* Location: ./test/GetoptiTest.php */