<?php


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
		echo json_encode(Output::getOutput());
        $errorLog = "</br>Connection from IP: <b>".$_SERVER['REMOTE_ADDR']."</b>"
                    ."</br>As User: <b>".(isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : 'ANON.')."</b>"
                    ."</br>To: <b>".$_SERVER['REQUEST_URI']."</b>"
                    ."</br>".Output::getError();
		if (Output::getHistory() !== "</br>"){
			$errorLog = $errorLog."</br></br></br>".Output::getHistory();
		}

        $errorLog = $errorLog."</br><b>-------------------------------------------------------------------------</b></br></br></br></br>";
        
        file_put_contents(realpath('/var/www/html').'/error.html', $errorLog, FILE_APPEND | LOCK_EX);

	}


}



?>