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
 * Testcase for the MVC Web Request class
 * 
 * @package		FLOW3
 * @version 	$Id:T3_FLOW3_Component_TransientObjectCacheTest.php 201 2007-03-30 11:18:30Z robert $
 * @copyright	Copyright belongs to the respective authors
 * @license		http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class T3_FLOW3_MVC_Web_RequestTest extends T3_Testing_BaseTestCase {

	/**
	 * @var T3_FLOW3_MVC_Web_Request
	 */
	protected $request;
	
	/**
	 * @var T3_FLOW3_MVC_URI
	 */
	protected $requestURI;
	
	/**
	 * Sets up this test case
	 *
	 * @author  Robert Lemke <robert@typo3.org>
	 */
	protected function setUp() {
		$this->componentManager->setComponentClassName('T3_FLOW3_Utility_Environment', 'T3_FLOW3_Utility_MockEnvironment');
		$this->environment = $this->componentManager->getComponent('T3_FLOW3_Utility_Environment');
		$this->environment->SERVER['ORIG_SCRIPT_NAME'] = '/path1/path2/index.php'; 
		$this->environment->SERVER['SCRIPT_NAME'] = '/path1/path2/index.php';
		
		$URIString = 'http://username:password@subdomain.domain.com:8080/path1/path2/index.php?argument1=value1&argument2=value2#anchor';
		$this->requestURI = $this->componentManager->getComponent('T3_FLOW3_MVC_URI', $URIString);
	}
	
	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function webRequestIsPrototype() {
		$request1 = $this->componentManager->getComponent('T3_FLOW3_MVC_Web_Request');
		$request2 = $this->componentManager->getComponent('T3_FLOW3_MVC_Web_Request');
		$this->assertNotSame($request1, $request2, 'Obviously the web request is not prototype!');
	}
	
	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getArgumentsReturnsProperlyInitializedArgumentsArrayObjectForNewRequest() {
		$request = new T3_FLOW3_MVC_Web_Request($this->environment);
		$this->assertType('ArrayObject', $request->getArguments(), 'getArguments() does not return an ArrayObject for a virgin request object.');
	}
	
	/**
	 * Checks if the request URI is returned as expected.
	 * 
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getRequestURIreturnsCorrectURI() {
		$request = new T3_FLOW3_MVC_Web_Request($this->environment);
		$request->setRequestURI($this->requestURI);
		
		$this->assertEquals($this->requestURI, $request->getRequestURI(), 'request->getRequestURI() did not return the expected URI.');
		$this->assertNotSame($this->requestURI, $request->getRequestURI(), 'request->getRequestURI() returned the same URI which is dangerous ...');
	}
	
	/**
	 * Checks if the test URI is detected correctly as the base URI
	 *
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getBaseURIdetectsSimpleURICorrectly() {		
		$this->environment->SERVER['ORIG_SCRIPT_NAME'] = NULL;
		$this->environment->SERVER['SCRIPT_NAME'] = '/';

		$requestURI = new T3_FLOW3_MVC_URI('http://www.server.com/index.php');
		$expectedBaseURI = new T3_FLOW3_MVC_URI('http://www.server.com/');
		
		$request = new T3_FLOW3_MVC_Web_Request($this->environment);
		$request->setRequestURI($requestURI);
		
		$this->assertEquals($expectedBaseURI, $request->getBaseURI(), 'The returned baseURI is not as expected.');
	}

	/**
	 * Checks if the base URI is detected correctly when TYPO3 resides in a subdirectory of the web root.
	 * 
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getBaseURIdetectsURIWithSubDirectoryCorrectly() {
		$this->environment->SERVER['ORIG_SCRIPT_NAME'] = NULL;
		$this->environment->SERVER['SCRIPT_NAME'] = '/path1/path2/index.php';

		$requestURI = new T3_FLOW3_MVC_URI('http://www.server.com/path1/path2/index.php');
		$expectedBaseURI = new T3_FLOW3_MVC_URI('http://www.server.com/path1/path2/');
		
		$request = new T3_FLOW3_MVC_Web_Request($this->environment);
		$request->setRequestURI($requestURI);
		
		$this->assertEquals($expectedBaseURI, $request->getBaseURI(), 'The returned baseURI is not as expected.');
	}
}
?>