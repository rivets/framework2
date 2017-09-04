<?php
    namespace Utility;
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
 * @example class MyClass { use Singletone; }
 *
 * @version 1.4
 *
 * @license GNU GPL v3
 *
 * Copyright (C) 2015 Kiril Savchev
 *
 * Minor mods by Lindsay Marshall to remove namespaces and reformat code into suitable
 * style for the Framework.
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
 *
 */
/**
 * The singleton trait
 */
    trait Singleton
    {
/**
 * The only instance of using class
 * @var object
 */
        protected static $_instance = null;
/**
 * Checks, instantiates and returns the only instance of the using class.
 *
 * @return object
 */
        public static function getinstance()
        {
            if (!(static::$_instance instanceof static)) // cannot get this to work with namespaces for some re
            {
                static::$_instance = new static();
            }
            return static::$_instance;
        }
/**
 * Class constructor. The concrete class using this trait can override it.
 */
        protected function __construct()
        {
            //void
        }
/**
 * Prevents oblect cloning
 * @throws Exception
 */
        public function __clone()
        {
            throw new Exception('Cannot clone Singleton objects');
        }
/**
 * Prevents object serialization
 * @throws Exception
 */
        public function __sleep()
        {
            throw new Exception('Cannot serialize Singleton objects');
        }
/**
 * Returns the only instance if is called as a function
 * @return object
 */
        public function __invoke()
        {
            return static::getInstance();
        }
    }
?>