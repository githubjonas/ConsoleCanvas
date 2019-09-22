<?php

namespace GII;

/**
 * Class ConsoleCanvas emulates a canvas which makes it possible to "draw" on any console terminal.
 *
 * The canvas will have a pixel resolution double the size of the terminal window, so for example a
 * standard 80x24 character terminal window, will have 160x48 pixels canvas available.
 *
 * @package GII
 */
class ConsoleCanvas
{
    private $canvas;
    private $color = null;
    private $output = true;
    private $cols;
    private $rows;
    private $cursorX = -1;
    private $cursorY = -1;
    private static $fonts = [];

    public const COLOR_BLACK = "0;30";
    public const COLOR_RED = "0;31";
    public const COLOR_GREEN = "0;32";
    public const COLOR_BROWN = "0;33";
    public const COLOR_BLUE = "0;34";
    public const COLOR_MAGENTA = "0;35";
    public const COLOR_CYAN = "0;36";
    public const COLOR_GREY = "0;37";
    public const COLOR_WHITE = "0";

    private const COLOR_MAP = [
        self::COLOR_BLACK =>    1,
        self::COLOR_RED =>      2,
        self::COLOR_GREEN =>    3,
        self::COLOR_BROWN =>    4,
        self::COLOR_BLUE =>     5,
        self::COLOR_MAGENTA =>  6,
        self::COLOR_CYAN =>     7,
        self::COLOR_GREY =>     8,
        self::COLOR_WHITE =>    9,
    ];

    public const TEXT_MODE_LEFT = 0;
    public const TEXT_MODE_CENTER = 1;

    public const BLIT_DEFAULT = 0;
    public const BLIT_MERGE = 1;
    public const BLIT_SAFE = 2;

    private const CLR_SCREEN = "\033[2J";

    private const CHARS = [
        0b0000 => " ",
        0b0001 => "▗",
        0b0010 => "▖",
        0b0011 => "▄",
        0b0100 => "▝",
        0b0101 => "▐",
        0b0110 => "▞",
        0b0111 => "▟",
        0b1000 => "▘",
        0b1001 => "▚",
        0b1010 => "▌",
        0b1011 => "▙",
        0b1100 => "▀",
        0b1101 => "▜",
        0b1110 => "▛",
        0b1111 => "█"
    ];

    function __construct($cols = null, $rows = null, $output = true)
    {
        $this->output = $output;

        if ($cols == null) {
            $this->cols = exec("tput cols");
            if (intval($this->cols) < 1) {
                $this->cols = 80;
            }
        } else {
            $this->cols = $cols;
        }

        if ($rows == null) {
            $this->rows = exec("tput lines");

            if (intval($this->rows) < 1) {
                $this->rows = 24;
            }
        } else {
            $this->rows = $rows;
        }

        $this->initCanvas();
    }

    /**
     * Init canvas array
     */
    private function initCanvas() {
        $this->canvas = [];
        for ($y = 0; $y < $this->rows * 2; $y++) {
            for ($x = 0; $x < $this->cols * 2; $x++) {
                if (!isset($this->canvas[$x])) {
                    $this->canvas[$x] = [];
                }
                $this->canvas[$x][$y] = 0;
            }
        }
    }

    /**
     * Fetch from canvas array and render correct `CHAR` on the position
     *
     * @param $xr int horizontal console position (pixel / 2)
     * @param $yr int vertical console position (pixel / 2)
     */
    private function renderPos($xr, $yr) {
        $char = self::CHARS[$this->canvas[$xr][$yr] & 0b1111];
        if ($this->output) {
            $colorVal = ($this->canvas[$xr][$yr] & 0b11110000) >> 4;
            if ($colorVal > 0) {
                $color = array_search($colorVal, self::COLOR_MAP);
                if ($color !== false) {
                    $this->setColor($color);
                }
            }

            if ($this->cursorX == $xr && $this->cursorY == $yr) {
                echo $char;
            } else {
                echo "\e[{$yr};{$xr}f$char";
            }
            $this->cursorX = $xr + 1;
            $this->cursorY = $yr;
        }
    }

    /**
     * @param Point[] $v
     * @param Point $p
     * @return bool
     */
    private function isInside(array $v, Point $p) : bool {
        $wn = 0;    // the  winding number counter
        // loop through all edges of the polygon
        for ($i = 0; $i < sizeof($v) - 1; $i++) {   // edge from V[i] to  V[i+1]
            if ($v[$i]->y <= $p->y) {          // start y <= P.y
                if ($v[$i + 1]->y > $p->y)      // an upward crossing
                    if ($this->isLeft( $v[$i], $v[$i + 1], $p) > 0)  // P left of  edge
                        ++$wn;            // have  a valid up intersect
            }
            else {                        // start y > P.y (no test needed)
                if ($v[$i + 1]->y <= $p->y)     // a downward crossing
                    if ($this->isLeft( $v[$i], $v[$i + 1], $p) < 0)  // P right of  edge
                        --$wn;            // have  a valid down intersect
            }
        }
        return $wn;
    }

    /**
     * @param Point $p0
     * @param Point $p1
     * @param Point $p2
     * @return float|int
     */
    private function isLeft(Point $p0, Point $p1, Point $p2) {
        return ( ($p1->x - $p0->x) * ($p2->y - $p0->y)
            - ($p2->x -  $p0->x) * ($p1->y - $p0->y) );
    }

    /**
     * Load font 1-5 from font dir
     * @param $size
     * @return mixed
     */
    private function getFont($size) {
        if (empty(self::$fonts[$size])) {
            self::$fonts[$size] = json_decode(file_get_contents(__DIR__ . "/../../font/font_$size.json"), true);
        }
        return self::$fonts[$size];
    }

    /**
     * Used to dump font data to /font/ json files
     * @param $size
     */
    private function dumpFromImageFont($size) {
        $maxally = 0;
        for ($i = 33; $i < 256; $i++) {

            $text = chr($i);
            $im = imagecreate(100, 30);
            imagecolorallocate($im, 0, 0, 0);
            $textColor = imagecolorallocate($im, 0, 255, 255);
            imagestring($im, $size, 0, 0, $text, $textColor);
            $maxx = 0;
            $maxy = 0;
            $plotArray = [];
            for ($ix = 0; $ix < 100; $ix++) {
                for ($iy = 0; $iy < 30; $iy++) {
                    $pix = imagecolorat($im, $ix, $iy);
                    if ($pix != 0) {
                        $plotArray[] = new Point($ix, $iy);
                        $maxx = max($maxx, $ix);
                        $maxy = max($maxy, $iy);
                    }
                }
            }
            imagedestroy($im);

            if ($maxx > 0 || $maxy > 0) {
                /** @var Point $point */
                echo "\"$i\":{\"width\":$maxx,\"data\":\"";
                foreach ($plotArray as $point) {
                    echo sprintf('%02x', $point->x << 4 | $point->y);
                }
                echo "\"},\n";
                if ($maxally < $maxy) $maxally = $maxy;
            }
        }
        echo "Vertical height: $maxally\n";
    }

    /**
     * Get pixel width of canvas
     *
     * @return float|int
     */
    public function width() {
        return $this->cols * 2;
    }

    /**
     * Get pixel height of canvas
     *
     * @return float|int
     */
    public function height() {
        return $this->rows * 2;
    }

    /**
     * Clear canvas
     */
    public function clear() {
        $this->initCanvas();
        if ($this->output) echo self::CLR_SCREEN;
        $this->cursorX = 0;
        $this->cursorY = 0;
    }

    /**
     * Set drawing color
     *
     * @param $color string containing any predefined `COLOR_` constants
     */
    public function setColor($color) {
        if ($this->output) echo "\e[{$color}m"; //TODO: Optimize with: && $this->color != $color
        $this->color = $color;
    }

    /**
     * Restore default foreground color
     */
    public function clearColor() {
        $this->color = null;
        if ($this->output) echo "\e[0m";
    }

    /**
     * Draw a line on the canvas
     *
     * @param $x1 int horizontal start position
     * @param $y1 int vertical start position
     * @param $x2 int horizontal end position
     * @param $y2 int vertical end position
     */
    public function line($x1, $y1, $x2, $y2) {
        $xd = abs($x2 - $x1);
        $yd = abs($y2 - $y1);
        $d = ceil(sqrt($xd * $xd + $yd * $yd));
        if ($d == 0) {
            return;
        }
        $xstep = ($x2 - $x1) / $d;
        $ystep = ($y2 - $y1) / $d;
        $cx = $x1;
        $cy = $y1;
        for ($i = 0; $i < $d; $i++) {
            $this->plot($cx, $cy);
            $cx += $xstep;
            $cy += $ystep;
        }
        $this->plot($cx, $cy);
    }

    /**
     * Draw a filled rectangle on the canvas
     * NOTE! WIP, points must be ordered: top left, top right, bottom right, bottom left
     * @param $x1
     * @param $y1
     * @param $x2
     * @param $y2
     * @param $x3
     * @param $y3
     * @param $x4
     * @param $y4
     */
    public function fillRect($x1, $y1, $x2, $y2, $x3, $y3, $x4, $y4) {
        $irTop = max($y1, $y2) + 1;
        $irLeft = max($x1, $x4) + 1;
        $irBottom = min($y3, $y4) - 1;
        $irRight = min($x2, $x3) - 1;

        if ($irTop % 2 != 0) $irTop += 1;
        if ($irLeft % 2 != 0) $irLeft += 1;
        if ($irBottom % 2 == 0) $irBottom -= 1;
        if ($irRight % 2 == 0) $irRight -= 1;

        for ($xr = intval($irLeft / 2); $xr < $irRight / 2; $xr++) {
            for ($yr = intval($irTop / 2); $yr < $irBottom / 2; $yr++) {
                $this->canvas[$xr][$yr] = 0b1111;
                if ($this->color != null) {
                    $this->canvas[$xr][$yr] |= self::COLOR_MAP[$this->color] << 4;
                }
                $this->renderPos($xr, $yr);
            }
        }

        $xmin = min($x1, $x4);
        $ymin = min($y1, $y2);
        $xmax = max($x2, $x3);
        $ymax = max($y3, $y4);

        $outerbox = [
            new Point($x1, $y1),
            new Point($x2, $y2),
            new Point($x3, $y3),
            new Point($x4, $y4),
            new Point($x1, $y1)
        ];

        $innerbox = [
            new Point($irLeft, $irTop),
            new Point($irRight, $irTop),
            new Point($irRight, $irBottom),
            new Point($irLeft, $irBottom),
            new Point($irLeft, $irTop)
        ];

        for ($x = $xmin; $x < $xmax; $x++) {
            for ($y = $ymin; $y < $ymax; $y++) {
                if ($this->isInside($outerbox, new Point($x, $y)) && !$this->isInside($innerbox, new Point($x, $y))) {
                    $this->plot($x, $y);
                }
            }
        }
    }

    /**
     * Copy one [part of] a canvas onto this canvas
     * @param int $x Upper left corner x pos of where to copy to
     * @param int $y Upper left corner y pos of where to copy to
     * @param ConsoleCanvas $canvas Copy from
     * @param array $options
     *                  method
     *                  xOffset
     *                  yOffset
     *                  width
     *                  height
     */
    public function blit($x, $y, ConsoleCanvas $canvas, $options = []) {

        $method = $options["method"] ?? self::BLIT_DEFAULT;
        $xOffset = $options["xOffset"] ?? 0;
        $yOffset = $options["yOffset"] ?? 0;
        $width = intval(($options["width"] ?? $canvas->width()) / 2);
        $height = intval(($options["height"] ?? $canvas->height()) / 2);

        $xt = intval($x / 2);
        $yt = intval($y / 2);

        $canvasData = $canvas->canvas;

        if ($method & self::BLIT_SAFE > 0) {
            $interCanvas = new ConsoleCanvas($width, $height, false);
            $interCanvas->blit(0, 0, $canvas, [
                "xOffset" => $xOffset,
                "yOffset" => $yOffset,
                "width" => $width * 2,
                "height" => $height * 2,
                "method" => $method & ~self::BLIT_SAFE
            ]);
            $canvasData = $interCanvas->getCanvasData();
        }

        for ($xp = 0; $xp < $width; $xp++) {
            for ($yp = 0; $yp < $height; $yp++) {
                if ($method == self::BLIT_MERGE) {
                    $this->canvas[$xt + $xp][$yt + $yp] = ($this->canvas[$xt + $xp][$yt + $yp] & 0b1111) | $canvasData[$xp + $xOffset][$yp + $yOffset] ?? 0;
                } else {
                    $this->canvas[$xt + $xp][$yt + $yp] = $canvasData[$xp + $xOffset][$yp + $yOffset] ?? 0;
                }
                $this->renderPos($xt + $xp, $yt + $yp);
            }
        }
    }

    public function clone() : ConsoleCanvas {
        $clone = new ConsoleCanvas($this->cols, $this->rows, false);
        $clone->setCanvasData($this->getCanvasData());

        return $clone;
    }

    /**
     * @return mixed
     */
    protected function getCanvasData() {
        return $this->canvas;
    }

    /**
     * @param $canvas
     */
    protected function setCanvasData($canvas) {
        $this->canvas = $canvas;
    }

    public function render() {
        $this->output = true;
        for ($yr = 0; $yr < $this->rows; $yr++) {
            for ($xr = 0; $xr < $this->cols; $xr++) {
                $this->renderPos($xr, $yr);
            }
        }
    }

    /**
     * @param $x
     * @param $y
     * @param $text
     * @param int $size
     * @param int $mode
     * @return int|mixed
     */
    public function text($x, $y, $text, $size = 5, $mode = self::TEXT_MODE_LEFT) {
        $width = 0;
        $plotArray = [];

        $font = $this->getFont($size);

        for ($i = 0; $i < strlen(utf8_decode($text)); $i++) {
            $ascii = ord(substr(utf8_decode($text), $i, 1));

            if (!empty($font[$ascii])) {
                for ($j = 0; $j < strlen($font[$ascii]["data"]); $j += 2) {
                    $chr = hexdec(substr($font[$ascii]["data"], $j, 2));
                    $ix = ($chr >> 4) & 0xf;
                    $iy = ($chr) & 0xf;
                    $plotArray[] = new Point($width + $ix + ($mode == self::TEXT_MODE_CENTER ? 0 : $x), $y + $iy);
                }
                $width += $font[$ascii]["width"] + 2;
            }
        }

        $offset = $mode == self::TEXT_MODE_CENTER ? $x - $width / 2 : 0;
        foreach ($plotArray as $point) {
            $this->plot($point->x + $offset, $point->y);
        }

        return $width - 2;
    }

    /**
     * Fetch from canvas array and render correct `CHAR` on the position
     *
     * @param $xr int horizontal console position (pixel / 2)
     * @param $yr int vertical console position (pixel / 2)
     */
    public function writeString($xr, $yr, $string) {
        if ($this->output) {
            $this->moveCursor($xr, $yr);
            echo $string;
        }
    }

    /**
     * Draw a circle on the canvas
     *
     * @param $x int horizontal center position
     * @param $y int vertical center position
     * @param $r int circle radius in pixels
     * @param float $aspect default 1.0, adjust aspect ratio. Due to regular terminal font, aspect ratio of about 2.0
     *                      will produce a approximately round circle.
     */
    public function circle($x, $y, $r, $aspect = 1.0) {
        for ($i = 0; $i < 2*pi(); $i += 1 / ($r * $aspect)) {
            $this->plot($x + sin($i) * $r * $aspect, $y + cos($i) * $r);
        }
    }

    public function getPoint($x, $y) {
        $val = 1;
        if ($y % 2 == 0) {
            $val = $val << 2;
        }
        if ($x % 2 == 0) {
            $val = $val << 1;
        }

        $yr = intval($y / 2);
        $xr = intval($x / 2);

        $data = $this->canvas[$xr][$yr] & $val ?? 0;

        return $data > 0;
    }

    /**
     * Plot one pixel
     *
     * @param $x int horizontal position
     * @param $y int vertial position
     */
    public function plot($x, $y) {
        if ($x < 0 || $y < 0 || $x > $this->cols * 2 || $y > $this->rows * 2) {
            return;
        }

        $val = 1;
        if ($y % 2 == 0) {
            $val = $val << 2;
        }
        if ($x % 2 == 0) {
            $val = $val << 1;
        }

        $yr = intval($y / 2);
        $xr = intval($x / 2);

        $this->canvas[$xr][$yr] |= $val;

        if ($this->color != null) {
            $this->canvas[$xr][$yr] = ($this->canvas[$xr][$yr] & 0b1111) | (self::COLOR_MAP[$this->color] << 4);
        }

        $this->renderPos($xr, $yr);
    }

    /**
     * Move console cursor to position. Can be used to position further output somewhere when canvas render is complete.
     *
     * @param $x int horizontal (pixel) position
     * @param $y int vertical (pixel) position
     */
    public function moveCursor($x, $y) {
        $yr = intval($y / 2);
        $xr = intval($x / 2);
        if ($this->output) {
            echo "\e[{$yr};{$xr}f";
            $this->cursorX = $xr;
            $this->cursorY = $yr;
        }
    }
}

/**
 * Class Point
 * @package GII
 */
class Point {
    public $x;
    public $y;

    public function __construct($x, $y)
    {
        $this->x = intval($x);
        $this->y = intval($y);
    }
}
