<?php
/**
 * A trait that implements the CSP handling for the Web class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2019-2021 Newcastle University
 * @package Framework
 */
    namespace Framework\Web;

    use \Config\Framework as FW;
/**
 * Adds functions for adding and removinng CSP
 */
    trait CSP
    {
/**
 * @var array<string>   Holds values that need to be added to CSP headers.
 */
        private $csp        = [];
/**
 * @var array<string>   Holds values that need to be removed from CSP headers.
 */
        private $nocsp      = [];
/**
 * @var which  CSP fields to check for hostnames
 */
        private static $cspFields = ['css' => 'style-src', 'js' => 'script-src', 'font' => 'font-src', 'img' => 'img-src'];
/**
 * compute, save and return a hash for use in a CSP header
 *
 * @param string  $type    What the hash is for (script-src, css-src etc.)
 * @param string  $data    The data to be hashed
 *
 * @return string Returns the hash
 * @psalm-suppress PossiblyUnusedMethod
 */
        public function saveCSP(string $type, string $data) : string
        {
            $hash = \Framework\Support\Security::getinstance()->hash($data);
            $this->addCSP([$type => ["'".$hash."'"]]);
            return $hash;
        }
/**
 * Add an item for use in a CSP header - could be 'unsafe-inline', a domain or other stuff
 *
 * @param string|array<mixed>  $type    What the item is for (script-src, style-src etc.)
 * @param string               $host    The host to add
 *
 * @return void
 */
        public function addCSP($type, string $host = '') : void
        {
            if (!is_array($type))
            {
                assert($host !== '');
                $type = [$type => [$host]];
            }
            foreach ($type as $t => $h)
            {
                if (!is_array($h))
                {
                    $h = [$h];
                }
                if (!isset($this->csp[$t]))
                {
                    $this->csp[$t] = $h;
                }
                else
                {
                    $this->csp[$t] = array_merge($this->csp[$t], $h);
                }
            }
        }
/**
 * Remove an item from a CSP header - could be 'unsafe-inline', a domain or other stuff
 *
 * @param string|array<string>  $type    What the item is for (script-src, style-src etc.)
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
                if (isset($this->csp[$t]))
                {
                    $this->csp[$t] = array_diff($this->csp[$t], is_array($h) ? $h : [$h]);
                }
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
                foreach ($this->csp as $key => $val)
                {
                    if (!empty($val))
                    {
                        $csp .= ' '.$key.' '.implode(' ', $val).';';
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
 * Initialise CSP
 *
 * If the data is in the database then use that, if not thensetup the table from Config::$defaultCSP
 *
 * @return void
 */
        public function initCSP() : void
        {
            $local = $this->context->local();
            if ($local->configval('usecsp'))
            {
                if (\Support\SiteInfo::tableExists(\Config\Framework::CSP))
                { // we have the table
                    $this->csp = [];
                    foreach (\R::findAll(\Config\Framework::CSP) as $csp)
                    {
                        $this->csp[$csp->type][] = $csp->host;
                    }
                }
                else
                { // copy the default set
                    $this->csp = \Config\Config::$defaultCSP;
                    foreach ($this->csp as $type => $host)
                    { // now set up the database for future working...
                        foreach ($host as $h)
                        {
                            $bn = \R::dispense(\Config\Framework::CSP);
                            $bn->type = $type;
                            $bn->host = $h;
                            $bn->essential = 1;
                            \R::store($bn);
                        }
                    }
                }
            }
        }
/**
 * Get the CSP values
 *
 * @return array<string>
 */
        public function getCSP() : array
        {
            return $this->csp;
        }
/**
 * Check to see if we need to update the CSP data for a new host
 *
 * @param string $url       The url for the resource
 * @param string $type      js, css etc.
 * @param bool   $essential If TRUE this is essential to site functioning
 *
 * @return bool TRUE if source was added
 */
        public function checkCSP(string $url, string $type, bool $essential = TRUE) : bool
        {
            if (isset(self::$cspFields[$type]))
            {
                $host = \parse_url($url, PHP_URL_HOST);
                if ($host !== '' && \R::findOne(FW::CSP, 'type=? and host=?', [$type, $host]) === NULL)
                { // it might be hidden behind a pattern
                    $x = \explode('.', $host);
                    if (\count($x) >= 3)
                    {
                        $x[0] = '*';
                        $x = \implode('.', $x);
                        if (\R::findOne(FW::CSP, 'type=? and host=?', [$type, $x]) === NULL)
                        { // doesn't seem to be in there
                            $bn = \R::dispense(\Config\Framework::CSP);
                            $bn->type = $type;
                            $bn->host = $host;
                            $bn->essential = $essential ? 1 : 0;
                            \R::store($bn);
                            return TRUE;
                        }
                    }
                }
            }
            return FALSE;
        }
    }
?>