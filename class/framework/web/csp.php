<?php
/**
 * A trait that implements the CSP handling for the Web class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2019-2020 Newcastle University
 * @package Framework
 */
    namespace Framework\Web;

/**
 * Adds functions for adding and removinng CSP
 */
    trait CSP
    {
/**
 * @var array   Holds values that need to be added to CSP headers.
 */
        private $csp        = [];
/**
 * @var array   Holds values that need to be removed from CSP headers.
 */
        private $nocsp      = [];
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
                    $this->csp[$t] = $this->csp[$t] + $h;
                }
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
 */
        public function initCSP()
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
                            \R::store($bn);
                        }
                    }
                }
            }
        }
/**
 * Get the CSP values
 *
 * @return array
 */
        public function getCSP() : array
        {
            return $this->csp;
        }
    }
?>