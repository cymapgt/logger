<?php

namespace cymapgt\core\utility\logger;

use Monolog\Logger as MonologLogger;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-05-21 at 23:39:38.
 */
class LoggerTest extends \PHPUnit\Framework\TestCase {

   /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        
    }
    
    /**
     * Test the addHandler function by passing in configurations for a CSV handler7
     * 
     * @covers cymapgt\core\utility\logger\Logger::addLogHandler
     */
    public function testAddLogHandler() {
        //create random string, which we will as CSV File name
        $randomName = $this->generateRandomName();
        $randomNameFq = "files/$randomName.csv";
        
        //prepare configurations for the log handler
        $logHandlerConfig = array (
            $randomNameFq,
            MonologLogger::WARNING
        );
        
        //bootstrap the log handler
        $logHandlerBootstrap = array (
            'test_channel' => array (
                '\FemtoPixel\Monolog\Handler\CsvHandler' => array (
                    'handler_parameters' => $logHandlerConfig
                )
            )
        );
        
        //assert the random file doesnt exist
        $this->assertFileNotExists($randomNameFq);
        
        //instantiate cymapgt logger and process log
        $cgtLogger = new Logger();
        $cgtLogger->addLogHandler($logHandlerBootstrap, true);
        $concreteLogger = $cgtLogger->getLogger();
        
        //write the log
        $logMessage = "Sissoko for Balon d\'Or 2019";
        $concreteLogger->addWarning (
            $logMessage
        );
        
        //assert the file exists
        $this->assertFileExists($randomNameFq);
        
        //open file and assert first line is as logged
        $logFileInMemory = file($randomNameFq);
        $this->assertStringStartsWith("'Sissoko for Balon d\'Or 2019'", $logFileInMemory[0]);
        
        //destroy the testfile
        unlink($randomNameFq);
    }
    
    /**
     * Helper function to generate random 4 digit string
     * 
     * @return string
     */
    protected function generateRandomName() {
        $randomBytes = \openssl_random_pseudo_bytes(2);
        $randomName = bin2hex($randomBytes);
        return $randomName;
    }
}
