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
		
		// Resolve IP address into userful whois data
		$whois = "";

		// First look to who.is
		$filename = 'https://who.is/whois-ip/ip-address/'.Connection::getIP();
		$whois = file_get_contents($filename);

		// Grab just the improtant data and load into an array
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
		
		// If data is returned for Organization and NetName, then recored this
		$whois = "";
		if (isset($whoisAssoc['NetName']) and isset($whoisAssoc['Organization'])){
			$whois = $whoisAssoc['NetName'].", ".$whoisAssoc['Organization'];
		}

		// If returned data points to RIPE Network Coordination Centre (RIPE), 
		//	then search the RIPE database for the whois data
		if (strpos($whoisAssoc['Organization'], 'RIPE') !== false){

			// Build the IP addresses with ranges to look up
			//	This is because the RIPE database might not have the full IP registered, but some ipaddress
			//	range upstream of the connecting IP address
			$ipParts = explode('.', Connection::getIP());
			$ipRanges = Array(
				$ipParts[0].".".$ipParts[1].".".$ipParts[2].".".$ipParts[3]."/32",
				$ipParts[0].".".$ipParts[1].".".$ipParts[2].".0/24",
				$ipParts[0].".".$ipParts[1].".0.0/16",
				$ipParts[0].".0.0.0/8"
			);

			try{
				$filename = 'https://rest.db.ripe.net/RIPE/inetnum/94.197.121.52/8';//.Connection::getIP();
				$whois = file_get_contents($filename);
				var_dump($whois);
			} catch {
				

			}



			// If data is returned, then store this in the database
			if (isset($result)) { 
				$whois = "RESULT!";
				var_dump($result);
			}
		}



        $query = New Query(UPDATE, "ConnectionLog SET CXTN_ERRORS=:msg WHERE `CXTN_ID` =:cID");
        $query->silentexecute(SIMPLIFY_QUERY_RESULTS_ON,  [':msg' => AbstractLoggedException::$dbMessage, ':cID' => Connection::getCID()]);

		$query = New Query(UPDATE, "ConnectionLog SET CXTN_WHOIS=:whoIs WHERE `CXTN_ID` =:cID");
        $query->silentexecute(SIMPLIFY_QUERY_RESULTS_ON,  [':whoIs' => $whois, ':cID' => Connection::getCID()]);
    }

    public static function callSlack($message){
        // Instantiate with defaults, so all messages created
        // will be sent from 'Cyril' and to the #accounting channel
        // by default. Any names like @regan or #channel will also be linked.
		$message = "*".$message."*".
						  "\n"."Connection from IP: *".Connection::getIP()
                        ."*\nAs User: *".Connection::getUserName()
					    ."*\nTo: ".Connection::getMethod()." @ ".Connection::getURI();


        $settings = [
            'username' => 'asstapi',
            'channel' => '#asstapi-log',
            'link_names' => true
        ];

        $client = new Client('https://hooks.slack.com/services/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', $settings);

        //$client->send($message);
    }

}




?>