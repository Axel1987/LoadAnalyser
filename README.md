# ⏱ PHP LoadAnalyser tool 🛠

## Highlight
- Support for Laravel framework » [Laravel](https://laravel.com)
- Support interface web, web console and command line
- Print information about PHP version, max exaction time and max memory
- Measure time, memory usage and memory peak
- Switch automatically between interfaces
- Support PHP version 5.6, 7.0, 7.1, 7.2

## Easy to use
```php
// Add namespace at the top
use LoadAnalyser\LoadAnalyser;

// Set measure point
LoadAnalyser::point();

//
// Run test code
//

// Finish all tasks and show test results
LoadAnalyser::results();

```
## Functions
Set measuring point with or without label

```php
LoadAnalyser::point( <optional:label> );
```

Finish previous measuring point 

```php
LoadAnalyser::finish();
```

Finish all measuring points and return test results

```php
LoadAnalyser::results();
```

## Command line

Run the performance test for the command line

```php
// Normal
$ php your_script.php

// Or live version
$ php your_script.php --live 
```
# Installation

## Install with Laravel
Get PHP performance tool by running the Composer command in the command line. 
```
 $ composer require Axel1987/LoadAnalyser
```

Open your file for the performance test.
```php
// Add namespace at the top
use LoadAnalyser\LoadAnalyser;

// Set measure point
LoadAnalyser::point();

//
// Run test code
//

// Finish all tasks and show test results
LoadAnalyser::results();
```

## Install with Composer
Get PHP performance by running the Composer command in the command line. 
```{r, engine='bash', count_lines}
 $ composer require AxelDzhurko/LoadAnalyser
```

Open your file for the performance test.
```php
// Require vender autoload
require_once('../vendor/autoload.php');

// Add namespace at the top
use LoadAnalyser\LoadAnalyser;

// Set measure point
LoadAnalyser::point();

//
// Run test code
//

// Finish all tasks and show test results
LoadAnalyser::results();
```
