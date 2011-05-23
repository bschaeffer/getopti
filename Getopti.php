<?php
/**
 * Getopti
 *
 * Getopti is a command-line parsing tool for PHP
 *
 * @package   Getopti
 * @author    Braden Schaeffer <hello@manasto.info>
 * @version   0.1.3
 * @link      http://github.com/bschaeffer/getopti
 *
 * @copyright Copyright (c) 2011
 * @license   http://www.opensource.org/licenses/mit-license.html MIT
 *
 * @filesource
 */

// We are either being required from source
if(strpos('@php_bin@', '@php_bin') === 0)
{
  define('GETOPTI_BASEPATH', __DIR__.'/Getopti/');
}
// or a PEAR installation
else
{
  define('GETOPTI_BASEPATH', 'Getopti/Getopti/');
}

require GETOPTI_BASEPATH.'Parser.php';
require GETOPTI_BASEPATH.'Switcher.php';
require GETOPTI_BASEPATH.'Output.php';
require GETOPTI_BASEPATH.'Exception.php';

// --------------------------------------------------------------------

/**
 * Getopti
 * 
 * A base class for wrapping multiple class functionalities into one
 * object.
 * 
 * @package     Getopti     
 * @author      Braden Schaeffer <hello@manasto.info>
 */
class Getopti {
  
  const VERSION = '0.1.3';
    
  const OPTIONAL_SEP_LEFT   = "[";
  const OPTIONAL_SEP_RIGHT  = "]";
  
  const DEFAULT_COLUMNS = 75;
  
  public static $columns = 0;
  public static $command = 'cmd';
  public static $left_padding = 2;
  public static $right_padding = 2;
  public static $option_padding = 26;
  public static $verbose = FALSE;
  
  public $switcher = NULL;
  public $output =  NULL;
  
  public $results = array();
  public $options = array();
  
  // --------------------------------------------------------------------
  
  /**
   * Getopti's constructor method
   *
   * @access public
   * @return void
   */
  function __construct()
  {
    if(static::$columns == 0)
    {
      static::$columns = self::get_columns();
    }
    
    $this->switcher = new Getopti\Switcher();
    $this->output = new Getopti\Output();
  }
  
  /**
   * Outputs automated help information (alias to Getopti\Output::help).
   * 
   * @access  public
   * @return  void
   */
  public function __toString()
  {
    return $this->output->help();
  }
  
  /**
   * Add banner text to the automated help output. This can be a simple
   * section seperator or eleborate usage information.
   * 
   * @access  public
   * @param   string  the banner text
   * @return  void
   */
  public function banner($text)
  {
    $this->output->banner($text);
  }
  
  /**
   * Add command text to the automated help output. This is similar to
   * the output generated when adding an option, but only needs a command
   * and a description
   * 
   * @access  public
   * @param   string  the command
   * @param   string  the description
   * @return  void
   */
  public function command($command, $description = '')
  {
    $this->output->command($command, $description);
  }
  
  /**
   * Add usage information to the automated help output
   * 
   * @access  public
   * @param   string  the usage information
   * @return  void
   */
  public function usage($usage)
  {
    $this->output->usage($usage);
  }
  
  /**
   * Adds options to the switcher and registers them with the automated
   * help output.
   * 
   * @access  public
   * @param   mixed   string (single option), or array (single option or two options)
   * @param   string  the name for the parameter it accepts (i.e... "ITEM" or "[ITEM]")
   * @param   string  description of what the option does
   * @param   closure a callback function to be invoked when the option is specified
   * @return  void
   */
  public function on($opts, $parameter = NULL, $description = '', $callback = NULL)
  { 
    if( ! is_array($opts))
    {
      $opts = array($opts);
    }
       
    $short = $opts[0];
    $long = (isset($opts[1])) ? $opts[1] : FALSE;
    
    $this->switcher->add(array($short, $long), $parameter, $callback);
    $this->output->option(array($short, $long), $parameter, $description);
  }
  
  /**
   * Return the resulting output from automated help creation
   */
  public function help()
  {
    return $this->output->help();
  }
  
  /**
   * A warpper for Getopti\Switcher::parse. Requires that you pass your
   * own arguments
   * 
   * @access  public
   * @param   array   the arguments to parse
   * @param   array   whether or not to return a flat, organized array of results
   * @return  array   an array of options, depending on $flatten
   */
  public function parse(array $args, $flatten = FALSE)
  {
    $this->results    = $this->switcher->parse($args);
    $this->options    = $this->switcher->options;
    $this->nonopts    = $this->switcher->nonopts;
    $this->breakopts  = $this->switcher->breakopts;
    
    if($flatten)
    {
      return $this->options;
    }
    
    return $this->results;
  }
  
  /**
   * Read Args
   * 
   * @static
   * @access  public
   * @param   int    the number of arguments to trim off the top
   * @return  array  the arguments
   */
  public static function read_args($trim = 0)
  {
    global $argv;
    
    if( ! is_array($argv))
    {
      if( ! @is_array($_SERVER['argv']))
      {
        return array();
      }
      
      $args = $_SERVER['argv'];
    }
    else
    {
      $args = $argv;
    }
    
    if($trim > 0)
    {
      for($i = 0; $i <= $trim - 1; $i++)
      {
        unset($args[$i]);
      }
      
      $args = array_merge(array(), $args);
    }
        
    return $args;
  }
  
  /**
   * Calculate the column wrap using builtin terminal commands
   * 
   * @static
   * @access  public
   * @return  int     the number of columns to use for wrapping
   */
  public static function get_columns()
  {
    if(0 !== \Getopti::$columns)
    {
      return \Getopti::$columns;
    }
     
    if(php_sapi_name() === 'cli' && 'darwin' === strtolower(PHP_OS))
    {
      \Getopti::$columns = (int)exec('tput cols');
    }
    elseif(0 === \Getopti::$columns)
    {
      \Getopti::$columns = self::DEFAULT_COLUMNS;
    }
    
    return \Getopti::$columns;
  }
}

/* End of file Getopti.php */
/* Location: ./Getopti.php */