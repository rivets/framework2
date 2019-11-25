<?php
/**
 * A trait that implements nocaching for pages
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
    trait NoCache
    {
/**
 * Make it so that the page does nto get cahced
 *
 * @param \Support\Context    $context The context object
 *
 * @return void
 */
        public function setCache(Context $context) : void
        {
            $hdrs = [
                'Expires'       => $this->makemod(time()) // expires now...
            ];
            $context->web()->addheader($hdrs);
            $this->set304Cache($context);
        }
/**
 * Set any cache headers that are wanted on a 304 response
 *
 * @param \Support\Context    $context   The context object for the site
 *
 * @return void
 */
        public function set304Cache(Context $context) : void
        {
            $context->web()->addCache([
                'no-store',
                'no-cache',
                'must-revalidate',
                'stale-while-revalidate=86400', // these are non-standard but used by some CDNs to give better service.
                'stale-if-error=259200'
            ]);
        }
    }
?>