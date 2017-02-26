<?php


class Output{

	private static $output;
	private static $history;

	private static function getOutput(){return Output::$output;}
	private static function getHistory(){return Output::$history;}
	private static function setHistory($newOutput){
		Output::$history = Output::$history."/n".$newOutput;
	}


	public static function setOutput($output){
		Output::setHistory(Output::$output);
		Output::$output = json_encode($output);
	}

	public static function go(){
		//echo decrypt_output(Output::getOutput());
		echo Output::getOutput();
		if (Output::getHistory() !== "/n"){
			echo Output::getHistory();
		}
	}


}



?>