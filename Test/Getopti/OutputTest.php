<?php

class OutputTest extends PHPUnit_Framework_TestCase 
{
  /**
   * @test
   * 
   * @covers  Getopti\Output::__construct
   * 
   * @author  bschaeffer
   */
  public function setsStaticOuputVariableToEmptyStringOnNew()
  {
    Getopti\Output::$output = "non empty string";
    $out = new Getopti\Output();
    $this->assertSame('', $out::$output);
  }
  
  // --------------------------------------------------------------------
   
  public function optionProvider()
  {
    return array(
      array(
        array(array('h'), '', 'show help'),
        str_pad(' -h', 26, " ").'show help'.PHP_EOL
      ),
      array(
        array(array('f'), 'FILE', 'show file'),
        str_pad(' -f FILE', 26, " ").'show file'.PHP_EOL
      ),
      array(
        array(array('h', 'help'), '', 'show help'),
        str_pad(' -h, --help', 26, " ").'show help'.PHP_EOL
      ),
      array(
        array(array('f', 'file'), 'FILE', 'show file'),
        str_pad(' -f, --file FILE', 26, " ").'show file'.PHP_EOL
      ),
    );
  }
  
  /**
   * @test
   * @dataProvider  optionProvider
   * 
   * @covers  Getopti\Output::option
   * 
   * @author  bschaeffer
   */
  public function addsOptionsCorrectly($opts, $expected)
  {
    $out = new Getopti\Output();
    $out->option($opts[0], $opts[1], $opts[2]);
    $this->assertSame($expected, $out->help());
  }
  
  // --------------------------------------------------------------------
  
  public function bannerProvider()
  {
    return array(
      array("global options:"),
      array("command options:"),
      array("this application is way cool!!")
    );
  }
  
  /**
   * @test
   * @dataProvider  bannerProvider
   * 
   * @covers  Getopti\Output::banner
   * 
   * @author  bschaeffer
   */
  public function addsBannersCorrectly($banner)
  {
    $out = new Getopti\Output();
    $out->banner($banner);
    $this->assertEquals(PHP_EOL.$banner.PHP_EOL.PHP_EOL, $out->help());
  }
}

/* End of file OutputTest.php */
/* Location: ./Test/Getopti/OutputTest.php */