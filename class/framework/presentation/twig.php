<?php
/**
 * Contains definition of Twig Rendering class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2021 Newcastle University
 * @package Framework
 */
    namespace Framework\Presentation;

/**
 * Class that uses Twig to render pages
 */
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
 * @param array    $options
 */
        public function __construct(\Framework\Local $local, array $options)
        {
            parent::__construct($local, $options);
            $twigdir = $local->makebasepath($options['templateDir'] ?? 'twigs');
            $loader = new \Twig\Loader\FilesystemLoader($twigdir);
            foreach (['admin', 'devel', 'edit', 'error', 'users', 'util', 'view'] as $tns)
            {
                $loader->addPath($twigdir.'/framework/'.$tns, $tns);
            }
            foreach (['content', 'info', 'surround'] as $tns)
            {
                $loader->addPath($twigdir.'/'.$tns, $tns);
            }
            //foreach (['util'] as $tns)
            //{
            //    $loader->addPath($twigdir.'/vue/framework/'.$tns, 'vue'.$tns);
            //}
            //foreach (['content'] as $tns)
            //{
            //    $loader->addPath($twigdir.'/vue/'.$tns, 'vue'.$tns);
            //}
            $this->engine = new \Twig\Environment(
                $loader,
                ['cache' => isset($options['cache']) ? $local->makebasepath($options['cache']) : FALSE]
            );
            $this->engine->addExtension(new \Framework\Utility\Plural());
/*
 * A set of basic values that get passed into the TWIG renderer
 *
 * Add new key/value pairs to this array to pass values into the twigs
 */
            $this->engine->addGlobal('base', $local->base());
            $this->engine->addGlobal('assets', $local->assets());
            foreach (self::$msgnames as $mn)
            {
                $this->engine->addGlobal($mn, []);
            }
        }
/**
 * Add a global template variable
 *
 * @param string  $name
 * @param mixed   $value
 */
        public function addGlobal(string $name, $value) : void
        {
            $this->engine->addGlobal($name, $value);
        }
/**
 * Add a template engine extension
 *
 * @param object $plugin
 */
        public function addExtension(object $plugin) : void
        {
            $this->engine->addExtension($plugin);
        }
/**
 * Enable debugging mode
 */
        public function enableDebug() : void
        {
            $this->engine->enableDebug();
        }
/**
 * Render a twig and return the string - do nothing if the template is the empty string
 *
 * @param string    $tpl    The template
 * @param mixed[]   $vals   Values to set for the twig
 */
        public function getRender(string $tpl, array $vals = []) : string
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
            return $this->engine->render($tpl, $this->tvals);
        }
/**
 * Add a value into the values stored for rendering the template
 *
 * @param string|array<mixed>   $vname    The name to be used inside the twig or an array of key/value pairs
 * @param mixed                 $value    The value to be stored or "" if an array in param 1
 * @param bool                  $tglobal  If TRUE add this as a twig global variable
 *
 * @throws \Framework\Exception\InternalError
 */
        public function addval($vname, $value = '', bool $tglobal = FALSE) : void
        {
            \assert(\is_object($this->engine)); // Should never be called if Twig is not initialised.
            if (\is_array($vname))
            {
                foreach ($vname as $key => $aval)
                {
                    if ($tglobal)
                    {
                        $this->engine->addGlobal($key, $aval);
                    }
                    else
                    {
                        $this->tvals[$key] = $aval;
                    }
                }
            }
            elseif ($tglobal)
            {
                $this->engine->addGlobal($vname, $value);
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
 * somewhere in the relevant template (usually at the top of the main body)
 *
 * @param int           $kind   The kind of message
 * @param array|string  $value  The value to be stored or an array of values
 */
        public function message(int $kind, array|string $value) : void
        {
            if (\is_array($value))
            {
                $this->messages[$kind] = \array_merge($this->messages[$kind], $value);
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
 */
        public function clearMessages(?int $kind = NULL) : void
        {
            if (\is_null($kind))
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