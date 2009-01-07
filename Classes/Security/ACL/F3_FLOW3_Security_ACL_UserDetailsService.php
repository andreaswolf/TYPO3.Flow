<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Security\ACL;

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
 * @subpackage Security
 * @version $Id$
 */

/**
 * The ACL UserDetailsService. It mainly calculates the current roles for the set request patterns from the given authentication token.
 *
 * @package FLOW3
 * @subpackage Security
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser Public License, version 3 or later
 */
class UserDetailsService implements \F3\FLOW3\Security\Authentication\UserDetailsServiceInterface {

	/**
	 * Returns the \F3\FLOW3\Security\Authentication\UserDetailsInterface object for the given authentication token.
	 *
	 * @param \F3\FLOW3\Security\Authentication\TokenInterface $authenticationToken The authentication token to get the user details for
	 * @return \F3\FLOW3\Security\Authentication\UserDetailsInterface The user details for the given token
	 */
	public function loadUserDetails(\F3\FLOW3\Security\Authentication\TokenInterface $authenticationToken) {
		//Uses the credentials in the token to figure out which user should be loaded
	}
}

?>