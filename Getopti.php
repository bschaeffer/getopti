<?php
/**
 * Getopti
 *
 * Getopti is a command-line parsing tool for PHP
 *
 * @package   Getopti
 * @author    Braden Schaeffer
 * @link      http://github.com/bschaeffer/getopti
 * @license   http://www.opensource.org/licenses/mit-license.html MIT
 */

if(strpos('@php_bin@', '@php_bin') === 0)
{
  set_include_path(dirname(__FILE__) . PATH_SEPARATOR . get_include_path());
}

require 'Getopti/Autoload.php';
Getopti\Autoload::register();

// --------------------------------------------------------------------

/**
 * Getopti
 * 
 * A base class for wrapping multiple class functionalities into one
 * object.
 * 
 * @package     Getopti
 */
class Getopti {
  
  /**
   * Getopti Version
   */
  const VERSION = '0.1.4';
    
  /**
   * Separator matchers for optional argument parameters
   */
  const OPTIONAL_SEP_LEFT   = "[";
  const OPTIONAL_SEP_RIGHT  = "]";
  
  /**
   * The default output width
   */
  const DEFAULT_COLUMNS = 75;
  
  /**
   * @access  public
   * @var     int     the overall output width
   */
  public static $columns = 0;
  
  /**
   * @access  public
   * @var     int     the left side white-space padding of the output
   */
  public static $left_padding = 2;
  
  /**
   * @access  public
   * @var     int     the right side white-space padding of the output
   */
  public static $right_padding = 2;
  
  /**
   * @access  public
   * @var     int     the white-space padding between an option/command and it's description
   */
  public static $option_padding = 26;
  
  /**
   * @access  public
   * @var     Getopti\Switcher
   */
  public $switcher = NULL;
  
  /**
   * @access  public
   * @var     Getopti\Output
   */
  public $output =  NULL;
  
  /**
   * @access  public
   * @var     array   the results from option parsing using Getopti\Parser
   */
  public $results = array();
  
  /**
   * @access  public
   * @var     array   the flattened, smartly index options organized by the Getopti\Switcher object
   */
  public $options = array();
  
  /**
   * @access  public
   * @var     array   all arguments unable to be matched as options
   */
  public $nonopts = array();
  
  /**
   * @access  public
   * @var     array   all values coming after a '--' argument
   */
  public $breakopts = array();
  
  // --------------------------------------------------------------------
  
  /**
   * Getopti's constructor method
   *
   * @access public
   * @return void
   */
  public function __construct()
  {
    if (static::$columns == 0)
    {
      static::$columns = \Getopti\Utils::get_columns();
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
   * Using with passed parameters:
   * 
   * <code>
   * $opts->on($opts, $parameter, $description, $callback);
   * </code>
   * 
   * Using with a Getopti\Option object:
   * 
   * <code>
   * $option = Getopti\Option::build($opts, $parameter, $callback);
   * $opts->on($option, $description);
   * </code>
   * 
   * @access  public
   * @param   mixed   string (single option), array (multiple), or a Getopt\Option\Base object
   * @param   mixed   the parameter string or description if the first param is a Getopti\Option\Base object
   * @param   string  description of what the option does
   * @param   closure a callback function to be invoked when the option is specified
   * @return  void
   */
  public function on($opts, $parameter = NULL, $description = '', $callback = NULL)
  {
    $option = $opts;
    
    if ( ! $option instanceof Getopti\Option\Base)
    {
      $option = Getopti\Option::build($opts, $parameter, $callback);
    }
    else
    {
      $description = $parameter;
    }
    
    $this->switcher->add($option);
    $this->output->option(
      array($option->short, $option->long),
      $option->parameter,
      $description
    );
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
    
    return ($flatten) ? $this->options : $this->results;
  }
}

/* End of file Getopti.php */
/* Location: ./Getopti.php */