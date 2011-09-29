<?php
namespace TYPO3\FLOW3\Log\Backend;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * An abstract Log backend
 *
 * @api
 */
abstract class AbstractBackend implements \TYPO3\FLOW3\Log\Backend\BackendInterface {

	/**
	 * One of the LOG_* constants. Anything below that will be filtered out.
	 * @var integer
	 */
	protected $severityThreshold = LOG_INFO;

	/**
	 * Flag telling if the IP address of the current client (if available) should be logged.
	 * @var boolean
	 */
	protected $logIpAddress = FALSE;

	/**
	 * Constructs this log backend
	 *
	 * @param mixed $options Configuration options - depends on the actual backend
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function __construct($options = array()) {
		if (is_array($options) || $options instanceof ArrayAccess) {
			foreach ($options as $optionKey => $optionValue) {
				$methodName = 'set' . ucfirst($optionKey);
				if (method_exists($this, $methodName)) {
					$this->$methodName($optionValue);
				}
			}
		}
	}

	/**
	 * The maximum severity to log, anything less severe will not be logged.
	 *
	 * @param integer $severityThreshold One of the LOG_* constants
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @api
	 */
	public function setSeverityThreshold($severityThreshold) {
		$this->severityThreshold = $severityThreshold;
	}

	/**
	 * Enables or disables logging of IP addresses.
	 *
	 * @param boolean $logIpAddress Set to TRUE to enable logging of IP address, or FALSE to disable
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function setLogIpAddress($logIpAddress) {
		$this->logIpAddress = $logIpAddress;
	}

}
?>