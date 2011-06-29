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
   * Option TYPE constants.
   */
  const TYPE_DEFAULT = 'default';

  /**
   * The string indicator for specifying an argument can be specified multiple times.
   */
  const MULTIPLE_INDICATOR = '[+]';
  
  /**
   * The multpile indicator matcher.
   */
  const MULTIPLE_MATCHER = "/\[\+\]$/";
  
  // --------------------------------------------------------------------
  
  /**
   * Fast way to build the correct properties for a new option object.
   * 
   * 
   * 
   * @access  public
   * @param   mixed   a single option or an array of short/long options
   * @param   mixed   the parameter string or a parameter configuration array
   * @param   closure the optional callback
   * @return  Getopti\Option\Base
   */
  public static function build($opts, $params = NULL, $callback = NULL)
  {
    list($short, $long) = self::parse_opts($opts);
    list($param, $type) = self::parse_params($params);
    
    switch ($type)
    {
      case self::TYPE_DEFAULT:  return new Option\Base($short, $long, $param, $callback);
      
      default:
        throw new \InvalidArgumentException(sprintf("Invalid option type: '%s'", $type));
        break;
    }
  }
  
  /**
   * Parses out the short and long options from a single parameter.
   * 
   * @access  public
   * @param   mixed   a single option string or an array of short/long options
   * @return  array   the short option, the long option
   */
  public static function parse_opts($opts)
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

    return array($short, $long);
  }
  
  /**
   * Parses out the param configuration from a single parameter.
   * 
   * @access  public
   * @param   mixed   a single parameter string or a param configuration array
   * @return  array   the parameter string, the option type
   */
  public static function parse_params($params)
  {
    if ( ! is_array($params))
    {
      $params = array($params, self::TYPE_DEFAULT);
    }
    elseif ( ! isset($params[1]))
    {
      $params[1] = self::TYPE_DEFAULT;
    }

    return $params;
  }
}

/* End of file Option.php */
/* Location: ./Getopti/Option.php */