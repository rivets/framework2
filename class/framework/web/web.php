<?php
/**
 * Contains definition of the Web class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2020 Newcastle University
 * @package Framework
 * @subpackage Web
 */
    namespace Framework\Web;

/**
 * A class that handles various web related things.
 */
    class Web extends WebBase
    {
        use CSP; // bring in CSP handling code
/**
 * Send a 204 response - OK but no content
 *
 * @return void
 */
        public function noContent() : void
        {
            $this->sendheaders(StatusCodes::HTTP_NO_CONTENT);
        }
/**
 * Send a 201 response - Created
 *
 * @param string  $value  a string to return
 * @param string  $mime   the mimetype
 *
 * @return void
 */
        public function created(string $value, string $mime = 'text/plain; charset=UTF-8') : void
        {
            $this->sendString($value, $mime, \Framework\Web\StatusCodes::HTTP_CREATED);
        }
/**
 * Send a 304 response - this assumes that the Etag etc. have been set up using the set304Cache function in the \Support\SiteAction class
 *
 * @see \Support\SiteAction
 *
 * @return void
 */
        public function send304() : void
        {
            $this->sendheaders(StatusCodes::HTTP_NOT_MODIFIED);
        }
/**
 * Generate a 400 Bad Request error return
 *
 * @param string    $msg    A message to be sent
 *
 * @return void
 * @psalm-return never-return
 */
        public function bad(string $msg = '') : void
        {
            $this->sendhead(StatusCodes::HTTP_BAD_REQUEST, $msg);
        }
/**
 * Generate a 403 Access Denied error return
 *
 * @param string    $msg    A message to be sent
 *
 * @psalm-return never-return
 * @return void
 */
        public function noAccess(string $msg = '') : void
        {
            $this->sendHead(StatusCodes::HTTP_FORBIDDEN, $msg);
        }
/**
 * Generate a 404 Not Found error return
 *
 * @param string    $msg    A message to be sent
 *
 * @psalm-return never-return
 * @return void
 */
        public function notFound(string $msg = '') : void
        {
            $this->sendHead(StatusCodes::HTTP_NOT_FOUND, $msg);
        }
/**
 * Generate a 500 Internal Error error return
 *
 * @param string    $msg    A message to be sent
 *
 * @psalm-return never-return
 * @return void
 */
        public function internal(string $msg = '') : void
        {
            $this->sendHead(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, $msg);
        }
/**
 * Check to see if the client accepts gzip encoding
 *
 * @return bool
 */
        public function acceptgzip() : bool
        {
            return filter_has_var(INPUT_SERVER, 'HTTP_ACCEPT_ENCODING') && substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') > 0;
        }
/**
 * What kind of request was this?
 *
 * @return string
 */
        public function method() : string
        {
            return $_SERVER['REQUEST_METHOD'];
        }
/**
 * Is this a POST?
 *
 * @return bool
 * @psalm-suppress PossiblyUnusedMethod
 */
        public function isPost() : bool
        {
            return $this->method() == 'POST';
        }
/**
 * Add an item for use in a Cache-Control header
 *
 * @param string[]  $items  An array of items
 *
 * @return void
 */
        public function addCache(array $items) : void
        {
            $this->cache = array_merge($this->cache, $items);
        }
    }
?>