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

After installation, all you have to do is require it in your application:

    require 'Getopti/Getopti.php';

## Usage

### Initialization

Just require the library and get a new instance:

    require 'Getopti/Getopti.php'; // PEAR Installation
    $opts = new Getopti();

### banner

Banners are simply unpadded lines of text to be added to the automated help output.

    $opts->banner(string $banner);

### usage

Usage lines should be used for longer, more descriptive lines of text. They are automatically wrapped and left padding is added to them (see the 'Configuration' section of this readme or padding information).

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

Specify the option `--revision` that expects an [OPTIONAL] revision number as parameter:

    $opts->on('revision', '[REV]', 'specify the revision number to operate on');


Specify both the `-h` and `--help` options, using a callback to display automated help output:

    $opts->on(array('h', 'help'), NULL, 'show help information',
      function ($help) use ($opts) {
        if($help)
        {
          echo $opts->help();
          exit();
        }
      }
    );

*Please note: The default is not to run the callback unless the options is specified. This may change based on the needs presented by real world usage.*

### read_args

A **static** function that attempts to retrieve the command-line arguments from various global PHP variables:

    Getopti::read_args([int $trim])

The optional `$trim` parameter simply removes `n` number of arguments from the beginning of the arguments array:

    // $ cmd my great arguments 
    $args = Getopti::read_args(1); // array('great', 'arguments')

### parse

Parses the passed arguments based on previously defined options.

    $opts->parse(array $arguments [, bool $flatten])

This method requires that you pass the `$arguments` array directly to it. Fortunately this is really easy.

    $args = Getopti::read_args();
    $results = $opts->parse($args);

The optional `$flatten` parameter is described in the *Results* section of this README.

### options (variable)

A variable holding the flattened results from the parsed options:

    $opts->options

### nonopts (variable)

A variable holding the non-option results (options which weren't matched by any rules):

    $opts->nonopts

### results (variable)

A variable holding the untouched parsed options. This data is the same as the returned data from the static method `Getopti\Parser::parse`:

    $opts->results

**Note:** See the *Results* section of this README for more information.

## Configuration

    Getopti::$columns = 0;          # parsed automatically if 0, number of columns before text wrap
    Getopti::$left_padding = 1;     # cmd/opt padding for the left side of the terminal
    Getopti::$right_padding = 2;    # all output padding for the right side
    Getopti::$option_padding = 26;  # padding between cmd/opt and their descriptions

## Full Example

Assuming a CLI that can handle the following command:

    $ cmd write -C "I love PHP!" --content "Getopti rules!" -N file.txt

We might set up our application like so:

    <?php
    
    require 'yourapp.php';
    require 'Getopti/Getopti.php';
    
    $APP = new YourApp();
    $opts = new Getopti();
    
    $opts->banner("cmd write");
    $opts->usage("A really, really hard way to create a file!")
    
    $opts->banner("");
    $opts->banner("command options:");
    
    $opts->on(array('N', 'name'), '[PATH]', 'set the name of the file',
      function ($name) use ($APP) {
        $APP->set_filename($name);
      }
    );
    
    $opts->on(array('C', 'content'), 'CONTENT', 'add content to the file',
      function ($content) use ($APP) {
        $APP->add_content($content);
      }
    );
    
    $opts->banner("");
    $opts->banner("global options:");
    
    $opts->on('help', FALSE, 'show help information for a given command',
      function ($help) use ($opts) {
          echo $opts->help();
          exit();
      }
    );
    
    $args = Getopti::read_args();
    $results = $opts->parse($args);

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

After setting up your command, running `$opts->parse()` would default to returning an array similar to this:

    $results = array(
      0 => array(
        0 => array('C', 'I love PHP!'),
        1 => array('content', 'Getopti rules!'),
        2 => array('N', 'filename.txt')
      ),
      1 => array(
        0 => 'makefile'
      )
    );

The first array (`$results[0]`) is populated with all the matched options from the command-line argument. The second (`$results[1]`) contains all the options that the parser was not able to match with any rules.

This is the default behavior due to the fact that some CLI applications might prefer to know in what order the options were called, exactly what flag was used... etc. If you do not care, the variable `$opts->options` (or passing `TRUE` as a second parameter to `$opts->parse()`) will return a flattened, smartly populated array of options regardless of whether they were included in the arguments or not. *This method does not return the non-options*. For example:

    array(
      'name'    => array('filename.txt'),
      'content' => array('I love PHP!', 'Getopti rules!'),
      'help'    => FALSE
    )

The following rules explain how the above options are organized:

1. They are sorted based on the order in which they were set using the `$opts->on()` method.
2. If a long option is present, they will be indexed based on the long option. Otherwise, we use the short option.
3. Uncalled options will be set to `FALSE` (note that neither `-h` or `--help` was called in the arguments).
4. If the option accepts a parameter, it can be specified multiple times. If it doesn't accept a parameter, it will either be set to `TRUE` (indicated in the arguments) or `FALSE` (not indicated).

## Using Callbacks

In PHP 5.3, you can pass [closures][phpdoc-closures] (anonymous functions) as parameters to other functions so that they may be used as callbacks. This is one of the reasons Getopti requires PHP 5.3.

Here are few examples of how to use closures/callbacks with Getopti (or, for that matter, any PHP application):

### Within an instance of an object:

    class Macintosh {
      
      public $version = "10.7";
      
      function __construct()
      { 
        // We must make a copy of $this so that we may reference
        // our app inside the closure.
        
        $self = $this;
        
        $opts = new Getopti();
        
        // When defining our closures, we must explicitly 'use' the copy/reference.
        $opts->on(array('v', 'version'), FALSE, 'show version',
          function ($show) use ($self) {
            if($show) echo "OS X {$self->version}";
          }
        );
        
        $args = Getopti::read_args();
        $opts->parse($args);
      }
    }

### Statically:

      class Macintosh {

        public static $version = "10.7";
        
        function __construct()
        {
          $opts = new Getopti();
          
          $opts->on('version', FALSE, 'show version',
            function ($show) {
              if($show) echo "OS X ".Macintosh::$version;
            }
          );
          
          $args = Getopti::read_args();
          $opts->parse($args);
        }
      }

**For more information on closures, see**:

* [Removal of $this in closures][phpdoc-closures-wiki]
* [Inheriting $this][phpdoc-closures-bug] (a bug report, but shows why you must use copies of `$this`)

## Problems to Solve/Todo

* Much more documentation is needed
* Many more tests are needed (PHPUnit)
* Add a `usage()` method. Allow for indicating usage for both verbose and non-verbose output.
* Should we run the 'callbacks' even if the option wasn't specified by the user?
* Add functionality that would allow indicating which options are allowed to be specified multiple times. Something like "ITEM+" or "[ITEM]+" (see Mercurial's help output as an example).

## License

Getopti is Copyright © 2011 Braden Schaeffer. It is free software, and may be redistributed under the terms specified in the LICENSE file.

[optparser]: http://www.ruby-doc.org/stdlib/libdoc/optparse/rdoc/index.html
[console-getopt]: http://pear.php.net/package/Console_Getopt
[phpdoc-closures]: http://php.net/manual/en/functions.anonymous.php
[phpdoc-closures-wiki]: http://wiki.php.net/rfc/closures/removal-of-this
[phpdoc-closures-bug]: http://bugs.php.net/bug.php?id=49543