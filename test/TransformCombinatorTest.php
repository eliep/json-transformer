<?php
/**
 * Created by PhpStorm.
 * User: elie
 * Date: 14/05/15
 * Time: 19:33
 */


require_once __DIR__ ."/../vendor/autoload.php";

use JsonTranformer\Transform;


class TransformCombinatorTest extends PHPUnit_Framework_TestCase {

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
      "key23" => ["alpha", "beta", "gamma"],
      "key24" => [
        "key241" => 234.123,
        "key242" => "value242"
      ],
      "key25" => ["alpha"]
    ],
    "key3" => 234
  ];

  public function testMerge() {
    $t = Transform::path("$.key2.key21")->pickBranch()->merge(
         Transform::path("$.key3")->pickBranch()
    );
    $res = $t->apply($this->testJson);
    $this->assertEquals([
      "key2" => [
        "key21" => 123
      ],
      "key3" => 234
    ], $res);


    $t = Transform::path("$.key2.key21")->put(42)->merge(
      Transform::path("$.key3")->put(23)
    );
    $res = $t->apply($this->testJson);
    $this->assertEquals([
      "key2" => [
        "key21" => 42
      ],
      "key3" => 23
    ], $res);


    $t = Transform::path("$.key2.key21")->update(function($jsonValue) { return 42; })->merge(
         Transform::path("$.key3")->prune()->andThen(
         Transform::path("$.key1")->prune())
    );
    $res = $t->apply($this->testJson);
    $this->assertEquals([
      "key2" => [
        "key21" => 42,
        "key22" => true,
        "key23" => ["alpha", "beta", "gamma"],
        "key24" => [
          "key241" => 234.123,
          "key242" => "value242"
        ],
        "key25" => ["alpha"]
      ]
    ], $res);


    $t = Transform::path("$.key2")->merge(
        Transform::path("$.key3")->prune()->andThen(
        Transform::path("$.key1")->prune())
    );
    $res = $t->apply($this->testJson);
    $this->assertEquals([
      "key21" => 123,
      "key22" => true,
      "key23" => ["alpha", "beta", "gamma"],
      "key24" => [
        "key241" => 234.123,
        "key242" => "value242"
      ],
      "key25" => ["alpha"],
      "key2" => [
        "key21" => 123,
        "key22" => true,
        "key23" => ["alpha", "beta", "gamma"],
        "key24" => [
          "key241" => 234.123,
          "key242" => "value242"
        ],
        "key25" => ["alpha"]
      ]
    ], $res);
  }

  public function testAndThen() {
    $t = Transform::path("$.key2.key21")->pickBranch()->andThen(
         Transform::path("$.key3")->opt()->pickBranch()
    );
    $res = $t->apply($this->testJson);
    $this->assertEquals([
      "key3" => []
    ], $res);


    $t = Transform::path("$.key2.key21")->put(42)->andThen(
         Transform::path("$.key3")->put(23)
    );
    $res = $t->apply($this->testJson);
    $this->assertEquals([
      "key3" => 23
    ], $res);


    $t = Transform::path("$.key2.key21")->update(function($jsonValue) { return 42; })->andThen(
         Transform::path("$.key3")->prune()->andThen(
         Transform::path("$.key1")->prune())
    );
    $res = $t->apply($this->testJson);
    $this->assertEquals([
      "key2" => [
        "key21" => 42,
        "key22" => true,
        "key23" => ["alpha", "beta", "gamma"],
        "key24" => [
          "key241" => 234.123,
          "key242" => "value242"
        ],
        "key25" => ["alpha"]
      ]
    ], $res);


    $t = Transform::path("$.key2")->andThen(
         Transform::path("$.key3")->opt()->prune()->andThen(
         Transform::path("$.key1")->opt()->prune())
    );
    $res = $t->apply($this->testJson);
    $this->assertEquals([
      "key21" => 123,
      "key22" => true,
      "key23" => ["alpha", "beta", "gamma"],
      "key24" => [
        "key241" => 234.123,
        "key242" => "value242"
      ],
      "key25" => ["alpha"]
    ], $res);
  }

  public function testOrElse() {

    $t =  Transform::path("key1")->prune()->andThen(
          Transform::path("key3")->prune());
    $this->testJson = $t->apply($this->testJson);

    $addField = function ($jsonValue) {
      $jsonValue["field243"] = "update";
      return $jsonValue;
    };

    $t =  Transform::path("$.key2.key24")->update($addField)->orElse(
          Transform::path("$.key2.key24")->put(array("field243" => "put")));

    $res = $t->apply($this->testJson);
    $this->assertEquals([
      "key2" => [
        "key21" => 123,
        "key22" => true,
        "key23" => ["alpha", "beta", "gamma"],
        "key24" => [
          "key241" => 234.123,
          "key242" => "value242",
          "field243" => "update"
        ],
        "key25" => ["alpha"]
      ]
    ], $res);


    $t =  Transform::path("$.key2.keyB4")->update($addField)->orElse(
          Transform::path("$.key2.keyB4")->insert(array("field243" => "insert")));

    $res = $t->apply($this->testJson);
    $this->assertEquals([
      "key2" => [
        "key21" => 123,
        "key22" => true,
        "key23" => ["alpha", "beta", "gamma"],
        "key24" => [
          "key241" => 234.123,
          "key242" => "value242"
        ],
        "keyB4" => [
          "field243" => "insert"
        ],
        "key25" => ["alpha"]
      ]
    ], $res);

    $t =  Transform::path("$.keyB.keyB4")->update($addField)->orElse(
          Transform::path("$.key2.key24")->prune());

    $res = $t->apply($this->testJson);
    $this->assertEquals([
      "key2" => [
        "key21" => 123,
        "key22" => true,
        "key23" => ["alpha", "beta", "gamma"],
        "key25" => ["alpha"]
      ]
    ], $res);

    $t = Transform::path("$.keyB.keyB4")->opt()->update($addField)->orElse(
         Transform::path("$.keyB.keyB4")->put(array("field243" => "put")));

    $res = $t->apply($this->testJson);
    $this->assertEquals([
      "key2" => [
        "key21" => 123,
        "key22" => true,
        "key23" => ["alpha", "beta", "gamma"],
        "key24" => [
          "key241" => 234.123,
          "key242" => "value242"
        ],
        "key25" => ["alpha"]
      ]
    ], $res);

  }
}