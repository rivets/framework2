<?php
/**
 * Contains the definition of the Context class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2022 Newcastle University
 * @package Framework
 */
    namespace Framework;

    use \Framework\Exception\BadValue;
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
            return $this->hasUser() && $this->user()->isAdmin();
        }
/**
 * Do we have a logged in developer user?
 *
 * @return bool
 */
        public function hasDeveloper() : bool
        {
            /** @psalm-suppress PossiblyNullReference */
            return $this->hasUser() && $this->user()->isDeveloper();
        }
/**
 * Check for a role
 *
 * @return bool
 */
	public function hasRole(string $context, string $role) : bool
	{
	    return $this->hasUser() && \is_object($this->user()->hasRole($context, $role));
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
 */
        public function setPages(?int $count = NULL, int $pageSize = 10) : void
        {
            $fdt = $this->formData('get');
            try
            {
                $page = (int) $fdt->fetch('page', 1, \FILTER_VALIDATE_INT);
                if ($page <= 0)
                {
                    $page = 1;
                }
            }
            catch (BadValue)
            {
                $page = 1;
            }
            try
            {
                $psize = (int) $fdt->fetch('pagesize', $pageSize, \FILTER_VALIDATE_INT);
                if ($psize <= 0)
                {
                    $psize = $pageSize;
                }
            }
            catch (BadValue)
            {
                $psize = $pageSize;
            }
            $values = [
                'page'      => $page,
                'pagesize'  => $psize,
            ];
            if ($count != NULL)
            {
                $values['pages'] = (int) \floor((($count % $psize > 0) ? ($count + $psize) : $count) / $psize);
                if ($values['page'] > $values['pages'])
                {
                    $values['page'] = $values['pages'];
                }
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
 * @psalm-return never-return
 */
        public function divert(string $where, bool $temporary = TRUE, string $msg = '', bool $nochange = FALSE, bool $use303 = FALSE) : never
        {
            $this->web()->relocate($this->local()->base().$where, $temporary, $msg, $nochange, $use303);
            /* NOT REACHED */
        }
/**
 * Return an iso formatted time for NOW  in UTC
 */
        public function utcnow() : string
        { /** @psalm-suppress InvalidOperand */
            return \R::isodatetime(time() - (int) date('Z'));
        }
/**
 * Return an iso formatted time in UTC
 */
        public function utcdate(string $datetime) : string
        { /** @psalm-suppress InvalidOperand */
            return \R::isodatetime(\strtotime($datetime) - (int) \date('Z'));
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