<?php

/**
 *
 *Every field of the array represents a project, should be a folder with less files inside.
 *watching_directories field maps to folders you want to watch inside that project.
 *Whenever a less file is saved within every one of that folders, the will be notified and
 *used by the Less_Watcher class.
 *
 *    'files_to_compile_ever' is an array of array pairs, indicating which less file
 *    should compile to which css file. Whenever a less files is saved within ANY of the watching_directories,
 *    the files within this field will be compiled.
 *
 *     Usually, only one .less file will be within this field, and its css output will be the very only one css
 *     served in a website. For example, a tipycal configration is to have an importer.less which imports all sorts
 *     of less files, and have this file be the only less file compiled to css. Adding the folder with the imported
 *     .less files lie within 'watching_directories', will do the work properly.
 *
 *     'files_to_compile_on_save' IF you want to compile a .less file ONLY when that exact file is modified
 *     and saved, you should add it to the 'files_to_compile_on_save'. It will compile to its css pair only
 *     if 1) it itself is modified and saved 2) it exists within a folder referred in 'watching_directories'.
 *
 *     Note: all the folders containing the css pairs must exists and be writable by the invoker of the program.
 */
return array(

    'rocket'    => array(
        
        'watching_directories'    => array('/home/sergeon/rocket/less' , '/home/sergeon/rocket/lessincludes' ),
        
        'files_to_compile_ever'    => array( array('/home/sergeon/rocket/less/style.less' => '/home/sergeon/rocket/css/style.css' )     ),
        
        'files_to_compile_on_save'    => array( array('/home/sergeon/rocket/lessincludes/special.less' => '/home/sergeon/rocket/css/special.css') ),
    ),


);
