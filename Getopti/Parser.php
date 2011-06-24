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
 * Getopt Parser Class 
 *
 * Parses a given set of options and arguements
 *
 * @package     Getopti
 */
class Parser {
  
  /**
   * Default value for all options.
   */
  const OPTION_DEFAULT = NULL;

  // Parsing variables
  private static $_args = array();
  private static $_shortopts = array();
  private static $_longopts = array();

  /**
   * @access  public
   * @var     array   the matched option/values
   */
  public static $opts = array();
  
  /**
   * @access  public
   * @var     array   all arguments unable to be matched as options
   */
  public static $nonopts = array();
  
  /**
   * @access  public
   * @var     array   all values coming after a '--' argument
   */
  public static $breakopts = array();
  
  // --------------------------------------------------------------------
    
  /**
   * Parses command line options based on the given short/long option
   * rules.
   * 
   * @static
   * @access  public
   * @param   array   the arguments array
   * @param   array   the optional array of short options
   * @param   array   the optional array of long options
   * @return  array   the array of option results
   */
  public static function parse($args, $shortopts = array(), $longopts = array())
  {
    static::$_args      = (empty($args)) ? array() : $args;
    static::$_shortopts = (empty($shortopts)) ? array() : $shortopts;
    static::$_longopts  = (empty($longopts)) ? array() : $longopts;
    static::$opts       = array();
    static::$nonopts    = array();
    static::$breakopts  = array();
    
    if ((empty(static::$_shortopts) && empty(static::$_longopts)) || empty(static::$_args))
    {
      return array(array(), static::$_args, array());
    }
    
    foreach (static::$_args as $index => $arg)
    {
      if ($arg === '--')
      {
        unset(static::$_args[$index]);
        static::$breakopts = array_merge(array(), static::$_args);
        break;
      }
      elseif (self::is_longopt($arg))
      {
        self::_parse_longopt($arg, $index);  
      }
      elseif (self::is_shortopt($arg))
      {
        self::_parse_shortopt($arg, $index);
      }
      elseif (isset(static::$_args[$index]))
      {
        // Only add it as a $nonopt if it has not been claimed by
        // a previously parsed option
        
        static::$nonopts[] = $arg;
      }
      
      unset(static::$_args[$index]);
    }
    
    return array(static::$opts, static::$nonopts, static::$breakopts);
  }
    
  /**
   * Parse Shortopt
   * 
   * @static
   * @access  private
   * @param   string  the shortopt(s) to parse
   * @param   int     the index of the current argument
   * @return  void
   */
  private static function _parse_shortopt($arg, $index)
  {
    $arg = ltrim($arg, '-');
    
    for ($i = 0; $i < strlen($arg); $i++)
    {
      $opt = $arg[$i];
      
      if ( ! array_key_exists($opt, static::$_shortopts))
      {
        throw new \Getopti\Exception("illegal option: -$opt");
      }
      
      $value = self::OPTION_DEFAULT;
      
      if (static::$_shortopts[$opt][0])
      {  
        // This 'if' allows for arguments like '-abc' where 'c'
        // expects a value. If 'a' or 'b' expected a value,
        // none would be assigned to it based on their locations
        // in the argument
        
        if ($i + 1 == strlen($arg))
        {  
          $next = $index + 1;
          $possible = (isset(static::$_args[$next])) ? static::$_args[$next] : FALSE;
          
          if ( ! empty($possible) && ! self::is_option($possible))
          {
            $value = $possible;
            unset(static::$_args[$next]);
          }
        }
        
        if (empty($value) && static::$_shortopts[$opt][1])
        {
          throw new \Getopti\Exception("option requires a parameter: '$opt' in -$arg");
        }
      }
    
      static::$opts[] = array($opt, $value);
    }
  }
    
  /**
   * Parse Longopts
   * 
   * @static
   * @access  private
   * @param   string  the longopt(s) to parse
   * @param   int     the index of the current argument
   * @return  void
   */
  private static function _parse_longopt($arg, $index)
  {  
    $opt = $arg;
    $value = NULL;
    
    if (preg_match("/^[a-zA-Z\-]+=/", substr($arg, 2)))
    {
      list($opt, $value) = explode("=", $arg, 2);
      $value = (empty($value)) ? NULL : $value;
    }
    
    $opt = ltrim($opt, '-');
      
    if ( ! array_key_exists($opt, static::$_longopts))
    {
      throw new \Getopti\Exception("illegal option: --$opt");
    }
    
    if (static::$_longopts[$opt][0])
    {
      if (empty($value))
      {
        $next = $index + 1;
        $possible = (isset(static::$_args[$next])) ? static::$_args[$next] : FALSE;
      
        if ( ! empty($possible) && ! self::is_option($possible))
        {
          $value = $possible;
          unset(static::$_args[$next]);
        }
        
        if (empty($value) && static::$_longopts[$opt][1])
        {
          throw new \Getopti\Exception("option requires a parameter: --$opt");
        }
      }
    }
  
    static::$opts[] = array($opt, $value);
  }
    
  /**
   * Is Option
   * 
   * @static
   * @access  public
   * @param   string  the argument string to check  
   * @return  bool    whether it's an option or not
   */
  public static function is_option($string)
  { 
    return $string == '--'
      || self::is_shortopt($string)
      || self::is_longopt($string);
  }
    
  /**
   * Whether or not the string is a short option
   * 
   * To be a valid short option, it must meet the following criteria:
   * 
   * 1) Must begin with '-'
   * 2) Then, be followed by a single letter or number: [a-zA-Z0-9]
   * 3) Then, the string must end
   * 
   * Valid short option examples:
   * 
   *   -a
   *   -0
   * 
   * Invalid short option examples:
   * 
   *   -aa
   *   -01
   *   -a=
   * 
   * @static
   * @access  public
   * @param   string  the argument string to check 
   * @return  bool    whether it's an option or not
   */
  public static function is_shortopt($string)
  {  
    return $string[0] == '-'
      && (bool)preg_match('/^[a-z0-9]+$/i', substr($string, 1));
  }
    
  /**
   * Whether or not the string is a long option
   * 
   * To be a valid long option, it must meet the following criteria:
   * 
   * 1) Must begin with '--'
   * 2) Then, be followed by atleast two letters, numbers or dashes: [a-zA-Z0-9\-]
   * 3) Then, optionally be followed by an '='
   * 4) Then, the matcher stops
   * 
   * Valid long option examples:
   * 
   *   --abcd
   *   --ab01=
   *   --ab-c
   *   --ab-0=
   *   --ab-cd
   *   --ab-01=
   * 
   * Invalid long option examples:
   * 
   *   --a
   *   --0=
   * 
   * @static
   * @access  public
   * @param   string  the argument string to check 
   * @return  bool    whether it's an option or not
   */
  public static function is_longopt($string)
  {
    return strlen($string) > 2
      && $string[0] == '-'
      && $string[1] == '-'
      && (bool)preg_match('/^[a-z0-9][a-z0-9\-]+=?/i', substr($string, 2));
  }
}

/* End of file Parser.php */
/* Location: ./Getopti/Parser.php */