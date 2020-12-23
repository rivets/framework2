<?php
/**
 * Contains definition of Twig Rendering class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2020 Newcastle University
 * @package Framework
 */
    namespace Framework\Presentation;

    use \Config\Config;
    use \Config\Framework as FW;
    use \Framework\Web\Web;

    class Twig extends Render
    {
/**
 * This is a class that maintains values about the local environment and does error handling
 *
 * Template rendering is done in here also so TWIG is initialised in this class. This allows TWIG
 * to be used for things like generating nice offline pages.
 *//**
 * Initialise twig template engine
 *
 * @param bool    $cache    if TRUE then enable the TWIG cache
 *
 * @return void
 */
        public function setuptwig(bool $cache = FALSE) : void
        {
            $twigdir = $this->makebasepath('twigs');
            $loader = new \Twig\Loader\FilesystemLoader($twigdir);
            foreach (['admin', 'devel', 'edit', 'error', 'users', 'util', 'view'] as $tns)
            {
                $loader->addPath($twigdir.'/framework/'.$tns, $tns);
            }
            foreach (['content', 'info', 'surround'] as $tns)
            {
                $loader->addPath($twigdir.'/'.$tns, $tns);
            }
            foreach (['util'] as $tns)
            {
                $loader->addPath($twigdir.'/vue/framework/'.$tns, 'vue'.$tns);
            }
            foreach (['content'] as $tns)
            {
                $loader->addPath($twigdir.'/vue/'.$tns, 'vue'.$tns);
            }
            $this->twig = new \Twig\Environment(
                $loader,
                ['cache' => $cache ? $this->makebasepath('twigcache') : FALSE]
            );
            $this->twig->addExtension(new \Framework\Utility\Plural());
/*
 * A set of basic values that get passed into the TWIG renderer
 *
 * Add new key/value pairs to this array to pass values into the twigs
 */
            $this->twig->addGlobal('base', $this->base());
            $this->twig->addGlobal('assets', $this->assets());
            foreach (self::$msgnames as $mn)
            {
                $this->twig->addGlobal($mn, []);
            }
            $this->tvals = [];
        }
/**
 * Calls a user defined function with the twig object as a parameter.
 * The user can then add extensions, filters etc.
 *
 * @param callable     $fn      A user defined function
 *
 * @return void
 */
        public function extendtwig(callable $fn) : void
        {
            $fn($this->twig);
        }
/**
 * Return TRUE if Twig is enabled
 *
 * @return bool
 */
        public function hastwig()
        {
            return is_object($this->twig);
        }
/**
 * Render a twig and return the string - do nothing if the template is the empty string
 *
 * @param string    $tpl    The template
 * @param mixed[]   $vals   Values to set for the twig
 *
 * @return string
 */
        public function getrender(string $tpl, array $vals = [])
        {
            if ($tpl === '')
            { // no template so no output
                return '';
            }
            foreach ($this->messages as $ix => $mvals)
            {
                if (!empty($mvals))
                {
                    $this->addval(self::$msgnames[$ix], $mvals);
                }
            }
            $this->clearMessages();
            $this->addval($vals); // set up any values that have been passed
            /** @psalm-suppress PossiblyNullReference */
            return $this->twig->render($tpl, $this->tvals);
        }
/**
 * Render a twig - do nothing if the template is the empty string
 *
 * @param string   $tpl       The template
 * @param mixed[]  $vals      Values to set for the twig
 * @param string   $mimeType
 * @param int      $status
 *
 * @return void
 */
        public function render(string $tpl, array $vals = [], string $mimeType = Web::HTMLMIME, int $status = \Framework\Web\StatusCodes::HTTP_OK) : void
        {
            if ($tpl !== '')
            {
                Web::getinstance()->sendstring($this->getrender($tpl, $vals), $mimeType, $status);
            }
        }
/**
 * Add a value into the values stored for rendering the template
 *
 * @param string|array<mixed>   $vname    The name to be used inside the twig or an array of key/value pairs
 * @param mixed                 $value    The value to be stored or "" if an array in param 1
 * @param bool                  $tglobal  If TRUE add this as a twig global variable
 *
 * @throws \Framework\Exception\InternalError
 *
 * @return void
 */
        public function addval(string|array $vname, mixed $value = '', bool $tglobal = FALSE) : void
        {
            assert(is_object($this->twig)); // Should never be called if Twig is not initialised.
            if (is_array($vname))
            {
                foreach ($vname as $key => $aval)
                {
                    if ($tglobal)
                    {
                        $this->twig->addGlobal($key, $aval);
                    }
                    else
                    {
                        $this->tvals[$key] = $aval;
                    }
                }
            }
            elseif ($tglobal)
            {
                $this->twig->addGlobal($vname, $value);
            }
            else
            {
                $this->tvals[$vname] = $value;
            }
        }
/**
 * Add a message into the messages stored for rendering the template
 *
 * The currently supported values for kind are :
 *
 *      \Framework\Local\ERROR
 *      \Framework\Local\WARNING
 *      \Framework\Local\MESSAGE
 *
 * To have your Twig deal with these you need
 *
 * {% include '@util/message.twig %}
 *
 * somewhere in the relevant twig (usually at the top of the main body)
 *
 * @param int                   $kind   The kind of message
 * @param string|array<string>  $value  The value to be stored or an array of values
 *
 * @return void
 */
        public function message(int $kind, string|array $value) : void
        {
            if (is_array($value))
            {
                $this->messages[$kind] = array_merge($this->messages[$kind], $value);
            }
            else
            {
                $this->messages[$kind][] = $value;
            }
        }
/**
 * Clear out messages
 *
 * @param ?int    $kind   Either NULL for all messages or a specific kind
 *
 * @return void
 */
        public function clearMessages(?int $kind = NULL) : void
        {
            if (is_null($kind))
            {
                $this->messages = [[], [], []];
            }
            else
            {
                $this->messages[$kind] = [];
            }
        }
    }
?>