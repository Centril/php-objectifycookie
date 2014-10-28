<?php
/*
 * Copyright 2014 Centril / Mazdak Farrokhzad <twingoow@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace objectifycookie;

/**
 * Simplifies cookie management
 * through a comfortable OOP interface.
 *
 * All work should be done with {@see Registry::instance()}
 * Do not try to instantiate {@see Registry} as it is a Singleton.
 *
 * You can also use cookie() as a short-cut to instance().
 *
 * Getting:
 * --------
 *	echo cookie() -> key -> sub_key;
 *
 * Which would get the value of $_COOKIE["key"]["sub_key"].
 *
 * Setting:
 * --------
 *	cookie() -> key = "value";
 *
 * Which would both add the value to $_COOKIE["key"]
 * and call setcookie() (can be changed depending on {@see Injector})
 * or:
 *
 *	cookie() -> key = [ "value_1", "value_2", "value_3" ];
 *
 * Which would do the same as above but setting all values in the array.
 *
 * Isset:
 * ------
 * Checking if a cookie-key is set can be done with PHPs isset().
 *
 * Unset:
 * ------
 * Unsetting a cookie-key is done with PHPs unset().
 * This unsets the key in PHP as well as calling setcookie().
 *
 * Foreach & Count:
 * ----------------
 *	if ( count( cookie() ) {
 *		foreach ( cookie() as $key => $value ) {
 *			do_something( $key, $value );
 *		}
 *	}
 *
 * ArrayAccess:
 * ------------
 * For all of above, it is also doable using ArrayAccess notation:
 *
 *	$cookie	=&	cookie();
 *	$cookie["key"]["sub_key"]	=	value;
 *
 * toArray:
 * --------
 * To get the underlying cookie data (element in $_COOKIE), call:
 *
 *	cookie() -> toArray();
 *
 * Injection:
 * ----------
 * {@see Injector}
 *
 * If you want to set a cookie with a special injector, use:
 *
 *	cookie() -> key	=	new Injector( $config, $value );
 *
 * Where $value is the value you want set, be it a scalar or a list(array)
 *
 * Parent: (Applies to {@see Component})
 * -------
 * You can get the parent component (that is the owning array|set)
 * by calling:
 *
 *	cookie() -> component -> getParent();
 *
 * Examining this call, getParent() will actually return a reference
 * to {@see Registry}
 *
 * @package Cookie
 * @category Cookie
 * @version 0.1
 * @author Mazdak Farrokhzad / Centril <twingoow@gmail.com>
 */
class Registry extends Base implements \Countable, \IteratorAggregate {
	/**
	 * Holds Singleton instance
	 *
	 * @var Registry instance of Registry
	 */
	protected static $instance;

	/**
	 * Holds Component bound to $_COOKIE
	 *
	 * @var Component
	 */
	protected $data;

	/**
	 * Init and bind $_COOKIE to Registry
	 * Hidden (Singleton) constructor.
	 *
	 * @return void
	 */
	protected function __construct() {
		// bind $_COOKIE as component
		if ( is_null( $this -> data ) ) {
			$this -> data	=	new Component( '', $_COOKIE, $this );
		}
	}

	/**
	 * Returns Instance
	 *
	 * @return Registry Instance of Registry
	 */
	public function & getInstance() {
		// instance exists? not -> make
		if ( !( self::$instance instanceof self ) ) {
			self::$instance	=	new self;
		}

		return self::$instance;
	}

	/**
	 * Alias of getInstance()
	 *
	 * @see getInstance()
	 * @return Registry Instance of Registry
	 */
	public function & instance() {
		return self::getInstance();
	}

	/**
	 * For IteratorAggregate {@see Component::getIterator()}
	 *
	 * @return ArrayIterator
	 */
	public function getIterator() {
		return $this -> data -> getIterator();
	}

	/**
	 * Get $_COOKIE
	 *
	 * @return array $_COOKIE
	 */
	public function & toArray() {
		return $this -> data -> toArray();
	}

	/**
	 * Counts amount of Cookies
	 *
	 * @return int
	 */
	public function count() {
		return count( $this -> data );
	}

	/**
	 * Is Cookie set?
	 *
	 * @param string|int $_name key to check for
	 * @return bool was it set? true if yes false if not
	 */
	public function __isset( $_name ) {
		return isset( $this -> data -> {$_name} );
	}

	/**
	 * Returns Cookie value
	 *
	 * @param string|int $_name key to get from
	 * @return mixed cookie value
	 */
	public function __get( $_name ) {
		return $this -> data -> {$_name};
	}

	/**
	 * Set a Cookie
	 *
	 * @param string|int $_name key to set
	 * @param mixed $_value value of Cookie to set
	 * @return void
	 */
	public function __set( $_name, $_value ) {
		$this -> data -> {$_name}	=	$_value;
	}

	/**
	 * Unset a Cookie
	 *
	 * @param string|int $_name key to unset
	 * @return void
	 */
	public function __unset( $_name ) {
		unset( $this -> data -> {$_name} );
	}
}