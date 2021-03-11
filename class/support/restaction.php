<?php
/**
 * Contains definition of a trait that implements RESTful drivers for SiteAction
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2012-2020 Newcastle University
 * @package Framework
 */
    namespace Support;

    use \Framework\Web\StatusCodes;
    use \Framework\Web\Web;
    use \Support\Context;
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
 *
 * @return string|array
 */
        //public function crudDriver(Context $context) : string
        //{
        //    $method = strtolower($context->web()->method());
        //    if (!method_exists(static::class, $method))
        //    {
        //        throw new \Framework\Exception\BadOperation($method.' is not supported');
        //    }
        //    return $this->{$method}($context, $context->rest());
        //}
/**
 * Call functions based on the method and the pattern in the URL. Driven by the contents of the pattern attribute
 *
 * @param Context      $context    The context object for the site
 *
 * @return string|array
 */
        public function patternDriver(Context $context) : string
        {
            $method = strtolower($context->web()->method());
            if (!isset(self::$patterns[$method]))
            {
                throw new \Framework\Exception\BadOperation($method.' is not supported');
            }
            $url = $context->rest();
            $uleng = count($url);
            foreach (self::$patterns[$method] as [$ptnmap, $fn, $oktpl, $errtpl])
            {
                if (count($ptnmap) != $uleng)
                { // looking for more or less data than we actually have so this is not a match
                    continue;
                }
                $data = [];
                $errors = [];
                foreach ($ptnmap as $ix => [$ptn, $map])
                {
                    if (!preg_match('#^'.$ptn.'$#i', $url[$ix]))
                    { // did not match the pattern so try the next set
                        continue 2;
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
                                $mfn = array_shift($check);
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
                    $context->local()->message(\Framework\Local::ERROR, $errrors);
                    return $errtpl  ;
                }
                if ($fn === NULL)
                { // no function to call just return the template
                    return $oktpl;
                }
                [$ok, $msg] = $this->{$fn}($context, $data);
                if (!$ok)
                { // the function failed - return error template
                    $context->local()->message(\Framework\Local::ERROR, $msg);
                    return $errtpl;
                }
                if (is_array($msg) || $msg !== '')
                { // there was a message - could be one in a string or several in an array
                    $context->local()->message(\Framework\Local::MESSAGE, $msg);
                }
                return $oktpl;
            }
            $context->local()->addval('page', $context->action().'/'.implode('/', $url));
            return '@error/404.twig';
        }
/**
 * Check and fetch a bean
 *
 * @param Context $context
 * @param int     $id
 * @param array   $data
 * @param string  $beanType
 *
 * @return array
 */
        protected function checkID(Context $context, string $id, array $data, string $beanType) : array
        {
            return [TRUE, $context->load($beanType, (int) $id)];
        }
    }
?>