<?php

/**
 * Getopti
 *
 * Getopti is a command-line parsing tool for PHP
 *
 * @package   Getopti
 * @author    Braden Schaeffer <hello@manasto.info>
 * @version   0.1.2
 * @link      http://github.com/bschaeffer/getopti
 *
 * @copyright Copyright (c) 2011
 * @license   http://www.opensource.org/licenses/mit-license.html MIT
 *
 * @filesource
 */

namespace Getopti;

class Exception extends \Exception {}

class Base {
  
  /**
	 * Read Args
	 * 
	 * @static
	 * @access  public
	 * @param   int    the number of arguments to trim off the top
	 * @return  array  the arguments
	 */
	public static function read_args($trim = 0)
	{
		global $argv;
		
		if( ! is_array($argv))
		{
			if( ! @is_array($_SERVER['argv']))
			{
			  return array();
			}
			
			$args = $_SERVER['argv'];
		}
		else
		{
			$args = $argv;
		}
		
		if($trim > 0)
		{
			for($i = 0; $i <= $trim - 1; $i++)
			{
				unset($args[$i]);
			}
			
			$args = array_merge(array(), $args);
		}
				
		return $args;
	}
	
	/**
	 * Calculate the column wrap using builtin terminal commands
	 * 
	 * @static
	 * @access  public
	 * @return  int     the number of columns to use for wrapping
	 */
	public static function get_columns()
	{
	  if(\Getopti::$columns > 0)
	  {
	    return \Getopti::$columns;
	  }
	  
	  $columns = (php_sapi_name() === 'cli') ? (int)exec("tput cols") : 80;
	  \Getopti::$columns = $columns;
	  return $columns;
	}
}

/* End of file Base.php */
/* Location: ./Getopti/Base.php */