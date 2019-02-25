<?php

header("Access-Control-Allow-Origin: *");

error_reporting(E_STRICT);
//error_reporting(E_ALL);

function startswith($init, $str) {
    return substr($str, 0, strlen($init)) == $init;
}

function solve($inst_text) {
    
    $descriptorspec = array(
        0 => array("pipe", "r"),  // STDIN ist eine Pipe, von der das Child liest
        1 => array("pipe", "w"),  // STDOUT ist eine Pipe, in die das Child schreibt
        2 => array("file", "/dev/zero", "w") // STDERR ist eine Datei,
        // in die geschrieben wird
    );
    
    $process = proc_open('clingo', $descriptorspec, $pipes, ".", array());

    if (!is_resource($process)) {
        // TODO handle error
    }

    include "lpsolver.php";
    fwrite($pipes[0], $solver);
    fwrite($pipes[0], $inst_text);
    fclose($pipes[0]);

    $answer = "";
    $read_answer = false;
    while($line = stream_get_line($pipes[1], 0, "\n")) {
        if($read_answer) {
            $answer = $line;
            $read_answer = false;
        } else if(startswith("Answer", $line)) {
            $read_answer = true;
        } else if(startswith("OPTIMUM", $line)) {
            break;
        } else if(startswith("UNSATISFIABLE", $line)) {
            break;
        }
    }
    
    fclose($pipes[1]);

    // Es ist wichtig, dass Sie alle Pipes schließen bevor Sie
    // proc_close aufrufen, um Deadlocks zu vermeiden
    proc_close($process);

    return explode(" ", $answer);
}

/*$plan = solve(file_get_contents("inst93.lp"));

foreach($plan as $move) {
    echo $move."\n";
}
*/


function read_input($json_input) {
    $cars = json_decode($json_input)->{"cars"};
    $cars_asp = array();
    foreach($cars as $car) {
        $atom = vsprintf("h(0,car(%s,%d,%d,%s,%d)).", $car);
        $cars_asp[] = $atom;
    }
    
    return $cars_asp;    
}

/*$cars_asp = read_input('{"cars":[[1,2,3,"x",4],[2,3,4,"y",5]]}');

foreach($cars_asp as $asp) {
    echo $asp."\n";
    } */

function answer_to_json($plan) {

    $moves = array();

    foreach($plan as $action) {
        preg_match("/do\((\d+),move\((.+),(\d+)\)\)/", $action, $res);
        $movenum = intval($res[1]);
        [$id, $dest] = array_slice($res,2);       
        $moves[$movenum][] = [$id, intval($dest)];
    }
    return json_encode($moves);
}

/*
$answer = explode(" ", "do(1,move(2,3)) do(2,move(red,9)) do(2,move(blue,9))");
print(answer_to_json($answer));
*/

function solve_json($json_inp) {
    return answer_to_json(solve(implode(" ", read_input($json_inp))));
}

print(solve_json($_GET['query']));

?>
