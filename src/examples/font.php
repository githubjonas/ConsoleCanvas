<?php

use GII\ConsoleCanvas;

require __DIR__ . '/../../vendor/autoload.php';

runs();

function runs() {
    $canvas = new ConsoleCanvas();

    $canvas->clear();
    $canvas->clearColor();

    $canvas->text(5,2,"Font type 1",1);
    $canvas->text(5,8,"Font type 2",2);
    $canvas->text(5,19,"Font type 3",3);
    $canvas->text(5,30,"Font type 4",4);
    $canvas->text(70,2,"Font type 5",5);

    $canvas->moveCursor(0,$canvas->height());
}
