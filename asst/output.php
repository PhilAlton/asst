<?php


class Output{

	private static $output;
	private static $history;
    private static $error;

	private static function getOutput(){return Output::$output;}
	private static function getHistory(){return Output::$history;}
    private static function getError(){return Output::$error;}
	private static function setHistory($newOutput){
		Output::$history = Output::$history."/n".$newOutput;
	}


	public static function setOutput($output){
		Output::setHistory(Output::$output);
		Output::$output = $output;
	}

    public static function errorMsg($errMsg){
        Output::$error = Output::$error."/n".date("Y-m-d H:i:s",time()).": ".$errMsg;

    }

	public static function go(){
		echo json_encode(Output::getOutput());

        $errorLog = Output::getError();
		if (Output::getHistory() !== "/n"){
			$errorLog = $errorLog."/n/n/n".Output::getHistory();
		}


        file_put_contents(realpath('/var/www/html//').'error.txt', $errorLog);

	}


}



?>