<?php namespace HelixTech\asstAPI;

      use \PDO as PDO;


    /**
     * Summary of Database: class to manage connection to database via PDO
     */
    class Database {
        private $config;
        private $dbConnection;
        private $error;
        private $statement;

        /**
         * __construct - establish a connection to the database
         * Load config.ini file for database variables
         * 
         * 
         */
        public function __construct(){
        
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
            return $this->statement->execute();
        }



        /**
         * resultset - return the results of an executed query
         * @return mixed
         */
        public function resultset(){
            return $this->statement->fetchAll(PDO::FETCH_ASSOC);
        }



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
        public function lastInsertId(){return $this->dbh->lastInsertId();}
        public function beginTransaction(){return $this->dbh->beginTransaction();}
        public function endTransaction(){return $this->dbh->commit();}
        public function cancelTransaction(){return $this->dbh->rollBack();}
        public function debugDumpParams(){return $this->statement->debugDumpParams();}
        #endregion



    }
    ?>