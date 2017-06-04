<?php namespace HelixTech\asstAPI\Exceptions;

use Maknz\Slack\Client;
use HelixTech\asstAPI\{Query, Connection};
/**
 * Abstract class for exceptions which need logging
 * @todo error logging
 */
abstract class AbstractLoggedException extends \Exception
{

    public static $dbMessage = "";
    public static $errorMessage = "";

    public function __construct($message = "", $code = 0, Throwable $previous = NULL){

        // ensure proper logging of error
        self::$errorMessage = $message;
        $this->logError();
        parent::__construct($message, $code, $previous);

    }


    abstract public function logError();

    public static function log(){
		$whois = "";
		$filename = 'https://who.is/whois-ip/ip-address/'.Connection::getIP();
		$whois = file_get_contents($filename);

						echo($whois);
		$start = strpos($whois, '<div class="col-md-12 queryResponseBodyKey"');
		$end = strpos($whois, '</pre>', $start);
		$whois = substr($whois, $start+49, $end-$start-49);


		$whois = explode("\n", $whois);
		$whoisAssoc = Array();
		foreach ($whois as $who){
			$temp = explode(":",$who);
			if (isset($temp[1])){
				$whoisAssoc[$temp[0]] = $temp[1];
			}
		}		
		$whois = $whoisAssoc['NetName'].", ".$whoisAssoc['Organization'];

        $query = New Query(UPDATE, "ConnectionLog SET CXTN_ERRORS=:msg, CXTN_WHOIS=:whoIs WHERE `CXTN_ID` =:cID");
        $query->silentExecute([':msg' => AbstractLoggedException::$dbMessage, ':whoIs' => $whois, ':cID' => Connection::getCID()]);
    }

    public static function callSlack($message){
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