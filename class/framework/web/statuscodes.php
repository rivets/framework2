<?php
/**
 * StatusCodes provides named constants for
 * HTTP protocol status codes. Written for the
 * Recess Framework (http://www.recessframework.com/)
 *
 * @author Kris Jordan
 * @license MIT
 * @package recess.http
 *
 * Modfied by LFM to separate the message text from the code number.
 */
    namespace Framework\Web;

/**
 * Contains definition of StatusCodes class
 */
    class StatusCodes
    {
	// [Informational 1xx]
        const HTTP_CONTINUE				            = 100;
        const HTTP_SWITCHING_PROTOCOLS			    = 101;
	// [Successful 2xx]
        const HTTP_OK					            = 200;
        const HTTP_CREATED				            = 201;
        const HTTP_ACCEPTED				            = 202;
        const HTTP_NONAUTHORITATIVE_INFORMATION		= 203;
        const HTTP_NO_CONTENT				        = 204;
        const HTTP_RESET_CONTENT			        = 205;
        const HTTP_PARTIAL_CONTENT			        = 206;

	// [Redirection 3xx]
        const HTTP_MULTIPLE_CHOICES			        = 300;
        const HTTP_MOVED_PERMANENTLY		        = 301;
        const HTTP_FOUND				            = 302;
        const HTTP_SEE_OTHER				        = 303;
        const HTTP_NOT_MODIFIED				        = 304;
        const HTTP_USE_PROXY				        = 305;
        const HTTP_UNUSED				            = 306;
        const HTTP_TEMPORARY_REDIRECT		        = 307;
        const HTTP_PERMANENT_REDIRECT		        = 308;

	// [Client Error 4xx]
        const errorCodesBeginAt				        = 400;
        const HTTP_BAD_REQUEST				        = 400;
        const HTTP_UNAUTHORIZED				        = 401;
        const HTTP_PAYMENT_REQUIRED			        = 402;
        const HTTP_FORBIDDEN				        = 403;
        const HTTP_NOT_FOUND				        = 404;
        const HTTP_METHOD_NOT_ALLOWED		        = 405;
        const HTTP_NOT_ACCEPTABLE			        = 406;
        const HTTP_PROXY_AUTHENTICATION_REQUIRED	= 407;
        const HTTP_REQUEST_TIMEOUT			        = 408;
        const HTTP_CONFLICT				            = 409;
        const HTTP_GONE					            = 410;
        const HTTP_LENGTH_REQUIRED			        = 411;
        const HTTP_PRECONDITION_FAILED			    = 412;
        const HTTP_REQUEST_ENTITY_TOO_LARGE		    = 413;
        const HTTP_REQUEST_URI_TOO_LONG			    = 414;
        const HTTP_UNSUPPORTED_MEDIA_TYPE		    = 415;
        const HTTP_REQUESTED_RANGE_NOT_SATISFIABLE	= 416;
        const HTTP_EXPECTATION_FAILED			    = 417;

	// [Server Error 5xx]
        const HTTP_INTERNAL_SERVER_ERROR	        = 500;
        const HTTP_NOT_IMPLEMENTED			        = 501;
        const HTTP_BAD_GATEWAY				        = 502;
        const HTTP_SERVICE_UNAVAILABLE		        = 503;
        const HTTP_GATEWAY_TIMEOUT			        = 504;
        const HTTP_VERSION_NOT_SUPPORTED	        = 505;
/**
 * @var string[]        The messages for each code.
 */
        private static $messages = [
        // [Informational 1xx]
            100	=> 'Continue',
            101	=> 'Switching Protocols',

        // [Successful 2xx]
            200	=> 'OK',
            201	=> 'Created',
            202	=> 'Accepted',
            203	=> 'Non-Authoritative Information',
            204	=> 'No Content',
            205	=> 'Reset Content',
            206	=> 'Partial Content',

        // [Redirection 3xx]
            300	=> 'Multiple Choices',
            301	=> 'Moved Permanently',
            302	=> 'Found',
            303	=> 'See Other',
            304	=> 'Not Modified',
            305	=> 'Use Proxy',
            306	=> '(Unused)',
            307	=> 'Temporary Redirect',

        // [Client Error 4xx]
            400	=> 'Bad Request',
            401	=> 'Unauthorized',
            402	=> 'Payment Required',
            403	=> 'Forbidden',
            404	=> 'Not Found',
            405	=> 'Method Not Allowed',
            406	=> 'Not Acceptable',
            407	=> 'Proxy Authentication Required',
            408	=> 'Request Timeout',
            409	=> 'Conflict',
            410	=> 'Gone',
            411	=> 'Length Required',
            412	=> 'Precondition Failed',
            413	=> 'Request Entity Too Large',
            414	=> 'Request-URI Too Long',
            415	=> 'Unsupported Media Type',
            416	=> 'Requested Range Not Satisfiable',
            417	=> 'Expectation Failed',

        // [Server Error 5xx]
            500	=> 'Internal Server Error',
            501	=> 'Not Implemented',
            502	=> 'Bad Gateway',
            503	=> 'Service Unavailable',
            504	=> 'Gateway Timeout',
            505	=> 'HTTP Version Not Supported'
        ];
/**
 * Generate a header
 *
 * @param int            $code    The code number
 *
 * @return string
 */
        public static function httpHeaderFor(int $code) : string
        {
            return 'HTTP/1.1 ' . self::getMessageForCode($code);
        }
/**
 * Return the message part for a code
 *
 * @param int            $code    The code number
 *
 * @return string
 */
        public static function getMessage(int $code) : string
        {
            return self::isValid($code) ? self::$messages[$code] : 'Unknown Error Code';
        }
/**
 * return code and message
 *
 * @param int            $code    The code number
 *
 * @return string
 */
        public static function getMessageForCode(int $code) : string
        {
            return  $code . ' ' . self::getMessage($code);
        }
/**
 * Is this an error code?
 *
 * @param int            $code    The code number
 *
 * @return bool
 */
        public static function isError(int $code) : bool
        {
            return $code >= self::HTTP_BAD_REQUEST;
        }
/**
 * Is this a valid code?
 *
 * @param int           $code    The code number
 *
 * @return bool
 */
        public static function isValid(int $code) : bool
        {
            return isset(self::$messages[$code]);
        }
/**
 * Can there be a body sent with this return code?
 *
 * @param int            $code    The code number
 *
 * @return bool
 */
        public static function canHaveBody(int $code) : bool
        {
            return
                // True if not in 100s
                ($code < self::HTTP_CONTINUE || $code >= self::HTTP_OK)
                && // and not 204 NO CONTENT
                $code != self::HTTP_NO_CONTENT
                && // and not 304 NOT MODIFIED
                $code != self::HTTP_NOT_MODIFIED;
        }
    }
?>