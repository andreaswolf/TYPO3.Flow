<?php
declare(encoding = 'utf-8');

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */ 

/**
 * A composite of controller arguments
 * 
 * @package		FLOW3
 * @subpackage	MVC
 * @version 	$Id:T3_FLOW3_MVC_Controller_Arguments.php 467 2008-02-06 19:34:56Z robert $
 * @copyright	Copyright belongs to the respective authors
 * @license		http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @scope		prototype
 */
class T3_FLOW3_MVC_Controller_Arguments extends ArrayObject {

	/**
	 * @var T3_FLOW3_Component_ManagerInterface A reference to the component manager
	 */
	protected $componentManager;
	
	/**
	 * @var array Names of the arguments contained by this object
	 */
	protected $argumentNames = array();
	
	/**
	 * Constructs this Arguments object
	 *
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function __construct(T3_FLOW3_Component_ManagerInterface $componentManager) {
		$this->componentManager = $componentManager;
		parent::__construct();
	}
	
	/**
	 * Adds or replaces the argument specified by $value. The argument's name is taken from the
	 * argument object itself, therefore the $offset does not have any meaning in this context.
	 *
	 * @param  mixed				$offset: Offset - not used here
	 * @param  mixed				$value: The argument.
	 * @return void
	 * @throws InvalidArgumentException if the argument is not a valid Controller Argument object
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function offsetSet($offset, $value) {
		if (!$value instanceof T3_FLOW3_MVC_Controller_Argument) throw new InvalidArgumentException('Controller arguments must be valid T3_FLOW3_MVC_Controller_Argument objects.', 1187953786);

		$argumentName = $value->getName();
		parent::offsetSet($argumentName, $value);
		$this->argumentNames[$argumentName] = TRUE;
	}
	
	/**
	 * Sets an argument
	 *
	 * @param  mixed				$value: The value
	 * @return void
	 * @throws InvalidArgumentException if the argument is not a valid Controller Argument object
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function append($value) {
		if (!$value instanceof T3_FLOW3_MVC_Controller_Argument) throw new InvalidArgumentException('Controller arguments must be valid T3_FLOW3_MVC_Controller_Argument objects.', 1187953786);
		$this->offsetSet(NULL, $value);
	}

	/**
	 * Unsets an argument
	 * 
	 * @param  mixed				$offset: Offset
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function offsetUnset($offset) {
		$translatedOffset = $this->translateToLongArgumentName($offset);
		parent::offsetUnset($translatedOffset);
		
		unset($this->argumentNames[$translatedOffset]);
		if ($offset != $translatedOffset) {
			unset($this->argumentShortNames[$offset]);
		}
	}
	
	/**
	 * Returns whether the requested index exists
	 * 
	 * @param  mixed				$offset: Offset
	 * @return boolean
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function offsetExists($offset) {
		$translatedOffset = $this->translateToLongArgumentName($offset);
		return parent::offsetExists($translatedOffset);
	}
	
	/**
	 * Returns the value at the specified index
	 * 
	 * @param  mixed				$offset: Offset
	 * @return T3_FLOW3_MVC_Controller_Argument The requested argument object
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function offsetGet($offset) {
		$translatedOffset = $this->translateToLongArgumentName($offset);
		return parent::offsetGet($translatedOffset);
	}

	/**
	 * Creates, adds and returns a new controller argument to this composite object.
	 * If an argument with the same name exists already, it will be replaced by the
	 * new argument object.
	 *
	 * @param  string										$name: Name of the argument
	 * @param  string										$dataType: Name of one of the built-in data types 
	 * @return T3_FLOW3_MVC_Controller_Argument			The new argument
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function addNewArgument($name, $dataType = 'text') {
		$argument = $this->componentManager->getComponent('T3_FLOW3_MVC_Controller_Argument', $name, $dataType);
		$this->addArgument($argument);
		return $argument;
	}

	/**
	 * Adds the specified controller argument to this composite object.
	 * If an argument with the same name exists already, it will be replaced by the
	 * new argument object.
	 * 
	 * Note that the argument will be cloned, not referenced.
	 * 
	 * @param  T3_FLOW3_MVC_Controller_Argument			$argument: The argument to add
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function addArgument(T3_FLOW3_MVC_Controller_Argument $argument) {
		$this->offsetSet(NULL, $argument);
	}
	
	/**
	 * Returns an argument specified by name
	 * 
	 * @param  string										$argumentName: Name of the argument to retrieve
	 * @return T3_FLOW3_MVC_Controller_Argument
	 * @throws T3_FLOW3_MVC_Exception_NoSuchArgument
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getArgument($argumentName) {
		if (!$this->offsetExists($argumentName)) throw new T3_FLOW3_MVC_Exception_NoSuchArgument('An argument "' . $argumentName . '" does not exist.', 1195815178);
		return $this->offsetGet($argumentName);
	}
	
	/**
	 * Checks if an argument with the specified name exists
	 * 
	 * @param  string										$argumentName: Name of the argument to check for
	 * @return boolean										TRUE if such an argument exists, otherwise FALSE
	 * @see offsetExists()
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function hasArgument($argumentName) {
		return $this->offsetExists($argumentName);
	}
	
	/**
	 * Returns the names of all arguments contained in this object
	 * 
	 * @return array				Argument names
	 * @author Robert Lemke <robert@typo3.org> 
	 */
	public function getArgumentNames() {
		return array_keys($this->argumentNames);
	}
	
	/**
	 * Returns the short names of all arguments contained in this object that have one.
	 * 
	 * @return array				Argument short names
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getArgumentShortNames() {
		$argumentShortNames = array();
		foreach ($this as $argument) {
			$argumentShortNames[$argument->getShortName()] = TRUE;
		}
		return array_keys($argumentShortNames);
	}

	/**
	 * Magic setter method for the argument values. Each argument
	 * value can be set by just calling the setArgumentName() method.
	 * 
	 * @param  string				$methodName: Name of the method
	 * @param  array				$arguments: Method arguments
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function __call($methodName, array $arguments) {
		if (T3_PHP6_Functions::substr($methodName, 0, 3) !== 'set') throw new LogicException('Unknown method "' . $methodName . '".');

		$firstLowerCaseArgumentName = $this->translateToLongArgumentName(T3_PHP6_Functions::strtolower($methodName{3}) . T3_PHP6_Functions::substr($methodName, 4));
		$firstUpperCaseArgumentName = $this->translateToLongArgumentName(T3_PHP6_Functions::ucfirst(T3_PHP6_Functions::substr($methodName, 3)));

		if (in_array($firstLowerCaseArgumentName, $this->getArgumentNames())) {
			$argument = parent::offsetGet($firstLowerCaseArgumentName);
			$argument->setValue($arguments[0]);
		} elseif (in_array($firstUpperCaseArgumentName, $this->getArgumentNames())) {
			$argument = parent::offsetGet($firstUpperCaseArgumentName);
			$argument->setValue($arguments[0]);			
		}
	}
	
	/**
	 * Translates a short argument name to its corresponding long name. If the
	 * specified argument name is a real argument name already, it will be returned again.
	 *  
	 * If an argument with the specified name or short name does not exist, an empty
	 * string is returned. 
	 * 
	 * @param  string			argument name
	 * @return string			long argument name or empty string
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function translateToLongArgumentName($argumentName) {
		if (in_array($argumentName, $this->getArgumentNames())) return $argumentName;

		foreach ($this as $argument) {
			if ($argumentName === $argument->getShortName()) return $argument->getName();	
		}
		return '';
	}
}

?>