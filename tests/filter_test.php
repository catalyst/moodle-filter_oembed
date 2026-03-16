<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Unit tests for the filter_oembed.
 *
 * @package    filter_oembed
 * @author Sushant Gawali (sushant@introp.net)
 * @author Erich M. Wappis <erich.wappis@uni-graz.at>
 * @author Guy Thomas <brudinie@googlemail.com>
 * @author Mike Churchward <mike.churchward@poetgroup.org>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright Microsoft, Inc.
 */

namespace filter_oembed;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/filter/oembed/filter.php');

/**
 * Unit tests for the filter_oembed.
 *
 * @group filter_oembed
 */
final class filter_test extends \advanced_testcase {
    /** @var object The filter plugin object to perform the tests on */
    protected $filter;

    /**
     * Sets up the test cases.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->filter = new \filter_oembed(\context_system::instance(), []);
        // Ensure all tested providers are enabled.
        $oembed = \filter_oembed\service\oembed::get_instance('all');
        foreach ($oembed->providers as $pid => $provider) {
            switch ($provider->providername) {
                case 'YouTube':
                    $oembed->enable_provider($pid);
                    break;

                case 'SoundCloud':
                    $oembed->enable_provider($pid);
                    break;

                case 'Office Mix':
                    $oembed->enable_provider($pid);
                    break;

                case 'Vimeo':
                    $oembed->enable_provider($pid);
                    break;

                case 'Ted':
                    $oembed->enable_provider($pid);
                    break;

                case 'Poll Everywhere':
                    $oembed->enable_provider($pid);
                    break;

                case 'SlideShare':
                    $oembed->enable_provider($pid);
                    break;

                case 'ISSUU':
                    $oembed->enable_provider($pid);
                    break;
            }
        }
    }

    /**
     * Performs unit tests for all services supported by the filter.
     *
     * Need to update this test to not contact external services.
     *
     * @covers \filter_oembed::filter()
     */
    public function test_filter(): void {
        $this->resetAfterTest(true);
        // This test is marked as skipped because, writing tests that rely
        // on talking to the Internet are a very, very bad idea.
        $this->markTestSkipped();

        $curl = new curl();
        try {
            $out = $curl->get('https://www.youtube.com');
        } catch (Exception $e) {
            $out = '';
        }

        $cancontactyoutube = stripos(trim($out), '<!DOCTYPE html') !== false;

        // Make sure that we have access to the internet.
        if (!$cancontactyoutube) {
            $this->markTestSkipped(
                'Unable to reach youtube'
            );
        }

        set_config('lazyload', 0, 'filter_oembed');

        $soundcloudlink = '<p><a href="https://soundcloud.com/forss/flickermood">soundcloud</a></p>';
        $youtubelink = '<p><a href="https://youtu.be/abuQk-6M5R4">Youtube</a></p>';
        $vimeolink = '<p><a href="http://vimeo.com/115538038">vimeo</a></p>';
        $tedlink = '<p><a href="https://ted.com/talks/aj_jacobs_how_healthy_living_nearly_killed_me">Ted</a></p>';
        $slidesharelink = '<p><a href="https://www.slideshare.net/timbrown/ideo-values-slideshare1">slideshare</a></p>';
        $issuulink = '<p><a href="https://issuu.com/thinkuni/docs/think_issue12">issuu</a></p>';
        $polleverywherelink = '<p><a href="https://www.polleverywhere.com/multiple_choice_polls/AyCp2jkJ2HqYKXc/web">';
        $polleverywherelink .= '$popolleverywhere</a></p>';

        $filterinput = $soundcloudlink . $youtubelink . $vimeolink . $tedlink . $slidesharelink . $issuulink . $polleverywherelink;

        $filteroutput = $this->filter->filter($filterinput);

        $youtubeoutput = '/.*<iframe .*src="https:\/\/www\.youtube\.com\/embed\/abuQk-6M5R4\?feature=oembed.*"/';
        $this->assertMatchesRegularExpression($youtubeoutput, $filteroutput, 'Youtube filter fails');

        $soundcloudoutput = '/.*<iframe .*src="https:\/\/w\.soundcloud\.com\/player\/' .
                            '\?visual=true&url=https%3A%2F%2Fapi\.soundcloud\.com' .
                            '%2Ftracks%2F293&show_artwork=true".*/';
        $this->assertMatchesRegularExpression($soundcloudoutput, $filteroutput, 'Soundcloud filter fails');

        $vimeooutput = '/.*<iframe .*src="https:\/\/player\.vimeo\.com\/video\/115538038\?.*".*/';
        $this->assertMatchesRegularExpression($vimeooutput, $filteroutput, 'Vimeo filter fails');

        $tedoutput = '/.*<a href="https:\/\/ted\.com\/talks\/aj_jacobs_how_healthy_living_nearly_killed_me".*/';
        $this->assertMatchesRegularExpression($tedoutput, $filteroutput, 'Ted filter fails');

        $polleverywhereoutput = '/.*<script src="https:\/\/www\.polleverywhere\.com\/' .
                                'multiple_choice_polls\/AyCp2jkJ2HqYKXc\/web\.js' .
                                '\?results_count_format=percent"><\/script>.*/';
        $this->assertMatchesRegularExpression($polleverywhereoutput, $filteroutput, 'Poll everywhare filter fails');

        $slideshareoutput = '/.*<iframe .*src="https:\/\/www\.slideshare\.net\/slideshow\/embed_code\/key\/ywBrCQRAE5DZrD".*/';
        $this->assertMatchesRegularExpression($slideshareoutput, $filteroutput, 'Slideshare filter fails');

        $issuuoutput = '/.*<div data-url="https:\/\/issuu\.com\/thinkuni\/docs\/think_issue12" .*';
        $issuuoutput .= 'class="issuuembed"><\/div>.*/';
        $this->assertMatchesRegularExpression($issuuoutput, $filteroutput, 'Issuu filter fails');
    }
}
