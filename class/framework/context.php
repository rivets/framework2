<?php
/**
 * Contains the definition of the Context class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2020 Newcastle University
 * @package Framework
 */
    namespace Framework;

/**
 * A class that stores various useful pieces of data for access throughout the rest of the system.
 */
    class Context extends Support\ContextBase
    {
/**
 * Do we have a logged in admin user?
 *
 * @return bool
 */
        public function hasAdmin() : bool
        {
            /** @psalm-suppress PossiblyNullReference */
            return $this->hasuser() && $this->user()->isadmin();
        }
/**
 * Do we have a logged in developer user?
 *
 * @return bool
 */
        public function hasDeveloper() : bool
        {
            /** @psalm-suppress PossiblyNullReference */
            return $this->hasuser() && $this->user()->isdeveloper();
        }
/*
 ***************************************
 * Miscellaneous utility functions
 ***************************************
 */
/**
 * Set up pagination data
 *
 * @param ?int    $count If not NULL then set pages based on this
 *
 * @return void
 */
        public function setpages($count = NULL) : void
        {
            $fdt = $this->formData('get');
            $psize = $fdt->fetch('pagesize', 10, FILTER_VALIDATE_INT);
            $values = [
                'page'      => $fdt->fetch('page', 1, FILTER_VALIDATE_INT), // just in case there is any pagination going on
                'pagesize'  => $psize,
            ];
            if ($count != NULL)
            {
                $values['pages'] = (int) \floor((($count % $psize > 0) ? ($count + $psize) : $count) / $psize);
            }
            $this->local()->addval($values);
        }
/**
 * Generate a Location header for within this site
 *
 * @param string    $where      The page to divert to
 * @param bool      $temporary  TRUE if this is a temporary redirect
 * @param string    $msg        A message to send
 * @param bool      $nochange   If TRUE then reply status codes 307 and 308 will be used rather than 301 and 302
 * @param bool      $use303     If TRUE then 303 will be used instead of 307
 *
 * @return void
 * @psalm-return never-return
 */
        public function divert(string $where, bool $temporary = TRUE, string $msg = '', bool $nochange = FALSE, bool $use303 = FALSE) : void
        {
            $this->web()->relocate($this->local()->base().$where, $temporary, $msg, $nochange, $use303);
            /* NOT REACHED */
        }
/**
 * Return an iso formatted time for NOW  in UTC
 *
 * @return string
 */
        public function utcnow() : string
        { /** @psalm-suppress InvalidOperand */
            return \R::isodatetime(time() - date('Z'));
        }
/**
 * Return an iso formatted time in UTC
 *
 * @param string       $datetime
 *
 * @return string
 */
        public function utcdate(string $datetime) : string
        { /** @psalm-suppress InvalidOperand */
            return \R::isodatetime(strtotime($datetime) - date('Z'));
        }
/*
 ***************************************
 * Setup the Context - the constructor is hidden in Singleton
 ***************************************
 */
/**
 * Initialise the context and return self
 *
 * @return \Framework\Support\ContextBase
 */
        public function setup() : \Framework\Support\ContextBase
        {
            parent::setup();
/**
 * Check to see if non-admin users are being excluded
 */
            $this->local()->adminOnly($this->hasAdmin());
            return $this;
        }
    }
?>
