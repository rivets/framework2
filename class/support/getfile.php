<?php
/**
 * A trait that allows extending the GetFile page handler
 *
 * @author Lindsay Marshall <lindsay.marshall@newcastle.ac.uk>
 * @copyright 2021-2024 Newcastle University
 * @package Framework\Support
 */
    namespace Support;

/**
 * Allows developers to handle missing files.
 */
    trait GetFile
    {
/**
 * No access to the file
 */
        public function noaccess() : string
        {
            return '@content/getfile/noaccess.twig';
        }
/**
 * File does not exist
 */
        public function missing() : string
        {
            return '@content/getfile/missing.twig';
        }
/**
 * Some other error
 */
        public function other(string $msg) : string
        {
            Context::getInstance()->local()->addval(['msg' => $msg]);
            return '@content/getfile/other.twig';
        }
/**
 * Make an etag for an item
 *
 * This needs to be overridden by pages that can generate etag. Defaults
 * to the mtime value.
 *
 * @param Context   $context    The context object for the site
 *
 * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
 */
        public function makeetag(Context $context) : string
        {
            return $this->mtime;
        }
/**
 * Get a last modified time for the page
 *
 * By default this returns the current time. For pages that need to use this in anger,
 * then this function may need to be overridden.
 *
 * @param Context   $context    The context object for the site
 *
 * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
 */
        public function lastmodified(Context $context) : string
        {
            return $this->mtime;
        }
/**
 * Check a timestamp to see if we need to send the page again or not.
 *
 * This always returns FALSE, indicating that we need to send the page again.
 * The assumption is that pages that implement etags will override this function
 * appropriately to do actual value checking.
 *
 * @param Context   $context    The context object for the site
 * @param string    $time       The time value to check
 *
 * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
 */
        public function checkmodtime(Context $context, string $time) : bool
        {
            return $time == $this->mtime;
        }
/**
 * Check an etag to see if we need to send the page again or not.
 *
 * This tests against the mtime (see above), indicating that we need to send the page again if not equal.
 * The assumption is that pages that implement etags will override this function
 * appropriately to do different value checking.
 *
 * @param Context   $context   The context object for the site
 * @param string    $tag       The etag value to check
 *
 * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
 */
        public function checketag(Context $context, string $tag) : bool
        {
            return $tag == $this->mtime || $tag == $this->mtime.'-gzip';
        }
    }
?>