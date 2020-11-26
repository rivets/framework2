<?php
/**
 * This trait provides Singleton pattern (only one instance for the concrete class) to the classes that use it.
 * All the entire application can accept its only instance via the public static method getInstance(),
 * provided by the trait.
 * If somebody tries to clone or serialize the object, the trait throws RuntimeException.
 * The static property for the only instance is declared as protected and is instantiated with the 'static' keyword
 * to ensure the posibility of class extending.
 *
 * @author Kiril Savchev (k.savchev@gmail.com)
 *
 * @example class MyClass { use Singleton; }
 *
 * @version 1.4
 *
 * @license GNU GPL v3
 *
 * Copyright (C) 2015 Kiril Savchev
 *
 * Minor mods by Lindsay Marshall to re-namespace and reformat code into suitable
 * style for the Framework. Also added some psalm comments
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
    namespace Framework\Utility;

/**
 * The singleton trait
 */
    trait Singleton
    {
/**
 * The only instance of using class
 * @var object
 */
        protected static $instance = NULL;
/**
 * Checks, instantiates and returns the only instance of the using class.
 *
 * @template object
 * @psalm-return object
 *
 * @psalm-suppress MismatchingDocblockReturnType
 * @psalm-suppress ReservedWord
 *
 * @return object
 */
        public static function getinstance() : object
        {
            if (!(static::$instance instanceof static)) // cannot get this to work with namespaces for some reason
            {
                static::$instance = new static(); // @phpstan-ignore-line
            }
            return static::$instance;
        }
/**
 * Class constructor. The concrete class using this trait can override it.
 * @internal
 */
        protected function __construct()
        {
            // void
        }
/**
 * Prevents object cloning
 * @internal
 * @throws \Framework\Exception\InternalError
 */
        public function __clone()
        {
            throw new \Framework\Exception\InternalError('Cannot clone Singleton objects');
        }
/**
 * Prevents object serialization
 * @internal
 * @throws \Framework\Exception\InternalError
 */
        public function __sleep() : array
        {
            throw new \Framework\Exception\InternalError('Cannot serialize Singleton objects');
        }
/**
 * Returns the only instance if is called as a function
 *
 * @internal
 *
 * @template object
 * @psalm-return object
 *
 * @return object
 */
        public function __invoke() : object
        {
            return static::getInstance();
        }
    }
?>