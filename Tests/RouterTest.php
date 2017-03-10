<?php

use HelixTech\asstAPI\Connection;
use HelixTech\asstAPI\Exceptions\InsecureConnection;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase{

    public function testConnectionCannotBeEstablishedByHTTPinSecurely(): void
    {        
        $this->expectException(InsecureConnection::class);
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = ''
        Connection::connect();


    }


}