<?php
/**
 * Contains definition of Test class
 *
 * @author Lindsay Marshall <lindsay.marshall@ncl.ac.uk>
 * @copyright 2020 Newcastle University
 */
    namespace Framework\Support;

    use \Framework\Local;
    use \Support\Context;
/**
 * A class that handles various site testing related things
 */
    class TestSupport
    {
        private $local;
        private $context;
        private $fdt;
        private $fdtold;
        private $fdtnew;
        private $noform = FALSE;
        
        public function __construct(Context $context, string $type)
        {
            $this->context = $context;
            $this->local = $context->local();
            $this->fdtold = $context->formdata('');
            $this->fdtnew = $context->formdata($type);
            $this->noform = $context->web()->method() == 'GET' && !isset($_GET['exist']) && !isset($_GET['cookie']);
        }
        
        private function display($pars, $all = FALSE)
        {
            $x = preg_replace('/array\(/', '[', preg_replace('/,\)/', ']', preg_replace('/\d=>/', '', preg_replace('/\s+/ims', '', var_export($pars, TRUE)))));
            return $all ? $x : preg_replace('/,/', ', ', substr($x, 1, strlen($x)-2));
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
            $msg = $func.'('.$this->display($params).')';
            if ($result == 'userid')
            {
                $result = $this->context->user()->getID();
            }
            try
            {
                $res = $this->fdt->{$func}(...$params);
                if ($result == 'iterator')
                {
                    if ($res instanceOf \ArrayIterator)
                    {
                        $this->local->message(Local::MESSAGE, $msg.' OK : expected ArrayIterator got '.display($res, TRUE));
                        return TRUE;
                    }
                    $this->local->message(Local::ERROR, $msg.' FAIL : expected ArrayIterator got '.class_name($res));
                }
                elseif (is_array($result))
                {
                    if (is_array($res) && empty(array_diff($res, $result)))
                    {
                        $this->local->message(Local::MESSAGE, $msg.' OK : expected '.display($result, TRUE).' got '.display($res, TRUE));
                        return TRUE;
                    }
                    $this->local->message(Local::ERROR, $msg.' FAIL : expected '.display($result, TRUE).' got '.display($res, TRUE));
                }
                else
                {
                    if ($res === $result)
                    {
                        $this->local->message(Local::MESSAGE, $msg.' OK : expected '.display($result, TRUE).' got '.display($res, TRUE));
                        return TRUE;
                    }
                    $this->local->message(Local::ERROR, $msg.' FAIL : expected '.($throwOK ? 'exception' : display($result, TRUE)).' got '.display($res, TRUE));
                }
            }
            catch (\Framework\Exception\BadValue $e)
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