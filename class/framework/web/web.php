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

    use \Support\Context;
/**
 * A class that handles various web related things.
 */
    class Web
    {
        use \Framework\Utility\Singleton;

        public const HTMLMIME  = 'text/html; charset="utf-8"';
/**
 * @var array   Holds values for headers that are required. Keyed by the name of the header
 */
        private $headers    = [];
/**
 * @var array   Holds values that need to be added to CSP headers.
 */
        private $csp        = [];
/**
 * @var array   Holds values that need to be removed from CSP headers.
 */
        private $nocsp      = [];
/**
 * @var array   Holds values for Cache-Control headers
 */
        private $cache      = [];
/**
 * @var object   The Context object
 */
        private $context;
/**
 * Class constructor. The concrete class using this trait can override it.
 * @internal
 */
        protected function __construct()
        {
            $this->context = Context::getinstance();
        }
/**
 * Generate a Location header
 *
 * These codes are a mess and are handled by brtowsers incorrectly....
 *
 * @param string    $where      The URL to divert to
 * @param bool      $temporary  TRUE if this is a temporary redirect
 * @param string    $msg        A message to send
 * @param bool      $nochange   If TRUE then reply status codes 307 and 308 will be used rather than 301 and 302
 * @param bool      $use303     If TRUE then use 303 rather than 302
 *
 * @psalm-return never-return
 * @return void
 */
        public function relocate(string $where, bool $temporary = TRUE, string $msg = '', bool $nochange = FALSE, bool $use303 = FALSE) : void
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
 * output a header and msg - this never returns
 *
 * @param int       $code   The return code
 * @param string    $msg    The message (or '')
 *
 * @return void
 * @psalm-return never-return
 */
        private function sendHead(int $code, string $msg) : void
        {
            if ($msg !== '')
            {
                $msg = '<p>'.$msg.'</p>';
                $length = strlen($msg);
            }
            else
            {
                $length = NULL;
            }
            $this->sendheaders($code, self::HTMLMIME, $length);
            if ($msg !== '')
            {
                echo $msg;
            }
            exit;
            /* NOT REACHED */
        }
/**
 * Check for a range request and check it
 *
 * Media players ask for the file in chunks.
 *
 * @param int           $size    The size of the output data
 * @param string|int    $code    The HTTP return code or ''
 *
 * @return array<mixed>
 *
 * @psalm-suppress InvalidOperand
 * @psalm-suppress PossiblyInvalidOperand
 * @psalm-suppress InvalidNullableReturnType
 */
        public function hasRange(int $size, $code = StatusCodes::HTTP_OK) : array
        {
            if (!isset($_SERVER['HTTP_RANGE']))
            {
                return [$code, [], $size];
            }
            if (preg_match('/=([0-9]+)-([0-9]*)\s*$/', $_SERVER['HTTP_RANGE'], $rng))
            { // split the range request
                if ($rng[1] <= $size)
                { // start is before end of file
                    if (!isset($rng[2]) || $rng[2] === '')
                    { // no top value specified, so use the filesize (-1 of course!!)
                        $rng[2] = $size - 1;
                    }
                    if ($rng[2] < $size)
                    { // end is before end of file
                        $this->addHeader(['Content-Range' => 'bytes '.$rng[1].'-'.$rng[2].'/'.$size]);
                        return [StatusCodes::HTTP_PARTIAL_CONTENT, [$rng[1], $rng[2]], $rng[2]-$rng[1]+1];
                    }
                }
            }
            $this->sendHead(StatusCodes::HTTP_REQUESTED_RANGE_NOT_SATISFIABLE, '');
            /* NOT REACHED */
        }
/**
 * Make a header sequence for a particular return code and add some other useful headers
 *
 * @param int       $code   The HTTP return code
 * @param string    $mtype  The mime-type of the file
 * @param ?int      $length The length of the data or NULL
 * @param string    $name   A file name
 *
 * @return void
 */
        public function sendHeaders(int $code, string $mtype = '', ?int $length = NULL, string $name = '') : void
        {
            header(StatusCodes::httpHeaderFor($code));
            $this->putheaders();
            if ($mtype !== '')
            {
                header('Content-Type: '.$mtype);
            }
            if ($length !== NULL)
            {
                header('Content-Length: '.$length);
            }
            if ($name !== '')
            {
                header('Content-Disposition: attachment; filename="'.$name.'"');
            }
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
 * Deliver a file as a response.
 *
 * @param string    $path    The path to the file
 * @param string    $name    The name of the file as told to the downloader
 * @param string    $mime    The mime type of the file
 *
 * @return void
 */
        public function sendFile(string $path, string $name = '', string $mime = '') : void
        {
            [$code, $range, $length] = $this->hasrange(filesize($path));
            if ($mime === '')
            {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                if (($mime = finfo_file($finfo, $path)) === FALSE)
                { // there was an error of some kind.
                    $mime = '';
                }
                finfo_close($finfo);
            }
    //      $this->addHeader(['Content-Description' => 'File Transfer']);
            $this->sendHeaders($code, $mime, $length, $name);
            $this->debuffer();
            if (!empty($range))
            {
                $fd = fopen($path, 'r'); // open the file, seek to the required place and read and return the required amount.
                fseek($fd, $range[0]);
                echo fread($fd, $length);
                fclose($fd);
            }
            else
            {
                readfile($path);
            }
        }
/**
 * Deliver a string as a response.
 *
 * @param string    $value   The data to send
 * @param string    $mime    The mime type of the file
 * @param int       $code    The HTTP return code
 *
 * @return void
 */
        public function sendString(string $value, string $mime = '', $code = StatusCodes::HTTP_OK) : void
        {
            $this->debuffer();
            [$code, $range, $length] = $this->hasRange(strlen($value), $code);
            $this->sendHeaders($code, $mime, $length);
            echo empty($range) ? $value : substr($value, $range[0], $length);
        }
/**
 * Deliver JSON response.
 *
 * @param mixed    $res
 * @param int      $code
 *
 * @return void
 */
        public function sendJSON($res, int $code = StatusCodes::HTTP_OK) : void
        {
            $this->sendString(json_encode($res, JSON_UNESCAPED_SLASHES), 'application/json', $code);
        }
/**
 * Add a header to the header list.
 *
 * This supports having more than one header with the same name.
 *
 * @param string|array<string>  $key  Either an array of key/value pairs or the key for the value that is in the second parameter
 * @param string                $value
 *
 * @return void
 * @psalm-suppress PossiblyUnusedMethod
 */
        public function addHeader($key, string $value = '') : void
        {
            if (!is_array($key))
            {
                $key = [$key => $value];
            }
            foreach ($key as $k => $val)
            {
                $this->headers[trim($k)][] = str_replace("\0", '', trim($val));
            }
        }
/**
 * Output the headers
 *
 * @return void
 */
        private function putHeaders() : void
        {
            foreach ($this->headers as $name => $vals)
            {
                foreach ($vals as $v)
                {
                    header($name.': '.$v);
                }
            }
            if (!empty($this->cache))
            {
                header(trim('Cache-Control: '.implode(',', $this->cache)));
            }
        }
/**
 * Check to see if the client accepts gzip encoding
 *
 * @return bool
 */
        public function acceptgzip() : bool
        {
            return filter_has_var(INPUT_SERVER, 'HTTP_ACCEPT_ENCODING') &&
                substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') > 0;
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
 * Debuffer - sometimes when we need to do output we are inside buffering. This seems
 * to be a problem with some LAMP stack systems.
 *
 * @return void
 */
        public function debuffer() : void
        {
            while (ob_get_length() > 0)
            { // just in case we are inside some buffering
                ob_end_clean();
            }
        }
/**
 * compute, save and return a hash for use in a CSP header
 *
 * @param string  $type    What the hash is for (script-src, css-src etc.)
 * @param string  $string  The data to be hashed
 *
 * @return string Returns the hash
 * @psalm-suppress PossiblyUnusedMethod
 */
        public function saveCSP(string $type, string $string) : string
        {
            $hash = 'sha256-'.base64_encode(hash('sha256', $string, TRUE));
            $this->addCSP($type, "'".$hash."'");
            return $hash;
        }
/**
 * Add an item for use in a CSP header - could be 'unsafe-inline', a domain or other stuff
 *
 * @param string|array<string>  $type    What the item is for (script-src, style-src etc.)
 * @param string                $host    The host to add
 *
 * @return void
 */
        public function addCSP($type, string $host = '') : void
        {
            if (!is_array($type))
            {
                assert($host !== '');
                $type = [$type => $host];
            }
            foreach ($type as $t => $h)
            {
                $this->csp[$t][] = $h;
            }
        }
/**
 * Remove an item from a CSP header - could be 'unsafe-inline', a domain or other stuff
 *
 * @param string|array  $type    What the item is for (script-src, style-src etc.)
 * @param string        $host    The item to remove
 *
 * @return void
 * @psalm-suppress PossiblyUnusedMethod
 */
        public function removeCSP($type, string $host = '') : void
        {
            if (!is_array($type))
            {
                assert($host !== '');
                $type = [$type => $host];
            }
            foreach ($type as $t => $h)
            {
                $this->nocsp[$t][] = $h;
            }
        }
/**
 * Set up default CSP headers for a page
 *
 * There will be a basic set of default CSP permissions for the site to function,
 * but individual pages may wish to extend or restrict these.
 *
 * @return void
 * @psalm-suppress PossiblyUnusedMethod
 */
        public function setCSP() : void
        {
            $local = $this->context->local();
            if ($local->configval('usecsp'))
            {
                $csp = '';
                foreach (\Config\Config::$defaultCSP as $key => $val)
                {
                    if (isset($this->nocsp[$key]))
                    {
                        $val = array_diff($val, $this->nocsp[$key]);
                    }
                    if (!empty($val))
                    {
                        $csp .= ' '.$key.' '.implode(' ', $val).(isset($this->csp[$key]) ? ' '.implode(' ', $this->csp[$key]) : '').';';
                    }
                }
                if ($local->configval('reportcsp'))
                {
                    $edp = $local->base().'/cspreport/';
                    $csp .= ' report-uri '.$edp.';'; // This is deprecated but widely supported
                    $csp .= ' report-to csp-report;';
                    $this->addheader([
                        'Report-To' => 'Report-To: { "group": "csp-report", "max-age": 10886400, "endpoints": [ { "url": "'.$edp.'" } ] }',
                    ]);
                }
                $this->addheader([
                    'Content-Security-Policy'   => $csp,
                ]);
            }
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
/**
 * Check a recaptcha value
 *
 * This assumes that file_get_contetns
 *
 * @param string    $secret  The recaptcha secret for this site
 *
 * @return bool
 * @psalm-suppress PossiblyUnusedMethod
 */
        public function recaptcha(string $secret) : bool
        {
            if (filter_has_var(INPUT_POST, 'g-recaptcha-response'))
            {
                $data = http_build_query([
                    'secret'    => $secret,
                    'response'  => $_POST['g-recaptcha-response'],
                    'remoteip'  => $_SERVER['REMOTE_ADDR'],
                ]);
                $opts = [
                    'http' => [
                        'method'  => 'POST',
                        'header'  => 'Content-Type: application/x-www-form-urlencoded',
                        'content' => $data,
                    ],
                ];
                $context  = stream_context_create($opts);
                $result = file_get_contents('https://www.google.com/recaptcha/api/siteverify', FALSE, $context);
                if ($result !== FALSE)
                {
                    $check = json_decode($result, TRUE);
                    return $check->success;
                }
            }
            return FALSE;
        }
/**
 * Make a nonce value for including inline CSS
 *
 * @return string
 */
        public function makeNonce()
        {
            $rand = '';
            for ($i = 0; $i < 32; $i++) {
                $rand .= chr(mt_rand(0, 255));
            }
            return hash('sha512', $rand);
        }
    }
?>