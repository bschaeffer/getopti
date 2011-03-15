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

namespace Getopti;

class Switcher {
  
  const INDICATOR_SHORT = ':';
  const INDICATOR_LONG  = "=";
  
  public $_short2long = array();
  public $_shortopts = '';
  public $_longopts = array();
  public $_callbacks = array();
  
  public $options = array();
  public $results = array();
  public $nonopts = array();
  
  // --------------------------------------------------------------------
  
  /**
   * Adds a set of options to the builder, setting up callbacks and
   * automated help output along the way.
   *
   * @access  public
   * @param   array   the short and long options to watch
   * @param   string  the parameter (ie. PATH or [PATH])
   * @param   string  optional description of the options
   * @param   closure optional callback for the option
   * @return  void
   */
  public function add(array $opts, $parameter = NULL, $description = '', $callback = NULL)
  {
    list($short, $long) = $this->_parse_opts($opts);
    
    $level = $this->_parse_requirement_level($parameter);
    
    if( ! empty($short))
    {
      $this->_shortopts .= $short.str_repeat(self::INDICATOR_SHORT, $level);
      
      if(is_callable($callback))
      {
        $this->_callbacks[$short] = $callback;
      }
    }
    
    if( ! empty($long))
    {
      $this->_longopts[] = $long.str_repeat(self::INDICATOR_LONG, $level);
      
      if(is_callable($callback))
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
    $this->results = Parser::parse($args, $this->_shortopts, $this->_longopts);
    
    $this->nonopts = $this->results[1]; 
    
    foreach($this->results[0] as $opt)
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
  private function _parse_opts($opts)
  {
    $short = (empty($opts[0])) ? FALSE : $opts[0];
    $long = FALSE;

    if(strlen($short) > 1)
    {
      // $short is actually $long
      
      // Default to FALSE here so we don't have to worry later
      // about tracking it down and make it so if it's not specified
      
      $this->options[$short] = FALSE;
      
      // .. and we're done
      return array(NULL, $short);
    }
    
    if(isset($opts[1]) && ! empty($opts[1]))
    {
      $long = $opts[1];
      
      $this->_short2long[$short] = $long;
      
      // see note above about defaulting to FALSE
      $this->options[$long] = FALSE;
    }
    else
    {
      $this->options[$short] = FALSE;
    }
    
    return array($short, $long);
  }
  
  /**
   * Parse the passed parameter
   *
   * @access  private
   * @param   string  the parameter
   * @return  int     padding on the rule string
   */
  private function _parse_requirement_level($parameter = '')
  {   
    if(empty($parameter))
    {  
      return 0; // It doesn't have params
    }
    elseif(preg_match("/^\[([a-z0-9\-_]+)\]\+?$/i", $parameter))
    {  
      return 1; // It's optional
    }
  
    return 2; // It's required (will raise an Getopti\Exception if not missing)
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
    
    if(strlen($option) == 1 && isset($this->_short2long[$option]))
    {
      $option = $this->_short2long[$option];
    }
    
    // If we have an empty value, it's not technically empty. It has
    // certainly been indicated by the user, so we set it TRUE and
    // move on.
    
    $value = (empty($value)) ? TRUE : $value;
    
    if($value === TRUE)
    {
      // If it accepts no paramters, it's just TRUE
      $this->options[$option] = $value;
    }
    else
    {
      // If it accepts parameters, add the value to the array
      $this->options[$option][] = $value;
    }
    
    if(isset($this->_callbacks[$option]))
    {
      call_user_func_array($this->_callbacks[$option], array($value));
    }
  }
}

/* End of file Switcher.php */
/* Location: ./Getopti/Switcher.php */