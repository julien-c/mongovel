# Mongovel [![Build Status](https://travis-ci.org/julien-c/mongovel.png?branch=master)](http://travis-ci.org/julien-c/mongovel)
## A Laravel-ish wrapper to the PHP Mongo driver

Most MongoDB packages for Laravel insist on abstracting away the PHP driver and implementing a SQL-like, full-fledged query builder. We think the PHP driver for Mongo is great by itself and Mongo's expressiveness out-of-the-box is actually what makes it awesome.

In that spirit, Mongovel is a **thin wrapper over the PHP driver that makes it more Eloquent-like**:
* you'll be able to **access models like objects**, not arrays
* you'll get query results as **Laravel Collections**
* and some more syntactic sugar, like Facade-inspired **static shortcuts** to make the whole experience more elegant. And remember, you always keep the full power of Mongo methods, as Mongovel always proxies calls to the underlying MongoCollections and MongoCursors. We're sure you'll love it!

### Usage overview

Enough talking, here's how to use Mongovel:

```php
class Book extends MongovelModel
{
}
```

**`GET books/512ce86b98dee4a87a000000`**:

```php
public function show($id)
{
	$book = Book::findOne($id);

	// Here, you can access the book's attributes like in Eloquent:
	// $book->title, $book->reviews, etc.
	// $book->id is a string representation of the object's MongoId.

	// Let's say we're an API, so let's just send the object as JSON:

	return $book;
}
```

Mongovel detects that `$id` is the string representation for a MongoId, and returns an object that will be automatically serialized and sent as JSON by Laravel.

**`POST books`**:

```php
public function store()
{
	$book = Input::only('title', 'content');

	Book::insert($book);
}
```

What if we want to update some field on our book? Let's say we're posting a review:

**`POST books/512ce86b98dee4a87a000000/reviews`**:

```php
public function reviewStore($id)
{
	$review = Input::all();

	// You can leverage the full power of Mongo query operators:
	Book::update($id,
		array('$push' => array('reviews' => $reviews))
	);

	return Response::json(array('status' => 201), 201);
}
```

Deleting a book is as simple as:
```php
public function destroy($id)
{
	Book::remove($id);
}
```

Finally, Mongovel wraps MongoCursor results into Laravel Collections, so you can just do:
**`GET books`**:

```php
public function index()
{
	$books = Book::find();

	$books->each(function($book) {
		// Do anything you would do on a Laravel Collection
	});

	return $books;
}
```

### How to install

Add `julien-c/mongovel` as a requirement to composer.json, then run `composer update`.

Add Mongovel's service provider to your Laravel application in `app/config/app.php`. In the `providers` array add :
```php
'Mongovel\MongovelServiceProvider'
```
Add then alias Mongovel's model class by adding its facade to the `aliases` array in the same file :
```php
'MongovelModel' => 'Mongovel\Model'
```
Finally, add a MongoDB hash at the end of your `app/config/database.php` (so, outside of `connections`):

```php
'mongodb' => array(
	'default' => array(
		'host'     => 'localhost',
		'port'     => 27017,
		'database' => 'laravel',
	)
)
```

If needed (for MongoHq and the likes), you can specify a `username` and `password` in this array as well.

### Authentification

To use Mongovel as your Auth provided, you'll simply need to go in the `app/config/auth.php` file and set `mongo` as your driver.

### License

Licensed under the [MIT License](http://cheeaun.mit-license.org/).

### Other MongoDB wrappers in PHP

Before writing our own, here are the wrappers we've checked out (and sometimes contributed to):
* [hpaul/mongor](https://github.com/hpaul/mongor)
* [monga/monga](https://github.com/FrenkyNet/Monga)
* [flatline/mongol](https://github.com/xFlatlinex/laravel-mongol)
* [navruzm/lmongo](https://github.com/navruzm/lmongo)
