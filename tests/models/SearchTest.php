<?php

use App\Search as Search;

class SearchTest extends TestCase
{
    /**
     * Test the show ignored arguments method.
     */
    public function test_showIgnoredArguments() {
        $string = "onomatopoeia"; // Something that is obviously not a stop word.
        $this->assertEmpty(Search::showIgnoredArguments($string)); // Nothing ignored by the search.

        $string = "and"; // Something that is obviously a stop word.
        $this->assertContains("and", Search::showIgnoredArguments($string)); // "and" should be ignored.

        $string = "onomatopoeia eldritch oowee"; // Multiple non-stop words.
        $this->assertEmpty(Search::showIgnoredArguments($string));

        $string = "and or is the was"; // Multiple stop words.
        $this->assertEquals(explode(" ", $string), Search::showIgnoredArguments($string)); // Everything ignored.

        $string = "and eldritch or onomatopoeia"; // Mixed input.

        $this->assertContains("and", Search::showIgnoredArguments($string));
        $this->assertContains("or", Search::showIgnoredArguments($string));
    }
}