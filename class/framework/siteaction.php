<?php
/**
 * Contains definition of abstract Siteaction class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2015 Newcastle University
 */
    namespace Framework;

    use \Framework\Web\Web as Web;
/**
 * A class that all provides a base class for any class that wants to implement a site action
 *
 * Common functions used across the various sub-classes should go in here
 *
 * The constants are used in index.php to indicate how a particular URL should be handled
 */
    abstract class Siteaction
    {
/**
 * Indicates that there is an Object that handles the call
 */
	const OBJECT	= 1;
/**
 * Indicates that there is only a template for this URL.
 */
	const TEMPLATE	= 2;
/**
 * Indicates that the URL should be temporarily redirected
 */
	const REDIRECT	= 3;
/**
 * Indicates that the URL should be permanently redirected
 */
	const REHOME	= 4;
/**
 * Indicates that the URL should be temporarily redirected
 */
	const XREDIRECT	= 5;
/**
 * Indicates that the URL should be permanently redirected
 */
	const XREHOME	= 6;
/**
 * Handle an action
 *
 * @param object	$context	The context object for the site
 *
 * @return mixed	A template name or an array [template name, mimetype, HTTP code]
 */
	public function handle($context)
	{ # should never get called really
	    $context->divert('/');
	    /* NOT REACHED */
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
 * The Data class provides (or will in the future...) an example of how to use this code when dealing with file data.
 *
 * @return void
 */
	public function ifmodcheck()
	{
	    $ifms = TRUE; # the IF_MODIFIED_SINCE status is needed to correctly implement IF_NONE_MATCH
	    if (filter_has_var(INPUT_SERVER, 'HTTP_IF_MODIFIED_SINCE'))
	    {
		$ifmod = $_SERVER['HTTP_IF_MODIFIED_SINCE'];
		if (preg_match('/^(.*);(.*)$/', $ifmod, $m))
		{
		    $ifmod = $m[1];
		}
		$st = strtotime($ifmod);
		$ifms = $st !== FALSE && $this->checkmodtime($st); # will 304 later if there is no NONE_MATCH or nothing matches
	    }
	    if (filter_has_var(INPUT_SERVER, 'HTTP_IF_NONE_MATCH'))
	    {
		if ($_SERVER['HTTP_IF_NONE_MATCH'] == '*')
		{
		    if ($this->exists())
		    { # this request would generate a page and has not been modified
			$this->etagmatched();
			/* NOT REACHED */
		    }
		}
		else
		{
		    foreach (explode(',', $_SERVER['HTTP_IF_NONE_MATCH']) as $etag)
		    {
			if ($this->checketag(str_replace('"', '', $etag))) # extract the ETag from its surrounding quotes
			{ # We have matched the etag and file has not been modified
			    $this->etagmatched();
			    /* NOT REACHED */
			}
		    }
		}
		$ifms = TRUE; # no entity tags matched  or matched but modified, so we must ignore any IF_MODIFIED_SINCE
	    }
	    if (!$ifms)
	    { # we dont need to send the page
		$this->etagmatched();
		/* NOT REACHED */
	    }
	    if (filter_has_var(INPUT_SERVER, 'HTTP_IF_MATCH'))
	    {
		$match = FALSE;
		if ($_SERVER['HTTP_IF_MATCH'] == '*')
		{
		    $match = $this->exists();
		}
		else
		{
		    foreach (explode(',', $_SERVER['HTTP_IF_MATCH']) as $etag)
		    {
			$match |= $this->checketag(substr(trim($etag), 1, -1)); # extract the ETag from its surrounding quotes
		    }
		}
		if (!$match)
		{ # nothing matched or did not exist
		    Web::getinstance()->sendheaders(StatusCodes::HTTP_PRECONDITION_FAILED);
		    exit;
		}
	    }
	    if (filter_has_var(INPUT_SERVER, 'HTTP_IF_UNMODIFIED_SINCE'))
	    {
		$ifus = $_SERVER['HTTP_IF_UNMODIFIED_SINCE'];
		if (preg_match('/^(.*);(.*)$/', $ifus, $m))
		{
		    $ifus = $m[1];
		}
		$st = strtotime($ifus); # ignore if not a valid time
		if ($st !== FALSE && $st < $this->lastmodified())
		{
		    Web::getinstance()->sendheaders(StatusCodes::HTTP_PRECONDITION_FAILED);
		    exit;
		}
	    }
	}
/**
 * Make an etag for an item
 *
 * This needs to be overridden by pages that can generate etags
 *
 * @return string
 */
	public function makeetag()
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
	public function exists()
	{
	    return TRUE;
	}
/**
 * Get a last modified time for the page
 *
 * By default this returns the current time. For pages that need to use this in anger,
 * then this function needs to be overridden.
 *
 * @return integer
 */
	public function lastmodified()
	{
	    return time();
	}
/**
 * Format a time suitable for Last-Modified header
 *
 * @param integer	$time	The last modified time
 *
 * @return string
 */
	public function makemod($time)
	{
	    return gmdate('D, d M Y H:i:s', $time).' GMT';
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
 * @return boolean
 */
	public function checkmodtime($time)
	{
	    return FALSE;
	}
/**
 * Check an etag to see if we need to send the page again or not.
 *
 * @param string	$tag	The etag value to check
 *
 * @return boolean
 */
	public function checketag($tag)
	{
	    return $tag === $this->makeetag();
	}
/**
 * We have a matched etag - check request method and send the appropriate header.
 * Does not return
 *
 * @link https://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.26
 *
 * @return void
 */
	private function etagmatched()
	{
	    $web = Web::getinstance();
	    $rqm = $web->method();
	    if ($rqm != 'GET' && $rqm != 'HEAD')
	    { # fail if not a GET or HEAD - see W3C specification
		$web->sendheaders(StatusCodes::HTTP_PRECONDITION_FAILED);
	    }
	    else
	    {
		$web->send304($this->makeetag(), $this->makemaxage());
	    }
	    exit;
	}
    }
?>
