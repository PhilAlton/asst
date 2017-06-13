<?php
CREATE TABLE `asstdb`.`cache` ( 
            `cacheID` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT , 
			//missing pages from ini
            `cacheLink` TEXT NOT NULL , `expiresOn` DATETIME NOT NULL , 
            `expired` TINYINT(1) NOT NULL , PRIMARY KEY (`cacheID`(11))
                                ) ENGINE = InnoDB; 

?>