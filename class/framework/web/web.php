<?php
    namespace Framework\Web;
    
/**
 * Contains definition of ther Web class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2015 Newcastle University
 */
/**
 * A class that handles various web related things.
 */
    class Web
    {
        use \Utility\Singleton;

 	const HTMLMIME	= 'text/html; charset="utf-8"';

/**
 * @var array   Holds values for headers that are required. Keyed by the name of the header
 */
        private $headers    = array();
/**
 * Generate a Location header
 *
 * @param string		$where		The URL to divert to
 * @param boolean		$temporary	TRUE if this is a temporary redirect
 * @param string		$msg		A message to send
 * @param boolean		$nochange	If TRUE then reply status codes 307 and 308 will be used rather than 301 and 302
 */
	public function relocate($where, $temporary = TRUE, $msg = '', $nochange = FALSE)
	{
	    if ($temporary)
	    {
		$code = $nochange ? StatusCodes::HTTP_TEMPORARY_REDIRECT : StatusCodes::HTTP_FOUND;
	    }
	    else
	    {
/**
 * @todo Check status of 308 code which should be used if nochage is TRUE. May not yet be official.
 */
		$code = StatusCodes::HTTP_MOVED_PERMANENTLY;		
	    }
	    $this->addheader('Location', $where);
	    $this->sendstring($msg, self::HTMLMIME);
	    exit;
	}
/**
 * output a header and msg - this never returns
 *
 * @param number	$code	The return code
 * @param string	$msg	The message (or '')
 */
	private function sendhead($code, $msg)
	{
	    $this->sendheaders(StatusCodes::httpHeaderFor($code));
	    if ($msg !== '')
	    {
		echo '<p>'.$msg.'</p>';
	    }
	    exit;
	}
/**
 * Check for a range request and check it
 *
 * Media players ask for the file in chunks.
 *
 * @param integer	$size	The size of the output data
 * @param mixed		$code	The HTTP return code or ''
 *
 * @return array
 */
	public function hasrange($size, $code = StatusCodes::HTTP_OK)
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
 * @param integer	$code	The HTTP return code
 * @param string	$mtype	The mime-type of the file
 * @param string 	$length	The length of the data
 * @param string	$name	A file name
 *
 * @return void
 */
	public function sendheaders($code, $mtype = '', $length = '', $name = '')
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
 * Send a 304 response
 *
 * @param	string		$etag	An entity tag
 * @param	integer		$maxage	Maximum age for page in seconds
 *
 * @return void
 */
	public function send304($etag, $maxage)
	{
	    $this->addheader([
		'Etag'	=> '"'.$etag.'"',
		'Cache-Control'	=> 'maxage='.$maxage
	    ]);
	    $this->sendheaders(StatusCodes::HTTP_NOT_MODIFIED);
	}
/**
 * Generate a 400 Bad Request error return
 *
 * @param string		$msg	A message to be sent
 */
	public function bad($msg = '')
	{
	    $this->sendhead(StatusCodes::HTTP_BAD_REQUEST, $msg);
	}
/**
 * Generate a 403 Access Denied error return
 *
 * @param string	$msg	A message to be sent
 */
	public function noaccess($msg = '')
	{
	    $this->sendhead(StatusCodes::HTTP_FORBIDDEN, $msg);
	}
/**
 * Generate a 404 Not Found error return
 *
 * @param string	$msg	A message to be sent
 */
	public function notfound($msg = '')
	{
	    $this->sendhead(StatusCodes::HTTP_NOT_FOUND, $msg);
	}
/**
 * Generate a 416 Not Satisfiable error return
 *
 * @param string	$msg	A message to be sent
 */
	public function notsatisfiable($msg = '')
	{
	    $this->sendhead(StatusCodes::HTTP_REQUESTED_RANGE_NOT_SATISFIABLE, $msg);
	}
/**
 * Generate a 500 Internal Error error return
 *
 * @param string		$msg	A message to be sent
 */
	public function internal($msg = '')
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
	public function sendfile($path, $name = '', $mime = '')
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
 * @param integer	$code	The HTTP return code
 *
 * @return void
 */
	public function sendstring($value, $mime = '', $code = StatusCodes::HTTP_OK)
	{
	    $this->debuffer();
 	    list($code, $range, $length) = $this->hasrange(strlen($value), $code);
	    $this->sendheaders($code, $mime, $length);
	    echo empty($range) ? $value : substr($value, $range[0], $length);
	}
/**
 * Deliver JSON response.
 *
 * @param object    $res
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
 * @param string        $key	Either an array of key/value pairs or the key for the value that is in the second parameter
 * @param string        $value
 *
 * @return void
 */
        public function addheader($key, $value = '')
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
        public function putheaders()
        {
            foreach ($this->headers as $name => $vals)
            {
                foreach ($vals as $v)
                {
                    header($name.': '.$v);
                }
            }
        }
/**
 * Check to see if the client accepts gzip encoding
 *
 * @return boolean
 */
        public function acceptgzip()
        {
            return filter_has_var(INPUT_SERVER, 'HTTP_ACCEPT_ENCODING') &&
	        substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') > 0;
        }
/**
 * What kind of request was this?
 *
 * @return string
 */
        public function method()
        {
            return $_SERVER['REQUEST_METHOD'];
        }
/**
 * Is this a POST?
 *
 * @return boolean
 */
        public function ispost()
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
    }
?>
