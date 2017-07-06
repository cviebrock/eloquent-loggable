# Eloquent-Loggable

Easy way to track changes to your Eloquent models in Laravel 5.

[![Build Status](https://travis-ci.org/cviebrock/eloquent-loggable.svg?branch=master&format=flat)](https://travis-ci.org/cviebrock/eloquent-loggable)
[![Total Downloads](https://poser.pugx.org/cviebrock/eloquent-loggable/downloads?format=flat)](https://packagist.org/packages/cviebrock/eloquent-loggable)
[![Latest Stable Version](https://poser.pugx.org/cviebrock/eloquent-loggable/v/stable?format=flat)](https://packagist.org/packages/cviebrock/eloquent-loggable)
[![Latest Unstable Version](https://poser.pugx.org/cviebrock/eloquent-loggable/v/unstable?format=flat)](https://packagist.org/packages/cviebrock/eloquent-loggable)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/cviebrock/eloquent-loggable/badges/quality-score.png?format=flat)](https://scrutinizer-ci.com/g/cviebrock/eloquent-loggable)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/0b966e13-6a6a-4d17-bcea-61037f04cfe7/mini.png)](https://insight.sensiolabs.com/projects/0b966e13-6a6a-4d17-bcea-61037f04cfe7)
[![License: MIT](https://img.shields.io/badge/License-MIT-brightgreen.svg?style=flat-square)](https://opensource.org/licenses/MIT)


* [Installation](#installation)
* [Updating your Eloquent Models](#updating-your-eloquent-models)
* [Usage](#usage)
  * [Change Sets](#change-sets)
  * [Other Query Scopes](#other-query-scopes)
* [Configuration](#configuration)
* [Bugs, Suggestions and Contributions](#bugs-suggestions-and-contributions)
* [Copyright and License](#copyright-and-license)



## Installation

First, you'll need to install the package via Composer:

```shell
$ composer require cviebrock/eloquent-loggable
```

Then, update `config/app.php` by adding an entry for the service provider
(if you are using Laravel 5.5 with package auto-discovery, you can skip this step):

```php
'providers' => [
    // ...
    Cviebrock\EloquentLoggable\ServiceProvider::class,
];
```

Finally, from the command line again, publish the default configuration file:

```shell
php artisan vendor:publish --provider="Cviebrock\EloquentLoggable\ServiceProvider"
```



## Updating your Eloquent Models

Your models should use the `Cviebrock\EloquentLoggable\Loggable` trait.  This trait provides
two methods which can be overloaded in your models:

1.  `getLoggableAttributes()` should return an array of all the model's attributes that you'd like
    to log.  By default, it returns an empty array, which tells the package to log *all* the attributes
    that have changed.

2.  `getUnloggableAttributes()` should return an array of all the model's attributes that you'd like
    to exclude from logging, even if they have changed.  By default, the timestamp columns `created_at` and
    `updated_at` are excluded, as is `deleted_at` if your model also uses Eloquent's `SoftDeletes` trait.

When calculating what attributes to log, the package starts with the "loggable" ones, then removes 
any "unloggable" ones, so configuring a combination of the two methods should provide the greatest flexibility 
in choosing what to log.  If you want to log the timestamp columns, then explicitly add them to the
array returned in `getLoggableAttributes()`.

> Note: any attributes defined as hidden via Eloquent's `hidden` property are obfuscated when
> they are logged.  This should prevent passwords and other sensitive information from being 
> logged in your database in plain text.  **Be sure to add the appropriate attributes to this array!**

```php
use Cviebrock\EloquentLoggable\Loggable;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use Loggable;

    // If the password is changed, it will be obfuscated in the logged change
    protected $hidden = [
        'secret_code'
    ];

    // Default value is an empty array, which will log all changed attributes
    public function getLoggableAttributes(): array
    {
        return [];
    }

    // The following attributes won't be logged at all
    public function getUnloggableAttributes(): array
    {
        return [
            'unimportant_field'
        ];
    }
}
```



## Usage

When a model is created, updated, deleted, or restored, a record of that change will be logged.  Specifically,
a new `Cviebrock\EloquentLoggable\Models\Change` model will be created with the relevant data, including who made the change.

You can get a history of changes for a particular model using the `changes()` relation that the trait sets up:

```php
$post = Post::find(1);

$changes = $post->changes;
```

This will be a collection of `Change` models, with the most recent changes first.  Each `Change` has the following attributes:

```php
// The user who made the change:
$change->user;

// The date of the change:
$change->created_at;

// The attribute changed, the previous and new values of that attribute:
$change->attribute;
$change->old_value;
$change->new_value;
```

You can also work from the other end: finding out what model changed based on a given `Change` model.

```php
$change = Change::find(1);

$model = $change->model;
```


### Change Sets

When a change to a model includes several attributes, each attribute that changed 
creates a new `Change` record/model.  However, you can group together all the changes 
that happened to that model at the same time via the `set` attribute.

(This attribute is really just a hash of the changed model, it's ID, the user that initiated the change,
and the time of the change).

```php
$change = Change::find(1);

$relatedChanges = Change::inSet($change->set)->get();
```


### Other Query Scopes

You can also search for changes using several other query scopes on the `Change` model:

```php
$post = Post::find(1);

$changes = Change::forModel($post)
	->ofType(Change::TYPE_UPDATE)
	->groupedBySet()
	->get();  // or paginate, even!
```



## Configuration

The package publishes it's configuration file to `config/loggable.php`.  The only setting 
required is `userModel` which should return the fully-qualified class name of your 
application's user model.  The default value -- `App\User::class` -- is usually sufficient.



## Bugs, Suggestions and Contributions

Thanks to [everyone](https://github.com/cviebrock/eloquent-loggable/graphs/contributors)
who has contributed to this project!

Please use [Github](https://github.com/cviebrock/eloquent-loggable) for reporting bugs, 
and making comments or suggestions.
 
See [CONTRIBUTING.md](CONTRIBUTING.md) for how to contribute changes.



## Copyright and License

[eloquent-loggable](https://github.com/cviebrock/eloquent-loggable)
was written by [Colin Viebrock](http://viebrock.ca) and is released under the 
[MIT License](LICENSE.md).

Copyright (c) 2017 Colin Viebrock
