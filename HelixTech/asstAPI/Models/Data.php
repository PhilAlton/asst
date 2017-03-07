<?php namespace HelixTech\asstAPI\Models;
    /**
    * @author Philip Alton
    * @copyright Helix Tech Ltd. 2017
    * @file Data.php
    * @package asstAPI
    *
    * @todo write sync methods
    */

    /**
    * Summary of Data
    */
    class Data {

    /**
     * @todo write method to recieve last 3 items and compare
     *
     * @todo write method to count dates as unique integers
     *
     * @todo write method to send and compare whole data list
     *
     *
     *
     *
     */


        /**
         * Summary of pushData: send a single data item to the server database
         * @param mixed $Data
         */
        public static function pushData($data){


        }


        /**
         * Summary of pullData: request a single data item from the server database
         * @param mixed $data
         * @return array $results
         */
        public static function pullData($data){
            $results = Array();



            return $results;
        }



        /**
         * Summary of checkDataConsistency:
         * @param mixed $count
         */
        public static function checkDataConsistency($count){


        }




	    /**
         * Summary of syncData:
	     */
	    public static function syncData(){
		    // method to get user data against timestamp and either update (call postData),
		    //	or withdraw (call pullData) any additional server data and pass back to user


        }










        /**
         * Summary of syncLastThree: check the last three records and sync where needed
         */
        public static function syncLastThree(){


        }

        /**
         * Summary of snycAllData: return
         */
        public static function pullAllData(){


        }


    }
?>