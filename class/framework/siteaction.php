<?php
/**
 * Contains definition of abstract Siteaction class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2022 Newcastle University
 * @package Framework
 */
    namespace Framework;

    use \Framework\Web\StatusCodes;
    use \Framework\Web\Web;
    use \Support\Context;
/**
 * A class that all provides a base class for any class that wants to implement a site action
 *
 * Common functions used across the various sub-classes should go in here
 */
    abstract class SiteAction
    {
        use \Support\SiteAction;

        private bool $ifms;
/**
 * Handle an action
 *
 * @param Context    $context    The context object for the site
 *
 * @return string|array     A template name or an array [template name, mimetype, HTTP code]
 *
 * @psalm-suppress InvalidReturnType
 */
        abstract public function handle(Context $context) : array|string;
/**
 * Set up any CSP headers for a page
 *
 * There will be a basic set of default CSP permissions for the site to function,
 * but individual pages may wish to extend or restrict these.
 *
 * @phpcsSuppress NunoMaduro.PhpInsights.Domain.ForbiddenSetter
 */
        public function setCSP() : void
        {
            Web::getinstance()->setCSP();
        }
/**
 * Look to see if there are any IF... headers, and deal with them. Exit if a 304 or 412 is generated.
 *
 * @link https://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
 *
 * This should be used in page classes where there is some way of
 * determining page freshness (ETags, last modified etc.), otherwise it need not be called.
 *
 * The actual ways of determining page freshness will be page specific and you may
 * need to override some of the other methods that this method calls in order to make things work!
 *
 * @param Context $context
 */
        final public function ifmodcheck(Context $context) : void
        {
            $this->ifms = TRUE; // the IF_MODIFIED_SINCE status is needed to correctly implement IF_NONE_MATCH
            $leave = FALSE;
            if (\filter_has_var(\INPUT_SERVER, 'HTTP_IF_MODIFIED_SINCE'))
            {
                $ifmod = $_SERVER['HTTP_IF_MODIFIED_SINCE'];
                if (\preg_match('/^(.*);(.*)$/', $ifmod, $m))
                {
                    $ifmod = $m[1];
                }
                $st = \strtotime($ifmod);
                /** @psalm-suppress InvalidScalarArgument */
                $this->ifms = $st !== FALSE && $this->checkmodtime($context, (string) $st); // will 304 later if there is no NONE_MATCH or nothing matches
            }
            if (\filter_has_var(\INPUT_SERVER, 'HTTP_IF_NONE_MATCH'))
            {
                $leave = $this->noneMatch($context); // If TRUE then etag was matched
            }
            if (!$this->ifms || $leave)
            { // we dont need to send the page
                $this->etagmatched($context);
                /* NOT REACHED */
            }
            if (\filter_has_var(\INPUT_SERVER, 'HTTP_IF_MATCH'))
            {
                $this->match($context);
            }
            if (\filter_has_var(\INPUT_SERVER, 'HTTP_IF_UNMODIFIED_SINCE'))
            {
                $ifus = $_SERVER['HTTP_IF_UNMODIFIED_SINCE'];
                if (\preg_match('/^(.*);(.*)$/', $ifus, $m))
                {
                    $ifus = $m[1];
                }
                $st = \strtotime($ifus); // ignore if not a valid time
                if ($st !== FALSE && $st < $this->lastmodified($context))
                {
                    $context->web()->sendheaders(StatusCodes::HTTP_PRECONDITION_FAILED);
                    exit;
                    /* NOT REACHED */
                }
            }
        }
/**
 * Check the IF_NONE_MATCH header
 *
 * @param Context $context
 */
        private function noneMatch(Context $context) : bool
        {
            if ($_SERVER['HTTP_IF_NONE_MATCH'] == '*')
            {
                if ($this->exists($context))
                { // this request would generate a page and has not been modified
                    return TRUE;
                }
            }
            else
            {
                foreach (\explode(',', $_SERVER['HTTP_IF_NONE_MATCH']) as $etag)
                {
                    if ($this->checketag($context, \substr(\trim($etag), 1, -1))) // extract the ETag from its surrounding quotes
                    { // We have matched the etag and file has not been modified
                        return TRUE;
                    }
                }
            }
            $this->ifms = TRUE; // no entity tags matched  or matched but modified, so we must ignore any IF_MODIFIED_SINCE
            return FALSE;
        }
/**
 * Check the IF_MATCH header
 *
 * @param Context $context
 */
        private function match(Context $context) : void
        {
            $match = FALSE;
            if ($_SERVER['HTTP_IF_MATCH'] == '*')
            {
                $match = $this->exists($context);
            }
            else
            {
                foreach (\explode(',', $_SERVER['HTTP_IF_MATCH']) as $etag)
                {
                    $match = $match || $this->checketag($context, \substr(\trim($etag), 1, -1)); // extract the ETag from its surrounding quotes
                }
            }
            if (!$match)
            { // nothing matched or did not exist
                $context->web()->sendheaders(StatusCodes::HTTP_PRECONDITION_FAILED);
                exit;
                /* NOT REACHED */
            }
        }
/**
 * Format a time suitable for Last-Modified header
 *
 * @param int   $time    The last modified time
 */
        public function makemod(int $time) : string
        {
            return \gmdate('D, d M Y H:i:s', $time).' GMT';
        }
/**
 * We have a matched etag - check request method and send the appropriate header.
 * Does not return
 *
 * @link https://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.26
 *
 * @psalm-return never-return
 */
        private function etagmatched(Context $context) : never
        {
            $web = $context->web();
            $rqm = $web->method();
            if ($rqm != 'GET' && $rqm != 'HEAD')
            { // fail if not a GET or HEAD - see W3C specification
                $web->sendheaders(StatusCodes::HTTP_PRECONDITION_FAILED);
            }
            else
            {
                $this->set304Cache($context); // set up the cache headers for the 304 response.
                $web->send304();
            }
            exit;
        }
/**
 * Validate the number of fields in the rest of the URL
 *
 * @todo This function could do a lot moreabout checking things....
 *
 * @param array<string>     $rest
 * @param int               $start    The element to start at
 * @param int               $num      The number of params required
 * @param string[]          $format   Currently not used
 *
 * @throws \Framework\Exception\ParameterCount
 * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
 */
        public function checkRest(array $rest, int $start, int $num, $format = []) : array
        {
            if (\count($rest) >= $num + $start)
            {
                return \array_slice($rest, $start, $num);
            }
            throw new \Framework\Exception\ParameterCount('Missing Parameter');
        }
    }
?>