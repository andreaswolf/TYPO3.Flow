<?php
namespace TYPO3\FLOW3\Tests\Unit\Validation\Validator;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

require_once('AbstractValidatorTestcase.php');
/**
 * Testcase for the string length validator
 *
 */
class StringValidatorTest extends \TYPO3\FLOW3\Tests\Unit\Validation\Validator\AbstractValidatorTestcase {

	protected $validatorClassName = 'TYPO3\FLOW3\Validation\Validator\StringValidator';

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function stringValidatorShouldValidateString() {
		$this->assertFalse($this->validator->validate('Hello World')->hasErrors());
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function stringValidatorShouldReturnErrorIfNumberIsGiven() {
		$this->assertTrue($this->validator->validate(42)->hasErrors());
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function stringValidatorShouldReturnErrorIfObjectWithToStringMethodStringIsGiven() {
		$className = 'TestClass' . md5(uniqid(mt_rand(), TRUE));

		eval('
			class ' . $className . ' {
				public function __toString() {
					return "ASDF";
				}
			}
		');
		$object = new $className();
		$this->assertTrue($this->validator->validate($object)->hasErrors());
	}

}

?>