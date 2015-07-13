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
 *  Cloudfront URL signing adminlib.
 *
 * @package    filter
 * @subpackage cloudfront_signurl
 * @copyright  2014 Owen Barritt, Wine & Spirit Education Trust
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 

 //  Admin setting for key pair id with verification
 class filter_cloudfront_signurl_keyid extends admin_setting_configtext {

    /**
     * Validate data.
     *
     * This ensures that key ID is specified if URL is provided
     *
     * @param string $data
     * @return mixed True on success, else error message.
     */
    public function validate($data) {
        $result = parent::validate($data);
        if ($result !== true) {
            return $result;
        }

        $url = get_config('filter_cloudfront_signurl','distributionurl');
        if ($url != '' && empty($data)) {
            return get_string('errornokeyid', 'filter_cloudfront_signurl');
        }
        return true;
    }
}


//  Admin setting for private key file with verification
 class filter_cloudfront_signurl_privatekey extends admin_setting_configstoredfile {

    /**
     * Validate data.
     *
     * This ensures that key file is provided if URL is provided.
     *
     * @param string $data
     * @return mixed True on success, else error message.
     */
    public function validate($data) {
        $result = parent::validate($data);
        if ($result !== true) {
            return $result;
        }

        $usercontext = context_user::instance($USER->id);
        $url = get_config('filter_cloudfront_signurl','distributionurl');
        $fs = get_file_storage();
        
        if ($url != '' && empty($data)) {
            return get_string('errornoprivatekey', 'filter_cloudfront_signurl');
        }
        return true;
    }
}