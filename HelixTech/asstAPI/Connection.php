<?php namespace HelixTech\asstAPI;
/** File to handle data input, and sanitization / filtering
 *  Includes class representing the HTTP connection to the server
 *
 * @author Philip Alton
 * @copyright Helix Tech Ltd. 2017
 * @file Connection.php
 * @package asstAPI
 *
 * @todo write connection class
 *
 */


/**
 * Connection class to register, store and error log connection details
 * Sanitize input through connection class
 */
class Connection{

    public function __construct(){

        "</br>Connection from IP: <b>".$_SERVER['REMOTE_ADDR']."</b>"
        ."</br>As User: <b>".(isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : 'ANON.')."</b>"
		."</br>To: <b>".$_SERVER['REQUEST_METHOD']."</b> @ <b>".$_SERVER['REQUEST_URI']."</b>"
        ."</br>At: <b>".date("Y-m-d, H:i:s", $_SERVER['REQUEST_TIME'])."</b>"

    }


}




?>