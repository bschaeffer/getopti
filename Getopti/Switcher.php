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
namespace Getopti;

use Getopti\Parser;

/**
 * Getopti Switcher Class 
 *
 * Sets up command line flags for parsing.
 *
 * @package     Getopti
 */
class Switcher {
  
  /**
   * The default option value
   */
  const OPTION_DEFAULT = FALSE;
  
  /**
   * @access  private
   * @var     array   a cache of registered Getopti\Option objects
   */
  public $_opts_cache = array();
  
  /**
   * @access  private
   * @var     array   short options mapped to their long option counterparts
   */
  public $_short2long = array();
  
  /**
   * @access  private
   * @var     array   the defined short options
   */
  public $_shortopts = array();
  
  /**
   * @access  private
   * @var     array   the defined long options
   */
  public $_longopts = array();
  
  /**
   * @access  public
   * @var     array   an associative option/value array
   */
  public $options = array();
  
  /**
   * @access  public
   * @var     array   the results returned from Getopti\Parser
   */
  public $results = array();
  
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
   * Uses the passed Getopti\Option object to generate a set of rules
   * to be used by the Getopti\Parser.
   * 
   * @access  public
   * @param   Getopti\Option
   * @return  void
   */
  public function add(\Getopti\Option\Base $option)
  { 
    if ( ! empty($option->short))
    { 
      if (isset($this->_shortopts[$option->short]))
      {
        throw new \InvalidArgumentException(sprintf(
          "Options cannot be specified twice. '%s' already added.",
          $option->short
        ));
      }
      
      $this->_shortopts[$option->short] = $option->rule;
    }
    
    if ( ! empty($option->long))
    {
      if (isset($this->_longopts[$option->long]))
      {
        throw new \InvalidArgumentException(sprintf(
          "Options cannot be specified twice. '%s' already added.",
          $option->long
        ));
      }
      
      $this->_longopts[$option->long] = $option->rule;
    }
    
    if ( ! empty($option->short) && ! empty($option->long))
    {
      $this->_short2long[$option->short] = $option->long;
    }
    
    $this->options["$option"] = self::OPTION_DEFAULT;
    $this->_opts_cache["$option"] = $option;
  }
  
  /**
   * Parse the provided options. Get the results. Run the callbacks.
   * 
   * @access  public
   * @param   array   the array of arguments to parse
   * @return  void
   */
  public function parse(array $args)
  {
    $this->results    = Parser::parse($args, $this->_shortopts, $this->_longopts);
    $this->nonopts    = $this->results[1];
    $this->breakopts  = $this->results[2]; 
    
    foreach ($this->results[0] as $opt)
    {
      $this->_run_option($opt[0], $opt[1]);
    }
    
    return $this->results;
  }
  
  /**
   * Break down the specified options. Organize. Run callbacks. Go!
   * 
   * @access  public
   * @param   string  the option specified
   * @param   mixed   either NULL or the value of the item
   * @return  void
   */
  private function _run_option($opt, $value = NULL)
  {
    // If we have a short option and can covert it to a long,
    // let's do that
    
    $switch = $opt;
    
    if (strlen($opt) == 1 && isset($this->_short2long[$opt]))
    {
      $switch = $this->_short2long[$opt];
    }
    
    // Retrieve the associated cached Getopti\Option object
    $option = $this->_opts_cache[$switch];
    
    if (empty($value) && empty($option->parameter))
    {
      // If we have an empty value, it's not technically empty. It has
      // certainly been indicated by the user, so we set it TRUE and
      // move on.
      $this->options[$switch] = TRUE;
    }
    elseif ($option->multiple)
    {
      // If it's a multipe, push the value to the option array
      $this->options[$switch][] = (empty($value)) ? self::OPTION_DEFAULT : $value;
    }
    else
    {
      $this->options[$switch] = (empty($value)) ? self::OPTION_DEFAULT : $value; 
    }
    
    $option->run_callback($value);
  }
}

/* End of file Switcher.php */
/* Location: ./Getopti/Switcher.php */