<?php
namespace cymapgt\core\utility\logger;

use cymapgt\Exception\LoggerException;
use \Monolog\Logger as MonologLogger;
use Monolog\Handler\NullHandler;

/**
 * Logger
 * 
 * Facilitate configuration of Log Handlers and Formatters to aid Developers log
 * to the destinations
 * 
 * 
 * @author    - Cyril Ogana <cogana@gmail.com>
 * @package   - cymapgt.core.utility.logger
 * @copyright - CYMAP BUSINESS SOLUTIONS
 */
class Logger implements \Psr\Log\LoggerAwareInterface
{
    //instantiate properties
    private $channelName = 'null_logger';
    private $concreteLogger = null;
    private $nullLogger = null;
    private $configuredLogHandlers = array();
    
    /**
     *  Constructor
     */
    public function __construct($channelName) {
        //Channel Name
        $this->channelName = $channelName;
        
        //instantiate the Null Logger (will be used if User did not want a "real" logger e.g. when testing)
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
     * @param array $logHandlers - Array of log handler configuration. The alphanumeric keys are the namespace
     * @param bool $createLogger - Boolean flag. If true, create concrete Logger and instantiate the log handlers
     */
    public function addLogHandler(array $logHandlers, $createLogger = false) {
        try {
            $this->configuredLogHandlers = array_merge($this->configuredLogHandlers, $logHandlers);

            if ($createLogger === true) {
                $this->createLogger($this->channelName);
                $this->createLogHandlers();
            }            
        } catch (Exception $ex) {
            throw new LoggerException (
                "An exception occurred when adding the Log Handler to "
                . $this->channelName
                . ":"
                . $ex->getMessage()
            );
        }
    }
    
    /**
     * Create the Monolog logger which will be injected with Handlers and Formatters
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
                      
        foreach ($logHandlers as $handlerNamespace => $handlerDetails) {
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
