<?php
namespace JsonTranformer;

use JsonTranformer\Exception\JsonPathNotFoundException;

class Transform {

  private $path;
  private $transformer = null;
  private $opt = null;

  private function __construct($path = "") {
    $this->path = $path;
    $this->pick();

  }

  public static function path($path = "$", $opt =  null) {
    $t = new Transform($path);
    $t->opt($opt);


    return $t;
  }

  public function opt($defaultValue = array()) {
    $this->opt = $defaultValue;
    return $this;
  }


  public function __invoke() {
    $args = func_get_args();
    // If we are called with a std array
    //  the transformer is applied and a std array is returned
    if (! $args[0] instanceof Json)
      return $this->apply($args[0]);

    // Else if we are called with a Json Object,
    //  the transformer is applied and a Json object is returned
    return call_user_func_array($this->transformer, $args);
  }

  public function apply($jsonValue) {
    return $this(new Json($jsonValue))->toValue();
  }


  /**
   *
   * @return Transform $this
   */
  public function pick() {
    $this->transformer = function (Json $json) {
      $res = $json->get($this->path, $this->opt);
      return $res;
    };

    return $this;
  }

  /**
   * @param callable $transformer
   * @return Transform $this
   */
  public function pickBranch(callable $transformer = null) {
    $this->transformer = function (Json $json) use ($transformer) {

      $jsonBranch = new Json(JsonPathHelper::createArrayFromPath($this->path));

      $json = $json->get($this->path, $this->opt);

      if (!is_null($transformer))
        $json = $json->transform($transformer);

      $jsonBranch->set($this->path, $json->toValue());

      return $jsonBranch;
    };

    return $this;
  }

  /**
   * @param callable $transformer
   * @return Transform $this
   */
  public function copyFrom(callable $transformer) {

    $this->transformer = function (Json $copyFromJson) use ($transformer) {

      $json = new Json(JsonPathHelper::createArrayFromPath($this->path));

      return $json->set($this->path, $copyFromJson->transform($transformer)->toValue());
    };

    return $this;
  }

  /**
   * @param callable $transformer
   * @return Transform $this
   */
  public function update(callable $transformer) {

    $this->transformer = function (Json $json) use ($transformer) {
      // We are going to update the Json in multiple places
      if (! JsonPathHelper::isDirectPath($this->path)) {
        $directPathList = JsonPathHelper::explode($json->toValue(), $this->path);
        $allPathTransformer = null;
        foreach ($directPathList as $directPath) {
          $directPathTransformer = Transform::path($directPath)->update($transformer);
          $allPathTransformer = (is_null($allPathTransformer))
            ? $directPathTransformer
            : $allPathTransformer->andThen($directPathTransformer);
        }

        $transformer = $allPathTransformer;

        $json->transform($transformer);

      // One place update
      } else {

        $toUpdateJson = $json->get($this->path, $this->opt);

        if (!$transformer instanceof Transform) {
          $transformer = function (Json $json) use ($transformer) {
            return new Json($transformer($json->toValue()));
          };
        }
        $json->set($this->path, $toUpdateJson->transform($transformer)->toValue(), !is_null($this->opt));
      }

      return $json;
    };

    return $this;
  }

  /**
   * @param $jsonToInsert
   * @return Transform $this
   */
  public function insert($jsonToInsert) {

    $this->transformer = function (Json $json) use ($jsonToInsert) {

      list($path, $subpath) = $json->extractExistingPath($this->path);

      // if the full path already exist, we upadte it
      if (JsonPathHelper::isJsonRoot($subpath))
        return $json->set($this->path, $jsonToInsert);

      // We update the existing path to insert the new one
      $t = Transform::path($path)->update(function($jsonValue) use ($subpath, $jsonToInsert) {
        $json = new Json($jsonValue);
        $t = Transform::path($subpath)->put($jsonToInsert);
        $jsonToMerge = $t($json);

        return $json->merge($jsonToMerge)->toValue();
      });

      return $json->transform($t);
    };

    return $this;
  }

  /**
   * @param $jsonToPut
   * @return Transform $this
   */
  public function put($jsonToPut) {

    $this->transformer = function (Json $json) use ($jsonToPut) {

      $newJsonBranch = new Json(JsonPathHelper::createArrayFromPath($this->path));

      return $newJsonBranch->set($this->path, $jsonToPut);
    };

    return $this;
  }


  /**
   * @return Transform $this
   */
  public function prune() {

    $this->transformer = function (Json $json) {
      return $json->remove($this->path, $this->opt);
    };

    return $this;
  }


  /**
   * @param callable $transformer
   * @return Transform $this
   */
  public function merge(callable $transformer) {
    $firstTransformer = $this->transformer;

    $this->transformer = function(Json $json) use ($firstTransformer, $transformer) {

      return $json->transform($firstTransformer)
        ->merge($json->transform($transformer));
    };

    return $this;
  }

  /**
   * @param callable $transformer
   * @return Transform $this
   */
  public function andThen(callable $transformer) {
    $firstTransformer = $this->transformer;

    $this->transformer = function(Json $json) use ($firstTransformer, $transformer) {
      $json = $json->transform($firstTransformer)->transform($transformer);

      return $json;
    };

    return $this;
  }


  /**
   * @param callable $transformer
   * @return Transform $this
   */
  public function orElse(callable $transformer) {
    $firstTransformer = $this->transformer;

    $this->transformer = function(Json $json) use ($firstTransformer, $transformer) {
      try {
        $json = $json->transform($firstTransformer);
      } catch(JsonPathNotFoundException $e) {
        $json = $json->transform($transformer);
      }

      return $json;
    };

    return $this;
  }

}