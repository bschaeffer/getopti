<?php

/**
 * Getopti
 *
 * Getopti is a command-line parsing tool for PHP
 *
 * @package   Getopti
 * @author    Braden Schaeffer <braden.schaeffer@gmail.com>
 * @version   0.1.3
 * @link      http://github.com/bschaeffer/getopti
 *
 * @copyright Copyright (c) 2011
 * @license   http://www.opensource.org/licenses/mit-license.html MIT
 *
 * @filesource
 */

namespace Getopti;

class Parser {

	// Parsing variables
	private static $_args = array();
	private static $_shortopts = array();
	private static $_longopts = array();

	// Result variables
	public static $opts = array();
	public static $nonopts = array();
		
	/**
	 * Getopti
	 * 
	 * @static
	 * @access  public
	 * @param   array   the arguments array
	 * @param   string  the shortopts string
	 * @param   array   the optional array of longopts
	 * @return  mixed   an error STRING or an ARRAY of parsed options
	 */
	public static function parse($args, $shortopts = '', $longopts = array())
	{	
		static::$_args      = $args;
		static::$_shortopts = self::get_shortopts($shortopts);
		static::$_longopts  = self::get_longopts($longopts);
		static::$opts       = array();
		static::$nonopts    = array();
		
		if((empty(static::$_shortopts) && empty(static::$_longopts)) || empty(static::$_args))
		{
			return array(array(), static::$_args);
		}
		
		foreach(static::$_args as $index => $arg)
		{
			if($arg === '--')
			{
			  unset(static::$_args[$index]);
				static::$nonopts = array_merge(static::$nonopts, static::$_args);
				break;
			}
			elseif(self::is_longopt($arg))
			{
				self::_parse_longopt($arg, $index);	
			}
			elseif(self::is_shortopt($arg))
			{
				self::_parse_shortopt($arg, $index);
			}
      elseif(isset(static::$_args[$index]))
      {
        // Only add it as a $nonopt if it has not been claimed by
        // a previously parsed option
        
        static::$nonopts[] = $arg;
      }
      
      unset(static::$_args[$index]);
		}
		
		return array(static::$opts, static::$nonopts);
	}
		
	/**
	 * Get Shortopts
	 * 
	 * @static
	 * @access  public
	 * @param   string  the string of short options
	 * @return  array   parsed short options
	 */
	public static function get_shortopts($string)
	{
		if(empty($string) || ! preg_match_all("/(\w\:{0,2})/", $string, $matches))
		{
			return array();
		}
		
		$shortopts = array();
		
		foreach($matches[1] as $opt)
		{
			$flag = substr($opt, 0, 1);
			
			$shortopts[$flag] = array((strlen($opt) >= 2), (strlen($opt) >= 3));
		}
		
		return $shortopts;
	}
	
	/**
	 * Get Longopts
	 * 
	 * @static
	 * @access  public
	 * @param   array   an array of long options
	 * @return  array   parsed long options
	 */
	public static function get_longopts($array)
	{
		if(empty($array))
		{
			return array();
		}
		
		$longopts = array();
		
		foreach($array as $opt)
		{
			$value = FALSE;
			$required = FALSE;
			
			if(preg_match("/(={1,2})$/", $opt, $matches))
			{
				$opt = trim($opt, "=");
				$value = TRUE;
        $required = strlen($matches[1]) == 2;
			}
			
			$longopts[$opt] = array($value, $required);
		}
		
		return $longopts;
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
		
		for($i = 0; $i < strlen($arg); $i++)
		{
			$opt = $arg[$i];
			
			if( ! array_key_exists($opt, static::$_shortopts))
			{
			  throw new Exception("illegal option: -$opt");
			}
			
			$value = NULL;
			
			if(static::$_shortopts[$opt][0])
			{	
			  // This 'if' allows for arguments like '-abc' where 'c'
			  // expects a value. If 'a' or 'b' expected a value,
			  // none would be assigned to it based on their locations
			  // in the argument
			  
				if($i + 1 == strlen($arg))
				{	
					$next = $index + 1;
					$possible = (isset(static::$_args[$next])) ? static::$_args[$next] : FALSE;
					
					if( ! empty($possible) && ! self::is_option($possible))
					{
					  $value = $possible;
  					unset(static::$_args[$next]);
					}
					
					if(empty($value) && static::$_shortopts[$opt][1])
					{
					  throw new Exception("option requires a parameter: '$opt' in -$arg");
					}
				}
				elseif(static::$_shortopts[$opt][1])
				{
				  throw new Exception("option requires a parameter: '$opt' in -$arg");
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
		
		if(preg_match("/^[a-zA-Z\-]+=/", substr($arg, 2)))
		{
			list($opt, $value) = explode("=", $arg, 2);
			$value = (empty($value)) ? NULL : $value;
		}
		
		$opt = ltrim($opt, '-');
			
		if( ! array_key_exists($opt, static::$_longopts))
		{
			throw new Exception("illegal option: --$opt");
		}
		
		if(static::$_longopts[$opt][0])
		{
			if(empty($value))
			{
				$next = $index + 1;
				$possible = (isset(static::$_args[$next])) ? static::$_args[$next] : FALSE;
			
				if( ! empty($possible) && ! self::is_option($possible))
				{
				  $value = $possible;
  				unset(static::$_args[$next]);
				}
				
        if(empty($value) && static::$_longopts[$opt][1])
        {
          throw new Exception("option requires a parameter: --$opt");
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