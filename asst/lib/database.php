<?php

class Database {
    private $config;
    private $dbConnection;
    private $error;
    private $statement;

    public function __construct(){

        if (!isset($this->dbConnection)){

            $this->config = parse_ini_file(realpath('../../../private/config.ini'));

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
        // e.g. $query='SELECT * table WHERE col=:param1'
        // bind(':param1',"value")
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



  //execute query
    public function execute(){
        return $this->statement->execute();
    }
    //get results of query
    public function resultset(){
        return $this->statement->fetchAll(PDO::FETCH_ASSOC);
    }
    //get number of rows in results
    public function rowCount(){
        return $this->statement->rowCount();
    }
    //returns single row of data
    public function single(){
        $this->execute();
        return $this->statement->fetch(PDO::FETCH_ASSOC);
    }
    public function lastInsertId(){
        return $this->dbh->lastInsertId();
    }
    public function beginTransaction(){
        return $this->dbh->beginTransaction();
    }
    public function endTransaction(){
        return $this->dbh->commit();
    }
    public function cancelTransaction(){
        return $this->dbh->rollBack();
    }
    public function debugDumpParams(){
        return $this->statement->debugDumpParams();
    }




    /**
     * Fetch rows from the database (SELECT query)
     *
     * @param $query - the query string
     * @return bool False on failure / array Database rows on success
     */
    public function select($query) {
        $rows = array();
        $result = $this -> query($query);
        if($result === false) {
            return false;
        }
        while ($row = $result -> fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * Fetch the last error from the database
     *
     * @return string Database error message
     */
    public function error() {
        $connection = $this -> connect();
        return $connection -> error;
    }

    /**
     * Quote and escape value for use in a database query
     *
     * @param string $value The value to be quoted and escaped
     * @return string The quoted and escaped string
     */
    public function quote($value) {
        $connection = $this -> connect();
        return "'" . $connection -> real_escape_string($value) . "'";
    }



}
?>