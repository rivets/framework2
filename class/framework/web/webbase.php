<?php
/**
 * Contains definition of the base Web class
 * This exists to reduce the number of method in Web itself
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2021 Newcastle University
 * @package Framework\Framework\Web
 */
    namespace Framework\Web;

    use \Support\Context;
/**
 * A class that provides some basic Web operations and Constants.
 */
    abstract class WebBase
    {
        use \Framework\Utility\Singleton;

        public const HTMLMIME  = 'text/html; charset="utf-8"';
/**
 * @var array<array<string>>   Holds values for headers that are required. Keyed by the name of the header
 */
        protected array $headers    = [];
/**
 * @var array<string>   Holds values for Cache-Control headers
 */
        protected array $cache      = [];
/**
 * @var object   The Context object
 */
        protected Context $context;
/**
 * Class constructor. The concrete class using this trait can override it.
 * @internal
 */
        protected function __construct()
        {
            $this->context = Context::getinstance();
        }
/**
 * Output the headers
 */
        private function putHeaders() : void
        {
            foreach ($this->headers as $name => $vals)
            {
                foreach ($vals as $v)
                {
                    \header($name.': '.$v);
                }
            }
            if (!empty($this->cache))
            {
                \header(\trim('Cache-Control: '.\implode(',', $this->cache)));
            }
        }
/**
 * Debuffer - sometimes when we need to do output we are inside buffering.
 *
 * This seems to be a problem with some LAMP stack systems.
 */
        private function debuffer() : void
        {
            while (\ob_get_length() > 0)
            { // just in case we are inside some buffering
                \ob_end_clean();
            }
        }
/**
 * output a header and msg - this never returns
 *
 * @param $code   The return code
 * @param $msg    The message (or '')
 */
        protected function sendHead(int $code, string $msg = '') : never
        {
            if ($msg !== '')
            {
                $msg = '<p>'.$msg.'</p>';
                $this->sendheaders($code, self::HTMLMIME, \strlen($msg));
                echo $msg;
            }
            else
            {
                $this->sendheaders($code, self::HTMLMIME);
            }
            exit;
        }
/**
 * Generate a Location header
 *
 * These codes are a mess and are handled by brtowsers incorrectly....
 *
 * @param $where      The URL to divert to
 * @param $temporary  TRUE if this is a temporary redirect
 * @param $msg        A message to send
 * @param $nochange   If TRUE then reply status codes 307 and 308 will be used rather than 301 and 302
 * @param $use303     If TRUE then use 303 rather than 302
 *
 * @return never
 */
        public function relocate(string $where, bool $temporary = TRUE, string $msg = '', bool $nochange = FALSE, bool $use303 = FALSE) : never
        {
            if ($temporary)
            {
                $code = $nochange ? StatusCodes::HTTP_TEMPORARY_REDIRECT : ($use303 ? StatusCodes::HTTP_SEE_OTHER : StatusCodes::HTTP_FOUND);
            }
            else
            {
/**
 * @todo Check status of 308 code which should be used if nochange is TRUE. May not yet be official.
 */
                $code = $nochange ? StatusCodes::HTTP_PERMANENT_REDIRECT : StatusCodes::HTTP_MOVED_PERMANENTLY;
            }
            $this->addHeader('Location', $where);
            $this->sendString($msg, self::HTMLMIME, $code);
            exit;
        }

/**
 * Check for a range request and check it
 *
 * Media players ask for the file in chunks.
 *
 * @param $size    The size of the output data
 * @param $code    The HTTP return code or ''
 *
 * @psalm-suppress InvalidOperand
 * @psalm-suppress PossiblyInvalidOperand
 * @psalm-suppress InvalidNullableReturnType
 */
        public function hasRange(int $size, string|int $code = StatusCodes::HTTP_OK) : array // @phan-suppress-current-line PhanPluginAlwaysReturnMethod
        {
            if (\filter_has_var(\INPUT_SERVER, 'HTTP_RANGE'))
            { // there is a range request
                if (\preg_match('/=([0-9]+)-([0-9]*)\s*$/', $_SERVER['HTTP_RANGE'], $rng))
                { // split the  request
                    if ((int) $rng[1] <= $size)
                    { // start is before end of file
                        if (!isset($rng[2]) || $rng[2] === '')
                        { // no top value specified, so use the filesize (-1 of course!!)
                            $rng[2] = $size - 1;
                        }
                        if ($rng[2] < $size)
                        { // end is before end of file
                            $this->addHeader(['Content-Range' => 'bytes '.$rng[1].'-'.$rng[2].'/'.$size]);
                            return [StatusCodes::HTTP_PARTIAL_CONTENT, [$rng[1], $rng[2]], (int) $rng[2] - (int) $rng[1]+1];
                        }
                    }
                }
                $this->sendHead(StatusCodes::HTTP_REQUESTED_RANGE_NOT_SATISFIABLE);
                /* NOT REACHED */
            }
            return [$code, [], $size];
        }
/**
 * Make a header sequence for a particular return code and add some other useful headers
 *
 * @param $code   The HTTP return code
 * @param $mtype  The mime-type of the file
 * @param $length The length of the data or NULL
 * @param $name   A file name
 */
        public function sendHeaders(int $code, string $mtype = '', ?int $length = NULL, string $name = '') : void
        {
            \header(StatusCodes::httpHeaderFor($code));
            $this->putheaders();
            if ($mtype !== '')
            {
                \header('Content-Type: '.$mtype);
            }
            if ($length !== NULL)
            {
                \header('Content-Length: '.$length);
            }
            if ($name !== '')
            {
                \header('Content-Disposition: attachment; filename="'.$name.'"');
            }
        }
/**
 * Deliver a file as a response.
 *
 * @param $path    The path to the file
 * @param $name    The name of the file as told to the downloader
 * @param $mime    The mime type of the file
 */
        public function sendFile(string $path, string $name = '', string $mime = '') : void
        {
            [$code, $range, $length] = $this->hasrange(filesize($path));
            if ($mime === '')
            {
                $mime = \Framework\Support\Security::getInstance()->mimetype($path);
            }
    //      $this->addHeader(['Content-Description' => 'File Transfer']);
            $this->sendHeaders($code, $mime, $length, $name);
            $this->debuffer();
            if (!empty($range))
            {
                $fd = \fopen($path, 'r'); // open the file, seek to the required place and read and return the required amount.
                \fseek($fd, $range[0]);
                echo fread($fd, $length);
                \fclose($fd);
            }
            else
            {
                \readfile($path);
            }
        }
/**
 * Deliver a string as a response.
 *
 * @param $value   The data to send
 * @param $mime    The mime type of the file
 * @param $code    The HTTP return code
 */
        public function sendString(string $value, string $mime = '', $code = StatusCodes::HTTP_OK) : void
        {
            $this->debuffer();
            [$code, $range, $length] = $this->hasRange(strlen($value), $code);
            $this->sendHeaders($code, $mime, $length);
            echo empty($range) ? $value : \substr($value, $range[0], $length);
        }
/**
 * Deliver JSON response.
 */
        public function sendJSON(array|bool|float|int|object|string $res, int $code = StatusCodes::HTTP_OK) : void
        {
            $this->sendString(\json_encode($res, \JSON_UNESCAPED_SLASHES), 'application/json', $code);
        }
/**
 * Add a header to the header list.
 *
 * This supports having more than one header with the same name.
 *
 * @param string|array<string>  $key  Either an array of key/value pairs or the key for the value that is in the second parameter
 *
 * @psalm-suppress PossiblyUnusedMethod
 */
        public function addHeader(array|string $key, string $value = '') : void
        {
            foreach (is_array($key) ? $key : [$key => $value] as $k => $val)
            {
                $this->headers[trim($k)][] = \str_replace("\0", '', trim($val));
            }
        }
/**
 * Check a recaptcha value
 *
 * This assumes that file_get_contents can access a URL
 *
 * @param $secret  The recaptcha secret for this site
 *
 * @psalm-suppress PossiblyUnusedMethod
 */
        public function recaptcha(string $secret) : bool
        {
            if (\filter_has_var(INPUT_POST, 'g-recaptcha-response'))
            {
                $data = \http_build_query([
                    'secret'    => $secret,
                    'response'  => $_POST['g-recaptcha-response'],
                    'remoteip'  => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'],
                ]);
                $opts = [
                    'http' => [
                        'method'  => 'POST',
                        'header'  => 'Content-Type: application/x-www-form-urlencoded',
                        'content' => $data,
                    ],
                ];
                $context  = \stream_context_create($opts);
                $result = \file_get_contents('https://www.google.com/recaptcha/api/siteverify', FALSE, $context);
                if ($result !== FALSE)
                {
                    return \json_decode($result, TRUE)->success; // @phan-suppress-current-line PhanTypeExpectedObjectPropAccess
                }
            }
            return FALSE;
        }
    }
?>