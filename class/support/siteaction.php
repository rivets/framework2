<?php
/**
 * Add any new methods you want the SiteAction bean to have here.
 *
 * N.B. The functions pre-defined in here can also be overridden by individual pages
 * They are defined in here in order to allow framework developers to have settings that apply
 * to ALL object pages rather than having to override theme everywhere.
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2019-2020 Newcastle University
 * @package Framework
 */
    namespace Support;

/**
 * Adds functions for dealing with various cache control circumstances.
 * If you add code here then these will apply to all pages. You can override these
 * functions if you want to have special behaviour for a particular page.
 */
    trait SiteAction
    {
/**
 * @var int - the default maxage for a page. This is a static because you can't have consts in a trait....
 */
        protected static $maxage = 3600; // 1 hour
/**
 * Set any cache headers that are wanted for a normal page delivery
 *
 * @param \Support\Context    $context The context object
 *
 * @return void
 * @psalm-suppress PossiblyUnusedMethod
 * @phpcsSuppress NunoMaduro\PhpInsights\Domain\Sniffs\ForbiddenSetterSniff
 */
        public function setCache(Context $context) : void
        {
            $this->set304Cache($context);
        }
/**
 * Set any cache headers that are wanted on a 304 response
 *
 * @param \Support\Context    $context   The context object for the site
 *
 * @return void
 * @phpcsSuppress NunoMaduro\PhpInsights\Domain\Sniffs\ForbiddenSetterSniff
 */
        public function set304Cache(Context $context) : void
        {
            $hdrs = [
                // 'Last-Modified' => $this->makemod($this->mtime),
                'Expires' => $this->makemod(time() + self::$maxage),
            ];
            if (($etag = $this->makeetag($context)) !== '')
            {
                $hdrs['Etag'] = '"'.$etag.'"';
            }
            $context->web()->addheader($hdrs);
            $context->web()->addCache([
                'maxage='.$this->makemaxage($context),
                'must-revalidate',
                'stale-while-revalidate=86400', // these are non-standard but used by some CDNs to give better service.
                'stale-if-error=259200',
            ]);
        }
/**
 * Make an etag for an item
 *
 * This needs to be overridden by pages that can generate etags
 *
 * @param \Support\Context    $context   The context object for the site
 *
 * @return string
 * @psalm-suppress PossiblyUnusedParam
 * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
 */
        public function makeetag(Context $context) : string
        {
            return '';
        }
/**
 * Make a max age value for an item
 *
 * This needs to be overridden by pages that want to use this
 *
 * @param \Support\Context    $context   The context object for the site
 *
 * @return int
 * @psalm-suppress PossiblyUnusedParam
 * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
 */
        public function makemaxage(Context $context) : int
        {
            return self::$maxage;
        }
/**
 * Returns true of the request would generate a page.
 *
 * This needs to be overridden if it is to be used. Currently returns TRUE,
 * thus assuming that pages always exist....
 *
 * @param \Support\Context    $context  The context object for the site
 *
 * @return bool
 * @psalm-suppress PossiblyUnusedParam
 * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
 */
        public function exists(Context $context) : bool
        {
            return TRUE;
        }
/**
 * Get a last modified time for the page
 *
 * By default this returns the current time. For pages that need to use this in anger,
 * then this function may need to be overridden.
 *
 * @param \Support\Context  $context  The context object for the site
 *
 * @return int
 * @psalm-suppress PossiblyUnusedParam
 * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
 */
        public function lastmodified(Context $context) : int
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
 * @param Context   $context    The context object for the site
 * @param string    $time       The time value to check
 *
 * @return bool
 * @psalm-suppress PossiblyUnusedParam
 * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
 */
        public function checkmodtime(Context $context, string $time) : bool
        {
            return FALSE;
        }
/**
 * Check an etag to see if we need to send the page again or not.
 *
 * @param Context   $context    The context object for the site
 * @param string    $tag        The etag value to check
 *
 * @return bool
 */
        public function checketag(Context $context, string $tag) : bool
        {
            $etag = $this->makeetag($context);
            return $tag === $etag || $tag === $etag.'-gzip';
        }
    }
?>
