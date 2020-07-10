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
        private $fdt;
        
        public function __construct(Context $context, string $type)
        {
            $this->local = $context->local();
            $this->fdt = $context->formdata($type);
        }
        
        private function display($v)
        {
            $res = var_export($v, TRUE);
            $res = preg_replace('/\s+/ims', ', ', $res);
            $res = preg_replace('/array\(/', '[', $res);
            $res = preg_replace('/\)/', ']', $res);
            return $res;
            if (is_array($v))
            {
                return '['.implode(', ', array_map($this->display, $v)).']';
            }
            return var_export($v, TRUE);
        }
/**
 * OK if true
 */
        private function test(string $func, array $params, $result, bool$throwOK) : bool
        {
            $this->local->addval('array', var_export($_REQUEST, TRUE));
            $msg = $func.'('.implode(', ', $this->display($params)).')';
            try
            {
                $res = $this->fdt->{$func}(...$params);
                if ($res === $result)
                {
                    $this->local->message(Local::MESSAGE, $msg.' OK : expected '.var_export($result, TRUE).' got '.var_export($res, TRUE));
                    return TRUE;
                }
                else
                {
                    $this->local->message(Local::ERROR, $msg.' expected '.var_export($result, TRUE).' got '.var_export($res, TRUE));
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
        public function run(array $tests)
        {
            foreach ($tests as $test)
            {
                [$func, $params, $result, $ok] = $test;
                $this->test($func, $params, $result, !$ok);
            }
        }
    }
?>