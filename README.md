# ConsoleCanvas
ConsoleCanvas PHP class emulates a canvas which makes it possible to "draw" on any console terminal.

### Example: basic drawing

    $canvas = new ConsoleCanvas();

    $w = $canvas->width();
    $h = $canvas->height();

    $canvas->clear();
    $canvas->setColor(ConsoleCanvas::COLOR_RED);
    $canvas->line(40, 10, 10, 30);
    $canvas->setColor(ConsoleCanvas::COLOR_GREEN);
    $canvas->line(10, 30, 50, 40);
    $canvas->setColor(ConsoleCanvas::COLOR_BLUE);
    $canvas->line(50, 40, 40, 10);

    $canvas->setColor(ConsoleCanvas::COLOR_CYAN);
    $canvas->circle(80, 20, 15, 2);

    $canvas->clearColor();
    $canvas->moveCursor(0, $h - 1);
    
[Basic shapes](https://github.com/githubjonas/ConsoleCanvas/blob/master/src/examples/basic.php)

![Basic Example](https://github.com/githubjonas/ConsoleCanvas/blob/master/doc/console-example-basic.png)

### Example: animated canvas
[3D Cube](https://github.com/githubjonas/ConsoleCanvas/blob/master/src/examples/3dcube.php)

![3D Cube Example](https://github.com/githubjonas/ConsoleCanvas/blob/master/doc/3dcube-2.gif)
