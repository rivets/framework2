<?php
/**
 * A trait that allows extending the model class for edit functionality for
 *
 * Add any new methods you want the SiteAction bean to have here.
 *
 * N.B. The functions pre-defined in here can also be overridden by individual pages
 * They are defined in here in order to allow framework developers to have settings that apply
 * to ALL object pages rather than having to override theme everywhere.
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2019 Newcastle University
 *
 */
    namespace Support;
/**
 * User table stores info about users of the syste,
 */
    trait SiteAction
    {
/**
 * Set any cache headers that are wanted
 *
 * This needs to be overridden if it is to do anything
 *
 * @param \Support\Context    $context The context object
 *
 * @return void
 */
        public function setCache(Context $context) : void
        {
            // void
        }
/**
 * Make an etag for an item
 *
 * This needs to be overridden by pages that can generate etags
 *
 * @return string
 */
        public function makeetag() : string
        {
            return '';
        }
/**
 * Make a max age value for an item
 *
 * This needs to be overridden by pages that want to use this
 *
 * @return mixed
 */
        public function makemaxage()
        {
            return '';
        }
/**
 * Returns true of the request would generate a page.
 *
 * This needs to be overridden if it is to be used. Currently returns TRUE,
 * thus assuming that pages always exist....
 *
 * @return boolean
 */
        public function exists() : bool
        {
            return TRUE;
        }
/**
 * Get a last modified time for the page
 *
 * By default this returns the current time. For pages that need to use this in anger,
 * then this function may need to be overridden.
 *
 * @return int
 */
        public function lastmodified() : int
        {
            return time();
        }
/**
 * Check a timestamp to see if we need to send the page again or not.
 *
 * This always returns FALSE, indicating that we need to send the page again.
 * The assumption is that pages that implement etags will override this function
 * appropriately to do actual value checking.
 *
 * @param string	$time	The time value to check
 *
 * @return bool
 */
        public function checkmodtime(string $time) : bool
        {
            return FALSE;
        }
/**
 * Check an etag to see if we need to send the page again or not.
 *
 * @param string	$tag	The etag value to check
 *
 * @return bool
 */
        public function checketag(string $tag) : bool
        {
            return $tag === $this->makeetag();
        }
    }
?>