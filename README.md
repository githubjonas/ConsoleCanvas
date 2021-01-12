# ConsoleCanvas
ConsoleCanvas emulates a canvas which makes it possible to "draw" on any console terminal.

[Basic shapes](https://github.com/githubjonas/ConsoleCanvas/blob/master/src/examples/basic.php)

![Basic Example](https://github.com/githubjonas/ConsoleCanvas/blob/master/doc/console-example-basic.png)

[3D Cube](https://github.com/githubjonas/ConsoleCanvas/blob/master/src/examples/3dcube.php)

![3D Cube Example](https://github.com/githubjonas/ConsoleCanvas/blob/master/doc/3dcube-2.gif)

The **-\*r A g E\*-** Python Console demo is written using the `ConsoleCanvas` python class.
Here's the demo running !

[![Rage Console 2019](https://img.youtube.com/vi/NrXqxxfYeSk/0.jpg)](https://www.youtube.com/watch?v=NrXqxxfYeSk)

## PHP Installation
composer.json

    "require": {
        "gii/consolecanvas": "dev-master"
    },
    "repositories": [
        {
            "type": "vcs",
            "url":  "https://github.com/githubjonas/ConsoleCanvas"
        }
    ]

## PHP Usage
    use GII\ConsoleCanvas;
    
    // Instance a new canvas
    $canvas = new ConsoleCanvas();
    
    // Clears the screen
    $canvas->clear();
    
    // Sets pen color to beautiful magenta
    $canvas->setColor(ConsoleCanvas::COLOR_MAGENTA);
    
    // Draws a circle with radius 5 pixels at position x=10 and y=10 and with
    // aspect ratio set to 2, which usually gives a quite round circle, depending
    // on terminal.
    $canvas->circle(10, 10, 5, 2);
    
    
    // Draws a line from point x=50, y=5 to point x=10, y=10
    $canvas->line(50, 5, 10, 10);
    
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
    
## Python usage
    from GII.ConsoleCanvas import ConsoleCanvas
    
    # Instance
    canvas = ConsoleCanvas()
    
    # Clear screen
    canvas.clear()
    
    # Create a 'ball' object
    ball = ConsoleCanvas(20, 10, False)
    
    # Set color on ball and draw it
    ball.setColor(canvas.COLOR_GREEN)
    ball.circle(10, 5, 5, 2)
    
    # copy two balls to main canvas
    canvas.blit(5, 5, ball)
    canvas.blit(25, 25, ball)
    
    
## Usage
#### Construction
|argument|type|default    |description    |
|--------|----|-----------|---------------|
|`cols`    |`int` |system width|number of terminal columns|
|`rows`    |`int` |system height|number of terminal rows|
|`output`  |`bool`|`true`|if set to false, canvas is not echoed|

#### Constants
##### Colors
|name|description    |
|-----|--------------|
|`COLOR_BLACK`       |Black color|
|`COLOR_RED`         |Red color|
|`COLOR_GREEN`       |Green color|
|`COLOR_BROWN`       |Brown color|
|`COLOR_BLUE`        |Blue color|
|`COLOR_MAGENTA`     |Magenta color|
|`COLOR_CYAN`        |Cyan color|
|`COLOR_GREY`        |Grey color|
|`COLOR_WHITE`       |White color (caution! not really "white" but terminal default foreground color)|

#### Methods
#### blit(x, y, canvas, options)
Copy [a part of] one canvas onto another.

|argument|type|default    |description    |
|--------|----|-----------|---------------|
|`x` |`int`|       |horizontal start position|
|`y` |`int`|       |vertical start position|
|`canvas` |`ConsoleCanvas`|       |object to copy into this|
|`options` |`dict/array`|`{}`       |options|

###### options

|argument|type|default    |description    |
|--------|----|-----------|---------------|
|`width` |`int`|canvas width       |horizontal start position|
|`height` |`int`|canvas height       |vertical start position|
|`xOffset` |`int`|`0`       |x offset on source object|
|`yOffset` |`int`|`0`       |y offset on source object|
|`method` |`BINARY FIELD`|`BLIT_DEFAULT`       |Add `BLIT_SAFE` to safe copy (slower). Add `BLIT_MERGE` to merge data into destination. |

###### return
`void`

#### clear()
Clears the screen.
###### return
`void`

#### clearColor()
Clears pen color back to terminal default foreground color.
###### return
`void`

#### clone()
Clone canvas.
###### return
`ConsoleCanvas` clone

#### height()
Get height of canvas.
###### return
`int` Height of canvas in pixels

#### fillRect(x1, y1, x2, y2, x3, y3, x4, y4)
NOTE! EXPERIMENTAL, PHP ONLY.
Draws a filled rectangle.

|argument|type|default    |description    |
|--------|----|-----------|---------------|
|`x1` |`int`|       |upper left horizontal position|
|`y1` |`int`|       |upper left vertical position|
|`x2` |`int`|       |upper right horizontal position|
|`y2` |`int`|       |upper right vertical position|
|`x3` |`int`|       |lower right horizontal position|
|`y3` |`int`|       |lower right vertical position|
|`x4` |`int`|       |lower left horizontal position|
|`y4` |`int`|       |lower left vertical position|

###### return
`void`

#### line(x1, y1, x2, y2)
Draw a line from one point to another point on the canvas.

|argument|type|default    |description    |
|--------|----|-----------|---------------|
|`x1` |`int`|       |horizontal start position|
|`y1` |`int`|       |vertical start position|
|`x2` |`int`|       |horizontal end position|
|`y2` |`int`|       |vertical end position|

###### return
`void`

#### setColor(color)
Sets pen color to selected color.

|argument|type|default    |description    |
|--------|----|-----------|---------------|
|`color` |`string`|       |must be one of the predefined color constants|

###### return
`void`

#### width()
Get width of canvas
###### return
`int` Width of canvas in pixels
