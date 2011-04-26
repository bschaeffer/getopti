<?php

/**
 * Getopti
 *
 * Getopti is a command-line parsing tool for PHP
 *
 * @package   Getopti
 * @author    Braden Schaeffer <braden.schaeffer@gmail.com>
 * @version   0.1.2
 * @link      http://github.com/bschaeffer/getopti
 *
 * @copyright Copyright (c) 2011
 * @license   http://www.opensource.org/licenses/mit-license.html MIT
 *
 * @filesource
 */

namespace Getopti;

class Output {
  
  public static $output = '';
  
  public function __construct()
  {
    static::$output = '';
  }
  
  /**
   * Add banner text to the automated help output. This can be a simple
   * section seperator or eleborate usage information.
   * 
   * @static
   * @access  public
   * @param   string  the banner text
   * @return  void
   */
  public static function banner($text)
  {
    $banner = self::wrap($text);
    static::write($banner);
  }
  
  /**
   * Add command text to the automated help output. This is similar to
   * adding an option, but it only needs a command and optional description
   * 
   * @static
   * @access  public
   * @param   string  the command
   * @param   string  the description
   * @return  void
   */
  public static function command($command = '', $description = '')
  {
    $string = self::pad($command, $description);
    static::write($string);
  }
  
  /**
   * Add short and long option information to the automated output
   * 
   * @static
   * @access  public
   * @param   array   the short and/or long option
   * @param   string  the parameter associated with the opts (optional)
   * @param   string  the description of the associated options (optional)
   * @return  void
   */
  public static function option(array $opts, $param = '', $description = '')
  { 
    $options = '';
    
    if(isset($opts[1]) && ! empty($opts[1]))
    {
      $options = "-{$opts[0]}, --{$opts[1]}";
    }
    elseif(strlen($opts[0]) > 1)
    {
      $options = "   --{$opts[0]}";
    }
    else
    {
      $options = "-{$opts[0]}";
    }
    
    $options .= " ".$param;
    $options = self::pad($options, $description);
    
    static::write($options);
  }

  /**
   * Uniformly add automated text to the output.
   * 
   * Automatically add a text to the automated output, followed by a new-line break
   * 
   * @static
   * @access  public
   * @param   string  the text to add to the output
   * @return  void
   */
  public static function write($text)
  {
    static::$output .= $text.PHP_EOL;
  }
  
  /**
   * Returns the automated help output
   * 
   * @static
   * @access  public
   * @return  string  the automated help output
   */
  public static function help()
  {
    return static::$output;
  }
  
  /**
   * Uniformly pad a string (for adding commands, options)
   * 
   * @static
   * @access  public
   * @param   string  the initial string to pad
   * @param   string  the description to pad
   * @return  string  the padded string
   */
  public static function pad($string = '', $description = '')
  {
    $padding = \Getopti::$option_padding;
    
    $string = " ".str_pad($string, $padding -1, " ");
    
    $break = PHP_EOL.str_pad('', $padding, " ");
    $description = self::wrap($description, $break);
    
    return $string.$description;
  }
  
  /**
   * Custom word wrap function that allows for customized breaking
   * 
   * @static
   * @access  public
   * @param   string  the text string to wrap
   * @param   string  the break string to wrap with
   * @param   string  the string to append to the beginning of the wrap string
   * @return  string  the formatted string
   */
  public static function wrap($string, $break = "\n", $append = '')
  {
    $width = \Getopti::get_columns() - \Getopti::$padding;
    
    $break = str_replace(array("\t", "\s"), array("    ", " "), $break);

    if(preg_match_all("/(\n)/", $break, $matches))
    {
      $width = $width - (strlen($break)) - count($matches[0]);
    }

    $string = wordwrap($string, $width, $break, FALSE);
    $string = preg_replace("/{$break}\s/", $break, $string);

    $pad = '';

    if( ! empty($append))
    {
      $finish = strlen($pad);
      $start = $finish - strlen($append);
	
      $pad = substr_replace($pad, $append, $start, $finish);
    }

    return $pad.$string;
  }
}

/* End of file Output.php */
/* Location: ./Getopti/Output.php */