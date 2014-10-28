[![badge: LICENSE]][LICENSE.md] [![badge: Semver 2.0.0]][badge url: Semver 2.0.0]

**NOTE: PROJECT IS NOT UNDER ACTIVE DEVELOPMENT AND IS PROVIDED AS-IS**

# PHP: [objectifycookie]

[objectifycookie] is a small PHP Library that aims to simplify cookie management through a comfortable OOP interface.

All work should be done with `objectifycookie\Registry::instance()`
You can also use `cookie()` in the default namespace as a short-cut.

## Getting
```php
assert cookie() -> key -> sub_key == $_COOKIE["key"]["sub_key"];
```

## Setting

```php
// Add the value to $_COOKIE["key"] and call setcookie()
// (can be changed depending on Injector)
cookie() -> key = "value";

// Or same as above but setting all values in the array.
cookie() -> key = ["value_1", "value_2", "value_3"];
```

## Isset:
Checking if a cookie-key is set can be done with PHPs `isset()`.

## Unset:
Unsetting a cookie-key is done with PHPs `unset()`.
This unsets the key in PHP as well as calling `setcookie()`.

## Foreach & Count:

```php
if ( count( cookie() ) {
	foreach ( cookie() as $key => $value ) {
		do_something( $key, $value );
	}
}
```

## ArrayAccess

For all of above, it is also doable using ArrayAccess notation:

```php
$cookie	=&	cookie();
$cookie["key"]["sub_key"]	=	value;
```

## toArray:
To get the underlying cookie data (element in `$_COOKIE`), call:
```php
cookie() -> toArray();
```

## Injection:
If you want to set a cookie with a special injector, use:

```php
cookie() -> key	=	new objectifycookie\Injector( $config, $value );
// Where $value is the value you want set, be it a scalar or a list(array)
```

## Parent: (Applies to Component)

You can get the parent component (that is the owning array|set) by calling:
```php
cookie() -> component -> parent();
```

Examining this call, `parent()` will actually return a reference to `objectifycookie\Registry`

## Author

[Mazdak Farrokhzad / Centril <twingoow@gmail.com>](https://github.com/Centril)

## Changelog

There won't be any =)

## Bugs / Issues / Feature requests / Contribution

If there are any bugs, fork the project and make a pull request,
the project is not in active development, it is provided as-is.

## License

[objectifycookie] is licensed under **Apache License 2.0**, see **[LICENSE.md]** for more information.

<!-- references -->

[objectifycookie]: https://github.com/Centril/objectifycookie

[badge: License]: http://img.shields.io/badge/license-ASL_2.0-blue.svg
[LICENSE.md]: LICENSE.md
[badge: Semver 2.0.0]: http://img.shields.io/badge/semver-2.0.0-blue.svg
[badge url: Semver 2.0.0]: http://semver.org/spec/v2.0.0.html

<!-- references -->

