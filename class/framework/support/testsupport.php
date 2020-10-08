<?php
/**
 * Contains definition of Test class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 * @package Framework
 * @subpackage SystemSupport
 */
    namespace Framework\Support;

    use \Framework\Local;
    use \Support\Context;
/**
 * A class that handles various site testing related things
 */
    class TestSupport
    {
/** @var Local */
        private $local;
/** @varContext */
        private $context;
/** @var object */
        private $fdt;
/** @var \Framework\Support\FormData */
        private $fdtold;
/** @var \Framework\FormData\AccesBase */
        private $fdtnew;
/** @var bool */
        private $noform = FALSE;
/**
 * Constructor
 *
 * @param Context $context
 * @param string  $type         get, post etc.
 */
        public function __construct(Context $context, string $type)
        {
            $this->context = $context;
            $this->local = $context->local();
            $this->fdtold = $context->formdata('');
            $this->fdtnew = $context->formdata($type);
            $this->noform = $context->web()->method() == 'GET' && !isset($_GET['exist']) && !isset($_GET['cookie']);
        }
/**
 * Displayable version of data
 *
 * @param mixed $pars
 * @param bool $all
 *
 * @return string
 */
        private function display($pars, $all = FALSE) : string
        {
            $x = preg_replace('/array\(/', '[', preg_replace('/,\)/', ']', preg_replace('/\d=>/', '', preg_replace('/\s+/ims', '', var_export($pars, TRUE)))));
            return preg_replace('/\[\)/', '[]', preg_replace('/,/', ', ', $all ? $x : substr($x, 1, strlen($x)-2)));
        }
/**
 * Run tests specified
 */
        private function test(string $func, array $params, $result, bool $throwOK) : bool
        {
            if ($this->noform)
            {
                return TRUE;
            }
            $this->local->addval('array', var_export($_REQUEST, TRUE));
            $msg = $func.'('.$this->display($params).') : '.$this->display($result, TRUE);
            if ($result === 'userid')
            {
                $result = $this->context->user()->getID();
            }
            try
            {
                $res = $this->fdt->{$func}(...$params);
                if (is_object($res))
                {
                    if (is_array($result) && $result[0] == 'iterator')
                    {
                        if ($res instanceof \ArrayIterator)
                        {
                            foreach ($res as $key => $value)
                            {
                                if (!isset($result[1][$key]))
                                {
                                    $this->local->message(Local::ERROR, $msg.' FAIL : got ArrayIterator with incorrect key '.$key.'/'.$value);
                                    return FALSE;
                                }
                                if ($value != $result[1][$key])
                                {
                                    $this->local->message(Local::ERROR, $msg.' FAIL : got ArrayIterator expected '.$key.'/'.$result[1][$key].' got '.$key.'/'.$value);
                                    return FALSE;
                                }
                            }
                            $this->local->message(Local::MESSAGE, $msg.' OK : expected ArrayIterator got '.get_class($res));
                            return TRUE;
                        }
                        $this->local->message(Local::ERROR, $msg.' FAIL : expected ArrayIterator got '.get_class($res));
                    }
                    elseif ($res instanceof \RedBeanPHP\OODBBean)
                    {
                        $this->local->message(Local::MESSAGE, $msg.' OK : expected \RedBeanPHP\OODBBean got '.get_class($res).' id='.$this->display($res->getID(), TRUE));
                        return TRUE;
                    }
                    else
                    {
                        $this->local->message(Local::ERROR, $msg.' FAIL : expected \RedBeanPHP\OODBBean got '.get_class($res));
                    }
                }
                elseif (is_array($result))
                {
                    if (is_array($res))
                    {
                        $diff = array_diff($res, $result);
                        if (empty($diff))
                        {
                            $this->local->message(Local::MESSAGE, $msg.' OK : expected '.$this->display($result, TRUE).' got '.$this->display($res, TRUE));
                            return TRUE;
                        }
                        $this->local->message(Local::ERROR, $msg.' FAIL : expected '.$this->display($result, TRUE).' got '.$this->display($res, TRUE).' diff '.$this->display($diff, TRUE));
                        return FALSE;
                    }
                    $this->local->message(Local::ERROR, $msg.' FAIL : expected array '.$this->display($result, TRUE).' got '.$this->display($res, TRUE));
                }
                else
                {
                    if ($res === $result)
                    {
                        $this->local->message(Local::MESSAGE, $msg.' OK : expected '.$this->display($result, TRUE).' got '.$this->display($res, TRUE));
                        return TRUE;
                    }
                    $this->local->message(Local::ERROR, $msg.' FAIL : expected '.($throwOK ? 'exception' : $this->display($result, TRUE)).' got '.$this->display($res, TRUE));
                }
            }
            catch (\Framework\Exception\BadValue $e)
            {
                $this->local->message($throwOK ? Local::MESSAGE : Local::ERROR, $msg.' throws exception: '.get_class($e).' '.$e->getMessage());
                return $throwOK;
            }
            catch (\Framework\Exception\MissingBean $e)
            {
                $this->local->message($throwOK ? Local::MESSAGE : Local::ERROR, $msg.' throws exception: '.get_class($e).' '.$e->getMessage());
                return $throwOK;
            }
            catch (\Exception $e)
            {
                $this->local->message(Local::ERROR, $msg.' throws exception: '.get_class($e).' '.$e->getMessage());
            }
            return FALSE;
        }
/**
 * Run tests
 *
 * @return void
 */
        public function run(array $tests, bool $old = TRUE)
        {
            $this->fdt = $old ? $this->fdtold : $this->fdtnew;
            foreach ($tests as $test)
            {
                [$func, $params, $result, $ok] = $test;
                $this->test($func, $params, $result, !$ok);
            }
        }
    }
?>