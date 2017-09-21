<?php namespace HelixTech\asstAPI;
/**
 * 
 *
 */

use HelixTech\asstAPI\{Connection, Query};
use HelixTech\asstAPI\Exceptions\{UnableToAuthenticateUserCredentials};

define("PAGESTEM", "https://axspa.org.uk/asst/Cache/");
define("FILEPATH_STEM", ($_SERVER['REMOTE_ADDR'] == "::1" ? 
                                'C:\xampp\htdocs\cache\asst\\' : 
                                realpath($_SERVER['DOCUMENT_ROOT'].'/cache/asst'))
        );
define("LINK_EXPIRE_TIME", 8);

 /**
  *
  *
  */
class Paginate{

	public static function retrieve($UserName, $cachefile){
		try{
			if (Connection::authenticate()){
				include(FILEPATH_STEM.'/'.$cachefile);

		    } else {
			    Output::setOutput('Invalid Username\Password Combination');
                $e = "Failed to validate UserName against Password";
                throw new UnableToAuthenticateUserCredentials($e);
		    }
        } catch (UnableToAuthenticateUserCredentials $e){
            http_response_code(401);
            Output::errorMsg("Unable to authenticate: ".$e->getMessage().".");

        }

	}


    public static function create($data, $paginationLimit = 50){

        // Clear any expired links from the cache
        Paginate::deleteCache();
        
            
        // Pass the results of the SQL query through to the class constructor
        // And devide into chunks based on $paginationLimit.
        $data = array_chunk($data, $paginationLimit, true);

        // Construct page 1 ("page 0" data sent in initial request)
        // Contains and displays the second fifty data items
        // Contains and passes forward the complete data set
        // When loaded, will need to construct page 2
        //      $data[1]

        $pageRef = Connection::getUserName()."-asstAPIcache-".uniqid();  
        $pageNum = 1;


		//construct all the file paths and update data to include the links to the next page
		foreach ($data as $dataKey => $dataItem){
			$filePath = ($dataKey+1) < count($data) ? $filePath = PAGESTEM.Paginate::createLink($pageRef, $dataKey+1) : null;
			$data[$dataKey] = array_merge($data[$dataKey], array("Page" => $filePath));
		}

		//create the next page
		Paginate::loadNextPage($pageRef, $pageNum, $data);

        // Store in database reference to cache
        $query = new Query(INSERT, "INTO cache (cacheLink, pages, expiresOn) ".
                        "VALUES (:pageRef, :pages, CURRENT_TIMESTAMP + INTERVAL ".LINK_EXPIRE_TIME." HOUR)");
        $query->execute(SIMPLIFY_QUERY_RESULTS_ON,  [":pageRef" => $pageRef, ":pages" => count($data)]);


        // Return the first data set, now including a link to the next page
        return $data[0];
        
        
    }

    private static function createLink($pageRef, $pageNum){

        $suffix = "-".$pageNum.".php";   
        $filePath = $pageRef.$suffix;
        return $filePath;
    }


    private static function writeFile($filePath, $page){
    
        file_put_contents(FILEPATH_STEM.'/'.$filePath, $page, LOCK_EX);

    }




    private static function loadTemplate($pageRef, $pageNum, $data){
       
        return $pageLayout = 
            "<?php "
			// Link cache file to the API files
            ."\n "."require_once dirname(dirname(dirname(__FILE__))).'/asst/HelixTech/bootstrap.php';"
            ."\n "."use HelixTech\asstAPI\{Connection, Paginate, Query, Output};"
			."\n "."use HelixTech\asstAPI\Exceptions\{ConnectionFailed, AttemptedToAccessUnauthorisedResources};"
            ."\n "."try {"
				// Ensure Connection is to a valid resource
				."\n "."if (explode('-asstAPIcache-','$pageRef')[0]<>Connection::getUserName()){"
					."\n "."throw new AttemptedToAccessUnauthorisedResources;"
				."\n "."}"
				// Create page specific parameters
				."\n "."\$filePath = '$pageRef';"
				."\n "."\$nextPage = $pageNum + 1;"
				."\n "."\$totalPages = ".count($data).";"
				// Output the next set of results
				."\n "."\$result=json_decode('".json_encode($data[$pageNum])."');"
				."\n "."if (\$nextPage < \$totalPages){"
					// Either execute code to load the next page
					."\n "."\$allData=json_decode('".json_encode($data)."');" 
					."\n "."Paginate::loadNextPage('$pageRef', \$nextPage, \$allData);"
				."\n "."} else {"
					// Or execute code to delete cached pages (update the DataBase)
					."\n "."\$query = New Query(UPDATE, 'cache '."
								."\n "."'SET expired=1 '."
								."\n "."'WHERE cacheLink = :pageRef');"
					."\n "."\$query->silentexecute(SIMPLIFY_QUERY_RESULTS_ON,  [':pageRef' => '$pageRef']);"
				."\n "."}"
				// Flush the output
				."\n "."Output::setOutput(\$result);"
			// Handle Errors
            ."\n "."} catch (AttemptedToAccessUnauthorisedResources \$e){"
				."\n "."Output::errorMsg('User details do not match requested resources');"
			."\n "."}"
            ."\n ?>"
            ;


    }


    private static function loadNextPage($pageRef, $pageNum, $data){
        //construct the path for the next page
        $filePath = Paginate::createLink($pageRef, $pageNum);

        // create the next page
        $page = Paginate::loadTemplate($pageRef, $pageNum, $data);
        
        // write the next page to file
        Paginate::writeFile($filePath, $page);
    }


    private static function deleteCache(){
        // Update expired cache
        $query = New Query(UPDATE, "cache SET expired=1 ".
            "WHERE UNIX_TIMESTAMP(expiresOn) < UNIX_TIMESTAMP(CURRENT_TIMESTAMP)");
        $query->execute(1);

        // get all expired cache links
        $query = New Query(SELECT, "cacheLink, Pages from cache where expired = 1");
        $expiredLinks = $query->execute(1);
        // loop through each link and remove the cache file
        foreach ($expiredLinks as $link){
            for ($i=1; $i <= $link['Pages']; $i++){
                $file =  FILEPATH_STEM."/".Paginate::createLink($link['cacheLink'], $i);
                if (file_exists($file)){unlink($file);}
				$query = New Query(DELETE, 'FROM `cache` WHERE `cacheLink` =:cacheLink');
				$query->execute(SIMPLIFY_QUERY_RESULTS_ON,  [':cacheLink' => $link['cacheLink']]);
            }
        }

    }





}



?>