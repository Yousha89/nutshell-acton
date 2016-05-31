<?php
/**
 * Created by PhpStorm.
 * User: ahsuoy
 * Date: 4/10/2016
 * Time: 4:12 PM
 */

class NutshellApiException extends Exception {
    protected $data;

    public function __construct($message, $code = 0, $data = NULL) {
        parent::__construct($message, $code);
        $this->data = $data;
    }

    public function getData() {
        return $this->data;
    }
}