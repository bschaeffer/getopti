<?php

class ExceptionTest extends PHPUnit_Framework_TestCase 
{
  /**
   * @test
   *
   * @covers  Getopti\Exception
   * 
   * @author  Braden Schaeffer
   */
  public function exception_class_exists()
  {
    $this->assertTrue(class_exists('Getopti\\Exception'));
    $this->assertInstanceOf('Exception', new Getopti\Exception);
  }
}

/* End of file ExceptionTest.php */
/* Location: ./Test/Getopti/ExceptionTest.php */