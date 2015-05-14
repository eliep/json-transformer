# Json Transformer
A library to transform json data in PHP.

This library is inspired from [Scala JSON transformers](https://www.playframework.com/documentation/2.1.0/ScalaJsonTransformers)
and use [JsonPath](https://github.com/Peekmo/JsonPath) to access json properties.

## Gizmo to Gremlin Example
```php

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

    $transform =
      Transform::path("$.name")->put("gremlin")->merge(
      Transform::path("$.description")->pickBranch(
        Transform::path("$.size")->update(function($jsonValue) { return $jsonValue * 3; })->merge(
        Transform::path("$.features")->put(["skinny", "ugly", "evil"]))->merge(
        Transform::path("$.danger")->put("always") )
      ) )->merge(
      Transform::path("$.hates")->copyFrom( Transform::path("$.loves") ) );

    $gremlin = $transform($gizmo);

    /**
    $gremlin == [
      "name" => "gremlin",
      "description" => [
        "features" => ["skinny", "ugly", "evil"],
        "size" => 30,
        "sex" => "undefined",
        "life_expectancy" => "very old",
        "danger" => "always"
      ],
      "hates" => "all"
    ]
    **/
```




