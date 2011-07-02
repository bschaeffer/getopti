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
   * @param   Getopti\Option\Base
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
    
    $this->options[$option->reference] = self::OPTION_DEFAULT;
    $this->_opts_cache[$option->reference] = $option;
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
   * @access  protected
   * @param   string  the option specified
   * @param   mixed   the value of the item
   * @return  void
   */
  protected function _run_option($opt, $value = TRUE)
  {
    $switch = $opt;
    
    // If we have a short option and can covert it to a long, let's do that.
    if (strlen($opt) == 1 && isset($this->_short2long[$opt]))
    {
      $switch = $this->_short2long[$opt];
    }
    
    $option = $this->_opts_cache[$switch];
    
    $option->set_value($this, $value);
  }
  
  /**
   * Sets the option value to the given value.
   * 
   * @access  public
   * @param   Getopti\Option\Base
   * @param   the value
   * @return  void
   */
  public function set(\Getopti\Option\Base $option, $value)
  {
    $this->options[$option->reference] = $value;
    $option->run_callback($value);
  }
  
  /**
   * Adds the given value to the option value array.
   * 
   * @access  public
   * @param   Getopti\Option\Base
   * @param   the value
   * @return  void
   */
  public function push(\Getopti\Option\Base $option, $value)
  {
    $ref = $option->reference;
    
    if ( ! is_array($this->options[$ref]))
    {
      $this->options[$ref] = array();
    }
    
    $this->options[$ref][] = $value;
    $this->options[$ref] = array_unique($this->options[$ref]);
    
    $option->run_callback();
  }
}

/* End of file Switcher.php */
/* Location: ./Getopti/Switcher.php */