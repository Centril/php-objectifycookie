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
 * Component class of package
 *
 * You should not instansiate a Component,
 * even tho it would work to do so...
 * for most cases, it would also be completely useless.
 *
 * Usage of its methods are explained briefly in
 * documentation of {@see Registry}
 *
 * @package Cookie
 * @category Cookie
 * @version 0.1
 * @author Mazdak Farrokhzad / Centril <twingoow@gmail.com>
 */
class Component extends Base implements \Countable, \IteratorAggregate {
	/**
	 * Name of component
	 *
	 * @var string|int
	 */
	protected $name;

	/**
	 * Direct reference to an element somewhere in / of $_COOKIE
	 * (Can be $_COOKIE itself)
	 *
	 * @var array
	 */
	protected $direct;

	/**
	 * Referenced cookie value (for cache)
	 *
	 * @var array
	 */
	protected $data	=	array();

	/**
	 * Constructs Component
	 *
	 * @param string|int $_name name of component
	 * @param array $_value values component will represent
	 * @param Base $_parent parent of component
	 * @return void
	 */
	public function __construct( $_name, &$_value, &$_parent ) {
		// remember name
		$this -> name	=	$_name;

		// bind data to 'direct'
		$this -> direct	=&	$_value;

		// bind parent
		parent::__construct( $_parent );
	}

	/**
	 * Construct cookie-name
	 *
	 * @param string|int $_name name of cookie
	 * @return string|int
	 */
	protected function make_name( &$_name ) {
		return		empty( $this -> name )
				?	$_name
				:	$this -> name . '[' . $_name . ']';
	}

	/**
	 * Construct a sub-component
	 *
	 * @param string|int $_name name of sub-component
	 * @return void
	 */
	protected function new_component( &$_name ) {
		$this -> data[$_name]	=	new Component(
			$this -> make_name( $_name ),
			$this -> direct[$_name],
			$this
		);
	}

	/**
	 * Fallback to "direct" element of $_COOKIE
	 *
	 * @param string|int $_name key of element
	 * @return void
	 */
	protected function fallback( $_name ) {
		// is sub-component?
		if ( is_array( $this -> direct[$_name] ) ) {
			// yes -> bind new sub-component referencing "direct" to cache
			$this -> new_component( $_name );
		} else {
			// no -> bind reference of the scalar value to cache
			$this -> data[$_name]	=&	$this -> direct[$_name];
		}
	}

	/**
	 * Implements IteratorAggregate for foreach
	 *
	 * @return ArrayIterator
	 */
	public function getIterator() {
		// do we have any cookies?
		if ( !empty( $this -> direct ) ) {
			// make sure all cookies are in cache
			foreach ( array_keys( array_diff_key( $this -> direct, $this -> data ) ) as $key) {
				$this -> fallback( $key );
			}
		}

		// return iterator
		return new ArrayIterator( $this -> data );
	}

	/**
	 * Returns reference to underlying element of $_COOKIE
	 *
	 * @return array
	 */
	public function & toArray() {
		return $this -> direct;
	}

	/**
	 * Counts amount of Cookies
	 *
	 * @return int
	 */
	public function count() {
		return count($this -> direct);
	}

	/**
	 * Is Cookie set?
	 *
	 * @param string|int $_name key to check for
	 * @return bool was it set? true if yes false if not
	 */
	public function __isset($_name) {
		// first try organized block:
		if ( isset( $this -> data[$_name] ) ) {
			return true;
		// then: fallback on $_COOKIE...
		} else if ( isset( $this -> direct[$_name] ) ) {
			$this -> fallback( $_name );
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Returns Cookie value
	 *
	 * @param string|int $_name key to get from
	 * @return mixed cookie value
	 */
	public function __get( $_name ) {
		// get from cache
		if ( isset( $this -> data[$_name] ) ) {
			return $this -> data[$_name];
		// get directly (and add to cache)
		} else if ( isset( $this -> direct[$_name] ) ) {
			$this -> fallback($_name);
			return $this -> data[$_name];
		// not set, return null
		} else {
			return null;
		}
	}

	/**
	 * Sets a set of cookies - helper to __set()
	 *
	 * @param array $_array the set
	 * @param string|int $_name key
	 * @param Injector|null $_injector an optional injector
	 * @return void
	 */
	protected function set_array( &$_array, &$_name, &$_injector = null ) {
		// add sub-component
		$this -> new_component( $_name );

		// if provided - set injector
		if ( isset( $_injector ) ) {
			$this -> data[$_name] -> setInjector( $_injector );
		}

		// set each value (recursive)
		foreach ( $_array as $key => $element ) {
			$this -> data[$_name] -> {$key}	=	$element;
		}
	}

	/**
	 * Set a Cookie
	 *
	 * @param string|int $_name key to set
	 * @param mixed $_value value of Cookie to set
	 * @return void
	 */
	public function __set( $_name, $_value ) {
		// is Cookie a set?
		if ( is_array( $_value ) ) {
			// yes - set each value
			$this -> set_array( $_value, $_name );
		} else {
			// nope - insert cookie
			// get content - is value itself an injector?
			if ( Injector::isInjector( $_value ) ) {
				$real_value	=	$_value -> getValue();

				// dealing with a set or a plain value?
				if ( is_array( $real_value ) ) {
					// set - clear value from injector
					$_value -> clearValue();

					// set each value of set
					$this -> set_array( $real_value, $_name, $_value );
					return;
				} else {
					// plain - use it to inject and get value out of injector
					$content	=	$_value -> inject( $this -> make_name( $_name ) );
				}
			} else {
				// no, use closest bound injector
				$content	=	$this -> getInjector() -> inject( $this -> make_name( $_name ), $_value );
			}

			if ( $content === false ) {
				return;
			}

			// add to "direct" element of $_COOKIE
			$this -> direct[$_name]	=	$content;

			// add to cache
			$this -> data[$_name]	=&	$this -> direct[$_name];
		}
	}

	/**
	 * Unset a Cookie
	 *
	 * @param string|int $_name key to unset
	 * @return void
	 */
	public function __unset( $_name ) {
		$data	=&	$this -> data[$_name];
		$direct	=&	$this -> direct[$_name];

		// index exists?
		if ( !$this -> __isset( $_name ) ) {
			return;
		}

		// is component?
		if ( $this -> data[$_name] instanceof Component ) {
			// yes -> foreach and unset each
			foreach ( array_keys( $this -> data[$_name] ) as $key ) {
				unset($this -> data[$_name] -> {$key});
			}

			unset( $this -> data[$_name], $this -> direct[$_name] );
		} else {
			setcookie( $this -> make_name( $_name ), false, time() - 86400 * 365, '/' );
			unset( $this -> data[$_name], $this -> direct[$_name] );
		}
	}
}