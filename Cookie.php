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

/**
 * Simplifies cookie management
 * through a comfortable OOP interface.
 *
 * To use the framework, just use the cookie() function
 * which in turn will call Cookie_Registry::getInstance();
 *
 * If you get an exception of type: Cookie_Exception with the code:
 * Cookie_Exception::SET_FAILED, it means that a cookie could not be set
 * This is most likely due to existing output | headers already sent
 *
 * @package Cookie
 * @category Cookie
 * @version 0.1
 * @author Mazdak Farrokhzad / Centril <twingoow@gmail.com>
 */

require_once 'lib/Registry.php';

/**
 * Alias of Registry::getInstance()
 *
 * @see Registry::getInstance()
 * @return Registry
 */
function & cookie() {
	return objectifycookie\Registry::getInstance();
}
