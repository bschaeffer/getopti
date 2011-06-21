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
   * Requirement level indicator for short options.
   */
  const INDICATOR_SHORT = ':';
  
  /**
   * Requirement level indicator for short options.
   */
  const INDICATOR_LONG  = "=";
  
  /**
   * The default value for an option (used is none was specified).
   */
  const OPTION_DEFAULT = FALSE;
  
  /**
   * Integer requirement levels.
   */
  const LEVEL_NONE      = 0;
  const LEVEL_OPTIONAL  = 1;
  const LEVEL_REQUIRED  = 2;
  
  /**
   * @access  private
   * @var     array   short options mapped to their long option counterparts
   */
  public $_short2long = array();
  
  /**
   * @access  private
   * @var     array   the defined short options
   */
  public $_shortopts = '';
  
  /**
   * @access  private
   * @var     array   the defined long options
   */
  public $_longopts = array();
  
  /**
   * @access  private
   * @var     array   the defined callbacks (mapped to long first, then short if long is missing)
   */
  public $_callbacks = array();
  
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
   * Adds a set of options to the builder, setting up callbacks and
   * automated help output along the way.
   *
   * @access  public
   * @param   array   the short and long options to watch
   * @param   mixed   the parameter string (i.e. ITEM or [ITEM]) or array with optional default
   * @param   closure optional callback for the option
   * @return  void
   */
  public function add(array $opts, $parameter = NULL, $callback = NULL)
  {
    if ( ! is_array($parameter))
    {
      $parameter = array($parameter, self::OPTION_DEFAULT);
    }
    
    list($short, $long) = $this->_parse_opts($opts, $parameter[1]);
    
    $level = $this->_parse_requirement_level($parameter[0]);
    
    if ( ! empty($short))
    {
      $this->_shortopts .= $short.str_repeat(self::INDICATOR_SHORT, $level);
      
      if (is_callable($callback))
      {
        $this->_callbacks[$short] = $callback;
      }
    }
    
    if ( ! empty($long))
    {
      $this->_longopts[] = $long.str_repeat(self::INDICATOR_LONG, $level);
      
      if (is_callable($callback))
      {
        $this->_callbacks[$long] = $callback;
      }
    }
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
   * This functions simply makes sure that there is a value for both the
   * short and long option before we setup how to parse them.
   * 
   * It also set's up a one-to-one relationship between a short and a
   * long option.
   * 
   * @access  private
   * @param   array   the options ('short', 'long')
   * @return  array   the short, then long options
   */
  private function _parse_opts($opts, $default = FALSE)
  {
    $short = (empty($opts[0])) ? FALSE : $opts[0];
    $long = FALSE;

    if (strlen($short) > 1)
    {
      // $short is actually $long
      
      // Default to FALSE here so we don't have to worry later
      // about tracking it down and make it so if it's not specified
      
      $this->options[$short] = $default;
      
      // .. and we're done
      return array(NULL, $short);
    }
    
    if (isset($opts[1]) && ! empty($opts[1]))
    {
      $long = $opts[1];
      
      $this->_short2long[$short] = $long;
      
      // see note above about defaulting to FALSE
      $this->options[$long] = $default;
    }
    else
    {
      $this->options[$short] = $default;
    }
    
    return array($short, $long);
  }
  
  /**
   * Parse the passed parameter
   *
   * @access  private
   * @param   mixed   the parameter or requirement level
   * @return  int     padding on the rule string
   */
  private function _parse_requirement_level($parameter = '')
  { 
    // If it's an integer, it's already a level, supposedly
    if (is_int($parameter))
    {
      switch ($parameter)
      {
        case self::LEVEL_NONE:      return self::LEVEL_NONE;
        case self::LEVEL_OPTIONAL:  return self::LEVEL_OPTIONAL;
        case self::LEVEL_REQUIRED:  return self::LEVEL_REQUIRED;
        default:
          throw new \InvalidArgumentException("'$parameter' is not a valid parameter requirement level");
          break;
      }
    }
      
    if (empty($parameter))
    {
      // It doesn't have params
      return self::LEVEL_NONE; 
    }
    elseif (preg_match("/^\[([a-z0-9\-_]+)\]\+?$/i", $parameter))
    {
      // It's optional
      return self::LEVEL_OPTIONAL;
    }
  
    // It's required (will raise an Getopti\Exception if not missing)
    return self::LEVEL_REQUIRED;
  }
  
  /**
   * Break down the specified options. Organize. Run callbacks. Go!
   * 
   * @access  public
   * @param   string  the option specified
   * @param   mixed   either NULL or the value of the item
   * @return  void
   */
  private function _run_option($option, $value = NULL)
  {
    // If we have a short option and can covert it to a long,
    // let's do that
    
    if (strlen($option) == 1 && isset($this->_short2long[$option]))
    {
      $option = $this->_short2long[$option];
    }
    
    // If we have an empty value, it's not technically empty. It has
    // certainly been indicated by the user, so we set it TRUE and
    // move on.
    
    $value = (empty($value)) ? TRUE : $value;
    
    if ($value === TRUE)
    {
      // If it accepts no paramters, it's just TRUE
      $this->options[$option] = $value;
    }
    else
    {
      // If it accepts parameters, add the value to the array
      $this->options[$option][] = $value;
    }
    
    if (isset($this->_callbacks[$option]))
    {
      call_user_func_array($this->_callbacks[$option], array($value));
    }
  }
}

/* End of file Switcher.php */
/* Location: ./Getopti/Switcher.php */