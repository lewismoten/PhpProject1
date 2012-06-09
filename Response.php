<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Response
 *
 * @author developer
 */
class Response {
    public $errorMessage;
    public $errorNumber;
    public $success;
    public $content;

    public static function AsException($number, $message)
    {
        $response = new Response();
        $response->success = FALSE;
        $response->errorMessage = $message;
        $response->errorNumber = $number;
        return $response;
    }
    public static function AsContent($content)
    {
        $response = new Response();
        $response->success = TRUE;
        $response->content = $content;   
        return $response;
    }
}

?>
