<?php namespace HelixTech\asstAPI;

    use \PDO as PDO;
    use HelixTech\asstAPI\{Output};


    /**
     * Summary of Database: class to manage connection to database via PDO
     */
    class Database {
        private $config;
        private $dbConnection;
        private $error;
        private $statement;

		private static $instance;


		public static function instance(){
			if (!isset(Database::$instance)){
				Database::$instance = new Database;
			}

			return Database::$instance;
        }
		
		
		/**
         * __construct - establish a connection to the database
         * Load config.ini file for database variables
         *
         *
         */
        private function __construct(){

            if (!isset($this->dbConnection)){
                $this->config = parse_ini_file(realpath('/var/www/private/config.ini'));

                // Set DSN
                $dsn = "mysql:host={$this->config['DB_HOST']};dbname={$this->config['DB_NAME']};charset=utf8mb4";

                // Set options
                $options = array(
                    PDO::ATTR_PERSISTENT    => false,
                    PDO::ATTR_ERRMODE       => PDO::ERRMODE_EXCEPTION
                );
                // new PDO instanace
                try{
                    $this->dbConnection = new PDO($dsn, $this->config['DB_USER'], $this->config['DB_PASSWORD'], $options);
                }
                catch(PDOException $err){
                    $this->error = $err->getMessage();
                    Output::errorMsg($this->error);
                }
            }
        }


         /**
         * Query the database
         *
         * @param $query - The query string
         * @return mixed The result of the mysqli::query() function
         */
        public function query($query) {

            // Query the database
                                                                        //$result = $dbConnection -> query($query);
            $this->statement = $this->dbConnection->prepare($query);

            return $this->statement;
        }






        //bind params to query


        /**
         * bind - bind params to a database query
         * @param mixed $param
         * @param mixed $value
         * @param mixed $type
         *
         * e.g. $query='SELECT * table WHERE col=:param1'
         * bind(':param1',"value")
         *
         */
        public function bind($param, $value, $type = null){
            if (is_null($type)) {
                switch (true) {
                  case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                  case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                  case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                  default:
                    $type = PDO::PARAM_STR;
                }
            }
            $this->statement->bindValue($param, $value, $type);
        }



        /**
         * Summary of execute
         * @return mixed
         */
        public function execute(){
            $results = null;
            try{ $results = $this->statement->execute();} 
            catch (\Exception $e) {
                Output::errorMsg("Exception thrown in execution of Query: ".$e->getMessage());
                http_response_code(500);
            }
            return $results;
        }

        /*
                public function execute(){
            return $this->statement->execute();
        }

        */



        /**
         * resultset - return the results of an executed query
         * @return mixed
         */
        public function resultset(){
            $results;
            try{ $results = $this->statement->fetchAll(PDO::FETCH_ASSOC);} 
            catch (\Exception $e) {
                Output::errorMsg("Exception thrown in fetching results of Query: ".$e->getMessage());
                http_response_code(500);
            }
            return $results;
        }

         /*
        public function resultset(){
            return $this->statement->fetchAll(PDO::FETCH_ASSOC);
        }
        */


        /**
         * single - return a single row of data
         * @return mixed
         */
        public function single(){
            $this->execute();
            return $this->statement->fetch(PDO::FETCH_ASSOC);
        }

        /**
         * rowCount - get the number of rows returned by a query
         * @return mixed
         */
        public function rowCount(){return $this->statement->rowCount();}


        #region Functions exposing underlying database and statement functions
        public function lastInsertId(){return $this->dbConnection->lastInsertId();}
        public function beginTransaction(){return $this->dbConnection->beginTransaction();}
        public function endTransaction(){return $this->dbConnection->commit();}
        public function cancelTransaction(){return $this->dbConnection->rollBack();}
        public function debugDumpParams(){return $this->statement->debugDumpParams();}
        public function setToFetchColumnsWithTableNames(){$this->dbConnection->setAttribute(PDO::ATTR_FETCH_TABLE_NAMES, true);}
        public function setToFetchColumnsWithoutTableNames(){$this->dbConnection->setAttribute(PDO::ATTR_FETCH_TABLE_NAMES, false);}
        #endregion



    }
?>