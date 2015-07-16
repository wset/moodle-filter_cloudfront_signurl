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
 *  Cloudfront URL signing filter
 *
 *  This filter will replace defined cloudfront URLs with signed
 *  URLs as described at http://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/private-content-signed-urls.html
 *
 * @package    filter
 * @subpackage cloudfront_signurl
 * @copyright  2014 Owen Barritt, Wine & Spirit Education Trust
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/filter/cloudfront_signurl/lib.php');

 class filter_cloudfront_signurl extends moodle_text_filter {
    /**
     * Implement the filtering.
     *
     * @param $text some HTML content.
     * @param array $options options passed to the filters
     * @return the HTML content after the filtering has been applied.
     */
    public function filter($text, array $options = array()) {
        if (!is_string($text) or empty($text)) {
            // Non string data can not be filtered anyway.
            return $text;
        }
        
        if (! $disturls = get_config('filter_cloudfront_signurl','distributionurl') ){
            //  Stop if no url set to look for
            return $text;
        }
        
        if (stripos($text, 'http') === false) {
            // Performance shortcut - all regexes below contain http/https protocol,
            // if not present nothing can match.
            return $text;
        }
        
        $urls = preg_split("~\s+~", $disturls);
        $regexurls = array();

        // Strip protocol and trailing / from disturl if present
        foreach ($urls as $disturl) {
            $disturl = preg_replace('~^https?://|/$~','',$disturl);
            if($disturl !== ''){
                $regexurls[] = $disturl;
            }
        }
        $urlregex = implode("|",$regexurls);

        //$newtext = preg_replace_callback($re = '~(https?://'.$disturl.'/^( |#|"|\')*~is',
        $newtext = preg_replace_callback($re = '~https?://('.$urlregex.')/[^ #"]*~is',
            array($this, 'callback'), $text);
        
        if (empty($newtext) or $newtext === $text) {
            // Error or not filtered.
            return $text;
        }
        
        return $newtext;
    }
    
    private function callback(array $matches) {
        return filter_cloudfront_signurl_urlsigner::get_canned_policy_stream_name($matches[0]);
   }
}