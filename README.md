# Get Options Improved for PHP

**Get Options Improved** (or **Getopti**) is written to be a better command-line parsing tool for PHP programs. Inspired by the [Optparser][optparser] library for Ruby, as well as the [Console_Getopt][console-getopt] library for PEAR/PHP, **Getopti** aims to make interacting with the command-line inside your PHP applications a much more enjoyable experience.

## Features

* PHP 5.3 Only (which is, in fact, a feature)
* No more formatting option strings
* Automated help output
* Callbacks (see the *Using Callbacks* section of this README)

## Usage

Assuming a CLI that can handle the following command:

    $ cmd write -C "I love PHP!" --content "Getopti rules!" -N file.txt -v

We might set up our application like so:

    <?php
    
    require 'yourapp.php';
    require 'Getopti/Getopti.php';
    
    $APP = new YourApp();
    $opts = new Getopti();
    
    // Set a simple description banner for use in the output
    $opts->banner("cmd makefile - a really, really hard way to create a file");
    
    // Set a banner to indicate command options
    $opts->banner("command options:");
    
    // Let's watch for the '-N' and '--name' flags
    $opts->on(array('N', 'name'), '[PATH]', 'set the name of the file',
      function ($name) use ($APP) {
        $APP->set_filename($name);
      }
    );
    
    // ...and the '-C' and '--content' flags
    $opts->on(array('C', 'content'), 'CONTENT', 'add content to the file',
      function ($content) use ($APP) {
        $APP->add_content($content);
      }
    );
    
    // Set a banner to indicate global options
    $opts->banner("global options:");
    
    // Watch for the '-v' and '--verbose' flags
    $opts->on(array('v', 'verbose'), FALSE, 'output more information',
      function ($verbose) use ($APP) {
        $APP->verbose = $verbose;
        // Usefull when indicating a usage using $opts->usage($text, TRUE)
        // to indicate output for verbose and non-verbose settings (TODO)
        Getopti::$verbose = true;
      }
    );
    
    // Register the '-h' and '--help' flags
    $opts->on(array('h', 'help'), FALSE, 'show help information for a given command',
      function ($help) {
        Getopti\Output::help();
      }
    );
    
    // Set a final line of output text
    $opts->footer("use 'cmd help makefile [-v]' for more information");
    
    // Read the command-line arguments
    // And parse the command
    $args = Getopti::read_args();
    $results = $opts->parse($args);

## Results

After setting up your command, running `$opts->parse()` would default to returning an array similar to this:

    array(
      0 => array('C', 'I love PHP!'),
      1 => array('content', 'Getopti rules!'),
      2 => array('N', 'filename.txt')
      3 => array('v', NULL)
    )

The default is to return the results as if you were parsing the arguments using the `Getopti\Parser::parse` method (which is based on PHP/PEAR's [Console_Getopt][console-getopt] class). This is due to the fact that some CLI applications might prefer to know in what order the options were called, exactly what flag was used... etc. If you do not care, using `$opts->flat_opts()` (or passing `TRUE` as a second parameter to `$opts->parse()`) will return a flattened, smartly populated array of options (regardless of whether they were included in the arguments or not). For example:

    array(
      'name'    => array('filename.txt'),
      'content' => array('I love PHP!', 'Getopti rules!'),
      'verbose' => TRUE,
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
        
        // When defining our closures, we must explicitly 'use'
        // the copy/reference.
        
        $opts->on(array('v', 'version'), FALSE, 'show version',   
          function ($show) use ($self) {
            if($show) echo "OS X {$self->version}";
          }
        );
        
        $args = Getopti::read_args();
        $opts->parse($args);
      }
    }

### In a static context (I prefer this implementation):

      class Macintosh {
        
        public static $version = "10.7";
        
        function __construct()
        {
          $opts = new Getopti();
          
          $opts->on('version', FALSE, 'show version', 
            function ($show) {
              // DO NOT USE `self::$version` or `static::$version`. It won't
              // resolve, will raise an exception and is no different than
              // using `Macintosh::$version`
              
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

[optparser]: http://www.ruby-doc.org/stdlib/libdoc/optparse/rdoc/index.html
[console-getopt]: http://pear.php.net/package/Console_Getopt
[phpdoc-closures]: http://php.net/manual/en/functions.anonymous.php
[phpdoc-closures-wiki]: http://wiki.php.net/rfc/closures/removal-of-this
[phpdoc-closures-bug]: http://bugs.php.net/bug.php?id=49543