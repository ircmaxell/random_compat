<?php

namespace PHP;

class Random {

    const BLOCK_SIZE = 32;

    const TOKEN_LOWER_ALPHA = "abcdefghijklmnopqrstuvwxyz";
    const TOKEN_UPPER_ALPHA = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    const TOKEN_ALPHA = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    const TOKEN_ALNUM = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

    private static $state = '';

    protected $secure = false;

    /**
     * Construct a new random number generator
     *
     * @param bool $secure Should generated random numbers be cryptographically secure
     */
    public function __construct($secure = false) {
        $this->secure = (bool) $secure;
    }

    /**
     * Generate a native binary string
     *
     * @param int $length The length of the string to generate
     *
     * @return string The generated string
     */
    public function bytes($length) {
        $blocks = max(ceil($length / $this::BLOCK_SIZE), 1);
        $result = '';
        for ($i = 0; $i < $blocks; $i++) {
            $result .= $this->genRandom();
        }
        self::$state = $this->mergeBuffers(self::$state, substr($result, $length));
        return substr($result, 0, $length);
    }

    /**
     * Generate a random integer
     *
     * @param int $min The starting point, or if max is omitted the ending point
     * @param int $max The ending point
     *
     * @return int The generated integer
     */
    public function int($min, $max = null) {
        if (is_null($max)) {
            $max = $min;
            $min = 0;
        }
        $range = (int) ($max - $min);
        $bits = $this->countBits($range) + 1;
        $bytes = (int) max(ceil($bits / 8), 1);
        $mask = pow(2, $bits) - 1;
        if ($mask >= \PHP_INT_MAX) {
            $mask = \PHP_INT_MAX;
        } else {
            $mask = (int) $mask;
        }
        do {
            $test = $this->bytes($bytes);
            $result = hexdec(bin2hex($test)) & $mask;
        } while($result > $range);

        return $result + $min;
    }

    /**
     * Generate a random floating point number between 0 and 1.0
     *
     * @return float The generated float
     */
    public function float() {
        return ($this->int(0, \PHP_INT_MAX) / (\PHP_INT_MAX));
    }

    /**
     * Given a sequence (string, array or object), pick one of them at random
     * 
     * @param string|array|object $value The sequence to choose from
     *
     * @return mixed The randomly chosen value
     */
    public function choose($value) {
        $count = 0;
        if (is_string($value)) {
            $count = strlen($value);
        } elseif (is_array($value)) {
            $value = array_values($value);
            $count = count($value);
        } elseif (is_object($value)) {
            $value = array_values(get_object_vars($value));
            $count = count($value);
        } else {
            throw new \InvalidArgumentException('Unsure what to choose from, please provide a string, array or object');
        }
        return $value[$this->int(0, $count - 1)];
    }

    /**
     * Given a sequence(string, array or object), generate a shuffled sequence
     * Note: for strings, the return is a string. For arrays and objects, it's an array
     *
     * @param string|array|object $value The sequence to choose from
     *
     * @return string|array The generated shuffled sequence
     */
    public function shuffle($value) {
        $buffer = array();
        if (is_string($value)) {
            $buffer = str_split($value, 1);
        } elseif (is_array($value)) {
            $buffer = array_values($value);
        } elseif (is_object($value)) {
            $buffer = array_values(get_object_vars($value));
        } else {
            throw new \InvalidArgumentException('Unsure what to shuffle, please provide a string, array or object');
        }
        $result = array();
        while (!empty($buffer)) {
            $seed = $this->genRandom();
            do {
                $bits = $this->countBits(count($buffer));
                $bytesNeeded = max(ceil($bits / 8), 1);
                $mask = (int) (pow(2, $bits) - 1);
                $stub = hexdec(bin2hex(substr($seed, 0, $bytesNeeded))) & $mask;
                $seed = substr($seed, $bytesNeeded);
                if (isset($buffer[$stub])) {
                    $result[] = $buffer[$stub];
                    unset($buffer[$stub]);
                    $buffer = array_values($buffer);
                }
            } while (isset($seed[$bytesNeeded]));
        }
        if (is_string($value)) {
            return implode('', $result);
        }
        return $result;
    }

    /**
     * Generate a token with the specified alphabet
     *
     * @param int          $length   The length of the token to generate
     * @param string|array $alphabet The "alphabet" (characters) to use
     *
     * @return string The generated random token
     */
    public function token($length, $alphabet = self::TOKEN_ALNUM) {
        if (is_string($alphabet)) {
            $alphabet = str_split($alphabet, 1);
        } elseif (is_array($alphabet)) {
            $alphabet = array_values($alphabet);
        } else {
            throw new \InvalidArgumentException("Expecting either a String or Array alphabet");
        }
        $alphabet = array_unique($alphabet);
        $count = count($alphabet);
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= $alphabet[$this->int(0, $count - 1)];
        }
        return $result;
    }
    
    /**
     * Protected function to overload the generator for unit testing purposes
     */
    protected function genRandom() {
        $buffer = $this->mergeBuffers(self::$state, $this->genNormal());
        if ($this->secure) {
            $buffer = $this->mergeBuffers($buffer, $this->genSecure());
        }
        self::$state = $this->mergeBuffers(self::$state, $buffer);
        return $buffer;
    }

    /**
     * Generate a non-CS random seed
     */
    private function genNormal() {
        $buffer = '';
        if (function_exists('mcrypt_create_iv')) {
            $buffer = mcrypt_create_iv($this::BLOCK_SIZE, \MCRYPT_DEV_URANDOM);
        }
        if (function_exists('openssl_random_pseudo_bytes')) {
            $strength = true;
            $buffer = $this->mergeBuffers($buffer, openssl_random_pseudo_bytes($this::BLOCK_SIZE, $strength));
        }
        if (file_exists('/dev/urandom') && is_readable('/dev/urandom')) {
            $buffer = $this->mergeBuffers($buffer, file_get_contents('/dev/urandom', false, null, -1, $this::BLOCK_SIZE));
        }
        if (file_exists('/dev/arandom') && is_readable('/dev/arandom')) {
            $buffer = $this->mergeBuffers($buffer, file_get_contents('/dev/arandom', false, null, -1, $this::BLOCK_SIZE));
        }

        $tmp = '';
        for ($i = 0; $i < $this::BLOCK_SIZE; $i++) {
            $tmp .= chr(mt_rand(0, 255));
        }
        $buffer = $this->mergeBuffers($buffer, $tmp);
        return $buffer;
    }

    /**
     * Generate a cryptographically-secure random seed
     */
    private function genSecure() {
        $buffer = '';
        if (function_exists('mcrypt_create_iv')) {
            $buffer = mcrypt_create_iv($this::BLOCK_SIZE, \MCRYPT_DEV_RANDOM);
        }
        if (function_exists('openssl_random_pseudo_bytes')) {
            $strength = true;
            /* Since mcrypt could have possibly generated content, 
             * We want to merge the result of the function with the existing buffer
             */
            $tmp = $this->mergeBuffers($buffer, openssl_random_pseudo_bytes($this::BLOCK_SIZE, $strength));
            if ($strength || $buffer) {
                /* We can use the generated buffer as long as openssl generated a secure
                 * buffer, or the buffer was already there by mcrypt.
                 */
                $buffer = $tmp;
            }
        }
        if (file_exists('/dev/random') && is_readable('/dev/random')) {
            $buffer = $this->mergeBuffers($buffer, file_get_contents('/dev/random', false, null, -1, $this::BLOCK_SIZE));
        }
        if (!$buffer) {
            throw new \LogicException('Could not generate secure random number');
        }
        return $buffer;
    }

    /**
     * Merge two buffers into a single buffer in a non-deterministic way
     */
    private function mergeBuffers($buffer1, $buffer2) {
        if (ord(self::$state) % 2 === 0) {
            return hash_hmac('sha256', $buffer1, $buffer2, true);
        } else {
            return hash_hmac('sha256', $buffer2, $buffer1, true);
        }
    }

    /**
     * Count the number of bits needed to represent an integer
     */
    private function countBits($number) {
        $log2 = 0;
        while ($number >>= 1) {
            $log2++;
        }
        return $log2;
    }
}