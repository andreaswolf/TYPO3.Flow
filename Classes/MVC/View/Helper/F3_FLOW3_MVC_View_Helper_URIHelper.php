<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\MVC\View\Helper;

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
 * @subpackage MVC
 * @version $Id$
 */

/**
 * A URI/Link Helper
 *
 * @package FLOW3
 * @subpackage MVC
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser Public License, version 3 or later
 */
class URIHelper extends \F3\FLOW3\MVC\View\Helper\AbstractHelper {

	/**
	 * @var \F3\FLOW3\MVC\Web\Routing\RouterInterface
	 */
	protected $router;

	/**
	 * Injects the Router
	 * 
	 * @param \F3\FLOW3\MVC\Web\Routing\RouterInterface $router
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function injectRouter(\F3\FLOW3\MVC\Web\Routing\RouterInterface $router) {
		$this->router = $router;
	}

	/**
	 * Creates a link by making use of the Routers reverse routing mechanism.
	 * 
	 * @param string $label Inner HTML of the generated link. Label is htmlspecialchared by default
	 * @param string $actionName Name of the action to be called
	 * @param array $arguments Additional arguments
	 * @param string $controllerName Name of the target controller. If not set, current controller is used
	 * @param string $packageKey Name of the target package. If not set, current package is used
	 * @param array $options Further options
	 * @return string the HTML code for the generated link
	 * @see UIRFor()
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function linkTo($label, $actionName, $arguments = array(), $controllerName = NULL, $packageKey = NULL, $options = array()) {
		$link = '<a href="' . $this->URIFor($actionName, $arguments, $controllerName, $packageKey, $options) . '">' . htmlspecialchars($label) . '</a>';
		return $link;
	}

	/**
	 * Creates an URI by making use of the Routers reverse routing mechanism.
	 * 
	 * @param string $actionName Name of the action to be called
	 * @param array $arguments Additional arguments
	 * @param string $controllerName Name of the target controller. If not set, current controller is used
	 * @param string $packageKey Name of the target package. If not set, current package is used
	 * @param array $options Further options
	 * @return string the HTML code for the generated link
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function URIFor($actionName, $arguments = array(), $controllerName = NULL, $packageKey = NULL, $options = array()) {
		$routeValues = $arguments;
		$routeValues['@action'] = $actionName;
		$routeValues['@controller'] = ($controllerName === NULL) ? $this->request->getControllerName() : $controllerName;
		$routeValues['@package'] = ($packageKey === NULL) ? $this->request->getControllerPackageKey() : $packageKey;

		$URIString = $this->router->resolve($routeValues);
		return $URIString;
	}
}

?>
