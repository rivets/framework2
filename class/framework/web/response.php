<?php
/**
 * A trait that implements various response functions
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2019-2021 Newcastle University
 * @package Framework
 */
    namespace Framework\Web;

/**
 * Adds functions for sending response headers
 */
    trait Response
    {
/**
 * Send a 204 response - OK but no content
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
 */
        public function created(string $value, string $mime = 'text/plain; charset=UTF-8') : void
        {
            $this->sendString($value, $mime, \Framework\Web\StatusCodes::HTTP_CREATED);
        }
/**
 * Send a 304 response - this assumes that the Etag etc. have been set up using the set304Cache function in the \Support\SiteAction class
 *
 * @see \Support\SiteAction
 */
        public function send304() : void
        {
            $this->sendHeaders(StatusCodes::HTTP_NOT_MODIFIED);
        }
/**
 * Generate a 400 Bad Request error return
 *
 * @param string    $msg    A message to be sent
 */
        public function bad(string $msg = '') : never
        {
            $this->sendHead(StatusCodes::HTTP_BAD_REQUEST, $msg);
        }
/**
 * Generate a 403 Access Denied error return
 *
 * @param string    $msg    A message to be sent
 */
        public function noAccess(string $msg = '') : never
        {
            $this->sendHead(StatusCodes::HTTP_FORBIDDEN, $msg);
        }
/**
 * Generate a 404 Not Found error return
 *
 * @param string    $msg    A message to be sent
 */
        public function notFound(string $msg = '') : never
        {
            $this->sendHead(StatusCodes::HTTP_NOT_FOUND, $msg);
        }
/**
 * Generate a 500 Internal Error error return
 *
 * @param string    $msg    A message to be sent
 */
        public function internal(string $msg = '') : never
        {
            $this->sendHead(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, $msg);
        }
    }
?>