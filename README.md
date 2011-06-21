# Get Options Improved for PHP

**Get Options Improved** (or **Getopti**) is written to be a better command-line parsing tool for PHP programs. Inspired by the [Optparser][optparser] library for Ruby, as well as the [Console_Getopt][console-getopt] library for PEAR/PHP, **Getopti** aims to make interacting with the command-line inside your PHP applications a much more enjoyable experience.

## Requirements

* PHP 5.3 or greater

## Features

* PHP 5.3 Only (which is, in fact, a feature)
* No more formatting option strings
* Automated help output
* Callbacks (see the *Using Callbacks* section of this README)

## PEAR Installation

To install using PEAR, you first have to discover my [Pearfarm Channel](http://bschaeffer.pearfarm.org/), then you can install the library (*please note, installation via this channel may only be a temporary solution.*). All you have to do is hit up your command-line and type the following:

    [sudo] pear channel-discover bschaeffer.pearfarm.org
    [sudo] pear install bschaeffer/Getotpi-beta


## Usage

### Initialization

Just require the library and get a new instance:

    require 'Getopti/Getopti.php';
    $opts = new Getopti;

### banner

Banners are simply unpadded lines of text to be added to the automated help output.

    $opts->banner(string $banner);

### usage

Usage lines should be used for longer, more descriptive lines of text. They are automatically wrapped and left padding is added to them (see the 'Configuration' section of this readme for padding information).

    $opts->usage(string $usage);

### command

    $opts->command(string $command[, string $description]);

This method is primarily hear to enable uniformity in generating output. The output is similar to the output generated when calling `$opts->on()`. Use it in your main help class when displaying a list of commands available within you CLI application.

### on

This method is used to add options. Option data is automatically added to the automated help output.

    $opts->on(mixed $opts, [string $parameter, string $description, closure $callback]);

**Examples**

Specify only the short option `-v` for indicating verbose output in your app:

    $opts->on('v', NULL, 'output more information where applicable');


Specify only the long option `--verbose` for indicating verbose output in your app:

    $opts->on('verbose', NULL, 'output more information where applicable');


Specify both `-v` and `--verbose` options for indicating verbose output in your app:

    $opts->on(array('v', 'verbose'), NULL, 'output more information where applicable');


Specify the option `--revision` that expects a REQUIRED revision number as a parameter (*Getopti will raise a `Getopti\Exception` if the parameter is missing.*):

    $opts->on('revision', 'REV', 'specify the revision number to operate on');

Specify the option `--revision` that expects an [OPTIONAL] revision number as a parameter:

    $opts->on('revision', '[REV]', 'specify the revision number to operate on');

Specify the option `--revision` with an [OPTIONAL] revision number that defaults to `HEAD`:

    $opts->on('revision', array('[REV]', 'HEAD'));

Specify both the `-h` and `--help` options, using a callback to display automated help output:

    $opts->on(array('h', 'help'), 'show help information',
      function ($help) use ($opts) {
        echo $opts->help();
        exit();
      }
    );

*Please note: The default is not to run the callback unless the option is specified.*

### parse

Parses the passed arguments based on previously defined options.

    $opts->parse(array $arguments [, bool $flatten])

This method requires that you pass the `$arguments` array directly to it. Fortunately this is really easy.

    $args = Getopti::read_args();
    $results = $opts->parse($args);

The optional `$flatten` parameter is described in the *Results* section of this README.

## Utility Methods

### read_args

A **static** function that attempts to retrieve the command-line arguments from various global PHP variables:

    Getopti\Utils::read_args([int $trim]);

The optional `$trim` parameter simply removes `n` number of arguments from the beginning of the arguments array:

    // $ cmd my great arguments 
    $args = Getopti\Utils::read_args(1); // array('great', 'arguments')

## Configuration

    Getopti::$columns         = 0;  # columns to wrap at (defaults to 75, auto-discovered if possible)
    Getopti::$left_padding    = 1;  # cmd/opt padding for the left side of the terminal
    Getopti::$right_padding   = 2;  # all output padding for the right side
    Getopti::$option_padding  = 26; # padding between cmd/opt and their descriptions

## Full Example

Assuming a CLI that can handle the following command:

    $ cmd write -C "I love PHP!" --content "Getopti rules!" -N file.txt -- brkopt

We might set up our application like so:

``` php
<?php

require 'yourapp.php';
require 'Getopti/Getopti.php';

$APP = new YourApp();
$opts = new Getopti();

$opts->banner('cmd write');
$opts->usage('A really, really hard way to create a file!');

$opts->banner('');
$opts->banner('command options:');

$opts->on(array('N', 'name'), '[PATH]', 'set the name of the file',
  function ($name) use ($APP) {
    $APP->set_name($name);
  }
);

$opts->on('ext', array('[EXT]', 'txt'), 'set the file extension',
  function ($ext) use ($APP) {
    $APP->set_ext($ext);
  }
);

$opts->on(array('C', 'content'), 'CONTENT', 'add content to the file',
  function ($content) use ($APP) {
    $APP->add_content($content);
  }
);

$opts->banner('');
$opts->banner('global options:');

$opts->on('help', FALSE, 'show help information for a given command',
  function ($help) use ($opts) {
      echo $opts->help();
      exit();
  }
);

$args = Getopti::read_args();
$results = $opts->parse($args);
?>
```

## Output

To output usage information for the above command, use `$opts->help()`. It would look something like this:

    cmd write
     A really, really hard way to create a file!
     
    command options:
     -N, --name [PATH]       set the name of the file
     -C, --content CONTENT   add content to this file
    
    global options:
         --help              show help information for a given command

## Results

After setting up the above command, running `$opts->parse()` would default to returning the following results:

    $results = array(
      0 => array(
        0 => array('C', 'I love PHP!'),
        1 => array('content', 'Getopti rules!'),
        2 => array('N', 'filename.txt')
      ),
      1 => array(
        0 => 'makefile'
      ),
      2 => array(
        0 => 'brkopt'
      )
    );

### Explanation

* `$results[0]` - all the matched option flags (and values) from the command-line arguments.
* `$results[1]` - all the non-options that the parser was not able to match with any flags.
* `$results[2]` - all the options specified after a `--` argument.

### Result Variables

The following variables will be populated after parsing:

    $opts->results      # identical to the entire $results array above
    $opts->options      # smartly indexed array of options (see 'Flattened Options' below)
    $opts->nonopts      # identical to $results[1] above
    $opts->breakopts    # identical to $results[2] above

### Flattened Options

After parsing, the `$opts->options` property will hold an array of values indexed based on the option used to specify them:

    $flattened = array(
      'name'    => array('filename.txt'),
      'ext'     => array('txt'),
      'content' => array('I love PHP!', 'Getopti rules!'),
      'help'    => FALSE
    );

The following rules explain how the above options are organized:

1. They are sorted based on the order in which they were set using the `$opts->on()` method.
2. If a long option is present, they will be indexed based on the long option. Otherwise, we use the short option.
3. Uncalled options will be set to `FALSE` unless a default was given (note that neither `-h`, `--help` or `--ext` was called in the arguments from the example above).
4. If the option accepts a parameter, it can be specified multiple times. If it doesn't accept a parameter, it will either be set to `TRUE` (indicated in the arguments) or `FALSE` (not indicated).

## Using Callbacks

In PHP 5.3, you can pass [closures][phpdoc-closures] (anonymous functions) as parameters to other functions so that they may be used as callbacks. This is one of the reasons Getopti requires PHP 5.3.

Here are few examples of how to use closures/callbacks with Getopti (or, for that matter, any PHP application):

### Within an instance of an object:

``` php
<?php

class Macintosh {
  
  public $version = "10.7";
  
  function __construct()
  { 
    // We must make a copy of $this
    $self = $this;
    
    $opts = new Getopti();
    
    // When defining our closures, we must explicitly 'use' the copy.
    $opts->on(array('v', 'version'), FALSE, 'show version',
      function ($show) use ($self) {
        echo "OS X {$self->version}";
      }
    );
    
    $args = Getopti::read_args();
    $opts->parse($args);
  }
}
?>
```

### Statically:

``` php
<?php

class Macintosh {

  const VERSION = "10.7";
  
  function __construct()
  {
    $opts = new Getopti();
    
    $opts->on('version', FALSE, 'show version',
      function ($show) {
        echo "OS X ".Macintosh::VERSION;
      }
    );
    
    $args = Getopti::read_args();
    $opts->parse($args);
  }
}
?>
```

**For more information on closures, see**:

* [Removal of $this in closures][phpdoc-closures-wiki]
* [Inheriting $this][phpdoc-closures-bug] (a bug report, but shows why you must use copies of `$this`)

## Problems to Solve/Todo

* Much more documentation is needed (variables, constants, etc..)
* A few more tests are needed (run `phpunit . && open Reports/index.html` for info on what needs testing)
* Add functionality that would allow indicating which options are allowed to be specified multiple times. Something like "ITEM [+]" or "[ITEM] [+]" (see Mercurial's help output as an example).

## License

Getopti is Copyright © 2011 Braden Schaeffer. It is free software, and may be redistributed under the terms specified in the LICENSE file.

[optparser]: http://www.ruby-doc.org/stdlib/libdoc/optparse/rdoc/index.html
[console-getopt]: http://pear.php.net/package/Console_Getopt
[phpdoc-closures]: http://php.net/manual/en/functions.anonymous.php
[phpdoc-closures-wiki]: http://wiki.php.net/rfc/closures/removal-of-this
[phpdoc-closures-bug]: http://bugs.php.net/bug.php?id=49543