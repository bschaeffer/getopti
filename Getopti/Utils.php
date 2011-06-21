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
 * Getopti Utilities 
 *
 * @package     Getopti
 */
class Utils {
  
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
    
    if ( ! is_array($argv))
    {
      if ( ! @is_array($_SERVER['argv']))
      {
        return array();
      }
      
      $args = $_SERVER['argv'];
    }
    else
    {
      $args = $argv;
    }
    
    if ($trim > 0)
    {
      for ($i = 0; $i <= $trim - 1; $i++)
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
    if (0 !== \Getopti::$columns)
    {
      return \Getopti::$columns;
    }
     
    if (php_sapi_name() === 'cli' && 'darwin' === strtolower(PHP_OS))
    {
      \Getopti::$columns = (int)exec('tput cols');
    }
    elseif (0 === \Getopti::$columns)
    {
      \Getopti::$columns = self::DEFAULT_COLUMNS;
    }
    
    return \Getopti::$columns;
  }
}

/* End of file Utils.php */
/* Location: ./Getopti/Utils.php */