<?php


class Output{

	private static $output;
	private static $history;

	private static function getOutput(){return Output::$output;}
	private static function getHistory(){return Output::$history;}
	private static function setHistory($newOutput){
		Output::$history = Output::$history."</br>".$newOutput;
	}


	public static function setOutput($output){
		Output::setHistory(Output::$output);
		Output::$output = json_encode($output);
	}

	public static function go(){
		echo Output::getOutput();
		if (Output::getHistory() !== "</br>"){
			echo Output::getHistory();
		}
	}


}



?>