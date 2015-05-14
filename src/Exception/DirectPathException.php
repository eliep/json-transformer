<?php
/**
 * Created by PhpStorm.
 * User: elie
 * Date: 09/05/15
 * Time: 11:54
 */


namespace JsonTranformer\Exception;

class DirectPathException extends \Exception {

  public function __construct($path, $code = 500, Exception $previous = null) {
    $message = sprintf("\nDirect path expected but got: %s. A direct path must resolve to a single json branch, like $.doc.sub or $.doc[0].sub but not $..sub nor $.doc[*].sub", $path);
    parent::__construct($message, $code, $previous);
  }


}