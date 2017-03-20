<?php namespace HelixTech\asstAPI;
/**
 * 
 *
 */

use HelixTech\asstAPI\{Connection, Query};

define("PAGESTEM", "https://axspa.org.uk/cache/asst/");
define("FILEPATH_STEM", ($_SERVER['REMOTE_ADDR'] == "::1" ? 
                                'C:\xampp\htdocs\cache\asst' : 
                                realpath('/var/www/html/cache/asst'))
        );
define("LINK_EXPIRE_TIME", 8);

 /**
  *
  *
  */
class Paginate{


    public static function create($data, $paginationLimit = 50){

        // Clear any expired links from the cache
        Paginate::deleteCache();
        
            
        // Pass the results of the SQL query through to the class constructor
        // And devide into chunks based on $paginationLimit.
        $data = array_chunk($data, $paginationLimit, true);

        // Construct page 2 ("page 1" data sent in initial request)
        // Contains and displays the second fifty data items
        // Contains and passes forward the complete data set
        // When loaded, will need to construct page 3
        //      $data[1]

        $pageRef = Connection::getUserName()."-".uniqid();  
        $pageNum = 1;

        //construct the initial paths
        $filePath = Paginate::createLink($pageRef, $pageNum);
        $filePath2 = Paginate::createLink($pageRef, ($pageNum+1));
        
        //update data with links to cache pages
        $data[0][] = array("Page" => PAGESTEM.$filePath);     
        $data[($pageNum+1)][] = array("Page" => PAGESTEM.$filePath2);
        
        $page = Paginate::loadTemplate($pageRef, $pageNum, $data);
        
        Paginate::writeFile($filePath, $page);


        // Store in database reference to cache
        $query = new Query(INSERT, "INTO cache (cacheLink, pages, expiresOn) ".
                        "VALUES (:pageRef, :pages, CURRENT_TIMESTAMP + INTERVAL ".LINK_EXPIRE_TIME." HOUR)");
        $query->execute([":pageRef" => $pageRef, ":pages" => count($data)]);


        // Return the first data set, now including a link to the next page
        return $data[0];
        
        
    }

    private static function createLink($pageRef, $pageNum){

        $suffix = "-".$pageNum.".php";   
        $filePath = $pageRef.$suffix;
        return $filePath;
    }


    private static function writeFile($filePath, $page){
    
        file_put_contents(FILEPATH_STEM.$filePath, $page, LOCK_EX);

    }




    private static function loadTemplate($pageRef, $pageNum, $data){
       
        return $pageLayout = 
            "<?php "
            ." "."require_once dirname(dirname(dirname(_FILE_))).'/asst/HelixTech/bootstrap.php';"
            ." "."require_once dirname(_FILE_).'/.php';"
            ." "."use HelixTech\asstAPI\{Connection, Pagination, Query};"
            ." "."Connection::connect();"
            ." "."try {"
                ." "."if (!Connection::isEstablished()){throw new ConnectionFailed;}"
                ." "."\$filePath = $pageRef;"
                ." "."\$nextPage = $pageNum + 1;"
                ." "."\$totalPages = ".count($data).";"
                //the dataset for this page must be updated 
                //  to contain the link of the following page
                ." "."echo ".json_encode($data[$pageNum]).";"
                ." "."if (\$nextPage <= \$totalPages){"
                    // Execute code to load the next page
                    ." "."\$allData=json_decode".json_encode($data).";" 
                    ." "."Pagination::loadNextPage(\$pageRef, \$nextPage, \$allData);"
                ." "."} else {"
                    // execute code to delete cached pages (update the DataBase)
                    ." "."\$query = New Query(UPDATE, 'cache '."
                                ." "."'SET expired=1 '."
                                ." "."'WHERE cacheLink = ".$pageRef."');"
		            ." "."return \$query->execute();"
                ." "."}"
            ." "."} catch (ConnectionFailed \$e) {"
                ." "."Output::errorMsg('Connection Failed: request terminated');"
            ." "."}"
            ." ?>"
            ;


    }


    private static function loadNextPage($pageRef, $pageNum, $data){
        //construct the path for the next page
        $filePath = Paginate::createLink($pageRef, $pageNum);
        
        //update data with links to cache pages
        $data[$pageNum][] = array("Page" => PAGESTEM.$filePath);     

        // create the next page
        $page = Paginate::loadTemplate($pageRef, $pageNum, $data);
        
        // write the next page to file
        Paginate::writeFile($filePath, $page);
    }


    private static function deleteCache(){
        // Update expired cache
        $query = New Query(UPDATE, "cache SET expired=1 ".
            "WHERE UNIX_TIMESTAMP(expiresOn) < UNIX_TIMESTAMP(CURRENT_TIMESTAMP)");
        $query->execute();

        // get all expired cache links
        $query = New Query(SELECT, "cacheLink, pages from cache where expired = 1");
        $expiredLinks = $query->execute();

        // loop through each link and remove the cache file
        foreach ($expiredLinks as $link){
            for ($i=1; $i <= $link['pages']; $i++){
                $file =  FILEPATH_STEM.Pagination::createLink($link['cacheLink'], $i);
                if (file_exists($file)){unlink($file);}
            }
        }

    }





}



?>