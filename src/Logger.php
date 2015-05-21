<?php
namespace cymapgt\core\utility\logger;

use cymapgt\Exception\LoggerException;
use Monolog\Logger as MonologLogger;
use cymapgt\core\utility\notifier\NotifierSmsAfricasTalkingService;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SwiftMailerHandler;
use cymapgt\core\utility\logger\handler\NotifierSmsAfricasTalkingServiceHandler;

/**
 * Logger
 * 
 * This class provides persistent access to logger, via encapsulating Monolog
 * 
 * This class is a Singleton
 * 
 * @author    - Cyril Ogana <cogana@gmail.com>
 * @package   - cymapgt.core.utility.logger
 * @copyright - CYMAP BUSINESS SOLUTIONS
 */
class Logger
{
    //instantiate properties
    private static $loggerStderr;
    private static $loggerLevel1;
    private static $loggerLevel2;
    private static $loggerLevel3;
    private static $loggerSecurity;
    
    /**
     *  Prevent direct object creation
     * 
     * @private
     * @final
     */
    final private function __construct() {
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
