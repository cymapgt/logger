<?php
namespace cymapgt\core\utility\logger\handler;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use cymapgt\core\utility\notifier\NotifierSmsAfricasTalkingService;

class NotifierSmsAfricasTalkingServiceHandler extends AbstractProcessingHandler
{
    private $notifierObj;
    private $recipientList;

    public function __construct(NotifierSmsAfricasTalkingService $notifierObj, $level = Logger::DEBUG, $bubble = true)
    {
        $this->notifierObj = $notifierObj;
        parent::__construct($level, $bubble);
    }

    public function setRecipients($recipientList) {
        $this->recipientList = $recipientList;
        return $this;
    }
    
    protected function write(array $record)
    {
        $logMessage = array(
            'RECIPIENTS' => $this->recipientList,
            'MESSAGE'    => "You have a alert/critical log message. Level = {$record['level']}, Channel = {$record['channel']},
                             Message = {$record['formatted']}, Time = {$record['datetime']->format('Y-m-d H:i:s')}"
        );
                             
        $this->notifierObj->setMessage($logMessage);
        //die(print_r($this->notifierObj));
        $this->notifierObj->sendMessageOne(array());
    }
}
