Skhema Template Engine
======================

Skhema is a templating engine with an elegant and compact notation, that I initially developed for use by [Bramble][].

[bramble]: http://blog.jacere.net/bramble


Syntax
------

* `{@template}`
	* Defines a binding source.
	* May be defined within another template.  Inline templates have no restrictions on usage.
	* May inherit another template.
	* May be included by another template.
	* Only templates are allowed at the root level.  Anything else is undefined (ignored or exception).

* `{$variable}`
	* Slot to be filled either by data-binding or template inheritance.
	* If the variable is available for binding, but no value is found in the binding context, the evaluator will look in the root context before giving up.  This is the current mechanism for handling global variables.
	* Undefined behavior if the variable never gets a value (ignored or exception).
	* Filter support, which allows calling registered user-defined functions.

* `{#include}`
	* Includes another named template inline.
	* The included template inherits the current binding context if a named binding is not found.
	* The generator will throw an exception if there are cycles in the dependency graph.

* `{^extend}`
	* Extends another template.
	* A template can extend at most one template, and this token must be first (ignoring whitespace).
	* Variables are inherited, with any values defined in the parent.

* `{.define}`
	* Defines the contents of a variable from an inherited template.
	* Variables currently have no access modifiers, so there are no private variables which cannot be defined (or redefined).

* `{?source}`
	* Binding source, either for a list or just for changing the binding context.
	* Implemented as anonymous templates that are transformed into includes.

* `{/close}`
	* Closes the current scope.
	* Any text after the `/` is irrelevant.

* `{%call}`
	* Basic function call with only a few built-in functions available for now.
	* User-defined functions can be called just like with variable filters.


Usage
-----

```php
$manager = manager = TemplateManager::create((TEMPLATE_PATH);
$output = $manager->evaluate('Example', $data);
```


Data Binding
------------

A root named binding will apply to all matching template instances.  There is currently no way to specify that a template usage cannot be bound by name.  I probably won't change this because it would be very poor design to have a template structure where that is a possibility.  Such silliness will not be allowed.

For now, binding is still very basic.  In the Bramble MVC pattern, the Model is responsible for querying the database and transforming the results into the template mappings.  The following snippets show part of the process of mapping query results to the expected template parameters and assigning them to a root named binding.

```php
$posts_sql = '
	SELECT p.ID, p.Date, p.Title, p.Slug, p.Content, u.DisplayName FROM Posts p
	INNER JOIN Users u ON p.AuthorID = u.ID
	WHERE p.Type = 2
	AND p.Date BETWEEN :start AND :end
	ORDER BY p.Date DESC
';

$list[] = [
	'author'  => $row['DisplayName'],
	'date'    => date('Y-m-d', $time),
	'url'     => FormatURL($time, $row['Slug']),
	'title'   => $row['Title'],
	'content' => $row['Content'],
	'categories' => $categories,
];

return [
	'Posts' => [
		'title' => $title,
		'list' => $list
	]
];
```


Functions and Filters
---------------------

Functions and variables are implemented as an evaluation token with a stack of filters, using a shared syntax.  Filters can be stacked on variables, functions, and other filters.

```
{%cycle[white,LightGray]}
{%format-url[post]}
{%first[list/time]:format-date[atom]}
{$content:subvert[code,root,header=3]}
```

The function/filter needs to be registered and the arguments are the filter options, the current binding context, and the input value (for filters).  Any applicable filter options are the responsibility of the filter handler.

```php
$manager->register('format-date', function($options, $context, $date) {
	// return the formatted date using the specified options
});
```


Examples
--------

This is a basic template with variables, inline template definitions, includes, and binding source.  The inline templates are probably not a common way of doing things, but they work the same as included instances.  In this particular case, they simply serve to change the named binding context.  The `{$content}` variable in this case is intended to be populated by an inheriting template.

```html
{@TemplateBase}
<!DOCTYPE html>
<html>
<head>
	<title>{$title}</title>
</head>
<body>
	<div id="header">
		{@Header}
			<h1><a href="{$root-url}">jacere.net</a></h1>
			{@Navigation}
				<div id="nav">
					<ul>
						{?nav-list}
							<li><a href="{$url}">{$text}</a></li>
						{/?}
					</ul>
				</div>
			{/@}
		{/@}
	</div>

	<div id="content">
		{$content}
	</div>
	<div id="sidebar">
		{#Sidebar}
	</div>
</body>
</html>
{/@}
```

Here is an example of inheriting this base template to show a list of posts.  The `{$content}` variable is defined as a binding source, which is basically a loop over the named data context.

```html
{@Posts}
	{^TemplateBase}
	{.content}
		{?list}
			{#PostSection}
		{/?}
	{/.}
{/@}

{@PostSection}
<div class="article">
	<div class="title">
		<small>Posted by: <strong>{$author}</strong> | {$date}</small>
		<h2><a href="{$url}">{$title}</a></h2>
	</div>
	<div class="post">
		<div class="entry">
			{$content:subvert[code,root]}
		</div>
		<div class="postinfo">
			Posted in
			<ul class="taglist">
				{?categories}
					<li><a href="{$url}">{$name}</a></li>
				{/?}
			</ul>
		</div>
	</div>
</div>
{/@}
```

This example shows an inheritance hierarchy where each template extends or defines the functionality of the parent.

```html
{@SidebarSection}
	<div class="right-sub-section" id="{$id}">
		<div class="title">
			<h2>{$title}</h2>
		</div>
		{$content}
	</div>
{/@}

{@SidebarSectionList}
	{^SidebarSection}
	{.content}
		<ul>
			{$list}
		</ul>
	{/.}
{/@}

{@SidebarSectionLinkList}
	{^SidebarSectionList}
	{.list}
		{?list}
			<li><a href="{$url}" title="{$text}">{$text}</a></li>
		{/?}
	{/.}
{/@}

{@SidebarSectionRecent}
	{^SidebarSectionLinkList}
	{.id}recent-posts{/}
	{.title}Recent Posts{/}
{/@}
```


License
-------

This project is licensed under the terms of the MIT license.