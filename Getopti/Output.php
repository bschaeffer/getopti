<?php
/**
 * Getopti
 *
 * Getopti is a command-line parsing tool for PHP
 *
 * @package   Getopti
 * @author    Braden Schaeffer <hello@manasto.info>
 * @version   0.1.3
 * @link      http://github.com/bschaeffer/getopti
 *
 * @copyright Copyright (c) 2011
 * @license   http://www.opensource.org/licenses/mit-license.html MIT
 *
 * @filesource
 */
namespace Getopti;

/**
 * Getopti Output Class 
 *
 * A class to manage automated help output.
 *
 * @package     Getopti
 * @author      Braden Schaeffer <hello@manasto.info>
 */
class Output {
  
  const SPACE = " ";
  
  public $output = '';
  
  /**
   * Add banner text to the automated help output. This can be a simple
   * section seperator or eleborate usage information.
   * 
   * @access  public
   * @param   string  the banner text
   * @return  void
   */
  public function banner($text)
  {
    $banner = self::wrap($text);
    $this->write($banner);
  }
  
  /**
   * Add command text to the automated help output. This is similar to
   * adding an option, but it only needs a command and optional description
   * 
   * @access  public
   * @param   string  the command
   * @param   string  the description
   * @return  void
   */
  public function command($command = '', $description = '')
  {
    $string = self::format_string($command, $description);
    $this->write($string);
  }
  
  /**
   * Add short and long option information to the automated output
   * 
   * @access  public
   * @param   array   the short and/or long option
   * @param   string  the parameter associated with the opts (optional)
   * @param   string  the description of the associated options (optional)
   * @return  void
   */
  public function option(array $opts, $param = '', $description = '')
  { 
    $options = '';
    
    if(isset($opts[1]) && ! empty($opts[1]))
    {
      $options = "-{$opts[0]}, --{$opts[1]}";
    }
    elseif(strlen($opts[0]) > 1)
    {
      $options = str_repeat(' ', 4)."--{$opts[0]}";
    }
    else
    {
      $options = "-{$opts[0]}";
    }
    
    $options .= " ".$param;
    
    $string = self::format_string($options, $description);
    $this->write($string);
  }
  
  /**
   * Add usage information
   * 
   * @access  public
   * @param   string  the usage string
   * @return  void
   */
  public function usage($usage)
  {
    $left_pad = \Getopti::$left_padding;
    $string = self::wrap($usage, self::br($left_pad));
    $string = self::pad($string, FALSE);
    $this->write($string);
  }

  /**
   * Uniformly add automated text to the output.
   * 
   * Automatically add a text to the automated output, followed by a new-line break
   * 
   * @access  public
   * @param   string  the text to add to the output
   * @return  void
   */
  public function write($text)
  {
    $this->output .= $text.PHP_EOL;
  }
  
  /**
   * Returns the automated help output
   * 
   * @access  public
   * @return  string  the automated help output
   */
  public function help()
  {
    return $this->output;
  }
  
  /**
   * Formats an option or command string and description
   * 
   * @static
   * @access  public
   * @param   string  the option or command string
   * @param   string  the description
   * @return  string  the fully formatted string
   */
  public static function format_string($opt, $description)
  {
    // Pad the option/command string
    $string = self::pad($opt);
    
    // If it's greater than the allowed $option_padding, we need to
    // add a new line so any description will start below
    if(strlen($string) > \Getopti::$option_padding + 1)
    {
      $string .= self::br();
    }
      
    $string = self::wrap($description, self::br(), $string);
    
    return $string;
  }
  
  /**
   * Uniformly pad a string (for adding commands, options)
   * 
   * @static
   * @access  public
   * @param   string  the initial string to pad
   * @param   string  whether or not to pad the string with option pading
   * @return  string  the padded string
   */
  public static function pad($string = '', $add_option_padding = TRUE)
  {
    $string = str_repeat(self::SPACE, \Getopti::$left_padding).$string;
    
    if($add_option_padding)
    {
      $string = str_pad($string, \Getopti::$option_padding, self::SPACE);
    }
    
    return $string;
  }
  
  /**
   * Simply creates a break string for use when adding options and
   * commands.
   * 
   * @static
   * @access  public
   * @param   int     the optional number of space to add to the break
   * @return  string  the break string
   */
  public static function br($repeat = NULL)
  {
    $repeat = (empty($repeat)) ? \Getopti::$option_padding : $repeat;
    return PHP_EOL.str_repeat(self::SPACE, $repeat);
  }
  
  /**
   * Custom word wrap function that allows for customized breaking
   * 
   * @access  public
   * @param   string  the text string to wrap
   * @param   string  the break string to wrap with
   * @param   string  the string to append to the beginning of the wrap string
   * @return  string  the formatted string
   */
  public static function wrap($string, $break = "\n", $append = '')
  { 
    $width = \Getopti::get_columns() - \Getopti::$right_padding;
    $width = $width - (strlen($break)) - substr_count($break, PHP_EOL);

    $break = str_replace(
      array("\t", "\s"),
      array(str_repeat(self::SPACE, 4), self::SPACE),
      $break
    );
  
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