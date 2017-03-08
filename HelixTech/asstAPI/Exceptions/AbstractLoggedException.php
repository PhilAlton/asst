<?php namespace HelixTech\asstAPI\Exceptions;

/**
 * Abstract class for exceptions which need logging
 * @todo error logging
 */
abstract class AbstractLoggedException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = NULL){

        // ensure proper logging of error
        $this->logError($message);
        parent::__construct($message, $code, $previous);

    }


    abstract public static function logError($message);
    
    public static function callSlack($message){
        echo "here";
        // Instantiate with defaults, so all messages created
        // will be sent from 'Cyril' and to the #accounting channel
        // by default. Any names like @regan or #channel will also be linked.
        $settings = [
            'username' => 'asstapi',
            'channel' => '#asstapi-log',
            'link_names' => true
        ];

        $client = new Client('https://hooks.slack.com/services/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', $settings);

        $client->send($message);


    }

}




?>