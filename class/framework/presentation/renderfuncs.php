<?php
/**
 * A trait that implements the functions to call a renderer
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2019-2022 Newcastle University
 * @package Framework\Framework\Presentation
 */
    namespace Framework\Presentation;

    use \Framework\Support\MessageType as Msg;
    use \Support\Context;
/**
 * Adds functions for calling the renderer
 */
    trait RenderFuncs
    {
/**
 * @var ?Render Template engine
 */
        private ?Render $renderer = NULL;
/**
 * Setup a renderer if wanted
 */
        public function initRender(array $render) : void
        {
            if (!empty($render))
            { // we want a renderer - this setups
                $class = '\\Framework\\Presentation\\'.$render[0];
                $this->renderer = new $class($this, $render[1]);
            }
        }
/**
 * Calls a user defined function with the twig object as a parameter.
 * The user can then add extensions, filters etc.
 *
 * @param callable     $fn      A user defined function
 */
        public function extendRenderer(callable $fn) : void
        {
            $this->renderer->extendEngine($fn);
        }
/**
 * Return TRUE if a renderer is enabled
 */
        public function hasRenderer() : bool
        {
            return \is_object($this->renderer);
        }
/**
 * Render a twig and return the string - will do nothing if the template is the empty string
 *
 * @param string    $tpl    The template
 * @param mixed[]   $vals   Values to set for the template
 */
        public function getRender(string $tpl, array $vals = []) : string
        {
            return $this->renderer->getRender($tpl, $vals);
        }
/**
 * Render a template - will do nothing if the template is the empty string
 *
 * @param string   $tpl       The template
 * @param mixed[]  $vals      Values to set for the template
 * @param string   $mimeType
 * @param int      $status
 */
        public function render(string $tpl, array $vals = [], string $mimeType = \Framework\Web\Web::HTMLMIME, int $status = \Framework\Web\StatusCodes::HTTP_OK) : void
        {
            if ($tpl !== '')
            {
                \Framework\Web\Web::getinstance()->sendstring($this->renderer->getrender($tpl, $vals), $mimeType, $status);
            }
        }
/**
 * Add a value into the values stored for rendering the template
 *
 * @param array|string   $vname    The name to be used inside the template or an array of key/value pairs
 * @param mixed          $value    The value to be stored or '' if an array in param 1
 * @param bool           $tglobal  If TRUE add this as a template global variable
 *
 * @throws \Framework\Exception\InternalError
 */
        public function addval(array|string $vname, $value = '', bool $tglobal = FALSE) : void
        {
            $this->renderer->addval($vname, $value, $tglobal);
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
 * To have your Template engine deal with these you need
 *
 * {% include '@util/message.twig %}
 *
 * somewhere in the relevant template (usually at the top of the main body)
 *
 * @param int           $kind   The kind of message
 * @param array|string  $value  The value to be stored or an array of values
 */
        public function message(int|Msg $kind, array|string $value) : void
        {
            $this->renderer->message($kind, $value);
        }
/**
 * Clear out messages
 *
 * @param ?int    $kind   Either NULL for all messages or a specific kind
 */
        public function clearMessages(?int $kind = NULL) : void
        {
            $this->renderer->clearmessages($kind);
        }
/**
 * Clear out values
 */
        public function clearValues() : void
        {
            $this->renderer->clearValues();
        }
/**
 * Make a template file if we have permission and it does not exist already
 *
 * @throws \Framework\Exception\InternalError
 */
        public function makeTemplate(Context $context, array $fileName) : void
        {
            $this->renderer->makeTemplate($context, $fileName);
        }
    }
?>