<?php

  /**
   * Hyphenates a string
   *
   * @return string - hyphenated
   */
  function str_hyphenated($string) {
    return strtolower(preg_replace("/[^\w]+/", "-", $string));
  }
