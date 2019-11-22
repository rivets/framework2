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
    use \Support\Context as Context;

    namespace Support;
/**
 * Adds functions for dealing with various cache control circumstances.
 * If you add code here then these will apply to all pages. You can override these
 * functions if you want to have special behaviour for a particular page.
 *
 */
    trait SiteAction
    {
/**
 * @var int - the default maxage for a page. This is a static because you can't have consts in a trait....
 */
        private static $maxage = 3600; // 1 hour
/**
 * Set any cache headers that are wanted for a normal page delivery
 *
 * @param \Support\Context    $context The context object
 *
 * @return void
 */
        public function setCache(Context $context) : void
        {
            $hdrs = [
                // 'Last-Modified' => $this->makemod($this->mtime),
                'Expires'       => $this->makemod(time() + self::$maxage),
            ];
            if (($etag = $this->makeetag()) !== '')
            {
                $hdrs['Etag'] = '"'.$etag.'"';
            }
            $this->set304Cache($context->web());
        }
/**
 * Set up etag if wanted
 *
 * @param \Support\Context    $context The context object
 *
 * @return void
 */
        public function setEtag(Context $context) : void
        {
            if (($etag = $this->makeetag()) !== '')
            {
                $context->web()->addheader('Etag', '"'.$etag.'"');
            }
        }
/**
 * Set any cache headers that are wanted on a 304 response
 *
 * @param \Framework\Web\Web    $web The web object
 *
 * @return void
 */
        public function set304Cache(\Framework\Web\Web $web) : void
        {
            $web->addCache([
                'maxage='.$this->makemaxage(),
                'must-revalidate',
                'stale-while-revalidate=86400', // these are non-standard but used by some CDNs to give better service.
                'stale-if-error=259200'
            ]);
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
 * @return int
 */
        public function makemaxage() : int
        {
            return self::$maxage;
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