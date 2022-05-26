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

    use \Config\Framework as FW;
    use \Framework\Support\MessageType as Msg;
    use \Support\Context;
/**
 * A class that handles various site testing related things
 */
    class Test
    {
        private static array $tests = [ // function, parameters, expected result, if FALSE then failure is expected and result may be default or an exception
            ['exists', ['exist'], TRUE, TRUE],
            ['exists', ['notexist'], FALSE, TRUE],
            ['exists', [['aexist', 0]], TRUE, TRUE],
            ['exists', [['aexist', 3]], FALSE, TRUE],
            ['exists', [['nexist', 0]], FALSE, TRUE],
            ['mustExist', ['exist'], TRUE, TRUE],
            ['mustExist', ['notexist'], FALSE, FALSE],
            ['fetch', ['exist', 3], '42', TRUE],
            ['fetch', ['notexist', 3], 3, FALSE],
            ['mustFetch', ['notarray'], 3, FALSE],
            ['mustFetch', ['exist'], '42', TRUE],
            ['mustFetch', ['notexist'], '42', FALSE],
            ['fetch', [['aexist', 0], 3], '42', TRUE],
            ['fetch', [['aexist', 3], 3], 3, FALSE],
            ['fetch', ['aexist', 3, NULL, 0, FALSE], 3, FALSE],
            ['fetch', ['aexist', 3, NULL, 0, TRUE], ['42', '66'], TRUE],
            ['mustFetch', ['aexist', NULL, 0, FALSE], 3, FALSE],
            ['mustFetch', ['aexist', NULL, 0, TRUE], ['42', '66'], TRUE],
            ['mustFetch', [['aexist', 1]],'66', TRUE],
            ['mustFetch', [['aexist', 3]], '42', FALSE],
            ['fetch', [['nexist', 14], 3], '42', TRUE],
            ['fetch', [['nexist', 13], 3], 3, FALSE],
            ['mustFetch', [['nexist', 14]],'42', TRUE],
            ['mustFetch', [['nexist', 13]], '42', FALSE],
            ['fetch', [['kexist', 'key1'], 3], '42', TRUE],
            ['fetch', [['kexist', 'key45'], 3], 3, FALSE],
            ['mustFetch', [['kexist', 'key1']],'42', TRUE],
            ['mustFetch', [['kexist', 'key45']], '42', FALSE],
            ['fetch', ['email', \FILTER_VALIDATE_EMAIL], 'foo@bar.com', TRUE],
            ['mustFetch', ['email', \FILTER_VALIDATE_EMAIL], 'foo@bar.com', TRUE,''],
            ['fetch', ['email', 3, \FILTER_VALIDATE_INT], 3, FALSE],
            ['mustFetch', ['email', \FILTER_VALIDATE_INT], 3, FALSE],
            ['mustFetchBean', ['beanid', 'user'], 'userid', TRUE],
            ['mustFetchBean', ['notbeanid', 'user'], 'userid', FALSE],
            ['mustFetchBean', ['badbeanid', 'user'], 'userid', FALSE],
            ['mustFetchBean', ['badbeanid2', 'user'], 'userid', FALSE],
            ['fetchArray', ['kexist'], ['iterator', ['key1' => 42, 'key2' => 43]], TRUE],
            ['mustFetchArray', ['kexist'], ['iterator', ['key1' => 42, 'key2' => 43]], TRUE],
            ['fetchArray', ['knotexist'], ['iterator', []], FALSE],
            ['mustFetchArray', ['knotexist'], ['iterator', []], FALSE],
        ];
/**
 * Test AJAX functions
 */
        public function ajax(Context $context) : string
        {
            $context->local()->addval('bean', \R::findOrCreate(FW::TEST, ['f1' => 'a string', 'tog' => 1]));
            return '@devel/testajax.twig';
        }
/**
 * Test failed assertion handling
 */
        public function assert(Context $context) : string
        {
            assert(TRUE == FALSE);
            $context->local()->message(Msg::ERROR, 'Assertion test : this should not be reached');
            return '@devel/devel.twig';
        }
/**
 * Test run time error handling
 */
        public function fail(Context $context) : string
        {
            2 / 0; // @phan-suppress-current-line PhanDivisionByZero,PhanNoopBinaryOperator
            $context->local()->message(Msg::ERROR, 'Failure test : this should not be reached');
            return '@devel/devel.twig';
        }
/**
 * Run a set of tests
 */
        private static function dotest(Context $context, string $type) : string
        {
            $tester = new \Framework\Support\TestSupport($context, $type);
            $tester->run(self::$tests);
            $context->local()->addval('op', $type);
            if (filter_has_var(INPUT_GET, 'remote'))
            {
                $context->local()->addval('remote', TRUE);
            }
            return '@devel/tests/formdata.twig';
        }
/**
 * Test the FormData Get functions
 */
        public function get(Context $context) : string
        {
            return self::dotest($context, 'get');
        }
/**
 * Test the FormData Post functions
 */
        public function post(Context $context) : string
        {
            return self::dotest($context, 'post');
        }
/**
 * Test the FormData Put functions
 */
        public function put(Context $context) : string
        {
            return self::dotest($context, 'put');
        }
/**
 * Test the FormData Cookie functions
 */
        public function cookie(Context $context) : string
        {
            return self::dotest($context, 'cookie');
        }
/**
 * Test the FormData File functions
 */
        public function file(Context $context) : string
        {
            // $tester = new \Framework\Support\TestSupport($context, 'file');
            $context->local()->addval('op', 'file');
            return '@devel/devel.twig';
        }
/**
 * Test mail
 */
        public function mail(Context $context) : string
        {
/**
 * @psalm-suppress PossiblyNullPropertyFetch
 * @psalm-suppress PossiblyNullArgument
 */
            $msg = $context->local()->sendmail([$context->user()->email], 'test', '<b>test</b>', 'plain test', ['From' => $context->user()->email]);
            if ($msg === '')
            {
                $context->local()->message(Msg::MESSAGE, 'sent');
            }
            else
            {
                $context->local()->message(Msg::ERROR, $msg);
            }
            return '@devel/devel.twig';
        }
/**
 * Generate a test page. This tests various twig macros etc.
 */
        public function page(Context $context) : string
        {
            $context->local()->message(Msg::ERROR, 'Error 1');
            $context->local()->message(Msg::ERROR, 'Error 2');
            $context->local()->message(Msg::WARNING, ['Warning 1', 'Warning 2']); // use array parameter style
            $context->local()->message(Msg::MESSAGE, ['Message 1', 'Message 2']);
            return '@devel/test.twig';
        }
/**
 * Throw an unhandled exception. Tests exception handling.
 *
 * @throws \Exception
 */
        public function toss(Context $context) : string
        {
            throw new \Exception('Unhandled Exception Test'); // @phan-suppress-next-line PhanPluginUnreachableCode
            $context->local()->message(Msg::ERROR, 'Throw test : this should not be reached');
            return '@devel/test.twig';
        }
/**
 * Test the Upload features
 */
        public function upload(Context $context) : string
        {
            $fdt = $context->formdata('file');
            if (isset($_GET['ok']))
            {
                $context->local()->message(Msg::MESSAGE, 'Deleted');
            }
            try
            {
                if ($fdt->hasForm())
                {
                    $upl = \R::dispense(FW::UPLOAD);
                    $fa = $fdt->fileData('upload');  // @phan-suppress-current-line PhanUndeclaredMethod
                    if (!$upl->savefile($context, $fa, FALSE, $context->user(), 0))
                    {
                        $umodel = FW::UPLOADMCLASS;
                        $umodel::fail($context, $fa);
                    }
                    else
                    {
                        $context->local()->message(Msg::MESSAGE, $fa['name'].' uploaded');
                        $context->local()->addval('download', $upl->getID());
                    }
                }
                $rest = $context->rest();
                if (count($rest) == 4)
                {
                    $id = (int) $rest[3];
                    switch ($rest[2])
                    {
                    case 'get':
                        $context->local()->addval('download', $id);
                        break;

                    case'delete':
                        \R::trash($context->load(FW::UPLOAD, $id));
                        $context->divert('/devel/test/upload?ok=1'); // this clears the RESTful URL
                        /* NOT REACHED */
                    default:
                        throw new \Framework\Exception\BadValue('Illegal operation "'.$rest[2].'"');
                    }
                }
            }
            catch (\Throwable $e)
            {
                $context->local()->message(Msg::ERROR, $e->getmessage());
            }
            return '@devel/testupload.twig';
        }
    }
?>