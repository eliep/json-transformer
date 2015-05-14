<?php

/**
 * Created by PhpStorm.
 * User: elie
 * Date: 08/05/15
 * Time: 11:14
 */

namespace JsonTranformer;

use Peekmo\JsonPath\JsonStore;
use JsonTranformer\Exception\JsonPathNotFoundException;

class Json implements \JsonSerializable {

  private $jsonStore;
  private $isSingleValue = false;

  public function __construct($json = array()) {
    if (!is_array($json) && !is_object($json)) {
      $this->isSingleValue = true;
      $json = array($json);
    }
    $this->jsonStore = new JsonStore($json);
  }

  public function transform(callable $t) {
    return $t($this);
  }

  public function get($path, $default = null) {

    $isRootPath = JsonPathHelper::isJsonRoot($path);
    $value = ($isRootPath)
                ? $this->jsonStore->toArray()
                : $this->jsonStore->get($path);

    if (count($value) == 0 && is_null($default))
      throw new JsonPathNotFoundException($path, $this);

    else if (count($value) == 0)
      $json = new Json($default);

    else {
      $isDirectPath = JsonPathHelper::isDirectPath($path);
      $json = new Json( (!$isDirectPath || $isRootPath) ? $value : $value[0]);
    }

    return $json;
  }

  public function set($path, $value, $optional = false) {
    $set = true;
    if (JsonPathHelper::isJsonRoot($path)) {
      $this->jsonStore->setData($value);
    } else
      $set = $this->jsonStore->set($path, $value);

    if (!$set && !$optional)
      throw new JsonPathNotFoundException($path, $this);

    return $this;
  }


  public function remove($path, $default = null) {
    $removed = true;
    if (JsonPathHelper::isJsonRoot($path))
      $this->jsonStore->setData(array());
    else
      $removed = $this->jsonStore->remove($path);

    if (!$removed && is_null($default))
      throw new JsonPathNotFoundException($path, $this);

    return $this;
  }

  public function merge(Json $json) {
    $this->jsonStore->setData(array_merge(
      $this->toValue(),
      $json->toValue()
    ));
    return $this;
  }

  public function hasPath($path) {
    return (count($this->jsonStore->get($path)) > 0);
  }

  public function extractExistingPath($directPath) {
    $pathList = JsonPathHelper::extractSubpath($directPath);
    foreach ($pathList as $path => $subpath) {
      if ($this->hasPath($path)) {
        return array($path, $subpath);
      }
    }

    return array("$", $directPath);
  }


  public function toValue() {
    $arr = $this->jsonStore->toArray();
    return  ($this->isSingleValue) ? $arr[0] : $arr;
  }

  public function jsonSerialize() {
    return $this->toValue();
  }


}
