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
 * Base class of package, must not be instanciated
 *
 * Usage of its methods are explained briefly in
 * documentation of {@see Registry}
 *
 * @package Cookie
 * @category Cookie
 * @version 0.1
 * @author Mazdak Farrokhzad / Centril <twingoow@gmail.com>
 */
abstract class Base implements \ArrayAccess {
	/**
	 * Holds parent component
	 *
	 * @var Base instance of Base
	 */
	protected $parent;

	/**
	 * Cookie Injector
	 *
	 * @var Injector|null instance of Injector if injector exists
	 */
	protected $injector;

	/**
	 * Construct component - takes parent
	 *
	 * @param Base $_parent instance of Base
	 * @return void
	 */
	protected function __construct( &$_parent ) {
		$this -> parent	=&	$_parent;
	}

	/**
	 * Forbid cloning
	 *
	 * @return void
	 */
	public function __clone() {
		trigger_error( 'Clone is not allowed.', E_USER_ERROR );
	}

	/**
	 * Returns Parent
	 *
	 * @return Base instance of Base
	 */
	public function getParent() {
		return $this -> parent;
	}

	/**
	 * Alias of {@link #getParent}
	 *
	 * @return Base instance of Base
	 */
	public function parent() {
		return $this -> getParent();
	}

	/**
	 * Sets Cookie Injector of Component
	 *
	 * @param Injector $_injector
	 * @return Base self
	 */
	public function setInjector( Injector &$_injector ) {
		$this -> injector	=	$_injector;
		return $this;
	}

	/**
	 * Alias of {@link #setInjector}
	 *
	 * @param Injector $_injector
	 * @return Base self
	 */
	public function injector( Injector &$_injector ) {
		return $this -> setInjector( $_injector );
	}

	/**
	 * Clears Cookie Injector of component
	 *
	 * @return Base
	 */
	public function clearInjector() {
		$this -> injector	=	null;
		return $this;
	}

	/**
	 * Returns default Cookie Injector
	 *
	 * @return Injector default injector
	 */
	protected static function defaultInjector() {
		// static storage
		static $injector;

		// init if first time
		if ( is_null( $injector ) ) {
			$injector	=	new Injector( array() );
		}

		return $injector;
	}

	/**
	 * Returns first available Cookie Injector
	 *
	 * @return Injector instance of first available Injector
	 */
	public function getInjector() {
		// Does this component have an injector?
		if ( Injector::isInjector( $this -> injector ) ) {
			return $this -> injector;
		} else {
			// Get parent
			$parent	=&	$this -> getParent();

			// Does parent have an injector?
			return	isset( $parent ) && Injector::isInjector( $parent -> getInjector() )
				?	$parent -> getInjector()
			// Fallback to default injector
				:	self::defaultInjector();
		}
	}

	/**
	 * "Isset" for ArrayAccess {@see Component::__isset()}
	 *
	 * @param string|int $_name key to check for
	 * @return bool did it exist?
	 */
	public function offsetExists( $_name ) {
		return $this -> __isset( $_name );
	}

	/**
	 * "Get" for ArrayAccess {@see Component::__get()}
	 *
	 * @param string|int $_name name of key to get from
	 * @return mixed
	 */
	public function offsetGet( $_name ) {
		return $this -> __get( $_name );
	}

	/**
	 * "Set" for ArrayAccess {@see Component::__set()}
	 *
	 * @param string|int $_name key name to set
	 * @param mixed $_value value to set
	 * @return void
	 */
	public function offsetSet( $_name, $_value ) {
		$this -> __set( $_name, $_value );
	}

	/**
	 * "Unset" for ArrayAccess {@see Component::__unset()}
	 *
	 * @param string|int $_name key to unset
	 * @return void
	 */
	public function offsetUnset( $_name ) {
		$this -> __unset( $_name );
	}

	/**
	 * Implements magic "call as function"
	 *
	 * If isset( $_value ) Then we're setting a key
	 * Else: we're getting
	 *
	 * @param string|int $_name key to get/set
	 * @param mixed $_value value to set (optional)
	 * @return mixed if setting -> self, else -> get-value
	 */
	public function __invoke( $_name, $_value = null ) {
		if ( isset( $_value ) ) {
			$this -> __set( $_name, $_value );
			return $this;
		} else {
			return $this -> __get( $_name );
		}
	}
}