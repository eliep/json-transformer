<?php
/**
 * Created by PhpStorm.
 * User: elie
 * Date: 14/05/15
 * Time: 23:35
 */
use JsonTranformer\JsonPathHelper;

class JsonPathHelperTest extends PHPUnit_Framework_TestCase {


  private $path = array(
    "$" => array(
      "isJsonRoot" => 1, "isDirectPath" => 1, "createArrayFromPath" => array(), "extractSubpath" => array("$" => "$")
    ),
    "$.store.book[*].author " => array(
      "isJsonRoot" => 0, "isDirectPath" => 0, "createArrayFromPath" => null, "extractSubpath" => null
    ),
    "$..author" => array(
      "isJsonRoot" => 0, "isDirectPath" => 0, "createArrayFromPath" => null, "extractSubpath" => null
    ),
    "$.store.*" => array(
      "isJsonRoot" => 0, "isDirectPath" => 0, "createArrayFromPath" => null, "extractSubpath" => null
    ),
    "$.store..price" => array(
      "isJsonRoot" => 0, "isDirectPath" => 0, "createArrayFromPath" => null, "extractSubpath" => null
    ),
    "$..book[2]" => array(
      "isJsonRoot" => 0, "isDirectPath" => 0, "createArrayFromPath" => null, "extractSubpath" => null
    ),
    "$..book[(@.length-1)]" => array(
      "isJsonRoot" => 0, "isDirectPath" => 0, "createArrayFromPath" => null, "extractSubpath" => null
    ),
    "$..book[-1:]" => array(
      "isJsonRoot" => 0, "isDirectPath" => 0, "createArrayFromPath" => null, "extractSubpath" => null
    ),
    "$..book[0,1]" => array(
      "isJsonRoot" => 0, "isDirectPath" => 0, "createArrayFromPath" => null, "extractSubpath" => null
    ),
    "$..book[:2]" => array(
      "isJsonRoot" => 0, "isDirectPath" => 0, "createArrayFromPath" => null, "extractSubpath" => null
    ),
    "$..book[?(@.isbn)]" => array(
      "isJsonRoot" => 0, "isDirectPath" => 0, "createArrayFromPath" => null, "extractSubpath" => null
    ),
    "$..book[?(@.price<10)]" => array(
      "isJsonRoot" => 0, "isDirectPath" => 0, "createArrayFromPath" => null, "extractSubpath" => null
    ),
    "$.book" => array(
      "isJsonRoot" => 0, "isDirectPath" => 1,
      "createArrayFromPath" => array("book" => array()),
      "extractSubpath" => array("$.book" => "$")
    ),
    "$.book.author" => array(
      "isJsonRoot" => 0, "isDirectPath" => 1,
      "createArrayFromPath" => array("book" => array("author" => array())),
      "extractSubpath" => array("$.book" => "$.author", "$.book.author" => "$")
    ),
    "$.book[0].author" => array(
      "isJsonRoot" => 0, "isDirectPath" => 1,
      "createArrayFromPath" => array("book" => array( array("author" => array() ) ) ),
      "extractSubpath" => array("$.book[0]" => "$.author", "$.book[0].author" => "$")
    ),
    "$.book.author[0]" => array(
      "isJsonRoot" => 0, "isDirectPath" => 1,
      "createArrayFromPath" => array("book" => array("author" => array( array() )  ) ),
      "extractSubpath" => array("$.book" => "$.author[0]", "$.book.author[0]" => "$")
    )

  );

  public function testIsJsonRoot() {
    foreach ($this->path as $path => $results) {
      $this->assertEquals($results["isJsonRoot"], JsonPathHelper::isJsonRoot($path),
                          "isJsonRoot($path) must be ".$results["isJsonRoot"]);
    }
  }

  public function testIsDirectPath() {
    foreach ($this->path as $path => $results) {
      $this->assertEquals($results["isDirectPath"], JsonPathHelper::isDirectPath($path),
                          "isDirectPath($path) must be ".$results["isDirectPath"]);
    }

  }

  public function testCreateArrayFromPath() {
    foreach ($this->path as $path => $results) {

      if (!is_null($results["createArrayFromPath"])) {
        $this->assertEquals($results["createArrayFromPath"], JsonPathHelper::createArrayFromPath($path),
          "createArrayFromPath($path) must be " . json_encode($results["createArrayFromPath"]));

      } else if (!$results["isDirectPath"]) {
        try {
          $exceptionRaised = false;
          JsonPathHelper::createArrayFromPath($path);
        } catch (\JsonTranformer\Exception\DirectPathException $e) {
          $exceptionRaised = true;
        }
        $this->assertTrue($exceptionRaised,
          "$path is not a direct path, DirectPathException must be raised when calling createArrayFromPath");
      }
    }

  }

  public function testExtractSubpath() {
    foreach ($this->path as $path => $results) {

      if (!is_null($results["extractSubpath"])) {
        $this->assertEquals($results["extractSubpath"], JsonPathHelper::extractSubpath($path),
          "extractSubpath($path) must be " . json_encode($results["extractSubpath"]));

      } else if (!$results["isDirectPath"]) {
        try {
          $exceptionRaised = false;
          JsonPathHelper::extractSubpath($path);
        } catch (\JsonTranformer\Exception\DirectPathException $e) {
          $exceptionRaised = true;
        }
        $this->assertTrue($exceptionRaised,
          "$path is not a direct path, DirectPathException must be raised when calling extractSubpath");
      }
    }

  }
}
