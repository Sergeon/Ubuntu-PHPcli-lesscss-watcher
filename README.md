# Ubuntu-PHPcli-lesscss-watcher
A simple lessc watcher for ubuntu

[![License](https://poser.pugx.org/leaphly/cart-bundle/license.png)](https://packagist.org/packages/leaphly/cart-bundle)

## Author
Mauro Caffaratto

This is a simple cli tool to watch changes on .less files and compile them to .css files. You can configure it to compile certain files whenever a set of files change, or to compile a file when it becomes changed.

#Issues:

    The watching process is blocking. So, if you want to run it in a web server, a tool like Screen https://help.ubuntu.com/community/Screen should be used to prevent undesired crashes.
    
    There is a known issue between Gedit and inotify -which this script uses-, so, if you modify a .less file with gedit, the compiling event will not fire. 

#dependencies:

    PHP cli, lessc and the inotify module of PHP must be installed. 

#Usage:

Configure your desired behaviour within the config.php file:

```php
  
  //check config.php docs to know more.
  //with this config, '/home/sergeon/rocket/less/style.less' will compile 
  //to '/home/sergeon/rocket/css/style.css' whenever a .less file changes under 
  //'/home/sergeon/rocket/less' or '/home/sergeon/rocket/lessincludes'.
  //On top of that, whenever '/home/sergeon/rocket/lessincludes/special.less'
  //becomes changed and saved, it will be compiled to '/home/sergeon/rocket/css/special.css'
  return array(

    'rocket'    => array(
        //will be watched and notify an event on save
        'watching_directories'    => array('/home/sergeon/rocket/less' , '/home/sergeon/rocket/lessincludes' ),
        //the files in this array will compile to their match in every inotify event
        'files_to_compile_ever'    => array( array('/home/sergeon/rocket/less/style.less' => '/home/sergeon/rocket/css/style.css' )     ),
        //those files are only compiled if they itselfs become notified
        'files_to_compile_on_save'    => array( array('/home/sergeon/rocket/lessincludes/special.less' => '/home/sergeon/rocket/css/special.css') ),
    ),



);

```

  To invoke the script, just call it with php and pass the proper parameters, for instance:
  
```
php path_to_script/less_watcher.php --project rocket --minify
```

  Parameters are:
  ```
  --project [projectName]  -> the name of the project data under config.php
  --minify  -> if present, minify  the css outputs applying -x param to lessc program  
  --verbose [true|false]  -> wether to print or not information to the terminal. True by default
  ```
  
  The rest of the script behaviour must be set in the config file. Check the docs of every file
  for further information.

