<?php
/**
 * Created by PhpStorm.
 * User: elie
 * Date: 08/05/15
 * Time: 01:39
 */

require_once __DIR__ ."/../vendor/autoload.php";

use JsonTranformer\Json;


class JsonTest extends PHPUnit_Framework_TestCase {


  public $testJson = [
    "key1" => [
      "key11" => [
        [
          "p11" => 11,
          "p12" => 12,
          "p13" => 13
        ],
        [
          "p11" => 21,
          "p12" => 22,
          "p13" => 23
        ]
      ],
      "key12" => [
        [
          "p11" => 11,
          "p12" => 12,
          "p13" => 13
        ]
      ]
    ],
    "key2" => [
      "key21" => 123,
      "key22" => true,
      "key23" => [ "alpha", "beta", "gamma"],
      "key24" => [
        "key241" => 234.123,
        "key242" => "value242"
      ],
      "key25" => ["alpha"]
    ],
    "key3" => 234
  ];

  public function testDirectPathMatchSingleValue() {
    $s = new Json($this->testJson);
    $res = $s->get("$.key2.key23")->toValue();
    $this->assertEquals(["alpha", "beta", "gamma"], $res);

    $s = new Json($this->testJson);
    $res = $s->get("$.key2.key25")->toValue();
    $this->assertEquals(["alpha"], $res);

    $s = new Json($this->testJson);
    $res = $s->get("$.key2.key21")->toValue();
    $this->assertEquals(123, $res);

    $s = new Json($this->testJson);
    $res = $s->get("$.key2.key24")->toValue();
    $this->assertEquals([
      "key241" => 234.123,
      "key242" => "value242"
    ], $res);

    $s = new Json($this->testJson);
    $res = $s->get("$.key1.key11[1].p11")->toValue();
    $this->assertEquals(21, $res);
  }


  public function testRecursivePathMatchSingleValue() {
    $s = new Json($this->testJson);
    $res = $s->get("$..key23")->toValue();
    $this->assertEquals([["alpha", "beta", "gamma"]], $res);

    $s = new Json($this->testJson);
    $res = $s->get("$..key25")->toValue();
    $this->assertEquals([["alpha"]], $res);

    $s = new Json($this->testJson);
    $res = $s->get("$..key21")->toValue();
    $this->assertEquals([123], $res);

    $s = new Json($this->testJson);
    $res = $s->get("$..key24")->toValue();
    $this->assertEquals([[
      "key241" => 234.123,
      "key242" => "value242"
    ]], $res);

    $s = new Json($this->testJson);
    $res = $s->get("$.key1.key12[*].p11")->toValue();
    $this->assertEquals(["11"], $res);
  }



  public function testRecursivePathMatchMultipleValue() {

    $s = new Json($this->testJson);
    $res = $s->get("$.key1.key11[*].p11")->toValue();
    $this->assertEquals(["11", "21"], $res);
  }

  public function testRootPath() {
    $s = new Json($this->testJson);
    $res = $s->get("$")->toValue();
    $this->assertEquals($this->testJson, $res);

    $s = new Json($this->testJson);
    $res = $s->set("$", [1, 2, 4])->toValue();
    $this->assertEquals([1, 2, 4], $res);


    $s = new Json($this->testJson);
    $res = $s->remove("$")->toValue();
    $this->assertEquals([], $res);
  }

}

