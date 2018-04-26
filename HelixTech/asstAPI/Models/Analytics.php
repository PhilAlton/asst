<?php namespace HelixTech\asstAPI\Models;

use HelixTech\asstAPI\{Connection, Query, Output};
use HelixTech\asstAPI\Exceptions\UnableToAuthenticateUserCredentials;

class Analytics{

    public static function display(){
       try{
           if (Connection::authenticate('AdminTable')){

           // build the data retrival queries
               $query = new Query(SELECT, "COUNT(DISTINCT CXTN_IP) FROM ConnectionLog");
               $numDistinctIP = $query->execute(SIMPLIFY_QUERY_RESULTS_ON);

               $query = new Query(SELECT, "COUNT(DISTINCT CXTN_USER) FROM ConnectionLog");
               $numDistinctUsers = $query->execute(SIMPLIFY_QUERY_RESULTS_ON);

               $timePeriod = time() - (7 * 24 * 60 * 60); //1 week
               $query = new Query(SELECT, "COUNT(*) FROM ConnectionLog WHERE UNIX_TIMESTAMP(CXTN_TIME) > $timePeriod");
               $numAPIRequestsINlastWeekPerDay = ($query->execute(SIMPLIFY_QUERY_RESULTS_ON))/7;

               $query = new Query(SELECT, "* FROM ConnectionLog" 
                                            ." WHERE CXTN_WHOIS IS NOT NULL" 
                                            ." ORDER BY CXTN_WHOIS, CXTN_IP, CXTN_USER"
                                    );
               $CnxtsByIP = $query->execute(SIMPLIFY_QUERY_RESULTS_ON);


            
               $query = new Query(SELECT, "UniqueID FROM UserTable");
               $uniqueIDArr = $query->execute(SIMPLIFY_QUERY_RESULTS_ON);
               //$cohortData = var_export($uniqueIDArr, TRUE);
               
               
               $cohortData = array();
               foreach ($uniqueIDArr as $uID){
                    $query = new Query(SELECT, "* FROM GEN_DATA_TABLE_".$uID['UniqueID']);
                    array_push($cohortData, $query->execute(SIMPLIFY_QUERY_RESULTS_OFF));
               }
               
               

                $cohorts = array();
                $firstCohort = 999999;
                $lastCohort = 0;

                foreach ($cohortData as $user){
                   
                    //asign the yyyymm of the earliest post ($user[?]["data"]) as $cohort
                    $cohort = 999999;
                    foreach ($user as $data){
                        $date = substr($data["Date"],0,4).substr($data["Date"],5,2);
                        if ((int)$date < $cohort){$cohort = (int)$date;}  
                    }
                    /*  orderedScores = sorted(score[1].keys())
                        cohort = datetime.datetime.fromtimestamp(int(orderedScores[0])/1000).strftime('%Y%m')*/

                    //if cohorts[cohort] is not set, then initialise 
                    if (!isset($cohorts[$cohort])){
                        $cohorts[$cohort] = Array("datapoints" => array(), "numofusers" => 0);
                    }
                  
                    $cohorts[$cohort]["numofusers"]++;
               
                    foreach ($user as $data){
                        $data = substr($data["Date"],0,4).substr($data["Date"],5,2);
                        $cohorts[$cohort]["datapoints"][$data] = isset($cohorts[$cohort]["datapoints"][$data]) ? $cohorts[$cohort]["datapoints"][$data] + 1 : 1;
                        
                        // compare $data with first cohort and last cohort to asign these values
                           if ((int)$data < $firstCohort){$firstCohort = (int)$data;} 
                           if ((int)$data > $lastCohort){$lastCohort = (int)$data;} 
                    }
               }
               

               $diffYearsPart = substr($lastCohort,0,4))-substr($firstCohort,0,4);
               $diffMonthsPart  = substr($lastCohort,5,2))-substr($firstCohort,5,2);
               $diffMonths = ($diffYearsPart * 12) + $diffMonthsPart + 1;
               
               $cohortArray = array();
               $i = 0;
               $month = int(substr($firstCohort,5,2));
               $year = int(substr($firstCohort,0,4));
               while ($i < $diffMonths){
                    while ($month <= 12){
                        if ($month < 10){$month = "0".$month;}
                        array_push($cohortArray, $year.$month);
                        $month++;
                        $i++;
                        if ($i >= $diffMonths){break;}
                    }
                    $month = 1;
                    $year++;
               }
                  
               $cohortData = "";
               foreach ($cohortArray as $cohort){
                    $cohortData.= "</br>";
                    $cohortData.= $cohort."- ";
                    $i = 0;
                    while ($i < $diffMonths){
                        if (isset($cohorts[$cohort])){
                            $cohortData.= ($i+1).": ";
                            
                            if (isset($cohorts[$cohort]["datapoints"][$cohortArray[$i]])){
                                $cohortData.= ($cohorts[$cohort]["datapoints"][$cohortArray[i]]/$cohorts[$cohort]["numofusers"]);
                            } else {
                                $cohortData.= "0.00";
                            }

                            $i++;
                        }
                            
                    }
               }
                   

                   
               





            // combine the quries and output as JSON via Output class
               $analyticResults = array(
                                    "DISTINCT_IP_COUNT" => $numDistinctIP,  
                                    "DISTINCT_USER_COUNT" => $numDistinctUsers, 
                                    "AVERAGE_REQUESTS" => $numAPIRequestsINlastWeekPerDay,
                                    "COHORT_DATA" => $cohortData,
                                    "DATA" => $CnxtsByIP);


             //  include $_Server.['DOCUMENT_ROOT/asst/HelixTech/Public/asstAPI/analytics.php'].'.php';
               Output::setOutput($analyticResults);



            } else {
                Output::setOutput('Invalid Username/Password Combination');
                $e = "Failed to validate UserName against Password";
                throw new UnableToAuthenticateUserCredentials($e);
            }
        } catch (UnableToAuthenticateUserCredentials $e){
            http_response_code(401);
            Output::errorMsg("Unable to authenticate: ".$e->getMessage().".");
        }

    }




}


?>