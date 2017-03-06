<?php namespace HelixTech\asstAPI;
/**
 * @author Philip Alton
 * @copyright Helix Tech Ltd. 2017
 * @file Router.php
 * @package asstAPI
 *
 * @todo write class
 */


      /**
       * Summery of Router: class to map URL Endpoints to functions
       *
       *
       */
      class Router{

          /**
           * @code
           * 
           * in index.php:
           *    MapToEndpoint(VERB, URL)
           *
           * in Router.php:
           *
           *    Public Static Function MapToEndpoint($method, $url){
           *        for the first entity in the url (i.e. between / /){
           *
           *            Entity::Route($method, rest of url);
           *
           *    }
           *
           *
           * in entity class (eg User class)
           *    Public Static function Route($method, $rest of url ){
           *        Select
           *        case 2nd entity = x
           *        case 2nd entity = y
           *        default: 2ndEntity::Route($method, $url further redacted)
           * }
           *
           * @endcode
           *
           */




      }


?>