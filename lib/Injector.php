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
 * Injects a Cookie
 *
 * An Injector is used everytime a cookie is being set.
 *
 * Binding an injector to a component:
 * -----------------------------------
 * An injector is searched for via hierarchy.
 * The first one found is used.
 *
 * If no injector is set at any level, the default injector
 * is used using default values from Cookie_Injector.
 *
 * If you want to set an injector at the global level,
 * you must do it on {@see Registry}.
 *
 *	cookie() -> injector( $injector );
 *
 * On the local level this is done with:
 *
 *	cookie() -> key = [];	-- first make it eligible for its own injector
 * 	cookie() -> key -> injector($injector);
 *
 * Now, when any element is set to cookie() -> key, it uses the local injector.
 *
 * If you want to set a specific cookie with an injector, do like this:
 *
 *	cookie() -> key = new Injector (
 *		["expiry" => new DateTime( "2011-12-25" )],	// config
 *		"value"												// plain value
 *	);
 *
 * Or if you want to set an array:
 *
 *	cookie() -> key = new Injector(
 *		["expiry" => new DateTime( "2011-12-25" )],	// config
 *		["key_1"  => "value_1", "value_3"]			// array
 *	);
 *
 * Clearing a binding:
 * -------------------
 * If you want to clear the binding to an
 * Cookie_Injector in a component, simply call:
 *
 *	cookie() -> clearInjector();
 *
 * Dependency:
 * -----------
 * It is possible to use this component alone,
 * (with the exception of {@see Exception}
 * but it is not recommended.
 *
 * @package Cookie
 * @category Cookie
 * @version 0.1
 * @author Mazdak Farrokhzad / Centril <twingoow@gmail.com>
 */
class Injector {
	/**
	 * Holds value to inject if set
	 *
	 * @var mixed
	 */
	protected $value;

	/**
	 * Config values (or callback)
	 *
	 * @var array|callback
	 */
	protected $config;

	/**
	 * Post callback
	 *
	 * @var callback
	 */
	protected $post_callback;

	/**
	 * Pre callback
	 *
	 * @var callback
	 */
	protected $pre_callback;

	/**
	 * Is value an injector?
	 *
	 * @param mixed $_injector value to validate
	 * @return boolean true if is injector, else false
	 */
	public function isInjector( &$_injector ) {
		return isset( $_injector ) && $_injector instanceof self;
	}

	/**
	 * Constructs injector
	 *
	 * @param array|callback $_config
	 *
	 * If $_config is a callback, it is used to set the actual cookie.
	 * This could be used if you wanted to eg. call setrawcookie(),
	 * or if you had some special processing to do...
	 *
	 * Otherwise, $_config must be an array of config values.
	 * Array - use the keys provided. 0 and 'expiry' are the same
	 * -----
	 * 0 | 'expiry'		=>	{@see Injector::setExpiry}
	 * 1 | 'path'		=>	{@see Injector::setPath}
	 * 2 | 'domain'		=>	{@see Injector::setDomain}
	 * 3 | 'secure'		=>	{@see Injector::setSecure}
	 * 4 | 'http_only'	=>	{@see Injector::setHttpOnly}
	 *
	 * @param mixed $_value value to set (optional)
	 *
	 * Only used when you directly want to set a Cookie
	 * via: cookie() -> key = new Injector(array(...), $_value);
	 * Otherwise: skip
	 *
	 * @param callback $_pre_callback (optional)
	 *
	 * {@see Injector::setPreCallback}
	 * Set this to a callback (all forms are eliglible).
	 * If provided, it is then called before cookie insertion
	 *
	 * Signature: ($_name, $_value, $_injector)
	 *
	 * @param callback $_post_callback (optional)
	 *
	 * {@see Injector::setPostCallback}
	 * See $_pre_callback with the exception of post instead of pre.
	 * $_value (in callback signature) is also now a string,
	 * whatever the previous $_value:type might have been.
	 *
	 * @return void
	 */
	public function __construct( $_config, $_value = null, $_pre_callback = null, $_post_callback = null ) {
		if ( isset( $_value ) ) {
			$this -> value	=	$_value;
		}

		if ( isset( $_pre_callback ) ) {
			$this -> setPreCallback( $_pre_callback );
		}

		if ( isset( $_post_callback ) ) {
			$this -> setPostCallback( $_post_callback );
		}

		// config-array
		if ( is_array( $_config ) ) {
			$this -> config	=	array();

			if ( !empty( $_config) ) {
				$isset	=	function( $_key1, &$_value ) use( &$_config ) {
					static $i	=	0;

					$_value	=	null;

					if ( isset($_config[$_key1] ) ) {
						$_value	=	$_config[$_key1];
					} else if ( isset($_config[$i] ) ) {
						$_value	=	$_config[$i];
					}

					$i++;

					return isset( $_value );
				};

				foreach ( array(
						'expiry'	=>	'Expiry',
						'path'		=>	'Path',
						'domain'	=>	'Domain',
						'secure'	=>	'Secure',
						'http_only'	=>	'HttpOnly'
					) as $config_key => $config_func ) {
					if ( $isset($config_key, $config_func ) ) {
						$this -> {'set' . $config_func}( $config_value );
					}
				}
			}
		// callback
		} else {
			$this -> config	=	$_config;
		}
	}

	/**
	 * Returns stored value
	 *
	 * @return mixed $this -> value
	 */
	public function getValue() {
		return $this -> value;
	}

	/**
	 * Alias of {@link #getValue()}
	 *
	 * @return mixed $this -> value
	 */
	public function value() {
		return $this -> getValue();
	}

	/**
	 * Clears stored value
	 *
	 * @return Injector self
	 */
	public function clearValue() {
		$this -> value	=	null;
		return $this;
	}

	/**
	 * Alias of {@link #clear()}
	 *
	 * @return Injector self
	 */
	public function clear() {
		return $this -> clearValue();
	}

	/**
	 * Inject a cookie
	 *
	 * @param string $_name key name of cookie to insert
	 * @param mixed $_value value of cookie (optional) $this -> value is used if not provided
	 * @throws Exception if cookie-injection did not work
	 * @return string|bool if false: injection process terminated | if string: toString of injected value
	 */
	public function inject( &$_name, $_value = null ) {
		// select value
		if ( !isset( $_value ) ) {
			// from $this
			$_value	=	$this -> getValue();

			// clear object value
			$this -> clearValue();
		}

		// pre callback
		if ( $this -> callback( $this -> pre_callback, $_name, $_value ) ) {
			return false;
		}

		// Avoid Pitfall: if bool, cast to int
		if ( is_bool($_value)) {
			$_value	=	$_value ? 1 : 0;
		}

		$_value	=	(string) $_value;

		// callable or config-array?
		if ( is_callable( $this -> config ) ) {
			// callable -> call
			$result	=	call_user_func( $this -> config, $_name, $_value );
		} else {
			// config array -> call
			$result	=	setcookie(
				$_name,
				$_value,
				$this -> getExpiry( true ),
				$this -> getPath( true ),
				$this -> getDomain( true ),
				$this -> getSecure( true ),
				$this -> getHttpOnly( true )
			);
		}

		// injection failed?
		if ( $result === false ) {
			// cookie injection failed - quit
			throw new Exception( 'Cookie insertion failed',  Exception::SET_FAILED );
			return false; // will never be reached
		}

		// post callback
		if ( $this -> callback( $this -> post_callback, $_name, $_value ) ) {
			return false;
		}

		return $_value;
	}

	/**
	 * Inject a cookie (alias of inject)
	 *
	 * @see Injector::inject()
	 * @param string $_name key name of cookie to insert
	 * @param mixed $_value value of cookie (optional) $this -> value is used if not provided
	 * @return string toString of injected value
	 */
	public function __invoke( &$_name, $_value = null ) {
		return $this -> inject( $_value );
	}

	/**
	 * Call a stored callback
	 *
	 * @param callback $_callback
	 * @param string|int $_name key
	 * @param mixed $_value
	 * @return bool true = terminate injection process
	 */
	protected function callback( &$_callback, &$_name, &$_value ) {
		if ( isset( $_callback ) && is_callable( $_callback ) ) {
			if ( !call_user_func( $_callback, $_name, $_value, $this ) ) {
				return true;
			}
		}
	}

	/**
	 * Set callback to call before injection
	 *
	 * The signature of the callback must be: ($_name, $_value, $_injector)
	 * If the return-value identifies false -> cookie injection process is terminated
	 *
	 * @param callback $_callback
	 * @return self
	 */
	public function setPreCallback( $_callback ) {
		$this -> post_callback	=	$_callback;
		return $this;
	}

	/**
	 * Alias of {@link #setPreCallback( $_callback )}
	 *
	 * @param callback $_callback
	 * @return self
	 */
	public function preCallback( $_callback ) {
		return $this -> setPreCallback( $_callback );
	}

	/**
	 * Set callback to call after injection
	 *
	 * The signature of the callback must be: ($_name, $_value, $_injector)
	 * If the return-value identifies false -> cookie injection process is terminated
	 *
	 * @param callback $_callback
	 * @return self
	 */
	public function setPostCallback( $_callback ) {
		$this -> pre_callback	=	$_callback;
	}

	/**
	 * Alias of {@link #setPostCallback( $_callback )}
	 *
	 * @param callback $_callback
	 * @return self
	 */
	public function postCallback( $_callback ) {
		return $this -> setPostCallback( $_callback );
	}

	/**
	 * Make sure $this -> config is an array
	 *
	 * @return void
	 */
	protected function ensureConfigArray() {
		if ( !is_array( $this -> config ) ) {
			$this -> config	=	array();
		}
	}

	/**
	 * Set a config value
	 *
	 * @param string $_key config-key
	 * @param mixed $_value config-value
	 * @param callback|null $_validator (optional) callback to validate $_value with
	 * @return self
	 */
	protected function setConfig( $_key, &$_value, $_validator = null ) {
		$this -> ensureConfigArray();

		// value not valid?
		if ( isset( $_validator ) && is_callable( $_validator ) && !$_validator( $_value ) ) {
			// fail
			throw new Exception("Config value - {$_key} - is not valid");
		}

		// set
		$this -> config[$_key]	=	$_value;
		return $this;
	}

	/**
	 * Get a config value
	 *
	 * @param string $_key config-key
	 * @param mixed $_default set config to this value if $_key is not set
	 * @param bool $_fallback_default should we fallback to default?
	 * @param callback|null $_apply (optional) callback to apply after - on config value
	 * @return mixed config-value
	 */
	protected function getConfig( $_key, $_default, $_fallback_default = false, $_apply = null ) {
		$this -> ensureConfigArray();

		// fallback to default value?
		if ( $_fallback_default ) {
			$value	=	isset( $this -> config[$_key] )
					?	$this -> config[$_key]
					:	$_default;
		} else {
			if ( isset( $this -> config[$_key]) ) {
				unset($default);
				$value	=	$this -> config[$_key];
			} else {
				return;
			}
		}

		if ( isset( $_apply ) ) {
			$_apply( $value );
		}

		return $value;
	}

	/**
	 * Returns callback: Can the value be read as a bool?
	 *
	 * @return callback
	 */
	protected function possibleBool() {
		return function( &$_value ) {
			return empty( $_value ) || is_scalar( $_value );
		};
	}

	/**
	 * Returns callback: "Converts" value to bool
	 *
	 * @return callback
	 */
	protected function interpretAsBool() {
		return function( &$_value ) {
			$_value	=	is_string( $_value ) && $_value === 'true' || $_value;
		};
	}

	/**
	 * Set Config - Domain
	 *
	 * Default: $_SERVER['SERVER_NAME']
	 *
	 * @param string $_value valid domain, ip...
	 * @return self
	 */
	public function setDomain( $_value ) {
		return $this -> setConfig( 'domain', $_value, function( &$_value ) {
			return	is_string( $_value ) && trim( $_value ) !== '';
		} );
	}

	/**
	 * Alias of {@link #setDomain( $_value )}
	 *
	 * @param string $_value valid domain, ip...
	 * @return self
	 */
	public function domain( $_value ) {
		return $this -> setDomain( $_value );
	}

	/**
	 * Get Config - Domain
	 *
	 * @param bool $_fallback_default should we fallback to default?
	 * @return string domain config-value
	 */
	public function getDomain( $_fallback_default = false ) {
		return $this -> getConfig(
			'domain',
			strpos( $_SERVER['SERVER_NAME'], '.' ) !== false ? $_SERVER['SERVER_NAME'] : '',
			$_fallback_default
		);
	}

	/**
	 * Set Config - Expiry Time
	 *
	 * Default: 30 days
	 *
	 * a)	"Empty" is converted to 0 -> session cookie
	 * b)	Numeric (also string) is time() + $_value
	 * b)	Exact Time
	 *		1)	{@see DateTime} is converted to it's timestamp
	 *		2)	String starting with @ is converted to an int
	 *			with @ first being removed
	 *		3)	Other strings are converted to timestamp with strtotime()
	 *
	 * @param DateTime|scalar|null $_value
	 * @return self
	 */
	public function setExpiry( $_value = '' ) {
		return $this -> setConfig( 'expiry', $_value, function( &$_value ) {
			return	$_value instanceof DateTime		||		// Derivate of DateTime
					is_string($_value)				&& (
						trim($_value) === ''			||			// Empty
						strtotime($_value) !== false	||			// Acceptable by strtotime
						is_numeric(									// Numeric
								is_string($_value) && $_value[0] === '@'	// String, [0] = "@" -> remove [0]
							?	substr($_value, 1)
							:	$_value
						)
					)										||
					empty( $_value )						||		// Empty
					is_numeric($_value);							// Numeric
		});
	}

	/**
	 * Alias of {@link #setExpiry( $_value )}
	 *
	 * Default: 30 days
	 *
	 * a)	"Empty" is converted to 0 -> session cookie
	 * b)	Numeric (also string) is time() + $_value
	 * b)	Exact Time
	 *		1)	{@see DateTime} is converted to it's timestamp
	 *		2)	String starting with @ is converted to an int
	 *			with @ first being removed
	 *		3)	Other strings are converted to timestamp with strtotime()
	 *
	 * @param DateTime|scalar|null $_value
	 * @return self
	 */
	public function expiry( $_value ) {
		return $this -> setExpiry( $_value );
	}

	/**
	 * Get Config - Expiry Time
	 *
	 * @param bool $_fallback_default should we fallback to default?
	 * @return int Unix Timestamp
	 */
	public function getExpiry( $_fallback_default = false ) {
		return $this -> getConfig( 'expiry', 60 * 60 * 24 * 30, $_fallback_default, function( &$_value ) {
			if ( empty( $_value ) || is_string( $_value ) && trim( $_value ) === '' ) {
				// Cookie expires when browser closes
				$_value	=	0;
			} else if ( $_value instanceof DateTime ) {
				// Exact: DateTime -> Timestamp
				$_value	=	$_value -> getTimestamp();
			} else if ( is_numeric( $_value ) ) {
				// now + $_value
				$_value	=	time() + (int) $_value;
			} else if ( is_string( $_value ) ) {
				$_value	=	$_value[0] === '@'
						?	(int) substr( $_value, 1 )	// Exact: Seconds since Unix Epoch
						:	strtotime( $_value ); 		// Exact: Text parsed by strtotime
			}
		} );
	}

	/**
	 * Set Config - Path
	 *
	 * Default: /
	 *
	 * @param string|null $_value path value
	 * @return self
	 */
	public function setPath( $_value = '' ) {
		return $this -> setConfig( 'path', $_value, function( &$_value ) {
			return is_string( $_value ) || empty( $_value );
		} );
	}

	/**
	 * Alias of {@link #setPath( $_value )}
	 *
	 * Default: /
	 *
	 * @param string|null $_value path value
	 * @return self
	 */
	public function path( $_value ) {
		return $this -> setPath( $_value );
	}

	/**
	 * Get Config - Path
	 *
	 * @param bool $_fallback_default should we fallback to default?
	 * @return string
	 */
	public function getPath( $_fallback_default = false ) {
		return $this -> getConfig( 'path', '/', $_fallback_default, function( &$_value ) {
			if ( empty( $_value ) || is_string( $_value ) && trim( $_value ) === '' ) {
				$_value	=	'/';
			}
		} );
	}

	/**
	 * Set Config - Secure
	 *
	 * Default: false
	 *
	 * @param bool $_value can also be a) string: true | false, b) int: 0 | 1, c) null
	 * @return self
	 */
	public function setSecure( $_value ) {
		return $this -> setConfig( 'secure', $_value, $this -> possibleBool() );
	}

	/**
	 * Alias of {@link #setSecure( $_value )}
	 *
	 * Default: false
	 *
	 * @param bool $_value can also be a) string: true | false, b) int: 0 | 1, c) null
	 * @return self
	 */
	public function secure( $_value ) {
		return $this -> setSecure( $_value );
	}

	/**
	 * Get Config - Secure
	 *
	 * @param bool $_fallback_default should we fallback to default?
	 * @return bool
	 */
	public function getSecure( $_fallback_default = false ) {
		return $this -> getConfig( 'secure', false, $_fallback_default, $this -> interpretAsBool() );
	}

	/**
	 * Set Config - Http Only
	 *
	 * @param bool $_value can also be a) string: true | false, b) int: 0 | 1, c) null
	 * @return self
	 */
	public function setHttpOnly( $_value ) {
		return $this -> setConfig( 'http_only', $_value, $this -> possibleBool() );
	}

	/**
	 * Alias of {@link #setHttpOnly( $_value )}
	 *
	 * @param bool $_value can also be a) string: true | false, b) int: 0 | 1, c) null
	 * @return self
	 */
	public function httpOnly( $_value ) {
		return $this -> setHttpOnly( $_value );
	}

	/**
	 * Get Config - Http Only
	 *
	 * @param bool $_fallback_default should we fallback to default?
	 * @return bool
	 */
	public function getHttpOnly( $_fallback_default = false ) {
		return $this -> getConfig( 'http_only', false, $_fallback_default, $this -> interpretAsBool() );
	}
}