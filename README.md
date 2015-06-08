# Utility Classes

This library provides a range of utility classes that are used throughout the framework

## What's in the toolbox?

### Hash

A ``Hash`` (as in PHP arrays) class, capable of extracting data using an intuitive DSL:

```php
$things = [
    ['name' => 'Mark', 'age' => 15],
    ['name' => 'Susan', 'age' => 30],
    ['name' => 'Lucy', 'age' => 25]
];

$bigPeople = Hash::extract($things, '{n}[age>21].name');

// $bigPeople will contain ['Susan', 'Lucy']
```

Check the [official Hash class documentation](http://book.cakephp.org/3.0/en/core-libraries/hash.html)

### Text

The Text class includes convenience methods for creating and manipulating strings.

```php
Text::insert(
    'My name is :name and I am :age years old.',
    ['name' => 'Bob', 'age' => '65']
);
// Returns: "My name is Bob and I am 65 years old."

$text = 'This is the song that never ends.';
$result = Text::wrap($text, 22);

// Returns
This is the song
that never ends.
```

Check the [official Text class documentation](http://book.cakephp.org/3.0/en/core-libraries/text.html)

### Xml

The Xml class allows you to easily transform arrays into SimpleXMLElement or DOMDocument objects
and back into arrays again

```php
$data = [
    'post' => [
        'id' => 1,
        'title' => 'Best post',
        'body' => ' ... '
    ]
];
$xml = Xml::build($data);
```