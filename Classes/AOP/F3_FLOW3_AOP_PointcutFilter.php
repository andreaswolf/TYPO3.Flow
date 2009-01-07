<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\AOP;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * @package FLOW3
 * @subpackage AOP
 * @version $Id$
 */

/**
 * A filter which refers to another pointcut.
 *
 * @package FLOW3
 * @subpackage AOP
 * @version $Id:\F3\FLOW3\AOP\PointcutFilter.php 201 2007-03-30 11:18:30Z robert $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser Public License, version 3 or later
 */
class PointcutFilter implements \F3\FLOW3\AOP\PointcutFilterInterface {

	/**
	 * @var string Name of the aspect class where the pointcut was declared
	 */
	protected $aspectClassName;

	/**
	 * @var string Name of the pointcut method
	 */
	protected $pointcutMethodName;

	/**
	 * @var \F3\FLOW3\AOP\Framework A reference to the AOP Framework
	 */
	protected $aopFramework;

	/**
	 * The constructor - initializes the pointcut filter with the name of the pointcut we're refering to
	 *
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function __construct($aspectClassName, $pointcutMethodName, \F3\FLOW3\AOP\Framework $aopFramework) {
		$this->aspectClassName = $aspectClassName;
		$this->pointcutMethodName = $pointcutMethodName;
		$this->aopFramework = $aopFramework;
	}

	/**
	 * Checks if the specified class and method matches with the pointcut
	 *
	 * @param \F3\FLOW3\Reflection\ClassReflection $class The class to check against
	 * @param \F3\FLOW3\Reflection\MethodReflection $method The method to check against
	 * @param mixed $pointcutQueryIdentifier Some identifier for this query - must at least differ from a previous identifier. Used for circular reference detection.
	 * @return boolean TRUE if the class and method matches the pointcut, otherwise FALSE
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function matches(\F3\FLOW3\Reflection\ClassReflection $class, \F3\FLOW3\Reflection\MethodReflection $method, $pointcutQueryIdentifier) {
		$pointcut = $this->aopFramework->findPointcut($this->aspectClassName, $this->pointcutMethodName);
		if ($pointcut === FALSE) throw new \RuntimeException('No pointcut "' . $this->pointcutMethodName . '" found in aspect class "' . $this->aspectClassName . '" .', 1172223694);
		return $pointcut->matches($class, $method, $pointcutQueryIdentifier);
	}

	/**
	 * Prepares this pointcut filter for sleep
	 *
	 * @return void
	 */
	public function __sleep() {
		return array("\0*\0aspectClassName", "\0*\0pointcutMethodName");
	}

	/**
	 * Updates the reference to the AOP framework
	 *
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function __wakeup() {
		if (isset($GLOBALS['FLOW3']['F3\FLOW3\AOP\Framework'])) {
			$this->aopFramework = $GLOBALS['FLOW3']['F3\FLOW3\AOP\Framework'];
		}
	}
}

?>