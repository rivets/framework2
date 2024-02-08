<?php
/**
 * Contains definition of a trait that implements RESTful drivers for SiteAction
 *
 * @author Lindsay Marshall <lindsay.marshall@newcastle.ac.uk>
 * @copyright 2021-2024 Newcastle University
 * @package Framework
 */
    namespace Support;

    use \Framework\Exception\BadOperation;
    use \Framework\Local;
/**
 * A class that all provides a base class for any class that wants to implement a site action
 *
 * Common functions used across the various sub-classes should go in here
 */
    trait RESTAction
    {
/**
 * Call functions based on the method
 *
 * @param Context    $context    The context object for the site
 */
        public function crudDriver(Context $context) : array|string
        {
            $method = \strtolower($context->web()->method());
            if (!\method_exists(static::class, $method))
            {
                throw new BadOperation($method.' is not supported');
            }
            return $this->{$method}($context, $context->rest());
        }
/**
 * Call functions based on the method and the pattern in the URL. Driven by the contents of the pattern attribute
 *
 * @param Context      $context    The context object for the site
 */
        public function patternDriver(Context $context) : array|string
        {
            $method = \strtolower($context->web()->method());
            if (!isset(self::$patterns[$method]))
            {
                throw new BadOperation($method.' is not supported');
            }
            $url = $context->rest();
            $uleng = \count($url);
            foreach (self::$patterns[$method] as [$ptnmap, $fn, $oktpl, $errtpl])
            {
/*
 * The next test could be != but that would currently make having optional fields tricky.
 */
                if (\count($ptnmap) < $uleng)
                { // looking for more data than we actually have so this is not a match
                    $tpl = $this->checkPtns($context, $url, $ptnmap, $fn, $oktpl, $errtpl);
                    if ($tpl !== FALSE)
                    { // the result is a template (or potentially an array) Messages will have been set up already
                        return $tpl;
                    }
                }
            }
            $context->local()->addval('page', $context->action().'/'.implode('/', $url)); // set up page name
            return '@error/404.twig';
        }
/**
 * Check patterns and apply functions
 */
        private function checkPtns(Context $context, array $url, array $ptnmap, string $fn, ?string $oktpl, string $errtpl) : null|string|bool
        {
            $data = [];
            $errors = [];
            foreach ($ptnmap as $ix => [$ptn, $map])
            {
                if (!\preg_match('#^'.$ptn.'$#i', $url[$ix]))
                { // did not match the pattern so try the next set
                    return FALSE;
                }
                if (empty($map))
                {
                    $data[$ix] = [$url[$ix]];
                }
                else
                {
                    foreach ($map as $check)
                    {
                        $data[$ix][] = $url[$ix];
                        if ($check !== NULL)
                        {
                            $mfn = \array_shift($check);
                            [$ok, $dd] =  $this->{$mfn}($context, $url[$ix], $data, ...$check);
                            if (!$ok)
                            { // error of some kind - move on to check the rest.
                                $errors[] = $dd;
                            }
                            $data[$ix][] = $dd;
                        }
                    }
                }
            }
            if (!empty($errors))
            { // we matched the URL but there were errors so report them and return the error template
                $context->local()->message(Local::ERROR, $errors);
                return $errtpl;
            }
            if ($fn === NULL)
            { // no function to call just return the template
                return $oktpl;
            }
            [$ok, $msg] = $this->{$fn}($context, $data);
            if (!$ok)
            { // the function failed - return error template
                $context->local()->message(Local::ERROR, $msg);
                return $errtpl;
            }
            if (\is_array($msg) || $msg !== '')
            { // there was a message - could be one in a string or several in an array
                $context->local()->message(Local::MESSAGE, $msg);
            }
            return $oktpl;
        }
/**
 * Check and fetch a bean - a utility function for specification in the pattern tables
 *
 * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
 */
        protected function checkID(Context $context, int $id, array $data, string $beanType) : array
        {
            return [TRUE, $context->load($beanType, $id)];
        }
    }
?>