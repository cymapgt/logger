<?php
namespace cymapgt\core\utility\logger;

use cymapgt\Exception\LoggerException;
use \Monolog\Logger as MonologLogger;
use Monolog\Handler\NullHandler;

/**
 * @TODO: Below required for backward compatibility...remove in next iterations - cogana@gmail.com
 */
use cymapgt\core\utility\notifier\NotifierSmsAfricasTalkingService;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SwiftMailerHandler;
use cymapgt\core\utility\logger\handler\NotifierSmsAfricasTalkingServiceHandler;

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
    
    //instantiate properties
    private static $loggerStderr;
    private static $loggerLevel1;
    private static $loggerLevel2;
    private static $loggerLevel3;
    private static $loggerSecurity;
    
    /**
     *  Constructor
     */
    public function __construct($channelName = null) { //TODO: Param shoudl be required when old functionality is no longer needed
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
    final private function __clone() {  //@TODO: Remove when old functionality no longer needed (cogana@gmail.com)
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
    public function getLoggerNew() {
        if (is_null($this->concreteLogger)) {
            return $this->nullLogger;
        } else {
            return $this->concreteLogger;
        }
    }
    
    /**
      * Static function to return an instance of a logger
     * @param   int   loggerType    - The logger type
     * @param   array loggerParams  - array of logger params
     * 
     * @return  self::$dbLink  - Returns static instance of mysqli connection
     * 
     * @public
     * @static
     */
    public static function getLogger($loggerType, $loggerParams = array()) {
        //trap all possible exceptions and throw LoggerException
        try {
            switch ($loggerType) {
                case LOGGER_STDERR:
                    return self::_getLoggerStderr();
                case LOGGER_LEVEL1:
                    return self::_getLoggerLevel1($loggerParams);
                case LOGGER_LEVEL2:
                    return self::_getLoggerLevel2($loggerParams);                
                case LOGGER_LEVEL3:
                    return self::_getLoggerLevel3($loggerParams);              
                case LOGGER_SECURITY:
                    return self::_getLoggerSecurity($loggerParams);
            }            
        } catch (\Exception $logException) {
            throw new LoggerException('Logger caught an exception in '
                . $logException->getTraceAsString() . ':'
                . $logException->getMessage(), 1001);
        }
    }
    
    /**
     * return the stderr logger
     * 
     * @return object
     * 
     * @private
     * @static 
     */
    private static function _getLoggerStderr() {
        if (!isset(self::$loggerStderr)) {        
            $CymapgtStderrLogger = new MonologLogger('cymapgt_stderr');
            $CymapgtStderrLogger->pushHandler(new ErrorLogHandler());
            self::$loggerStderr = $CymapgtStderrLogger;
        }
        return self::$loggerStderr;
    }
    
    /**
     * return the level1 logger
     * 
     * @param  array  $loggerParams - Array of parameters to configure the level 1 logger
     * 
     * @return object
     * @private
     * @static
     */    
    private static function _getLoggerLevel1($loggerParams) {
        if (!isset(self::$loggerLevel1)) {
            $CymapgtLevel1LogDir = $loggerParams['log_dir'];
            $CymapgtLevel1Stream = new StreamHandler($CymapgtLevel1LogDir, MonologLogger::DEBUG);
            $CymapgtLevel1Logger = new MonologLogger('cymapgt_level1');
            $CymapgtLevel1Logger->pushHandler($CymapgtLevel1Stream);
            self::$loggerLevel1 = $CymapgtLevel1Logger;
        }
        return self::$loggerLevel1;
    }
    
    /**
     * return the level2 logger
     * 
     *  @param  array  $loggerParams - Array of parameters to configure the level 2 logger
     * 
     *  @return object
     * @private
     *  @static
     */     
    private static function _getLoggerLevel2($loggerParams) {
        if (!isset(self::$loggerLevel2)) {        
            $CymapgtLevel2LogDir = $loggerParams['log_dir'];
            $CymapgtLevel2Stream = new StreamHandler($CymapgtLevel2LogDir, MonologLogger::ERROR);
            $CymapgtLevel2Logger = new MonologLogger('cymapgt_level2');
            $CymapgtLevel2Mailer  = \Swift_Mailer::newInstance($loggerParams['swiftmailer_transport']);
            $CymapgtLevel2Message = $loggerParams['swiftmailer_message'];
            $CymapgtLevel2Logger->pushHandler($CymapgtLevel2Stream);
            $CymapgtLevel2Logger->pushHandler(new SwiftMailerHandler($CymapgtLevel2Mailer, $CymapgtLevel2Message));
            self::$loggerLevel2 = $CymapgtLevel2Logger;
        }
        return self::$loggerLevel2;
    }
    
    /**
     * return the level3 logger
     * 
     *  @param  array  $loggerParams - Array of parameters to configure the level 3 logger
     * 
     *  @return object
     *  @private
     *  @static
     */      
    private static function _getLoggerLevel3($loggerParams) {
        if (!isset(self::$loggerLevel3)) {          
            /*create notifier object for africas talking. If you are behind a proxy, the
             *second parameter should be true, and your configuration array should contain
             *the proxy server settings
             */
            $notifierObj = new NotifierSmsAfricasTalkingService($loggerParams['notifier_params'], true);
            $recipientList = $loggerParams['notifier_recipients'];

            $CymapgtLevel3LogDir = $loggerParams['log_dir'];
            $CymapgtLevel3Stream = new StreamHandler($CymapgtLevel3LogDir, MonologLogger::ERROR);
            $CymapgtLevel3Logger = new MonologLogger('cymapgt_level3');
            $CymapgtLevel3Mailer  = \Swift_Mailer::newInstance($loggerParams['swiftmailer_transport']);
            $CymapgtLevel3Message = $loggerParams['swiftmailer_message'];        
            $CymapgtLevel3Logger->pushHandler($CymapgtLevel3Stream);
            $CymapgtLevel3Logger->pushHandler(new SwiftMailerHandler($CymapgtLevel3Mailer, $CymapgtLevel3Message));
            $smsHandler = new NotifierSmsAfricasTalkingServiceHandler($notifierObj);
            $smsHandler->setRecipients($recipientList);
            $CymapgtLevel3Logger->pushHandler($smsHandler);
            self::$loggerLevel3 = $CymapgtLevel3Logger;
        }
        return self::$loggerLevel3;
    }
    
    /**
     * return the security logger
     * 
     *  @param  array  $loggerParams - Array of parameters to configure the security logger
     * 
     *  @return object
     * @private
     *  @static
     */ 
    private static function _getLoggerSecurity($loggerParams) {
        if (!isset(self::$loggerSecurity)) {
            /*create notifier object for africas talking. If you are behind a proxy, the
                        *second parameter should be true, and your configuration array should contain
                        *the proxy server settings
                        */
            $notifierObj = new NotifierSmsAfricasTalkingService(($loggerParams['notifier_params']), ($loggerParams['notifier_params']['IS_BEHIND_PROXY']));
            $recipientList = $loggerParams['notifier_recipients'];

            $CymapgtSecurityLogDir = $loggerParams['log_dir'];
            $CymapgtSecurityStream = new StreamHandler($CymapgtSecurityLogDir, MonologLogger::ERROR);
            $CymapgtSecurityLogger = new MonologLogger('cymapgt_security');
            $CymapgtSecurityMailer  = \Swift_Mailer::newInstance($loggerParams['swiftmailer_transport']);
            $CymapgtSecurityMessage = $loggerParams['swiftmailer_message'];        
            $CymapgtSecurityLogger->pushHandler($CymapgtSecurityStream);
            $CymapgtSecurityLogger->pushHandler(new SwiftMailerHandler($CymapgtSecurityMailer, $CymapgtSecurityMessage));
            $smsHandler = new NotifierSmsAfricasTalkingServiceHandler($notifierObj);
            $smsHandler->setRecipients($recipientList);
            $CymapgtSecurityLogger->pushHandler($smsHandler);
            self::$loggerSecurity = $CymapgtSecurityLogger;
        }
        return self::$loggerSecurity;
    }    
}
