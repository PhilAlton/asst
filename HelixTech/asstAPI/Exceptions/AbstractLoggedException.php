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
	private static $whoIs = "";

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
		// This should really point here! (to look up the managing reagion, and find the appropiate whois enpoint) : http://www.iana.org/assignments/ipv4-address-space/ipv4-address-space.xhtm
		$filename = 'https://who.is/whois-ip/ip-address/'.Connection::getIP();
		$whois = file_get_contents($filename);

		// Grab just the improtant data and load into an array
		$start = strpos($whois, '<div class="col-md-12 queryResponseBodyKey"');
		$end = strpos($whois, '</pre>', $start);
		$whois = substr($whois, $start+49, $end-$start-49);
		$whois = explode("\n", $whois);

		if (isset($whois[0]) and isset($whois[1])){
		//because we've done a lot of unstable as hell site scrapping!

			$whoisAssoc = Array();
			foreach ($whois as $who){
				$temp = explode(":",$who);
				if (isset($temp[1])){
					$whoisAssoc[$temp[0]] = $temp[1];
				}
			}		
		
			// If data is returned for Organization and NetName, then recored this
			if (isset($whoisAssoc['NetName']) and isset($whoisAssoc['Organization'])){
				$whois = $whoisAssoc['Organization'].", ".$whoisAssoc['NetName'];
			} else {
				$whoisAssoc['Organization'] = $whois[0];
				$whoisAssoc['NetName'] = $whois[1];
				$whois = $whoisAssoc['Organization'].", ".$whoisAssoc['NetName'];
			}

			// If returned data points to RIPE Network Coordination Centre (RIPE), 
			//	then search the RIPE database for the whois data
			if (strpos($whoisAssoc['Organization'], 'RIPE') !== false){

		
				$filename = 'https://apps.db.ripe.net/search/query.html?searchtext='.Connection::getIP().'#resultsAnchor';
				$whois = file_get_contents($filename);

				// If data is returned, then store this in the database

					// First isolate and grab the organisation name
				$start = strpos($whois, 'Responsible organisation:');
				$end = strpos($whois, '</a>', $start);
				$whoisAssoc['Organization'] = substr($whois, $start+135, $end-$start-135);
			
					// Then isolate and grab the NetName
				$start = $end;
				$end = strpos($whois, '</ul>', $start);
				$whois = substr($whois, $start, $end-$start);

				$start = strpos($whois, 'netname:');
				$end = strpos($whois, '</li>', $start);
				$whoisAssoc['NetName'] = substr($whois, $start+17, $end-$start-17);

				// Combine the data to be incorporated into the slack message
				if (isset($whoisAssoc['NetName']) and isset($whoisAssoc['Organization'])){
					$whois = $whoisAssoc['Organization'].", ".$whoisAssoc['NetName'];
				} else {
					$whois = "Unable to Resolve IP Address:";
				}
				
			}

		} else {
			$whois = "Unable to Resolve IP Address:";
		}



        $query = New Query(UPDATE, "ConnectionLog SET CXTN_ERRORS=:msg WHERE `CXTN_ID` =:cID");
        $query->silentexecute(SIMPLIFY_QUERY_RESULTS_ON,  [':msg' => AbstractLoggedException::$dbMessage, ':cID' => Connection::getCID()]);

		$query = New Query(UPDATE, "ConnectionLog SET CXTN_WHOIS=:whoIs WHERE `CXTN_ID` =:cID");
        $query->silentexecute(SIMPLIFY_QUERY_RESULTS_ON,  [':whoIs' => $whois, ':cID' => Connection::getCID()]);


		AbstractLoggedException::$whoIs = $whois;
    }

    public static function callSlack($message){
        // Instantiate with defaults, so all messages created
        // will be sent from 'Cyril' and to the #accounting channel
        // by default. Any names like @regan or #channel will also be linked.
		$message = "*".$message."*".
						  "\n"."Connection from IP: *".Connection::getIP()
						."*\nWho is: *".AbstractLoggedException::$whoIs
                        ."*\nAs User: *".connection::getUserName()
					    ."*\nTo: ".Connection::getMethod()." @ ".Connection::getURI();


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