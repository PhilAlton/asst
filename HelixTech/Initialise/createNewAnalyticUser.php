<?php
require_once dirname(dirname(dirname(__FILE__))) . '/../bootstrap.php';

use Defuse\Crypto\KeyProtectedByPassword;
use Defuse\Crypto\Crypto;
use HelixTech\asstAPI\Query;


$userName = "steven.zhao..25@gmail..com";
$password = "axspa123";


$protected_key = KeyProtectedByPassword::createRandomPasswordProtectedKey($password);
$protected_key_encoded = $protected_key->saveToAsciiSafeString();

$password =	Crypto::encrypt(
                            password_hash(
                                base64_encode(hash('sha384', $password, true)),
                                PASSWORD_DEFAULT
                            ),
                            (KeyProtectedByPassword::loadFromAsciiSafeString($protected_key_encoded))->unlockKey($password)
						);

$query = new Query(INSERT, "INTO AdminTable (UserName, Password, UserKey)".
                "VALUES (:UserName, :Password, :UserKey)"
                );
$query->execute(SIMPLIFY_QUERY_RESULTS_ON,  [':UserName' => $userName, ':Password' => $password, ':UserKey' => $protected_key_encoded]);



?>