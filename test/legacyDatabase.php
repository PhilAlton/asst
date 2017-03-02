<?php

     /**
     * Query the database
     *
     * @param $query The query string
     * @return mixed The result of the mysqli::query() function
     */
    public function query($query) {
    
        // Query the database
                                                                    //$result = $dbhost -> query($query);
        $this->stmt = $this->dbhost->prepare($query);

        return $this->stmt;
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
        $this->stmt->bindValue($param, $value, $type);
    }
  


  //execute query
    public function execute(){
        return $this->stmt->execute();
    }
    //get results of query
    public function resultset(){
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    //get number of rows in results
    public function rowCount(){
        return $this->stmt->rowCount();
    }
    //returns single row of data
    public function single(){
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
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
        return $this->stmt->debugDumpParams();
    }




    /**
     * Fetch rows from the database (SELECT query)
     *
     * @param $query The query string
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



    /*LEGECY


    //create query                                              
    public function query($query){
        $this->stmt = $this->dbhost->prepare($query);
    }

  


    //bind params to query
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
        $this->stmt->bindValue($param, $value, $type);
    }
    //execute query
    public function execute(){
        return $this->stmt->execute();
    }
    //get results of query
    public function resultset(){
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    //get number of rows in results
    public function rowCount(){
        return $this->stmt->rowCount();
    }
    //returns single row of data
    public function single(){
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
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
        return $this->stmt->debugDumpParams();
    }
    public function encryptData($data){
    	return $data;
    }
    public function decryptData($data){
    	return $data;
    }


    */