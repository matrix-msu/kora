<?php

  /**
   * Hyphenates a string
   *
   * @return string - hyphenated
   */
  public static function str_hyphenated($string) {
    return strtolower(preg_replace("/[^\w]+/", "-", $string));
  }
