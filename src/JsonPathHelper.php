<?php

/**
 * Created by PhpStorm.
 * User: elie
 * Date: 08/05/15
 * Time: 11:14
 */

namespace JsonTranformer;

use JsonTranformer\Exception\DirectPathException;
use Peekmo\JsonPath\JsonPath;


class JsonPathHelper  {


  public static function isJsonRoot($path) {
    return ($path == "$");
  }

  public static function isDirectPath($path) {
    return (
      strpos($path, "..") === false                   // no access through .. notation
    ) && (                                            // and
      preg_match("/\[.*\]/", $path) === 0             // no access through [] array notation
      || preg_match("/\[[0-9]+\]/", $path) !== 0      //   or else by specifying an array index
    ) && (                                            // and
      preg_match("/\*/", $path) === 0                 // no access through * array notation
    );
  }

  public static function createArrayFromPath($path = "$") {
    if (self::isJsonRoot($path))
      return array();


//    if ($path[0] != '$' || $path[1] != '.')
//      throw new DirectPathException();

//    else
    if (!self::isDirectPath($path))
      throw new DirectPathException($path);

    $path = substr($path, 2);

    $keys = explode(".", $path);

    $pathValue = $keys[0];
    unset($keys[0]);

    if (empty($pathValue))
      throw new \Exception();

    preg_match("/([^[]*)(\[(.*)\])?/", $pathValue, $pathToken);

    if (count($pathToken) > 2) {
      $pathValue = $pathToken[1];
      $pathIndex = $pathToken[3];
    }


    $jsonArray = array();
    $value = (count($keys) > 0)
                ? self::createArrayFromPath("$." . implode(".", $keys))
                : array();

    if (isset($pathIndex))
      $value = array( $pathIndex => $value);

    $jsonArray[$pathValue] = $value;

    return $jsonArray;
  }

  public static function extractSubpath($directPath) {
    if (self::isJsonRoot($directPath))
      return array("$" => "$");

    if (!self::isDirectPath($directPath))
      throw new DirectPathException($directPath);

    $directPath = substr($directPath, 2);

    $keys = explode(".", $directPath);
    $res = array();
    for ($i = 0; $i < count($keys); $i++) {
      $path = self::pathFromKeys(array_slice($keys, 0, $i+1));
      $subpath = self::pathFromKeys(array_slice($keys, $i+1));
      $res[$path] = $subpath;
    }

    return array_reverse($res, true);
  }

  private static function pathFromKeys($keys) {
    return (count($keys) > 0) ? "$.".implode(".", $keys) : "$";
  }

  public static function explode($jsonValue, $path) {
    $jsonPath = new JsonPath();
    $rawPathList = $jsonPath->jsonPath($jsonValue, $path, array("resultType" => "PATH"));
    return array_map(function($rawPath) { return self::toJsonPath($rawPath); }, $rawPathList);
  }

  private static function toJsonPath($rawPath) {
    return preg_replace(array("/\[[\"']/", "/[\"']\]/"), array(".", ""), $rawPath);
  }

}
