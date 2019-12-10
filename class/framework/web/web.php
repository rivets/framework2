<?php
/**
 * Contains definition of the Web class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2018 Newcastle University
 */
    namespace Framework\Web;
    use Support\Context as Context;
/**
 * A class that handles various web related things.
 */
    class Web
    {
        use \Framework\Utility\Singleton;

        const HTMLMIME	= 'text/html; charset="utf-8"';

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
 * Generate a Location header
 *
 * These codes are a mess and are handled by brtowsers incorrectly....
 *
 * @param string	$where		The URL to divert to
 * @param bool       	$temporary	TRUE if this is a temporary redirect
 * @param string	$msg		A message to send
 * @param bool       	$nochange	If TRUE then reply status codes 307 and 308 will be used rather than 301 and 302
 * @param bool       	$use303         If TRUE then use 303 rather than 302
 *
 * @psalm-return never-return
 * @return void
 */
        public function relocate(string $where, bool $temporary = TRUE, string $msg = '', bool $nochange = FALSE, bool $use303 = FALSE)
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
            $this->addheader('Location', $where);
            $this->sendstring($msg, self::HTMLMIME);
            exit;
        }
/**
 * output a header and msg - this never returns
 *
 * @param int    	$code	The return code
 * @param string	$msg	The message (or '')
 *
 * @psalm-return never-return
 * @return void
 */
        private function sendhead(int $code, string $msg)
        {
            $this->sendheaders($code);
            if ($msg !== '')
            {
                echo '<p>'.$msg.'</p>';
            }
            exit;
            /* NOT REACHED */
        }
/**
 * Check for a range request and check it
 *
 * Media players ask for the file in chunks.
 *
 * @param int    	$size	The size of the output data
 * @param mixed		$code	The HTTP return code or ''
 *
 * @return array
 *
 * @psalm-suppress InvalidOperand
 * @psalm-suppress PossiblyInvalidOperand
 * @psalm-suppress InvalidNullableReturnType
 */
        public function hasrange(int $size, $code = StatusCodes::HTTP_OK) : array
        {
            if (!isset($_SERVER['HTTP_RANGE']))
            {
                return [$code, [], $size];
            }
            if (preg_match('/=([0-9]+)-([0-9]*)\s*$/', $_SERVER['HTTP_RANGE'], $rng))
            { # split the range request
                if ($rng[1] <= $size)
                { # start is before end of file
                    if (!isset($rng[2]) || $rng[2] === '')
                    { # no top value specified, so use the filesize (-1 of course!!)
                        $rng[2] = $size - 1;
                    }
                    if ($rng[2] < $size)
                    { # end is before end of file
                        $this->addheader(['Content-Range' => 'bytes '.$rng[1].'-'.$rng[2].'/'.$size]);
                        return [StatusCodes::HTTP_PARTIAL_CONTENT, [$rng[1], $rng[2]], $rng[2]-$rng[1]+1];
                    }
                }
            }
            $this->notsatisfiable();
            /* NOT REACHED */
        }
/**
 * Make a header sequence for a particular return code and add some other useful headers
 *
 * @param int    	$code	The HTTP return code
 * @param string	$mtype	The mime-type of the file
 * @param string 	$length	The length of the data
 * @param string	$name	A file name
 *
 * @return void
 */
        public function sendheaders(int $code, string $mtype = '', $length = '', string $name = '') : void
        {
            header(StatusCodes::httpHeaderFor($code));
            $this->putheaders();
            if ($mtype !== '')
            {
                header('Content-Type: '.$mtype);
            }
            if ($length !== '')
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
 * @param string		$msg	A message to be sent
 *
 * @psalm-return never-return
 * @return void
 */
        public function bad(string $msg = '')
        {
            $this->sendhead(StatusCodes::HTTP_BAD_REQUEST, $msg);
        }
/**
 * Generate a 403 Access Denied error return
 *
 * @param string	$msg	A message to be sent
 *
 * @psalm-return never-return
 * @return void
 */
        public function noaccess(string $msg = '')
        {
            $this->sendhead(StatusCodes::HTTP_FORBIDDEN, $msg);
        }
/**
 * Generate a 404 Not Found error return
 *
 * @param string	$msg	A message to be sent
 *
 * @psalm-return never-return
 * @return void
 */
        public function notfound(string $msg = '')
        {
            $this->sendhead(StatusCodes::HTTP_NOT_FOUND, $msg);
        }
/**
 * Generate a 416 Not Satisfiable error return
 *
 * @param string	$msg	A message to be sent
 *
 * @psalm-return never-return
 * @return void
 */
        public function notsatisfiable(string $msg = '')
        {
            $this->sendhead(StatusCodes::HTTP_REQUESTED_RANGE_NOT_SATISFIABLE, $msg);
        }
/**
 * Generate a 500 Internal Error error return
 *
 * @param string		$msg	A message to be sent
 *
 * @psalm-return never-return
 * @return void
 */
        public function internal(string $msg = '')
        {
            $this->sendhead(StatusCodes::HTTP_INTERNAL_SERVER_ERROR, $msg);
        }
/**
 * Deliver a file as a response.
 *
 * @param string	$path	The path to the file
 * @param string	$name	The name of the file as told to the downloader
 * @param string	$mime	The mime type of the file
 *
 * @return void
 */
        public function sendfile(string $path, string $name = '', string $mime = '')
        {
            list($code, $range, $length) = $this->hasrange(filesize($path));
            if ($mime === '')
            {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                if (($mime = finfo_file($finfo, $path)) === FALSE)
                { # there was an error of some kind.
                    $mime = '';
                }
                finfo_close($finfo);
            }
    //	    $this->addheader(['Content-Description' => 'File Transfer']);
            $this->sendheaders($code, $mime, $length, $name);
            $this->debuffer();
            if (!empty($range))
            {
                $fd = fopen($path, 'r'); # open the file, seek to the required place and read and return the required amount.
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
 * @param string	$value	The data to send
 * @param string	$mime	The mime type of the file
 * @param int    	$code	The HTTP return code
 *
 * @return void
 */
        public function sendstring(string $value, string $mime = '', $code = StatusCodes::HTTP_OK)
        {
            $this->debuffer();
            list($code, $range, $length) = $this->hasrange(strlen($value), $code);
            $this->sendheaders($code, $mime, $length);
            echo empty($range) ? $value : substr($value, $range[0], $length);
        }
/**
 * Deliver JSON response.
 *
 * @param mixed    $res
 *
 * @return void
 */
        public function sendJSON($res)
        {
            $this->sendstring(json_encode($res, JSON_UNESCAPED_SLASHES), 'application/json');
        }
/**
 * Add a header to the header list.
 *
 * This supports having more than one header with the same name.
 *
 * @param mixed        $key	Either an array of key/value pairs or the key for the value that is in the second parameter
 * @param string       $value
 *
 * @return void
 */
        public function addheader($key, string $value = '')
        {
            if (is_array($key))
            {
                foreach ($key as $k => $val)
                {
                    $this->headers[$k][] = $val;
                }
            }
            else
            {
                $this->headers[$key][] = $value;
            }
        }
/**
 * Output the headers
 *
 * @return void
 **/
        private function putheaders()
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
                header('Cache-Control: '.implode(',', $this->cache));
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
 */
        public function ispost() : bool
        {
            return $this->method() == 'POST';
        }
/**
 * Debuffer - sometimes when we need to do output we are inside buffering. This seems
 * to be a problem with some LAMP stack systems.
 *
 * @return void
 */
        public function debuffer()
        {
            while (ob_get_length() > 0)
            { # just in case we are inside some buffering
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
 * @param string  $type    What the item is for (script-src, css-src etc.)
 * @param string  $string  The item to add
 *
 * @return void
 */
        public function addCSP(string $type, string $string)
        {
            $this->csp[$type][] = $string;
        }
/**
 * Remove an item from a CSP header - could be 'unsafe-inline', a domain or other stuff
 *
 * @param string  $type    What the item is for (script-src, css-src etc.)
 * @param string  $string  The item to add
 *
 * @return void
 */
        public function removeCSP($type, $string)
        {
            $this->nocsp[$type][] = $string;
        }
/**
 * Set up default CSP headers for a page
 *
 * There will be a basic set of default CSP permissions for the site to function,
 * but individual pages may wish to extend or restrict these.
 *
 * @param \Support\Context   $context    The context object
 *
 * @return void
 */
        public function setCSP(Context $context) : void
        {
            $local = $context->local();
            /** @psalm-suppress PossiblyNullPropertyFetch */
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
                        $csp .= ' '.$key.' '.implode(' ', $val).(isset($this->csp[$key])  ? (' '.implode(' ', $this->csp[$key])) : '').';';
                    }
                }
                if ($local->configval('reportcsp'))
                {
                    $edp = $local->base().'/cspreport/';
                    $csp .= ' report-uri '.$edp.';'; // This is deprecated but widely supported
                    $csp .= ' report-to csp-report;';
                    $this->addheader([
                        'Report-To' => 'Report-To: { "group": "csp-report", "max-age": 10886400, "endpoints": [ { "url": "'.$edp.'" } ] }'
                    ]);
                }
                $this->addheader([
                    'Content-Security-Policy'   => $csp
                ]);
            }
        }
/**
 * Add an item for use in a Cache-Control header
 *
 * @param array  $items  An array of items
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
 */
        public function recaptcha(string $secret) : bool
        {
            if (filter_has_var(INPUT_POST, 'g-recaptcha-response'))
            {
                $data = http_build_query([
                    'secret'    => $secret,
                    'response'  => $_POST['g-recaptcha-response'],
                    'remoteip'  => $_SERVER['REMOTE_ADDR']
                ]);
                $opts = ['http' =>
                    [
                        'method'  => 'POST',
                        'header'  => 'Content-Type: application/x-www-form-urlencoded',
                        'content' => $data
                    ]
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
    }
?>
