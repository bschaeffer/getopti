<?php

/**
 * Getopti
 *
 * Getopti is a command-line parsing tool for PHP
 *
 * @package   Getopti
 * @author    Braden Schaeffer <hello@manasto.info>
 * @version   0.1.0
 * @link      http://github.com/bschaeffer/getopti
 *
 * @copyright Copyright (c) 2011
 * @license   http://www.opensource.org/licenses/mit-license.html MIT
 *
 * @filesource
 */

// It is either a PEAR installation instance...
if(strpos('@php_bin@', '@php_bin') === 0)
{
  define('GETOPTI_BASEPATH', __DIR__.'/Getopti/');
}
// ... or it isn't
else
{
  define('GETOPTI_BASEPATH', 'Getopti/Getopti/');
}

require GETOPTI_BASEPATH.'Base.php';
require GETOPTI_BASEPATH.'Parser.php';
require GETOPTI_BASEPATH.'Switcher.php';
require GETOPTI_BASEPATH.'Output.php';

// --------------------------------------------------------------------

class Getopti extends Getopti\Base {
  
  const VERSION = '0.1.0';
    
  const OPTIONAL_SEP_LEFT   = "[";
  const OPTIONAL_SEP_RIGHT  = "]";
  
  public static $columns = 0;
  public static $command = 'cmd';
  public static $padding = 2;
  public static $verbose = FALSE;
  
  public $switcher = NULL;
  public $output =  NULL;
  
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
    
    $this->switcher->add(array($short, $long), $parameter, $description, $callback);
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
   * @return  array   multi-dimensional array, ($opts, $nonopts)
   */
  public function parse(array $args, $flatten = FALSE)
  {
    return $this->switcher->parse($args, $flatten);
  }
  
  /**
   * Returns the flattened array of options from the results of parsing the
   * command-line arguments.
   * 
   * @access  public
   * @return  array   the flattened, parsed options
   */
  public function flat_opts()
  {
    return $this->switcher->options;
  }
  
  /**
   * Retrieve non-options from the parsed arguments
   * 
   * @access  public
   * @return  array   the non-options from the parsed arguments
   */
  public function nonopts()
  {
    return $this->switcher->nonopts;
  }
}

/* End of file Getopti.php */
/* Location: ./Getopti.php */