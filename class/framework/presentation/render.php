<?php
/**
 * Contains definition of Twig Rendering class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2021 Newcastle University
 * @package Framework\Framework\Presentation
 */
    namespace Framework\Presentation;

    use \Framework\Web\Web;
/**
 * Base class for other Twig like renderers
 */
    abstract class Render
    {
/**
 * @var array Contains string names for the message constants - used for Template variables
 */
        protected static array $msgnames  = ['fwerrmessage', 'fwwarnmessage', 'fwmessage'];
/**
 * @var array    Key/value array of data to pass into template renderer
 */
        protected array $tvals          = [];
/**
 * @var array<array>    Stash away messages so that the renderer can treat them properly
 */
        protected array $messages       = [[], [], []];
/**
 * @var ?object
 */
        protected ?object $engine       = NULL;
/**
 * Initialise template engine
 *
 * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
 */
        public function __construct(\Framework\Local $local, array $options = [])
        {
            $this->clearValues();
            $this->clearMessages();
        }
/**
 * Calls a user defined function with Render object as a parameter.
 * The user can then add extensions, filters etc.
 *
 * @param callable     $fn      A user defined function
 */
        public function extendEngine(callable $fn) : void
        {
            $fn($this->engine);
        }
/**
 * Add a global template variable
 *
 * @param string   $name
 * @param mixed    $value
 */
        abstract public function addGlobal(string $name, $value) : void;
/**
 * Add a template engine extension
 *
 * @param object $plugin
 */
        abstract public function addExtension(object $plugin) : void;
/**
 * Enable debugging mode
 */
        abstract public function enableDebug() : void;
/**
 * Render a template and return the string - do nothing if the template is the empty string
 *
 * @param string    $tpl    The template
 * @param mixed[]   $vals   Values to set for the renderer
 */
        abstract public function getRender(string $tpl, array $vals = []) : string;
/**
 * Render a template - do nothing if the template is the empty string
 *
 * @param string   $tpl       The template
 * @param mixed[]  $vals      Values to set for the twig
 * @param string   $mimeType
 * @param int      $status
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
 * @param array|string    $vname    The name to be used inside the twig or an array of key/value pairs
 * @param mixed           $value    The value to be stored or "" if an array in param 1
 * @param bool            $tglobal  If TRUE add this as a twig global variable
 *
 * @throws \Framework\Exception\InternalError
 */
        public function addval(array|string $vname, $value = '', bool $tglobal = FALSE) : void
        {
            \assert(\is_object($this->engine)); // Should never be called if Twig is not initialised.
            if (!\is_array($vname))
            {
                $vname = [$vname => $value];
            }
            foreach ($vname as $key => $aval)
            {
                if ($tglobal)
                {
                    $this->addGlobal($key, $aval);
                }
                else
                {
                    $this->tvals[$key] = $aval;
                }
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
 * @param int            $kind   The kind of message
 * @param array|string   $value  The value to be stored or an array of values
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
/**
 * Clear out values
 */
        public function clearValues() : void
        {
            $this->tvals = [];
        }
    }
?>