# FRIX PHP FRAMEWORK

Frix is a [PHP](http://php.net) framework for rapid web development
inspired by [Django](http://www.djangoproject.com/), a well-known python
web framework.

The project was created some years ago as an attempt to conquer fame and
glory bringing a Django clone to the PHP world, taking as an advantage
the ease of finding PHP hosting in opposition to Python hosting.

Unfortunatelly, PHP object-orientation isn't as good as Python's, making
it difficult to reproduce many Django's basic features, specially when
object introspection was needed.

This project is discontinued, it's being shared just for fun, in case
someone finds it useful, it's not extensively tested to be used in
production on large websites but it may be suitable for small websites.

## Features

* Admin (scaffolding): create, read, update, delete objects:
	* Support for hierarchical data;
	* Custom ordering;
	* Inline editing of related objects;
* Object Relational Mapper (ORM): data models, query abstraction:
	* MySQL database backend;
* Regular expression URL Router;
* Pure-PHP templating engine with blocks and inheritance support:
	* Warning: not a great performer;
* Error handling;


## Installation

Clone the [project](http://github.com/ricobl/frix) on [Github](http://github.com/):

	git clone git://github.com/ricobl/frixphp.git

Create a directory for your project and place an `index.php` file with
the following content:

	<?
	// Replace the path according to your setup
	require_once('path/to/frix/main.php');
	Frix::start();
	?>

Create a `config.php` file with, at least, the database configuration:

	<?
	$config['DB_URL'] = 'mysql://user:pass@localhost/database';
	?>

Frix only has support for MySQL, but it's possible to create backends for
different databases. Take a look at `frix/mod/Db` and `frix/mod/DbMysql`.

The framework will automatically set some configuration variables but
customization is possible using a `config.php` file. For an overview of
available settings, take a look at the `default_config.php` file.

## Database Structure

Some of the	bundled apps have SQL scripts with the required database
structure or initial data for the app.

Just create your database, and run each SQL script to generate the
initial structure.

## Static Media

Frix comes with some auxiliary static media files that need to be
mapped in the config file in order to use some of the bundled apps.

Your project may have its own media directory, with javascript, css, 
and image files as long as any user uploaded content.

## Bundled Apps

* Auth: user authentication
* Admin: models CRUD (or scaffolding)
	* Filebrowser: file manager
* Cms: pages, sub-pages and menus
* Settings: website settings (generic configuration)

For the bundled filebrowser feature to work, the `upload` directory inside
the project's media directory must be writable.

