<?php
declare(ENCODING = 'utf-8');
namespace F3::FLOW3::AOP;

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
 * @package FLOW3
 * @subpackage AOP
 * @version $Id$
 */

/**
 * The main class of the AOP (Aspect Oriented Programming) framework.
 *
 * @package FLOW3
 * @subpackage AOP
 * @version $Id:F3::FLOW3::AOP::Framework.php 201 2007-03-30 11:18:30Z robert $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Framework {

	/**
	 * @var F3::FLOW3::Component::ManagerInterface A reference to the component manager
	 */
	protected $componentManager;

	/**
	 * @var F3::FLOW3::Component::FactoryInterface A reference to the component factory
	 */
	protected $componentFactory;

	/**
	 * @var F3::FLOW3::Configuration::Container The FLOW3 configuration
	 */
	protected $configuration;

	/**
	 * @var F3::FLOW3::Reflection::Service A reference to the reflection service
	 */
	protected $reflectionService;

	/**
	 * @var F3::FLOW3::AOP::PointcutExpressionParserInterface An instance of the pointcut expression parser
	 */
	protected $pointcutExpressionParser;

	/**
	 * @var F3::FLOW3::Cache::Factory A reference to the cache factory
	 */
	protected $cacheFactory;

	/**
	 * @var array A registry of all known aspects
	 */
	protected $aspectContainers = array();

	/**
	 * @var array An array of all proxied class names and adviced methods with information about the advice which has been applied.
	 */
	protected $advicedMethodsInformationByTargetClass = array();

	/**
	 * @var array An array target class names and their proxy class name.
	 */
	protected $targetAndProxyClassNames = array();

	/**
	 * @var array List of component names which must not be proxied. The blacklist must be complete before calling initialize()!
	 */
	protected $componentProxyBlacklist = array();

	/**
	 * @var boolean Flag which signals if this class has already been initialized.
	 */
	protected $isInitialized = FALSE;

	/**
	 * Constructor
	 *
	 * @param F3::FLOW3::Component::ManagerInterface $componentManager: An instance of the component manager
	 * @param F3::FLOW3::Component::FactoryInterface $componentFactory: An instance of the component factory
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function __construct(F3::FLOW3::Component::ManagerInterface $componentManager, F3::FLOW3::Component::FactoryInterface $componentFactory) {
		$this->componentManager = $componentManager;
		$this->componentFactory = $componentFactory;
		$this->registerFrameworkComponents();
	}

	/**
	 * Injects the reflection service
	 *
	 * @param F3::FLOW3::Reflection::Service $reflectionService
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectReflectionService(F3::FLOW3::Reflection::Service $reflectionService) {
		$this->reflectionService = $reflectionService;
	}

	/**
	 * Injects an instance of the pointcut expression parser
	 *
	 * @param F3::FLOW3::AOP::PointcutExpressionParser $pointcutExpressionParser
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectPointcutExpressionParser(F3::FLOW3::AOP::PointcutExpressionParser $pointcutExpressionParser) {
		$this->pointcutExpressionParser = $pointcutExpressionParser;
	}

	/**
	 * Injects a reference to the cache factory
	 *
	 * @param F3::FLOW3::Cache::Factory $cacheFactory
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectCacheFactory(F3::FLOW3::Cache::Factory $cacheFactory) {
		$this->cacheFactory = $cacheFactory;
	}

	/**
	 * Injects a reference to the configuration manager
	 *
	 * @param F3::FLOW3::Configuration::Manager $configurationManager The configuration manager
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectConfigurationManager(F3::FLOW3::Configuration::Manager $configurationManager) {
		$this->configuration = $configurationManager->getSettings('FLOW3');
	}

	/**
	 * Adds a registered component to the proxy blacklist to prevent the component class
	 * from being proxied by the AOP framework.
	 *
	 * @param string $componentName: Name of the component to add to the blacklist
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function addComponentNameToProxyBlacklist($componentName) {
		if ($this->isInitialized) throw new RuntimeException('Cannot add components to the proxy blacklist after the AOP framework has been initialized!', 1169550998);
		$this->componentProxyBlacklist[$componentName] = $componentName;
	}

	/**
	 * Initializes the AOP framework.
	 *
	 * During initialization the specified configuration of components is searched for possible
	 * aspect annotations. If an aspect class is found, the poincut expressions are parsed and
	 * a new aspect with one or more advisors is added to the aspect registry of the AOP framework.
	 * Finally all advices are woven into their target classes by generating proxy classes.
	 *
	 * The class names of all proxied classes is stored back in the $componentConfigurations array.
	 *
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function initialize(array &$componentConfigurations) {
		if ($this->isInitialized) throw new F3::FLOW3::AOP::Exception('The AOP framework has already been initialized!', 1169550994);
		$this->isInitialized = TRUE;

		$loadedFromCache = FALSE;
		$context = $this->componentManager->getContext();

		if ($this->configuration->aop->proxyCache->enable) {
			$proxyCache = $this->cacheFactory->create('FLOW3_AOP_Proxy', 'F3::FLOW3::Cache::VariableCache', $this->configuration->aop->proxyCache->backend, $this->configuration->aop->proxyCache->backendOptions);
			$configurationCache = $this->cacheFactory->create('FLOW3_AOP_Configuration', 'F3::FLOW3::Cache::VariableCache', $this->configuration->aop->proxyCache->backend, $this->configuration->aop->proxyCache->backendOptions);

			if ($proxyCache->has('proxyBuildResults') && $configurationCache->has('advicedMethodsInformationByTargetClass')) {

					// The AOP Pointcut Filter needs a fresh reference to the AOP framework - this is passed through a global:
				$GLOBALS['FLOW3']['F3::FLOW3::AOP::Framework'] = $this;
				$proxyBuildResults =  $proxyCache->load('proxyBuildResults');
				$this->advicedMethodsInformationByTargetClass = $configurationCache->load('advicedMethodsInformationByTargetClass');
				$this->aspectContainers = $configurationCache->load('aspectContainers');
				$loadedFromCache = TRUE;
				unset($GLOBALS['FLOW3']['F3::FLOW3::AOP::Framework']);
			}
		}

		if (!$loadedFromCache) {
			$allAvailableClasses = array();
			$proxyableClasses = array();
			foreach ($componentConfigurations as $componentConfiguration) {
				$className = $componentConfiguration->getClassName();
				$allAvailableClasses[] = $className;
				if (substr($className, 0, 9) != 'F3::FLOW3::') {
					$proxyableClasses[] = $className;
				}
			}
			$this->aspectContainers = $this->buildAspectContainersFromClasses($allAvailableClasses);
			$proxyBuildResults = $this->buildProxyClasses($proxyableClasses, $this->aspectContainers, $context);
		}

		foreach ($proxyBuildResults as $targetClassName => $proxyBuildResult) {
			$this->targetAndProxyClassNames[$targetClassName] = $proxyBuildResult['proxyClassName'];
			if (!class_exists($proxyBuildResult['proxyClassName'])) {
				eval($proxyBuildResult['proxyClassCode']);
			}
			$componentConfigurations[$targetClassName]->setClassName($proxyBuildResult['proxyClassName']);
		}

		if ($this->configuration->aop->proxyCache->enable && !$loadedFromCache) {
			$tags = array('F3_FLOW3_AOP', F3::FLOW3::Cache::Manager::TAG_PACKAGES_CODE);
			$configurationCache->save('advicedMethodsInformationByTargetClass', $this->advicedMethodsInformationByTargetClass, $tags);
			$configurationCache->save('aspectContainers', $this->aspectContainers, $tags);
			$proxyCache->save('proxyBuildResults', $proxyBuildResults, $tags);
		}
	}

	/**
	 * If the AOP Framework has been initialized already.
	 *
	 * @return boolean If the AOP framework has been initialized
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function isInitialized() {
		return $this->isInitialized;
	}

	/**
	 * Returns the names of all target and their proxy classes which were affected by
	 * at least one advice and therefore needed to be proxied.
	 *
	 * @return array An array of target class names and their proxy counterpart.
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getTargetAndProxyClassNames() {
		return $this->targetAndProxyClassNames;
	}

	/**
	 * Traverses the aspect containers to find a pointcut from the aspect class name
	 * and pointcut method name
	 *
	 * @param string $aspectClassName: Name of the aspect class where the pointcut has been declared
	 * @param string $pointcutMethodName: Method name of the pointcut
	 * @return mixed The F3::FLOW3AOPPointcut or FALSE if none was found
	 * @throws F3::FLOW3::AOP::Exception if no aspect container was defined for the given aspect class name
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function findPointcut($aspectClassName, $pointcutMethodName) {
		if (!$this->isInitialized) throw new F3::FLOW3::AOP::Exception('The AOP framework has not yet been initialized!', 1207216396);
		if (!isset($this->aspectContainers[$aspectClassName])) throw new F3::FLOW3::AOP::Exception('No aspect class "' . $aspectClassName . '" found while searching for pointcut "' . $pointcutMethodName . '".', 1172223654);
		foreach ($this->aspectContainers[$aspectClassName]->getPointcuts() as $pointcut) {
			if ($pointcut->getPointcutMethodName() == $pointcutMethodName) {
				return $pointcut;
			}
		}
		return FALSE;
	}

	/**
	 * Returns an array of method names and advices which were applied to the specified class. If the
	 * target class has no adviced methods, an empty array is returned.
	 *
	 * @param string $targetClassName: Name of the target class
	 * @return mixed An array of method names and their advices as array of F3::FLOW3::AOP::AdviceInterface
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getAdvicedMethodsInformationByTargetClass($targetClassName) {
		if (!isset($this->advicedMethodsInformationByTargetClass[$targetClassName])) return array();
		return $this->advicedMethodsInformationByTargetClass[$targetClassName];
	}

	/**
	 * Checks the annotations of the specified classes for aspect tags
	 * and creates an aspect with advisors accordingly.
	 *
	 * @param array $classNames: Classes to check for aspect tags.
	 * @return array An array of F3::FLOW3::AOP::AspectContainer for all aspects which were found.
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function buildAspectContainersFromClasses($classNames) {
		$aspectContainers = array();
		foreach ($classNames as $className) {
			if (class_exists($className, TRUE)) {
				if ($this->reflectionService->isClassTaggedWith($className, 'aspect')) {
					$aspectContainer =  $this->buildAspectContainerFromClass($className);
					if ($aspectContainer !== NULL) {
						$aspectContainers[$className] = $aspectContainer;
					}
				}
			}
		}
		return $aspectContainers;
	}

	/**
	 * Creates and returns an aspect from the annotations found in a class which
	 * is tagged as an aspect. The component acting as an advice will already be
	 * fetched (and therefore instantiated if neccessary).
	 *
	 * @param  string $aspectClassName: Name of the class which forms the aspect, contains advices etc.
	 * @return F3::FLOW3::AOP::AspectContainer The aspect container containing one or more advisors
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function buildAspectContainerFromClass($aspectClassName) {
		$aspectContainer = new F3::FLOW3::AOP::AspectContainer($aspectClassName);

		foreach ($this->reflectionService->getClassMethodNames($aspectClassName) as $methodName) {
			foreach ($this->reflectionService->getMethodTagsValues($aspectClassName, $methodName) as $tagName => $tagValues) {
				foreach ($tagValues as $tagValue) {
					switch ($tagName) {
						case 'around' :
							$advice = $this->componentFactory->create('F3::FLOW3::AOP::AroundAdvice', $aspectClassName, $methodName);
							$pointcut = $this->componentFactory->create('F3::FLOW3::AOP::Pointcut', $tagValue, $this->pointcutExpressionParser, $aspectClassName);
							$advisor = $this->componentFactory->create('F3::FLOW3::AOP::Advisor', $advice, $pointcut);
							$aspectContainer->addAdvisor($advisor);
						break;
						case 'before' :
							$advice = $this->componentFactory->create('F3::FLOW3::AOP::BeforeAdvice', $aspectClassName, $methodName);
							$pointcut = $this->componentFactory->create('F3::FLOW3::AOP::Pointcut', $tagValue, $this->pointcutExpressionParser, $aspectClassName);
							$advisor = $this->componentFactory->create('F3::FLOW3::AOP::Advisor', $advice, $pointcut);
							$aspectContainer->addAdvisor($advisor);
						break;
						case 'afterreturning' :
							$advice = $this->componentFactory->create('F3::FLOW3::AOP::AfterReturningAdvice', $aspectClassName, $methodName);
							$pointcut = $this->componentFactory->create('F3::FLOW3::AOP::Pointcut', $tagValue, $this->pointcutExpressionParser, $aspectClassName);
							$advisor = $this->componentFactory->create('F3::FLOW3::AOP::Advisor', $advice, $pointcut);
							$aspectContainer->addAdvisor($advisor);
						break;
						case 'afterthrowing' :
							$advice = $this->componentFactory->create('F3::FLOW3::AOP::AfterThrowingAdvice', $aspectClassName, $methodName);
							$pointcut = $this->componentFactory->create('F3::FLOW3::AOP::Pointcut', $tagValue, $this->pointcutExpressionParser, $aspectClassName);
							$advisor = $this->componentFactory->create('F3::FLOW3::AOP::Advisor', $advice, $pointcut);
							$aspectContainer->addAdvisor($advisor);
						break;
						case 'pointcut' :
							$pointcut = $this->componentFactory->create('F3::FLOW3::AOP::Pointcut', $tagValue, $this->pointcutExpressionParser, $aspectClassName, $methodName);
							$aspectContainer->addPointcut($pointcut);
						break;
					}
				}
			}
		}

		foreach ($this->reflectionService->getClassPropertyNames($aspectClassName) as $propertyName) {
			foreach ($this->reflectionService->getPropertyTagsValues($aspectClassName, $propertyName) as $tagName => $tagValues) {
				foreach ($tagValues as $tagValue) {
					switch ($tagName) {
						case 'introduce' :
							$splittedTagValue = explode(',', $tagValue);
							if (!is_array($splittedTagValue) || count($splittedTagValue) != 2)  throw new F3::FLOW3::AOP::Exception('The introduction in class "' . $aspectClassName . '" does not contain the two required parameters.', 1172694761);
							$pointcut = $this->componentFactory->create('F3::FLOW3::AOP::Pointcut', trim($splittedTagValue[1]), $this->pointcutExpressionParser, $aspectClassName);
							$interface = new F3::FLOW3::Reflection::ClassReflection(trim($splittedTagValue[0]));
							$introduction = $this->componentFactory->create('F3::FLOW3::AOP::Introduction', $aspectClassName, $interface, $pointcut);
							$aspectContainer->addIntroduction($introduction);
						break;
					}
				}
			}
		}

		if (count($aspectContainer->getAdvisors()) < 1 && count($aspectContainer->getPointcuts()) < 1 && count($aspectContainer->getIntroductions()) < 1) throw new F3::FLOW3::AOP::Exception('The class "' . $aspectClassName . '" is tagged to be an aspect but doesn\'t contain advices nor pointcut or introduction declarations.', 1169124534);
		return $aspectContainer;
	}

	/**
	 * Tests for all specified classes if they match one or more pointcuts and if so
	 * builds an AOP proxy class which contains interceptor code. Returns all class
	 * names (and the name of their new proxy class) of those classes which needed to
	 * be proxied.
	 *
	 * @param array $classNames Class names to take into consideration.
	 * @param array $aspectContainers Aspect containers whose pointcuts are matched against the specified classes.
	 * @param string $context The application context to build proxy classes for.
	 * @return array $proxyBuildResults An array which contains at least the proxy class name and the generated code, indexed by the target class names
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function buildProxyClasses(array $classNames, array $aspectContainers, $context) {
		$proxyBuildResults = array();

		foreach ($classNames as $targetClassName) {
			if (array_search($targetClassName, $this->componentProxyBlacklist) === FALSE && substr($targetClassName, 0, 13) != 'F3::FLOW3::') {
				try {
					if (!$this->reflectionService->isClassTaggedWith($targetClassName, 'aspect') && !$this->reflectionService->isClassAbstract($targetClassName) && !$this->reflectionService->isClassFinal($targetClassName)) {
						$proxyBuildResult = F3::FLOW3::AOP::ProxyClassBuilder::buildProxyClass(new F3::FLOW3::Reflection::ClassReflection($targetClassName), $aspectContainers, $context, $this->reflectionService);
						if ($proxyBuildResult !== FALSE) {
							$proxyBuildResults[$targetClassName] = $proxyBuildResult;
							$this->advicedMethodsInformationByTargetClass[$targetClassName] = $proxyBuildResult['advicedMethodsInformation'];
						}
					}
				} catch (ReflectionException $exception) {
					throw new F3::FLOW3::AOP::Exception::UnknownClass('The class "' . $targetClassName . '" does not exist.', 1187348208);
				}
			}
		}
		return $proxyBuildResults;
	}

	/**
	 * Registers certain classes of the AOP Framework as components, so they can
	 * be used for dependency injection elsewhere.
	 *
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function registerFrameworkComponents() {
		$this->componentManager->registerComponent('F3::FLOW3::AOP::Advisor');
		$this->componentManager->registerComponent('F3::FLOW3::AOP::AfterReturningAdvice');
		$this->componentManager->registerComponent('F3::FLOW3::AOP::AfterThrowingAdvice');
		$this->componentManager->registerComponent('F3::FLOW3::AOP::AroundAdvice');
		$this->componentManager->registerComponent('F3::FLOW3::AOP::BeforeAdvice');
		$this->componentManager->registerComponent('F3::FLOW3::AOP::Introduction');
		$this->componentManager->registerComponent('F3::FLOW3::AOP::Pointcut');
		$this->componentManager->registerComponent('F3::FLOW3::AOP::PointcutInterface', 'F3::FLOW3::AOP::Pointcut');
		$this->componentManager->registerComponent('F3::FLOW3::AOP::PointcutFilter');
		$this->componentManager->registerComponent('F3::FLOW3::AOP::PointcutExpressionParser');

		$componentConfigurations = $this->componentManager->getComponentConfigurations();
		$componentConfigurations['F3::FLOW3::AOP::Advisor']->setScope('prototype');
		$componentConfigurations['F3::FLOW3::AOP::AfterReturningAdvice']->setScope('prototype');
		$componentConfigurations['F3::FLOW3::AOP::AfterThrowingAdvice']->setScope('prototype');
		$componentConfigurations['F3::FLOW3::AOP::AroundAdvice']->setScope('prototype');
		$componentConfigurations['F3::FLOW3::AOP::BeforeAdvice']->setScope('prototype');
		$componentConfigurations['F3::FLOW3::AOP::Introduction']->setScope('prototype');
		$componentConfigurations['F3::FLOW3::AOP::Introduction']->setAutowiringMode(F3::FLOW3::Component::Configuration::AUTOWIRING_MODE_OFF);
		$componentConfigurations['F3::FLOW3::AOP::Pointcut']->setScope('prototype');
		$componentConfigurations['F3::FLOW3::AOP::PointcutInterface']->setScope('prototype');
		$componentConfigurations['F3::FLOW3::AOP::PointcutFilter']->setScope('prototype');
		$this->componentManager->setComponentConfigurations($componentConfigurations);
	}
}
?>