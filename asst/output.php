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
		Output::$output = $output;
		Output::setHistory($output);
	}

	public static function output(){
		echo Output::getOutput();
		echo Output::getHistory();
	}


}



?>