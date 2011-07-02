<?php
/**
 * @package   Getopti
 * @author    Braden Schaeffer
 * @link      http://github.com/bschaeffer/getopti
 * @license   http://www.opensource.org/licenses/mit-license.html MIT
 */
namespace Getopti\Option;

/**
 * Boolean Option 
 *
 * @package     Getopti
 * @subpackage  Option
 * @since       0.1.0
 */
class Bool extends Base {
  
  /**
   * Boolean option values are either TRUE or FALSE, so let's make sure
   * they get set like that.
   * 
   * @access  public
   * @param   Getopti\Switcher
   * @param   mixed   the value to set
   * @return  void
   */
  public function set_value(\Getopti\Switcher $switcher, $value = FALSE)
  {
    $switcher->set($this, ($value === TRUE));
  }
}

/* End of file Bool.php */
/* Location: ./Getopti/Option/Bool.php */