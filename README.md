# logger

Wrapper for Monolog library. Implements custom log levels out of the box. 
Based on the log importance, custom logger levels allow logging to file, email,
sms or a combination of either /all

## Description

The logger package stands on the shoulders of giants; Monolog library is PHP's
de facto logger which supports multiple channels, as well as building
of custom channels, as has been done with the SmsChannel of the cymapgt logger.
As such, most of the logger implementation is dedicated to configuration, with
the heavy logging work being delegated to the elegant Monolog.

## Installing

### Install application via Composer

    require "cymapgt/logger" : "^1.0.0"

## Usage

### Overview

The logger package implements the following Logger strategy:

-Stderr Logger: This can be loaded in order to provide Stream Logging capability for CLI applications

-Logger Level1: This application will provide basic logging services to just the default destination.
The default destination for this logger is usually either a file or a database. As such, the log directory
will have to be provided. Another option is database, but this is not implemented for now.

-Logger Level 2: This will still provide logging services to the log file as well as to email. It is utilized for log items that need more immediate attention.

-Logger Level 3: This will provide logging services to the log file, to email as well as to SMS.
The idea behind the strategy is to enable graceful escalation in the event that an application may
need to provide a more responsive log message by gradually choosing which log level to use.

-Logger Security: The security logger will provide security related logs to a separate secure file

-Logger Alert: This is still in design, and is an escalated level of the Logger Security class

### Using the Logger package

#### Named Constants
    //Log levels for Logger Class
    if (!(defined('LOGGER_STDERR'))) {
        define('LOGGER_STDERR', 0);
    }

    if (!(defined('LOGGER_LEVEL1'))) {
        define('LOGGER_LEVEL1', 1);
    }

    if (!(defined('LOGGER_LEVEL2'))) {
        define('LOGGER_LEVEL2', 2);
    }

    if (!(defined('LOGGER_LEVEL3'))) {
        define('LOGGER_LEVEL3', 3);
    }

    if (!(defined('LOGGER_SECURITY'))) {
        define('LOGGER_SECURITY', 4);
    }

#### Bootstraping the Logger

You need to have the bootstrap package, which you will use to set up the [logger]
group configurations for this package in the file cymapgt.network.ini; for example.

See cymapgt/bootstrap documentation for how to run this configuration.

    [logger]
    logger_file_location = '/var/www/html/logger_test.txt'
    logger_security_file_location = '/var/www/html/logger_security.txt'
    logger_email_alert_administrators = '{"cogana@gmail.com": "Cyril Ogana"}'
    logger_sms_alert_administrators = '254123456789'
    logger_email_alert_subject = 'SYSTEM IMPORTANT LOG MESSAGE ALERT'
    logger_email_security_alert_administrators = '{"cogana@gmail.com": "Cyril Ogana"}'
    logger_sms_security_alert_administrators = '254123456789'
    logger_email_security_alert_subject = 'SYSTEM IMPORTANT LOG MESSAGE ALERT'

#### Logging Example

    use cymapgt\core\utility\bootstrap\Bootstrap;
    use cymapgt\core\utility\logger\Logger;

    $loggerDbError = Logger::getLogger(\LOGGER_SECURITY, Bootstrap::LoggerSecurityParams());
    $loggerDbError->addError('Critical error, the core database is down'); //log to file, email and sms

### Testing

PHPUnit Tests are provided with the package

### Contribute

* Email @rhossis or contact via Skype
* You will be added as author for contributions

### License

PROPRIETARY
