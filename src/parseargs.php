<?php

/**
* scirpt to parse php cli arguments as named ones:
*
*use:
* php somescript -name foo -class bar
*
* single arguments can be passed to:
*
* php somescript --safe --minify --name foo.txt
*
*@read be careful changing 'if(strpos( $value , "-" )  !== false  ){' for something like "if(strpos( $value , "-" ) )" or similar. Since strpos returns both boolean or number, such comparisons must be strictly executed.
*/

function parse_arguments( $arguments ){

$parsed_args = array();
$current_key = "";

foreach( $arguments as $key => $value){

    //0 is the program that is beign executed
    if ($key == 0)
        continue;

    if($current_key){
        //we are processing a key, while current $value is a key parameter too. We store previous key as true and continue parsing.
        if(strpos( $value , '-' )  === 0  ){
            $parsed_args[ltrim($current_key , '-')] = true;

            //we store value too in case is the last argument. 
            $parsed_args[ltrim($value , '-')] = true;
            $current_key = $value;
        }
        //we had a valid param key and find a std value. We store its value in the result and set $current_key as false, so next iteration nows.
        else {
            $parsed_args[ltrim($current_key , '-')] = $value;
            $current_key = false;
        }

    }
    else{
        if(strpos( $value , "-" )  === false  )
            throw new Exception(PHP_EOL . "you must prepend argument keys with - or --" . PHP_EOL );

        $current_key = $value;
        //we must save it just in case is the last paremeter. If a valid value is find after, it will be just rewrited
        $parsed_args[ltrim($value , '-')] = true;

    }

}//end foreach $argv

return $parsed_args;

}

?>
