<?php namespace HelixTech\asstAPI;
/**
 * @author Philip Alton
 * @copyright Helix Tech Ltd. 2017
 * @file Output.php
 * @package asstAPI
 *
 */

use HelixTech\asstAPI\Connection;
/**
 * Class to handle buisness logic return variables,
 * Including sanitizing output, and error logging.
 *
 */
class Output{

# region MemberVariables
	/** @var mixed - $output - to be JSON encoded and returned to the client */
	private static $output;
	/** @var mixed - $history - to be outputed to error log file */
	private static $history;
	/** @var mixed - $errorLog - to be ouputed to the error log file */
    private static $errorLog;
# endregion

#region Setters and Getters
	/** @return mixed getter Output::$output */
	private static function getOutput(){return Output::$output;}
    /** @return string getter Output:$history */
    private static function getHistory(){return Output::$history;}
    /** @return string getting Output::$error */
    private static function getError(){return Output::$error;}
	/** @return string getting Output::$errorLog */
    private static function getErrorLog(){return Output::$errorLog;}

    /** @param mixed $newOutput setter for Output::$history */
    private static function setHistory($newOutput){Output::$history = Output::$history."</br>".$newOutput;}
	/** @param mixed $output setter for Output::$output, which also sets $history via it's setter */
	public static function setOutput($output){Output::setHistory(Output::$output); Output::$output = $output;}
    /** @param mixed $errMsg setter for Output::$error */
    public static function errorMsg($errMsg){
		Output::$errorLog = Output::$errorLog."</br><b>"
							.date("Y-m-d, H:i:s",time())." - </b>"
							.$errMsg;
		Output::$error[] = $errMsg;
	}
#endregion


	/**
	 * Summary of Output::go - process output from buisness logic.
     * Passes back a JSON encoded string in the HTTP body to the client with return data,
     * Writes errors to the error log file.
	 * @return void
	 */
	public static function go(){
		if(!empty(Output::getOutput())){

            // sanaitize output to remove Unique ID from the output using preg_replace.
            $output = json_encode(Output::getOutput());
	//		$output = Connection::getAuthToken() ? '":AuthToken": "'.Connection::getAuthToken().'",'.$output : $output;
	//		var_dump($output);
            $output = preg_replace('/":*UniqueID":"\w*",?/', "", $output);
			$output = preg_replace('/":*Password":"\w*",?/', "", $output);

            // return values: sent to client in the HTTP body via echo.
            header('Content-Type: application/json');
            echo $output;

        }

        if(!empty(Output::getError())){
			// Send JSON'd error message to screen
			$errorOutput = json_encode(Array("Error" => Output::getError()));
			$errorOutput = preg_replace('/":*UniqueID":"\w*",?/', "", $errorOutput);
			$errorOutput = preg_replace('/":*Password":"\w*",?/', "", $errorOutput);

			echo $errorOutput;

            // Construct error log header with connection details:
		    $errorLog = "</br>Connection from IP: <b>".Connection::getIP()."</b>"
                        ."</br>As User: <b>".Connection::getUserName()."</b>"
					    ."</br>To: <b>".Connection::getMethod()."</b> @ <b>".Connection::getURI()."</b>"
                        ."</br>At: <b>".date("Y-m-d, H:i:s", Connection::getConnectionTime())."</b>"

		    // Then output the error log
		    ."</br>".Output::getErrorLog();
            //echo Output::getHistory();
		    // Add history after
            $errorLog = $errorLog."</br></br></br><b>History:</b></br>".Output::getOutput();
		    if (Output::getHistory() !== "</br>"){
			    $errorLog = $errorLog."</b></br>".Output::getHistory();
		    }

		    // Enclose entry to improve readability
            $errorLog = $errorLog."</br></br></br><b>-------------------------------------------------------------------------</b></br>";

            // Write error log to log file:
            $errorLog_PATH = ($_SERVER['REMOTE_ADDR'] == "::1" ? 'C:\xampp\htdocs\errorlogs\asst' : realpath('/var/www/html'));
			file_put_contents(($errorLog_PATH).'/error.html', $errorLog, 10);
        }

	}


}



?>