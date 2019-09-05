<?php

use GII\ConsoleCanvas;

require __DIR__ . '/../../vendor/autoload.php';

runs();

function runs() {
    $canvas = new ConsoleCanvas();

    $w = $canvas->width();
    $h = $canvas->height();


    $canvas->clear();
    $canvas->clearColor();
    $canvas->setColor(ConsoleCanvas::COLOR_RED);
    $canvas->line(40,10, 10,30);
    $canvas->setColor(ConsoleCanvas::COLOR_GREEN);
    $canvas->line(10,30, 50, 40);
    $canvas->setColor(ConsoleCanvas::COLOR_BLUE);
    $canvas->line(50, 40, 40, 10);

    $canvas->setColor(ConsoleCanvas::COLOR_CYAN);
    $canvas->circle(80, 20, 15, 2);

    $canvas->clearColor();
    $canvas->moveCursor(0, $h - 1);
}
