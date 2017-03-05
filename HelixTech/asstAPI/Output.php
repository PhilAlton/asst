<?php namespace HelixTech\asstAPI;

/**
 * Class to handle buisness logic return variables
 */
class Output{

	private static $output;
	private static $history;
    private static $error;

	private static function getOutput(){return Output::$output;}
	private static function getHistory(){return Output::$history;}
    private static function getError(){return Output::$error;}
	private static function setHistory($newOutput){
		Output::$history = Output::$history."</br>".$newOutput;
	}


	public static function setOutput($output){
		Output::setHistory(Output::$output);
		Output::$output = $output;
	}

    public static function errorMsg($errMsg){
        Output::$error = Output::$error."</br><b>".date("Y-m-d, H:i:s",time())." - </b>".$errMsg;

    }

	public static function go(){
		if(!empty(Output::getOutput())){
            $output = json_encode(Output::getOutput());
            $uIDcount = substr_count($output, "UniqueID");

            for ($i = 0; $i < $uIDcount; $i++){
                $start = strpos($output, "UniqueID")-1;
                $sub = substr($output, $start);
                $end = strpos($sub, '",')+1;
                $uIDstr = substr($output, $start, $end);

                $output = str_replace($uIDstr, "", $output);
            }

            echo $output;
        }

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
        file_put_contents(($errorLog_PATH).'/error.html', $errorLog, 10);

	}


}



?>