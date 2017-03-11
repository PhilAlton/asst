<?php

use Defuse\Crypto\KeyProtectedByPassword;
use Defuse\Crypto\Crypto;
use HelixTech\asstAPI\Query;


    $userName = "phil.alton@helix-tech.co.uk";
    $password = "Puzzl3d?";


    $protected_key = KeyProtectedByPassword::createRandomPasswordProtectedKey($password);
    $protected_key_encoded = $protected_key->saveToAsciiSafeString();

    $password =	Crypto::encrypt(
							    password_hash(
								    base64_encode(hash('sha384', $password, true)),
								    PASSWORD_DEFAULT
							    ),
                                (KeyProtectedByPassword::loadFromAsciiSafeString($protected_key_encoded))->unlockKey($password)
						    );

    echo $userName ."</br>". $password . "</br>" . $protected_key_encoded;
    $query = new Query(INSERT, "INTO AdminTable ".
                         "(Firstname, Surname, UserKey)".
                    "VALUES ".
                        "(:UserName, :Password, :UserKey)"
                    );

    $query->execute([':Username'=>$userName, ':Password'=>$password,':UserKey'=>$protected_key_encoded]);




?>