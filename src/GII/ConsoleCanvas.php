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
    private $color;

    public const COLOR_BLACK = "0;30";
    public const COLOR_RED = "0;31";
    public const COLOR_GREEN = "0;32";
    public const COLOR_BROWN = "0;33";
    public const COLOR_BLUE = "0;34";
    public const COLOR_MAGENTA = "0;35";
    public const COLOR_CYAN = "0;36";
    public const COLOR_GREY = "0;37";

    private const CLR_SCREEN = "\033[2J";

    private const CHARS = [
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

    function __construct($x = null, $y = null)
    {
        if ($x == null) {
            $this->cols = exec("tput cols");
            if (intval($this->cols) < 1) {
                $this->cols = 80;
            }
        } else {
            $this->cols = $x;
        }

        if ($y == null) {
            $this->rows = exec("tput lines");

            if (intval($this->rows) < 1) {
                $this->rows = 24;
            }
        } else {
            $this->rows = $y;
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
        $char = self::CHARS[$this->canvas[$xr][$yr]];
        echo "\e[{$yr};{$xr}f$char";
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
        echo self::CLR_SCREEN;
    }

    /**
     * Set drawing color
     *
     * @param $color string containing any predefined `COLOR_` constants
     */
    public function setColor($color) {
        $this->color = $color;
        echo "\e[{$this->color}m";
    }

    /**
     * Restore default foreground color
     */
    public function clearColor() {
        $this->color = null;
        echo "\e[0m";
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
        echo "\e[{$yr};{$xr}f";
    }
}
