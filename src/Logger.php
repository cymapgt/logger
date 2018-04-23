<?php
namespace cymapgt\core\utility\logger;

use cymapgt\Exception\LoggerException;
use \Monolog\Logger as MonologLogger;
use Monolog\Handler\NullHandler;

/**
 * Logger
 * 
 * This class provides persistent access to logger, via encapsulating Monolog
 * 
 * 
 * @author    - Cyril Ogana <cogana@gmail.com>
 * @package   - cymapgt.core.utility.logger
 * @copyright - CYMAP BUSINESS SOLUTIONS
 */
class Logger implements \Psr\Log\LoggerAwareInterface
{
    //instantiate properties
    private $isUsingConcreteHandler = false;
    private $concreteLogger = null;
    private $nullLogger = null;
    private $configuredLogHandlers = array();
    
    /**
     *  Prevent direct object creation
     * 
     * @private
     * @final
     */
    public function __construct() {
        //instantiate the Null Logger
        $nullLogger = new MonologLogger('null_logger');
        $nullHandler = new NullHandler;
        $nullLogger->pushHandler($nullHandler);
        $this->nullLogger = $nullLogger;
    }
    
    /**
     * Prevent cloning
     * 
     * @private
     * @final
     */
    final private function __clone() {
    }
    /**
     * Add log handlers to the configuration
     * 
     * @param array $logHandlers
     * @param bool $createLogger
     */
    public function addLogHandler(array $logHandlers, $createLogger = false) {
        $this->configuredLogHandlers = array_merge($this->configuredLogHandlers, $logHandlers);
        
        if ($createLogger === true) {
            $this->createLogger(key($logHandlers));
            $this->createLogHandlers();
        }
    }
    
    /**
     * Create the Monolog logger which will be injected with Handlers and Formaters
     */
    public function createLogger($channelName) {
        $loggerObj = new MonologLogger($channelName);
        
        $this->concreteLogger = $loggerObj;
    }
    
    /**
     * Iterate the configured log handlers and create concrete handlers
     */
    protected function createLogHandlers() {
        $logHandlers = $this->configuredLogHandlers;
        
        foreach ($logHandlers as $logHandler) {
            if (is_array($logHandler)) {                
                foreach ($logHandler as $handlerNamespace => $handlerDetails) {
                    if (
                        is_array($handlerDetails)
                        && array_key_exists('handler_parameters', $handlerDetails)
                    ) {
                        $handlerParameters = $handlerDetails['handler_parameters'];
                        $handlerObj = new $handlerNamespace(...$handlerParameters);
                        $this->concreteLogger->pushHandler($handlerObj);
                    }
                }
            }
        }
    }
    
    /**
     *  Implement setLogger from  Psr\Log\LoggerAwareInterface
     * 
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function setLogger(\Psr\Log\LoggerInterface $logger) {
        $this->concreteLogger = $logger;
    }
    
    /**
     *  Return instance of Monolog Logger, configured with all handlers and formatters
     * 
     * @return \Monolog\Logger
     */
    public function getLogger() {
        if (is_null($this->concreteLogger)) {
            return $this->nullLogger;
        } else {
            return $this->concreteLogger;
        }
    }
}
