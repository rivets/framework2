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
 * Parse the raw input data
 *
 * @param string  $data
 *
 * @return array
 */
        private function parse(string $data) : array
        {
            $result = [];
            $contentType = $_SERVER['CONTENT_TYPE'];

            if (!preg_match('/boundary=(.*)$/is', $contentType, $m))
            {
                return NULL;
            }

            $sep = $m[1];
            $parts = preg_split('/\\R?-+/' . preg_quote($sep, '/') . '/s', $data);
            array_pop($parts);

            foreach ($parts as $part)
            {
                if (!empty($part))
                {
                    [$headers, $value] = preg_split('/\\R\\R/', $part, 2);
                    foreach (preg_split('/\\R/s', $headers, -1, PREG_SPLIT_NO_EMPTY) as $bit)
                    {
                        $hd = $this->parsePart($bit);
                        if (isset($hd['content-disposition']['name']))
                        {
                            $result[$d['content-disposition']['name']] = $value;
                        }
                    }
                }
            }
            return $result;
        }
/**
 * Parses body param headers
 *
 * @param string $data
 *
 * @return string[]
 */
        private function parsePart(string $data) : array
        {
            $headers = [];
            if (strpos($part, ':') !== FALSE)
            {
                [$name, $value] = explode(':', $part, 2);
                $name = strtolower(trim($name));
                if ($name == 'content-disposition')
                {
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
                                $sname = strtolower(trim($sname));
                                $svalue = trim(trim($svalue), '"');
                                $headers[$name][$sname] = $svalue;
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
            if ($data !== FALSE || $data !== '')
            {
                $ct = explode(';', $_SERVER['CONTENT_TYPE'] ?? '');
                switch (trim($ct[0]))
                {
                case '':
                case 'application/x-www-form-urlencoded':
                    parse_str($data, $this->super);
                    break;
                case 'multipart/form-data':
                    $this->super = $this->parse($data);
                    break;
                default:
                    throw new \Framework\Exception\BadValue('Unknown encoding type PUT/PATCH: '.$_SERVER['CONTENT_TYPE']);
                    break;
                }
            }
        }
    }
?>
