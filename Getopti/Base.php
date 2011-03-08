<?php

namespace Getopti;

class Base {
  
  /**
	 * Read Args
	 * 
	 * @static
	 * @access public
	 * @param  int    the number of arguments to trim off the top
	 * @return array  the arguments
	 */
	public static function read_args($trim = 0)
	{
		global $argv;
		
		if( ! is_array($argv))
		{
			if( ! @is_array($_SERVER['argv']))
			{
			    if( ! @is_array($GLOBALS['HTTP_SERVER_VARS']['argv']))
			    {
			    	return array();
			    }
			    
			    $args = $GLOBALS['HTTP_SERVER_VARS']['argv'];
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
	public static function get_cols()
	{
	  return (int)exec("tput cols") - \Getopti::$padding;
	}
}

/* End of file Base.php */
/* Location: ./Getopti/Base.php */