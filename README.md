Firelite CMS
============

Firelite is a Laravel 3 CMS, created as a bundle, it is intended to sit alongside your application allowing you to create a website to supplement it.

It can also be used to create a website without any custom application work.

It is currently in a development state, although it has been used successfully to create a live site.

Notes on installation (mainly for myself at the moment)

Copy the bundle folder to your application's bundles folder
activate it in bundles.php with:
'''	'firelite' => array('auto' => true), '''

The laramce bundle is also required for rich text editing: https://github.com/CharlGottschalk/laramce

copy the templates folder (application/views/firelite/templates) into application/views

Then run:

artisan migrate:firelite
artisan bundle:publish
artisan firelite::setup:tree --name=Main
artisan firelite::nodetype:install alias
artisan firelite::nodetype:install page
artisan firelite::datatype:install simpletext
artisan firelite::datatype:install largetext
artisan firelite::datatype:install simpleimage

installing a template is:
artisan firelite::template:install --view=standard-page.index
