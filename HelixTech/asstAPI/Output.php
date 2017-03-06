<?php namespace HelixTech\asstAPI;
/**
 * @author Philip Alton
 * @copyright Helix Tech Ltd. 2017
 * @file Output.php
 * @package asstAPI
 */

/**
 * Class to handle buisness logic return variables,
 * Including sanitizing output, and error logging.
 */
class Output{

	/** @var mixed - $output - to be JSON encoded and returned to the client */
	private static $output;

	/** @var mixed - $history - to be outputed to error log file */
	private static $history;
    
	/** @var mixed - $error - to be ouputed to teh erro log file */
    private static $error;

	/** @return mixed getter Output::$output */
	private static function getOutput(){return Output::$output;}

    /** @return string getter Output:$history */
    private static function getHistory(){return Output::$history;}

    /** @return string getting Output::$error */
    private static function getError(){return Output::$error;}

    /**
     * Summary of setHistory
     * @param mixed $newOutput
     */
    private static function setHistory($newOutput){
		Output::$history = Output::$history."</br>".$newOutput;
	}

	/**
	 * Summary of setOutput
	 * @param mixed $output
	 */
	public static function setOutput($output){
		Output::setHistory(Output::$output);
		Output::$output = $output;
	}

    /**
     * Summary of errorMsg
     * @param mixed $errMsg
     */
    public static function errorMsg($errMsg){
        Output::$error = Output::$error."</br><b>".date("Y-m-d, H:i:s",time())." - </b>".$errMsg;

    }

	/**
	 * Summary of go - processes returns from buisness logic
     * Passes back a JSON encoded string in the HTTP body to the client with return data
     * Writes errors to the error log file
	 */
	public static function go(){
		if(!empty(Output::getOutput())){

            // sanaitize output to remove Unique ID from the output using preg_replace.
            $output = json_encode(Output::getOutput());
            $output = preg_replace('/":*UniqueID":"\w*",?/', "", $output);

            // return values sent to client in the HTTP body
            echo $output;
        }



        // Construct error log
        $errorLog = "</br>Connection from IP: <b>".$_SERVER['REMOTE_ADDR']."</b>"
                    ."</br>As User: <b>".(isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : 'ANON.')."</b>"
        ."</br>To: <b>".$_SERVER['REQUEST_METHOD']."</b> @ <b>".$_SERVER['REQUEST_URI']."</b>"
                    ."</br>At: <b>".date("Y-m-d, H:i:s", $_SERVER['REQUEST_TIME'])."</b>"
                    ."</br>".Output::getError();
		if (Output::getHistory() !== "</br>"){
			$errorLog = $errorLog."</br></br></br><b>History:</b></br>".Output::getHistory();
		}

        $errorLog = $errorLog."</br></br></br><b>-------------------------------------------------------------------------</b></br>";

        $errorLog_PATH = ($_SERVER['REMOTE_ADDR'] == "::1" ? 'C:\xampp\htdocs\errorlogs\asst' : realpath('/var/www/html'));

        // Write error log to log file
        file_put_contents(($errorLog_PATH).'/error.html', $errorLog, 10);

	}


}



?>