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
 *  Cloudfront URL signing settings.
 *
 * @package    filter
 * @subpackage cloudfront_signurl
 * @copyright  2014 Owen Barritt, Wine & Spirit Education Trust
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    require_once(__DIR__ . '/lib.php');
    require_once(__DIR__ . '/adminlib.php');

    $settings->add(new admin_setting_configtextarea('filter_cloudfront_signurl/distributionurl',
        get_string('distributionurl', 'filter_cloudfront_signurl'),
        get_string('distributionurldesc', 'filter_cloudfront_signurl'),
        ''));

    $settings->add(new filter_cloudfront_signurl_keyid('filter_cloudfront_signurl/keypairid',
        get_string('keyid', 'filter_cloudfront_signurl'),
        get_string('keyiddesc', 'filter_cloudfront_signurl'),
        ''));
        
    $settings->add(new filter_cloudfront_signurl_privatekey('filter_cloudfront_signurl/privatekey',
        get_string('privatekey', 'filter_cloudfront_signurl'),
        get_string('privatekeydesc', 'filter_cloudfront_signurl'),
        'privatekey',
        0,
        array(
            'accepted_types' => array('.pem')
        )));
            
    $settings->add(new admin_setting_configduration('filter_cloudfront_signurl/validduration',
        get_string('validduration', 'filter_cloudfront_signurl'),
        get_string('validdurationdesc', 'filter_cloudfront_signurl'),
        86400,
        1));
}
?>