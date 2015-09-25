<?php


/**
 * Watches certain files based upon configuration
 * and compiles a certain less files to css files.
 * see the config.php documentation for the schema.
 */
class Less_Watcher{

    /**
     * wether or not print output to terminal
     * @var boolean
     */
    private $verbose;

    /**
     * List of directories that will be watched for file changes
     * @var array
     */
    private $watching_directories;

    /**
     * Those files will compile to its match within $config
     * whenever an IN_MODIFY inotify_event fires.
     * @var array
     */
    private $files_to_compile_ever;

    /**
     * Those files will compile to its match within $config
     * whenever a proper inotify_event with the same name as
     * the file fires.
     * @var array
     */
    private $files_to_compile_on_save;

    //Inotify instance
    private $inotify;

    /**
     * Keeps time of the last compiling.
     * @var integer
     */
    private $time = 0;

    /**
     * lessc minify param ('-x') or '', depending upon configuration.
     * @var string
     */
    private $minify_option;

    public function __construct( array $data , $minify = false ,  $verbose = true ){

            $this->verbose = $verbose;

            $this->watching_directories = $data['watching_directories'];

            if(empty($this->watching_directories))
                throw new Exception("There are no watching directories defined in this project");

            $this->files_to_compile_ever = $data['files_to_compile_ever'];

            $this->files_to_compile_on_save = isset($data['files_to_compile_on_save']) ? $data['files_to_compile_on_save'] : array() ;

            $this->minify_option = $minify ? " -x " : "" ;

            $this->inotify = inotify_init();

    }



    /**
     * just a simple log tool
     */
    private function line( $str , $preline = true , $extra = 0){

        if(! $this->verbose)
            return;


        if($preline)
            echo PHP_EOL;

        echo $str . PHP_EOL;

        for($i = 0; $i < $extra; $i++)
            echo PHP_EOL;
    }

    /**
     * Set inotify watchers for every directory listed
     * and compiles given files when proper event fire.
     * Will block the terminal since listen_changes() is blocking.
     * See listen_changes() docs for  further info.
     */
    public function watch(){


        $files_info = $this->files_are_valid();
        if( $files_info === true  ){
            $this->line("initializing watching of project " , 1, 2 );

            $this->set_watchers();

            $this->listen_changes();
        }
        else{
            echo PHP_EOL . ( $files_info ) . PHP_EOL ;
            exit(0);
        }
    }

    /**
     * Checks that all .less files to be compiled actually exists.
     * @return multi boolean or error message
     */
    private function files_are_valid(){

        foreach($this->files_to_compile_ever as $compile_unit )
            foreach($compile_unit as $less_file => $css_file )
                if(! is_file($less_file))
                    return $less_file ." is not a currently existing file. Are you sure the config file paths are OK? Program will terminate with no further actions.";


        foreach( $this->files_to_compile_on_save as $compile_unit )
            foreach( $compile_unit as $less_file => $css_file)
                if(! is_file($less_file))
                    return $less_file ." is not an existing file. Are you sure the config file paths are OK? Program will terminate with no further actions.";

        return true;
    }


    /**
     * set inotify watchers for every directories
     */
    private function set_watchers(){

        foreach($this->watching_directories as $directory ){

            $this->line("watching directory " . $directory , 2);
            inotify_add_watch($this->inotify , $directory , IN_MODIFY);

        }//end each

    }


    //   ------BLOCKING!!!------ //

    /**
     * Listen for inotify events and process them.
     * This is an eternal loop, so will BLOCK the terminal from where is executed.
     * If you are running this in a webserver, you want
     * to get this in a detached proccess like in Screen: https://help.ubuntu.com/community/Screen
     */
    private function listen_changes(){

        while($event = inotify_read($this->inotify) ){


            $now  = time();

            //time check comes in handy to prevent undesired triggers.
            if($now > ($this->time + 2 )){
                $this->process( $event );
                $this->time = $now;
            }
        }

    }


    /**
     * Process an inotify event and compiles less files
     * based upon configuration
     * @param  inotify_event $event
     */
    private function process( $event ){

            $name = $event[0]['name'];
            if($this->ends_with($name , '.less')){

                $this->compile_mandatory_files();

                $this->compile_saved_file($name);


            }//end if file changed is a less file

    }//end process


    /**
     * Compiles all the less files wich must compile on every event notification
     */
    private function compile_mandatory_files(){

        foreach($this->files_to_compile_ever as $less_compile_unit){

            foreach($less_compile_unit as $less_file => $css_file){

                $this->line("compiling " . $less_file . " into " . $css_file );
                exec( "lessc " . $this->minify_option  . $less_file . " " . $css_file);
            }
        }//end each

    }//end function


    /**
     * If provided file is a key within $this->files_to_compile_on_save,
     * it will be compiled to its css pair.
     * @param  [string] $name the name in a notified event
     */
    private function compile_saved_file($name){


        foreach($this->files_to_compile_on_save as $less_compile_unit){

            foreach($less_compile_unit as $less_file => $css_file){
                //if the event equals the name of the file:
                if( $name == $this->get_last_segment($less_file) ){
                    $this->line("An event on save fired: " . $name );

                    $this->line("compiling: " . $less_file . " into " . $css_file );
                    exec( "lessc " . $this->minify_option . $less_file . " > " . $css_file );
                }
            }
        }//end each files on save


    }


    /**
     * returns wether $haystack ends with $needle
     * @param  string $haystack
     * @param  string $needle
     * @return boolean
     */
    private function ends_with($haystack, $needle) {
        // search forward starting from end minus needle length characters
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
    }


    /**
     * returns the last segment of an uri
     * @param  string $str
     * @return string
     */
    private function get_last_segment( $str ){

        return substr( $str , strrpos($str , "/" )+1 , strlen($str )  );
    }

}//end class File_Watcher


$config = include('config.php');

include('parseargs.php');

$args = parse_arguments($argv);



$project = $args['project'];

$verbose = isset($args['verbose']) ?  $args['verbose'] : true;

if ($verbose === 'false' )
    $verbose = false;

$minify = isset( $args['minify'] ) ? $args['minify'] : false;


if(! isset( $config[$project] )){
    echo"A project passed by command line is not recorded in the script. Add the project or change the parameter" ;
    exit(0);
}


$file_watcher = new Less_Watcher($config[$project] , $minify,  $verbose );

$file_watcher->watch();


 ?>
