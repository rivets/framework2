<?php
/**
 * Contains the definition of Formdata PUT or PATCH support class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 * @package Framework
 * @subpackage FormData
 */
    namespace Framework\FormData;

/**
 * A class that provides helpers for accessing PUT OR PATCH form data
 */
    class Put extends AccessBase
    {
/**
 * Parses the form data and extract the fields
 *
 * @return array
 */
        private function parse() : array
        {
            $result = [];
            $data = file_get_contents('php://input'); //get raw input data
            if (empty($data))
            {
                return NULL;
            }

            $contentType = $_SERVER['CONTENT_TYPE'];

            if (!preg_match('/boundary=(.*)$/is', $contentType, $m))
            {
                return NULL;
            }

            $sep = $m[1];
            $parts = preg_split('/\R?-+/' . preg_quote($sep, '/') . '/s', $data);
            array_pop($parts);

            foreach ($parts as $part)
            {
                if (!empty($part))
                {
                    [$headers, $value] = preg_split('/\R\R/', $part, 2);
                    $headers = $this->parseHeaders($headers);
                    if (isset($headers['content-disposition']['name']))
                    {
                        $result[$headers['content-disposition']['name']] = $value;
                    }
                }
            }
            return $result;
        }
/**
 * Parses body param headers
 *
 * @param string  $data  The header data
 *
 * @return array
 */
        private function parseHeaders(string $data) : array
        {
            $headers = [];
            foreach (preg_split('/\\R/s', $data, -1, PREG_SPLIT_NO_EMPTY) as $part)
            {
                if (strpos($part, ':') !== FALSE)
                {
                    [$name, $value] = explode(':', $part, 2);
                    $name = strtolower(trim($name));
                    $value = trim(value);
                    if (strpos($value, ';') === FALSE)
                    {
                        $headers[$name] = $value;
                    }
                    else
                    {
                        $headers[$name] = [];
                        foreach (explode(';', $value) as $part)
                        {
                            $part = trim($part);
                            if (strpos($part, '=') === FALSE)
                            {
                                $headers[$name][] = $part;
                            }
                            else
                            {
                                [$sname, $svalue] = explode('=', $part, 2);
                                $headers[$name][strtolower(trim($sname))] = trim($svalue, "\" \t\n\r\0\x0B");
                            }
                        }
                    }
                }
            }
            return $headers;
        }
/**
 * Constructor
 */
        public function __construct()
        {
            parent::__construct(NULL);

            $data = file_get_contents('php://input');
            $ct = explode(';', $_SERVER['CONTENT_TYPE'] ?? '');
            switch (trim($ct[0]))
            {
            case '':
            case 'application/x-www-form-urlencoded':
                parse_str($data, $this->super);
                break;
            case 'multipart/form-data':
                $this->super = $this->parse();
                break;
            default:
                throw new \Framework\Exception\BadValue('Unknown encoding type PUT/PATCH: '.$_SERVER['CONTENT_TYPE']);
                break;
            }
        }
    }
?>