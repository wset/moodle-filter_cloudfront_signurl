moodle-filter_cloudfront_signurl
================================

This Moodle filter recognises cloudfront URLs from the URL defined in the 
filter settings and replaces them with signed URLs.

See http://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/private-content-signed-urls.html
for full details

Installation
------------

Simply place the filter files in ./filter/cloudfront_signurl 

Setup
-----

In the filter settings you will need to define the URL for the cloudfront distribution
along with the Key Pair ID and private key file to use for the signing process.

See http://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/private-content-trusted-signers.html

At present only canned policies are supported.