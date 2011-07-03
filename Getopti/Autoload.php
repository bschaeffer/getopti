<?php
/**
 * @package   Getopti
 * @author    Braden Schaeffer
 * @link      http://github.com/bschaeffer/getopti
 * @license   http://www.opensource.org/licenses/mit-license.html MIT
 */
namespace Getopti;

/**
 * Getopti Autoloader
 *
 * @package Getopti
 * @since   0.1.0
 */
class Autoload {

  /**
   * The PHP file extenstion.
   */
  const EXT = '.php';

  /**
   * @static
   * @access  public
   * @var     array   the list of previously loaded classes
   */
  private static $_loaded = array();

  /**
   * Registers the Getopti\Autoload::load method as an autoloader.
   * 
   * @static
   * @access  public
   * @return  void
   */
  public static function register()
  {
    // @codeCoverageIgnoreStart
    spl_autoload_register(array(__CLASS__, 'load'));
  }
  // @codeCoverageIgnoreEnd

  /**
   * Load Getopti class files. This is the autoload function registered
   * by the Getopti\Autoload::register method.
   * 
   * @static
   * @access public
   * @param  string the fully namespaced class name
   * @return bool
   */
  public static function load($class)
  { 
    if (strpos($class, 'Getopti') !== 0)
    {
      return FALSE;
    }

    if (in_array(strtolower($class), static::$_loaded))
    {
      return TRUE;
    }

    $load_class = str_replace('\\', '/', $class);

    $file = $load_class.self::EXT;

    if (file_exists($file))
    {
      array_push(static::$_loaded, strtolower($class));
      require_once $file;
      return TRUE;
    }

    return FALSE;
  }
}

/* End of file Autoload.php */
/* Location: ./Firefly/Autoload.php */