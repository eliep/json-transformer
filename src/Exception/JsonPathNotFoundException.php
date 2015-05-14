<?php
/**
 * Created by PhpStorm.
 * User: elie
 * Date: 09/05/15
 * Time: 10:59
 */

namespace JsonTranformer\Exception;

class JsonPathNotFoundException extends \Exception {

  public function __construct($path, $json, $code = 404, Exception $previous = null) {
    $message = sprintf("\nThe path %s does not exist in json \n%s\n\n", $path, json_encode($json));
    parent::__construct($message, $code, $previous);
  }

}