<?php
/**
 * Created by PhpStorm.
 * User: elie
 * Date: 08/05/15
 * Time: 01:39
 */

require_once __DIR__ ."/../vendor/autoload.php";

use JsonTranformer\Transform;

class TransformValidationTest extends PHPUnit_Framework_TestCase {

  public $testJson = [
    "key1" => "value1",
    "key2" => [
      "key21" => 123,
      "key22" => true,
      "key23" => ["alpha", "beta", "gamma"],
      "key24" => [
        "key241" => 234.123,
        "key242" => "value242"
      ]
    ],
    "key3" => 234
  ];

  public $gizmo = [
    "name" => "gizmo",
    "description" => [
      "features" => ["hairy", "cute", "gentle"],
      "size" => 10,
      "sex" => "undefined",
      "life_expectancy" => "very old",
      "danger" => [
        "wet" => "multiplies",
        "feed after midnight" => "becomes gremlin"
      ]
    ],
    "loves" => "all"
  ];

  public $gremlin = [
    "name" => "gremlin",
    "description" => [
      "features" => ["skinny", "ugly", "evil"],
      "size" => 30,
      "sex" => "undefined",
      "life_expectancy" => "very old",
      "danger" => "always"
    ],
    "hates" => "all"
  ];

  /**
   * If a path do not exist, pick must throw a JsonPathNotFoundException
   */
  public function testPickWithWrongKey() {
    $t = Transform::path("$.keyB.keyB3")->opt()->pick();
    $res = $t->apply($this->testJson);
    $this->assertEquals([], $res);


    $t = Transform::path("$.keyB.keyB3")->pick();
    try {
      $exceptionRaised = false;
      $res = $t->apply($this->testJson);
    } catch (\JsonTranformer\Exception\JsonPathNotFoundException $e) {
      $exceptionRaised = true;
    }
    $this->assertTrue($exceptionRaised);
  }


  /**
   * If a path do not exist, pickBranch must throw a JsonPathNotFoundException
   */
  public function testPickBranchWithWrongKey() {
    $t = Transform::path("$.keyB.keyB4.keyB41")->opt()->pickBranch();
    $res = $t->apply($this->testJson);
    $this->assertEquals([
      "keyB" => [
        "keyB4" => [
          "keyB41" => []
        ]
      ]
    ], $res);

    $t = Transform::path("$.keyB.keyB4.keyB41")->pickBranch();
    try {
      $exceptionRaised = false;
      $res = $t->apply($this->testJson);
    } catch (\JsonTranformer\Exception\JsonPathNotFoundException $e) {
      $exceptionRaised = true;
    }
    $this->assertTrue($exceptionRaised);

    $t = Transform::path("$.key2.key24.key241[*]")->pickBranch();
    try {
      $exceptionRaised = false;
      $res = $t->apply($this->testJson);
    } catch (\JsonTranformer\Exception\DirectPathException $e) {
      $exceptionRaised = true;
    }
    $this->assertTrue($exceptionRaised);
  }

  /**
   * If the path to copy from do not exist, copyFrom must throw a JsonPathNotFoundException
   *
   */
  public function testCopyFrom() {
    $t = Transform::path("$.key25.key251")->copyFrom((new Transform("$.keyB.keyB1"))->opt()->pick());
    $res = $t->apply($this->testJson);
    $this->assertEquals(["key25" => ["key251" => []]], $res);

    $t = Transform::path("$.key25.key251")->copyFrom((new Transform("$.keyB.keyB1"))->pick());
    try {
      $exceptionRaised = false;
      $res = $t->apply($this->testJson);
    } catch (\JsonTranformer\Exception\JsonPathNotFoundException $e) {
      $exceptionRaised = true;
    }
    $this->assertTrue($exceptionRaised);

    $t = Transform::path("$.key25[*].key251")->copyFrom((new Transform("$.keyB.keyB1"))->pick());
    try {
      $exceptionRaised = false;
      $res = $t->apply($this->testJson);
    } catch (\JsonTranformer\Exception\DirectPathException $e) {
      $exceptionRaised = true;
    }
    $this->assertTrue($exceptionRaised);
  }

  /**
   * If the path to copy from do not exist, copyFrom must throw a JsonPathNotFoundException
   */
  public function testUpdate() {
    $t = Transform::path("$.keyB.keyB4")->opt()->update(function ($jsonValue) {
      $jsonValue["field243"] = "coucou";
      return $jsonValue;
    });
    $res = $t->apply($this->testJson);
    $this->assertEquals([
      "key1" => "value1",
      "key2" => [
        "key21" => 123,
        "key22" => true,
        "key23" => [ "alpha", "beta", "gamma"],
        "key24" => [
          "key241" => 234.123,
          "key242" => "value242"
        ]
      ],
      "key3" => 234
    ], $res);


    $t = Transform::path("$.keyB.keyB4")->update(function ($jsonValue) {
      $jsonValue["field243"] = "coucou";
      return $jsonValue;
    });
    try {
      $exceptionRaised = false;
      $res = $t->apply($this->testJson);
    } catch (\JsonTranformer\Exception\JsonPathNotFoundException $e) {
      $exceptionRaised = true;
    }
    $this->assertTrue($exceptionRaised);
  }


  public function testPut() {
    $t = Transform::path("$.key24.key241")->opt()->put(456);
    $resA = $t->apply($this->testJson);
    $t = Transform::path("$.key24.key241")->put(456);
    $resB = $t->apply($this->testJson);

    $this->assertEquals($resA, $resB);


    $t = Transform::path("$.key25[*].key251")->put(456);
    try {
      $exceptionRaised = false;
      $res = $t->apply($this->testJson);
    } catch (\JsonTranformer\Exception\DirectPathException $e) {
      $exceptionRaised = true;
    }
    $this->assertTrue($exceptionRaised);
  }


  public function testPrune() {

    $t = Transform::path("$.keyB.keyB2")->opt()->prune();
    $res = $t->apply($this->testJson);
    $this->assertEquals($this->testJson, $res);

    $t = Transform::path("$.keyB.keyB2")->prune();
    try {
      $exceptionRaised = false;
      $res = $t->apply($this->testJson);
    } catch (\JsonTranformer\Exception\JsonPathNotFoundException $e) {
      $exceptionRaised = true;
    }
    $this->assertTrue($exceptionRaised);
  }
}