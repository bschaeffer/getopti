<?php
/**
 * @package   Getopti
 * @author    Braden Schaeffer
 * @link      http://github.com/bschaeffer/getopti
 * @license   http://www.opensource.org/licenses/mit-license.html MIT
 */

require_once 'Getopti/Autoload.php';
Getopti\Autoload::register();

/**
 * Getopti Base Class 
 *
 * @package     Getopti
 */
class Getopti {
  /**
   * Getopti Version
   */
  const VERSION = '0.1.4';
   
  /**#@+
   * Separator matchers for optional argument parameters
   */
  const OPTIONAL_SEP_LEFT   = "[";
  const OPTIONAL_SEP_RIGHT  = "]";
  /**#@-*/
  
  /**
   * The default output width
   */
  const DEFAULT_COLUMNS = 75;
  
  /**
   * @access  public
   * @var     int     the overall output width
   */
  public static $columns = 0;
  
  /**
   * @access  public
   * @var     int     the left side white-space padding of the output
   */
  public static $left_padding = 2;
  
  /**
   * @access  public
   * @var     int     the right side white-space padding of the output
   */
  public static $right_padding = 2;
  
  /**
   * @access  public
   * @var     int     the white-space padding between an option/command and it's description
   */
  public static $option_padding = 26;
}

/* End of file Getopti.php */
/* Location: ./Getopti.php */