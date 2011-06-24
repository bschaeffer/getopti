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

/**
 * Getopti Option
 *
 * @package     Getopti
 */
class Option {
  
  /**
   * The default option value.
   */
  const OPTION_DEFAULT = NULL;
  
  /**
   * The string indicator for specifying an argument can be specified multiple times.
   */
  const MULTIPLE_INDICATOR = '[+]';
  
  /**
   * The multpile indicator matcher.
   */
  const MULTIPLE_MATCHER = "/\[\+\]$/";
  
  /**
   * @access  public
   * @var     string  the short option flag
   */
  public $short = NULL;
  
  /**
   * @access  public
   * @var     string  the long option flag
   */
  public $long = NULL;
  
  /**
   * @access  public
   * @var     string  the parameter string
   */
  public $parameter = NULL;
  
  /**
   * @access  public
   * @var     bool    whether or not the option requires a parameter when specified
   */
  public $required = FALSE;
  
  /**
   * @access  public
   * @var     bool    whether or not the option can be specified multiple times
   */
  public $multiple = FALSE;
  
  /**
   * @access  public
   * @var     array   a set of parsing rules indicating the arguments parameter expectations
   */
  public $rule = array(FALSE, FALSE);
  
  /**
   * @access  public
   * @var     mixed   the default value of the parameter
   */
  public $default = NULL;
  
  /**
   * @access  public
   * @var     closure the optional callback
   */
  public $callback = NULL;
  
  // --------------------------------------------------------------------
  
  /**
   * Fast way to build the correct properties for a new option object.
   * 
   * @access  public
   * @param   mixed   a single option or an array of short/long options
   * @param   mixed   the parameter values
   * @param   closure the optional callback
   * @return  void
   */
  public static function build($opts, $param = NULL, $callback = NULL)
  {
    if ( ! is_array($opts))
    {
      $opts = array($opts, NULL);
    }
    
    $short = (empty($opts[0])) ? NULL : $opts[0];
    $long = NULL;

    if (strlen($short) > 1)
    {
      $long = $short;
      $short = NULL;
    }
    elseif (isset($opts[1]) && ! empty($opts[1]))
    {
      $long = $opts[1];
    }
    
    return new self($short, $long, $param, $callback);
  }
  
  /**
   * Set's up the option's properties.
   * 
   * @access  public
   * @param   string  the short opt flag
   * @param   string  the long opt flag
   * @param   mixed   the parameter string or array with default value
   * @param   string  the option description
   * @param   closure the optional callback (called when the option is specified)
   * @return  void
   */
  public function __construct($short = NULL, $long = NULL, $param = NULL, $callback = NULL)
  {
    if (empty($short) && empty($long))
    {
      throw new \InvalidArgumentException('A short or long option must be specified.');
    }
    
    if ( ! empty($callback) && ! is_callable($callback))
    {
      $_option = empty($long) ? $short : $long;
      throw new \InvalidArgumentException("The callback supplied is invalid for the option '$_option'.");
    }
    
    $this->short = empty($short) ? NULL : $short;
    $this->long = empty($long) ? NULL : $long;
    $this->callback = empty($callback) ? NULL : $callback;
    
    if ( ! empty($param))
    {
      $this->_parse_parameter($param);
    }
  }
  
  /**
   * Helper to ensure we are always referencing the long option first
   * before the short one.
   * 
   * @access  public
   * @return  string  the option string representation
   */
  public function __toString()
  {
    return ( ! empty($this->long)) ? $this->long : $this->short;
  }
  
  /**
   * Runs the callback with the given value.
   * 
   * @access  public
   * @param   mixed   the value to send to the callback
   * @return  void
   */
  public function run_callback($value = FALSE)
  {
    if ( ! empty($this->callback))
    {
      call_user_func($this->callback, $value);
    }
  }
  
  /**
   * Parse the passed parameter
   *
   * @access  private
   * @param   mixed   the formatted parameter string or array with optional default
   * @return  void
   */
  private function _parse_parameter($param)
  {
    if ( ! is_array($param))
    {
      $param = array($param, self::OPTION_DEFAULT);
    }
    elseif ( ! isset($param[1]))
    {
      $param[1] = self::OPTION_DEFAULT;
    }
    
    $string = trim($param[0]);
    
    // There's nothing to parse
    if (empty($string)) return;
    
    $this->parameter = $string;
    $this->default   = $param[1];
    
    if (strpos($string, self::MULTIPLE_INDICATOR, strlen($string) - strlen(self::MULTIPLE_INDICATOR)) !== FALSE)
    {
      $string = trim(str_replace(self::MULTIPLE_INDICATOR, '', $string));
      $this->multiple = TRUE;
    }
    
    if ( ! preg_match('/^\[(.+)\]$/', $string))
    {
      // No [OPTIONAL] brackets means it's required.
      $this->required = TRUE;
    }
    
    $this->rule = array(TRUE, $this->required);
  }
}

/* End of file Option.php */
/* Location: ./Getopti/Option.php */