<?php
/**
 * Contains definition of Test class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020-2022 Newcastle University
 * @package Framework
 * @subpackage SystemSupport
 */
    namespace Framework\Support;

    use \Framework\Support\MessageType as Msg;
    use \Support\Context;
/**
 * A class that handles various site testing related things
 */
    class TestSupport
    {
        private readonly \Framework\Local $local;
        private readonly \Framework\FormData\AccessBase $fdt;
        private bool $noform = FALSE;
/**
 * Constructor
 *
 * @param Context $context
 * @param string  $type         get, post etc.
 */
        public function __construct(private readonly Context $context, string $type)
        {
            $this->local = $context->local();
            $this->fdt = $context->formdata($type);
            $this->noform = $context->web()->method() == 'GET' && !isset($_GET['exist']) && !isset($_GET['cookie']);
        }
/**
 * Displayable version of data
 *
 * @param mixed $pars
 */
        private function display($pars, bool $all = FALSE) : string
        {
            $x = (string) \preg_replace('/array\(/', '[', \preg_replace('/,\)/', ']', \preg_replace('/\d=>/', '', \preg_replace('/\s+/ims', '', \var_export($pars, TRUE)))));
            return \preg_replace('/\[\)/', '[]', \preg_replace('/,/', ', ', $all ? $x : \substr($x, 1, \strlen($x)-2)));
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
            $this->local->addval('array', \var_export($_REQUEST, TRUE));
            $msg = $func.'('.$this->display($params).') : '.$this->display($result, TRUE);
            if ($result === 'userid')
            {
                $result = $this->context->user()->getID();
            }
            try
            {
                $res = $this->fdt->{$func}(...$params);
                if (\is_object($res))
                {
                    if (\is_array($result) && $result[0] == 'iterator')
                    {
                        if ($res instanceof \ArrayIterator)
                        {
                            foreach ($res as $key => $value)
                            {
                                if (!isset($result[1][$key]))
                                {
                                    $this->local->message(Msg::ERROR, $msg.' FAIL : got ArrayIterator with incorrect key '.$key.'/'.$value);
                                    return FALSE;
                                }
                                if ($value != $result[1][$key])
                                {
                                    $this->local->message(Msg::ERROR, $msg.' FAIL : got ArrayIterator expected '.$key.'/'.$result[1][$key].' got '.$key.'/'.$value);
                                    return FALSE;
                                }
                            }
                            $this->local->message(Msg::MESSAGE, $msg.' OK : expected ArrayIterator got '.$res::class);
                            return TRUE;
                        }
                        $this->local->message(Msg::ERROR, $msg.' FAIL : expected ArrayIterator got '.$res::class);
                    }
                    elseif ($res instanceof \RedBeanPHP\OODBBean)
                    {
                        $this->local->message(Msg::MESSAGE, $msg.' OK : expected \RedBeanPHP\OODBBean got '.$res::class.' id='.$this->display($res->getID(), TRUE));
                        return TRUE;
                    }
                    else
                    {
                        $this->local->message(Msg::ERROR, $msg.' FAIL : expected \RedBeanPHP\OODBBean got '.$res::class);
                    }
                }
                elseif (\is_array($result))
                {
                    if (\is_array($res))
                    {
                        $diff = \array_diff($res, $result);
                        if (empty($diff))
                        {
                            $this->local->message(Msg::MESSAGE, $msg.' OK : expected '.$this->display($result, TRUE).' got '.$this->display($res, TRUE));
                            return TRUE;
                        }
                        $this->local->message(Msg::ERROR, $msg.' FAIL : expected '.$this->display($result, TRUE).' got '.$this->display($res, TRUE).' diff '.$this->display($diff, TRUE));
                        return FALSE;
                    }
                    $this->local->message(Msg::ERROR, $msg.' FAIL : expected array '.$this->display($result, TRUE).' got '.$this->display($res, TRUE));
                }
                else
                {
                    if ($res === $result)
                    {
                        $this->local->message(Msg::MESSAGE, $msg.' OK : expected '.$this->display($result, TRUE).' got '.$this->display($res, TRUE));
                        return TRUE;
                    }
                    $this->local->message(Msg::ERROR, $msg.' FAIL : expected '.($throwOK ? 'exception' : $this->display($result, TRUE)).' got '.$this->display($res, TRUE));
                }
            }
            catch (\Framework\Exception\BadValue|\Framework\Exception\MissingBean $e)
            {
                $this->local->message($throwOK ? Msg::MESSAGE : Msg::ERROR, $msg.' throws exception: '.$e::class.' '.$e->getMessage());
                return $throwOK;
            }
            catch (\Throwable $e)
            {
                $this->local->message(Msg::ERROR, $msg.' throws exception: '.$e::class.' '.$e->getMessage());
            }
            return FALSE;
        }
/**
 * Run tests
 */
        public function run(array $tests) : void
        {
            if ($this->fdt instanceof \Framework\FormData\Base)
            {
                $this->local->message(Msg::MESSAGE, 'hasForm returns '.($this->fdt->hasForm() ? 'TRUE' : 'FALSE').' for '.$this->fdt::class);
            }
            foreach ($tests as $test)
            {
                [$func, $params, $result, $ok] = $test;
                $this->test($func, $params, $result, !$ok);
            }
        }
    }
?>