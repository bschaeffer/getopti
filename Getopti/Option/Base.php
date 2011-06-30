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
namespace Getopti\Option;

use Getopti\Option;

/**
 * Option Base class.
 *
 * @package     Getopti
 */
class Base {
  
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
   * @var     closure the optional callback
   */
  public $callback = NULL;
  
  // --------------------------------------------------------------------
  
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
      $param= trim($param);
      $this->parameter = $param;

      if (strpos($param, \Getopti\Option::MULTIPLE_INDICATOR) )
      {
        $param = trim(str_replace(\Getopti\Option::MULTIPLE_INDICATOR, '', $param));
        $this->multiple = TRUE;
      }

      if ( ! preg_match('/^\[(.+)\]$/', $param))
      {
        // No [OPTIONAL] brackets means it's required.
        $this->required = TRUE;
      }

      $this->rule = array(TRUE, $this->required);
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
   * Generic option value setting.
   * 
   * @access  public
   * @param   Getopti\Switcher the current switcher utilizing the Option object
   * @param   mixed   the value assigned to the option by the command-line arguments
   * @return  void
   */
  public function set_value(\Getopti\Switcher $switcher, $value = NULL)
  {
    if (empty($value) && empty($this->parameter))
    {
      // If we have an empty value, it's not technically empty. It has
      // certainly been indicated by the user, so we set it TRUE and
      // move on.
      $value = TRUE;
    }
    
    if ($this->multiple)
    {
      // If it's a multiple, push the value to the option array
      $switcher->push($this, $value);
    }
    else
    {
      $switcher->set($this, $value); 
    }
  }
}

/* End of file Base.php */
/* Location: ./Getopti/Base.php */