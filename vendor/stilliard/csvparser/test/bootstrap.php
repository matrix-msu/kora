<?php

require_once dirname(__FILE__) . '/../vendor/autoload.php';

// backward compatibility
if (!class_exists('\PHPUnit_Framework_TestCase') &&
    class_exists('\PHPUnit\Framework\TestCase')) {
    class_alias('\PHPUnit\Framework\TestCase', '\PHPUnit_Framework_TestCase');
}
