# Json Transformer
A library to transform json data in PHP.

This library is inspired from [Scala JSON transformers](https://www.playframework.com/documentation/2.1.0/ScalaJsonTransformers)
and use [JsonPath](https://github.com/Peekmo/JsonPath) to access json properties.

## Gizmo to Gremlin Example
```php
  <?php

  use JsonTransformer\Transform;

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

  /*
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
  */
```

## How it work
Changing gizmo in gremlin require several transformation of the original json,
like scaling the size property or copying the loves property to the hates one.
These transformations are all functions that take a json as input and return a new one.

The Transform class contains several method to create these functions, depending on
the desired transformation. They are described in the "Basic transformation" section.

The Transform class also contains method to combine these functions, all explained
in the "Combined transformation" section.


## Basic transformation

### Create a Transform
A Transform is created via the `path($path)` static method

```php
  $tranform = Transform::path("$.name");
```

The path argument must be a valid json path as defined [here](http://goessner.net/articles/JsonPath/).
By default, this transformation will return value for the given path when applied to a json.
Here it will be the value of the `name` property.

```php
  $name = $transform($gizmo);
  // $name == "gizmo"
```

### pick
This transformation is the one used by default when creating a Transform.
So `Transform::path("$.name")` is the same as `Transform::path("$.name")->pick()`.

### pickBranch

### copyFrom

### put

### update

### insert


## Combined transformation

### merge

### andThen

### orElse


## Optional value for invalid path

### Path validation

### opt



