<?php
/**
 * Created by PhpStorm.
 * User: elie
 * Date: 08/05/15
 * Time: 01:39
 */

require_once __DIR__ ."/../vendor/autoload.php";

use JsonTranformer\Transform;


class TransformTest extends PHPUnit_Framework_TestCase {


  public $testJson = [
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
  ];


  public function testPick() {
    $t = Transform::path("$.key2.key23")->pick();

    $res = $t->apply($this->testJson);

    $this->assertEquals(["alpha", "beta", "gamma"], $res);
  }


  public function testPickBranch() {
    $t = Transform::path("$.key2.key24.key241")->pickBranch();

    $res = $t->apply($this->testJson);

    $this->assertEquals([
                          "key2" => [
                            "key24" => [
                              "key241" => 234.123,
                            ]
                          ]
                        ], $res);
  }


  public function testCopyFrom() {
    $t = Transform::path("$.key25.key251")->copyFrom(Transform::path("$.key2.key21"));

    $res = $t->apply($this->testJson);

    $this->assertEquals([
      "key25" => [
        "key251" => 123
      ]
    ], $res);
  }


  public function testUpdate() {
    $t = Transform::path("$.key2.key24")->update(function ($jsonValue) {
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
          "key242" => "value242",
          "field243" => "coucou"
        ]
      ],
      "key3" => 234
    ], $res);
  }

  public function testInsert() {
    $t = Transform::path("$.keyB.keyB4")->insert(24);
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
      "keyB" => [
        "keyB4" => 24
      ],
      "key3" => 234
    ], $res);


    $t = Transform::path("$.key2.key24")->insert(24);
    $res = $t->apply($this->testJson);
    $this->assertEquals([
      "key1" => "value1",
      "key2" => [
        "key21" => 123,
        "key22" => true,
        "key23" => [ "alpha", "beta", "gamma"],
        "key24" => 24
      ],
      "key3" => 234
    ], $res);


    $t = Transform::path("$.key2.keyB4")->insert(24);
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
        ],
        "keyB4" => 24
      ],
      "key3" => 234
    ], $res);
  }

  public function testPut() {
    $t = Transform::path("$.key24.key241")->put(456);
    $res = $t->apply($this->testJson);
    $this->assertEquals([
        "key24" => [
          "key241" => 456,
        ]
      ], $res);

    $t = Transform::path("$.key24[0].key241")->put(456);
    $res = $t->apply($this->testJson);
    $this->assertEquals([
      "key24" => [[
        "key241" => 456,
      ]]
    ], $res);


    $t = Transform::path("$.key24[0].key241.key2411")->put(456);
    $res = $t->apply($this->testJson);
    $this->assertEquals([
      "key24" => [[
        "key241" => [
          "key2411" => 456
        ]
      ]]
    ], $res);


    $t = Transform::path("$.key24.key241[0]")->put(456);
    $res = $t->apply($this->testJson);
    $this->assertEquals([
      "key24" => [
        "key241" => [456]
      ]
    ], $res);
  }

  public function testPrune() {

    $t = Transform::path("$.key2.key22")->prune();


    $res = $t->apply($this->testJson);

    $this->assertEquals([
      "key1" => "value1",
      "key2" => [
        "key21" => 123,
        "key23" => [ "alpha", "beta", "gamma"],
        "key24" => [
          "key241" => 234.123,
          "key242" => "value242"
        ]
      ],
      "key3" => 234
    ], $res);

  }


  public function testPickAndUpdate() {

    $t = Transform::path("$.key2")->pickBranch(
      Transform::path("$.key21")->update(function ( $jsonValue) { return $jsonValue + 10; })
      ->andThen
      (Transform::path("$.key23")->update(function ( $jsonValue) { $jsonValue[] = "delta"; return $jsonValue; }))
      ->merge
      (Transform::path("$.key26")->put(false))
    );

    $res = $t->apply($this->testJson);


    $this->assertEquals([
      "key2" => [
        "key21" => 133,
        "key22" => true,
        "key23" => [ "alpha", "beta", "gamma", "delta"],
        "key24" => [
          "key241" => 234.123,
          "key242" => "value242"
        ],
        "key26" => false
      ],
    ], $res);
  }


  public function testPickAndPrune() {

    $t = Transform::path("$.key2")->pickBranch(
      Transform::path("$..key23")->prune()
    );

    $res = $t->apply($this->testJson);

    $this->assertEquals([
      "key2" => [
        "key21" => 123,
        "key22" => true,
        "key24" => [
          "key241" => 234.123,
          "key242" => "value242"
        ]
      ],
    ], $res);
  }

  public function testTransformGizmoToGremlin() {

    $gizmo = [
      "name" => "gizmo",
      "description" => [
        "features" => [ "hairy", "cute", "gentle"],
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

    $gremlin = [
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

    $t =
      Transform::path("$.name")->put("gremlin")->merge(
      Transform::path("$.description")->pickBranch(
        Transform::path("$.size")->update(function($jsonValue) { return $jsonValue * 3; })->merge(
        Transform::path("$.features")->put(["skinny", "ugly", "evil"]))->merge(
        Transform::path("$.danger")->put("always") )
      ) )->merge(
      Transform::path("$.hates")->copyFrom( Transform::path("$.loves") ) );

    $res = $t($gizmo);

    $this->assertEquals($gremlin, $res);
  }


  public function testUpdateWithRecursivePath() {
    $json1 = [
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
        "key12" => 23
      ],
      "key2" => [
        "key21" => [
          "key211" => 01,
          "key212" => 79
        ]
      ]
    ];

    $t = Transform::path("$.key1.key11")->update(Transform::path("$..p11"))->merge(
         Transform::path("$.key2.key21.key212")->prune() );


    $res = $t->apply($json1);

    $this->assertEquals([
      "key1" => [
        "key11" => [11, 21],
        "key12" => 23
      ],
      "key2" => [
        "key21" => [
          "key211" => 01        ]
      ]
    ], $res);


    $json1 = [
      "key1" => [
        "key11" => [
          [
            "p11" => 11,
            "p12" => 12,
            "p13" => 13
          ]
        ],
        "key12" => 23
      ],
      "key2" => [
        "key21" => [
          "key211" => 01,
          "key212" => 79
        ]
      ]
    ];

    $t = Transform::path("$.key1.key11")->update(Transform::path("$..p11"));


    $res = $t->apply($json1);

    $this->assertEquals([
      "key1" => [
        "key11" => [11],
        "key12" => 23
      ],
      "key2" => [
        "key21" => [
          "key211" => 01,
          "key212" => 79
        ]
      ]
    ], $res);

  }


  public function testRecursivePathUpdate() {
    $json1 = [
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
        "key12" => 23
      ],
      "key2" => [
        "key21" => [
          "key211" => 01,
          "key212" => 79
        ]
      ]
    ];

    $t = Transform::path("$.key1.key11[*].p11")->update(function($jsonValue) { return $jsonValue+10; });


    $res = $t->apply($json1);

    $this->assertEquals([
      "key1" => [
        "key11" => [
          [
            "p11" => 21,
            "p12" => 12,
            "p13" => 13
          ],
          [
            "p11" => 31,
            "p12" => 22,
            "p13" => 23
          ]
        ],
        "key12" => 23
      ],
      "key2" => [
        "key21" => [
          "key211" => 01,
          "key212" => 79
        ]
      ]
    ], $res);

  }
}

