<?php
/**
 * Contains definition of the RandomStringGenerator class
 *
 * Code taken from here:
 * http://stackoverflow.com/a/13733588/1056679
 *
 * @package Framework
 * @subpackage Utility
 */
    namespace Framework\Utility;

/**
 * Class RandomStringGenerator
 */
    class RandomStringGenerator
    {
/** @var string */
        protected $alphabet;

/** @var int */
        protected $alphabetLength;
/**
 * Set up the class
 */
        public function __construct(string $alphabet = '')
        {
            $this->setAlphabet($alphabet !== '' ? $alphabet : \implode(range('a', 'z')). \implode(range('A', 'Z')) . \implode(range(0, 9)));
        }
/**
 * Set up the alphabet to use
 */
        public function setAlphabet(string $alphabet) : void
        {
            $this->alphabet = $alphabet;
            $this->alphabetLength = strlen($alphabet);
        }
/**
 * Genrate a string of the given length
 */
        public function generate(int $length) : string
        {
            $token = '';
            for ($i = 0; $i < $length; $i++)
            {
                $randomKey = $this->getRandomInteger(0, $this->alphabetLength);
                $token .= $this->alphabet[$randomKey];
            }
            return $token;
        }
/**
 * Return a random integer in the given range
 */
        protected function getRandomInteger(int $min, int $max) : int
        {
            $range = ($max - $min);

            if ($range < 0)
            {
                // Not so random...
                return $min;
            }

            $log = \log($range, 2);

            // Length in bytes.
            $bytes = (int) ($log / 8) + 1;

            // Length in bits.
            $bits = (int) $log + 1;

            // Set all lower bits to 1.
            $filter = (int) (1 << $bits) - 1;

            do
            {
                $rnd = \hexdec(\bin2hex(\random_bytes($bytes)));
                // Discard irrelevant bits.
                $rnd &= $filter;

            }
            while ($rnd >= $range);
            return $min + $rnd;
        }
    }
?>