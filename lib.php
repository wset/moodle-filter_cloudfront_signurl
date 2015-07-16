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
 *  Cloudfront URL signing library.
 *
 * @package    filter
 * @subpackage cloudfront_signurl
 * @copyright  2014 Owen Barritt, Wine & Spirit Education Trust
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
 // Cloudfront signing functions adapted from Amazon http://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/CreateURL_PHP.html
 
 
 class filter_cloudfront_signurl_urlsigner {
    private static function rsa_sha1_sign($policy) {
        $signature = "";

        // load the private key
        $privatekeyfile = preg_split("~/~",ltrim(get_config('filter_cloudfront_signurl', 'privatekey'),"/"));
        $pkfilename = array_pop($privatekeyfile);
        if (!$privatekeyfile) {
            $pkfilepath = '/'; // $args is empty => the path is '/'
        } else {
            $pkfilepath = '/'.implode('/', $privatekeyfile).'/'; // $args contains elements of the filepath
        }
        $syscontext = context_system::instance();
        $fs = get_file_storage();
        $pkfile = $fs->get_file($syscontext->id, 'filter_cloudfront_signurl', 'privatekey', 0, $pkfilepath, $pkfilename);
        if($pkfile){
            $priv_key = $pkfile->get_content();
            $pkeyid = openssl_get_privatekey($priv_key);

            // compute signature
            openssl_sign($policy, $signature, $pkeyid);

            // free the key from memory
            openssl_free_key($pkeyid);
        }

        return $signature;
    }

    private static function url_safe_base64_encode($value) {
        $encoded = base64_encode($value);
        // replace unsafe characters +, = and / with the safe characters -, _ and ~
        return str_replace(
            array('+', '=', '/'),
            array('-', '_', '~'),
            $encoded);
    }

    private static function create_stream_name($stream, $policy, $signature, $expires) {
        $result = $stream;
        // if the stream already contains query parameters, attach the new query parameters to the end
        // otherwise, add the query parameters
        $separator = strpos($stream, '?') == FALSE ? '?' : '&';
        // the presence of an expires time means we're using a canned policy
        if($expires) {
            $result .= $separator . "Expires=" . $expires . "&Signature=" . $signature . "&Key-Pair-Id=" . get_config('filter_cloudfront_signurl','keypairid');
        } 
        // not using a canned policy, include the policy itself in the stream name
        else {
            $result .= $separator . "Policy=" . $policy . "&Signature=" . $signature . "&Key-Pair-Id=" . get_config('filter_cloudfront_signurl','keypairid');
        }

        // new lines would break us, so remove them
        return str_replace('\n', '', $result);
    }

    public static function get_canned_policy_stream_name($video_path) {
        $expires = time() + get_config('filter_cloudfront_signurl','validduration');
        // this policy is well known by CloudFront, but you still need to sign it, since it contains your parameters
        $canned_policy = '{"Statement":[{"Resource":"' . $video_path . '","Condition":{"DateLessThan":{"AWS:EpochTime":'. $expires . '}}}]}';
        // the policy contains characters that cannot be part of a URL, so we base64 encode it
        $encoded_policy = self::url_safe_base64_encode($canned_policy);
        // sign the original policy, not the encoded version
        $signature = self::rsa_sha1_sign($canned_policy);
        // make the signature safe to be included in a url
        $encoded_signature = self::url_safe_base64_encode($signature);

        // combine the above into a stream name
        $stream_name = self::create_stream_name($video_path, null, $encoded_signature, $expires);
        // url-encode the query string characters to work around a flash player bug
        return $stream_name;
    }

    public static function get_custom_policy_stream_name($video_path, $policy) {
        // the policy contains characters that cannot be part of a URL, so we base64 encode it
        $encoded_policy = self::url_safe_base64_encode($policy);
        // sign the original policy, not the encoded version
        $signature = self::rsa_sha1_sign($policy);
        // make the signature safe to be included in a url
        $encoded_signature = self::url_safe_base64_encode($signature);

        // combine the above into a stream name
        $stream_name = self::create_stream_name($video_path, $encoded_policy, $encoded_signature, null);
        // url-encode the query string characters to work around a flash player bug
        return $stream_name;
    }
}