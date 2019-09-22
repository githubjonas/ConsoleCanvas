<?php

use GII\ConsoleCanvas;

require __DIR__ . '/../../vendor/autoload.php';

runs();

function runs() {
    $canvas = new ConsoleCanvas();

    $canvas->clearColor();

    $w = $canvas->width();
    $h = $canvas->height();

    $nodes = [[-1, -1, -1], [-1, -1, 1], [-1, 1, -1], [-1, 1, 1],
        [1, -1, -1], [1, -1, 1], [1, 1, -1], [1, 1, 1]];
    $edges = [[0, 1], [1, 3], [3, 2], [2, 0], [4, 5], [5, 7], [7, 6],
        [6, 4], [0, 4], [1, 5], [2, 6], [3, 7]];

    function scale($nodes, $factor0, $factor1, $distance) {
        $scaledNodes = [];
        foreach ($nodes as $node) {
            $scaledNodes[] = [
                0 => $node[0] * $factor0 / ($node[2] + $distance),
                1 => $node[1] * $factor1 / ($node[2] + $distance),
                //2 => $node[2] * $factor2
            ];
        }
        return $scaledNodes;
    }

    function rotateX($node, $angle) {
        $sin = sin($angle);
        $cos = cos($angle);
        $node[1] = $node[1] * $cos - $node[2] * $sin;
        $node[2] = $node[2] * $cos + $node[1] * $sin;
        return $node;
    }
    function rotateY($node, $angle) {
        $sin = sin($angle);
        $cos = cos($angle);
        $node[0] = $node[0] * $cos - $node[2] * $sin;
        $node[2] = $node[2] * $cos + $node[1] * $sin;
        return $node;
    }
    function rotateZ($node, $angle) {
        $sin = sin($angle);
        $cos = cos($angle);
        $node[0] = $node[0] * $cos - $node[1] * $sin;
        $node[1] = $node[1] * $cos + $node[0] * $sin;
        return $node;
    }

    function rotate0($nodes, $angleX, $angleY, $angleZ) {
        $rotatedNodes = [];
        foreach ($nodes as $node) {
            $rotatedNodes[] = rotateZ(rotateY(rotateX($node, $angleX), $angleY), $angleZ);
        }
        return $rotatedNodes;
    }


    function rotate($nodes, $angleX, $angleY) {
        $sinX = sin($angleX);
        $cosX = cos($angleX);
        $sinY = sin($angleY);
        $cosY = cos($angleY);
        $rotatedNodes = [];
        foreach ($nodes as $node) {
            $x = $node[0];
            $y = $node[1];
            $z = $node[2];
            $z2 = $z * $cosX + $x * $sinX;
            $rotatedNodes[] = [
                0 => $x * $cosX - $z * $sinY,
                1 => $y * $cosY - $z2 * $sinY,
                2 => $z2 * $cosY + $y * $sinY
            ];
        }
        return $rotatedNodes;
    }
    $n = 0;
    while (1) {
        $canvas->clear();

        $transformedNodes = scale(rotate($nodes, $n, $n), 100, 100, 7);

        foreach ($edges as $edge) {
            $canvas->line(
                $transformedNodes[$edge[0]][0] * 1.5 + $w / 2,
                $transformedNodes[$edge[0]][1] + $h / 2,
                $transformedNodes[$edge[1]][0] * 1.5 + $w / 2,
                $transformedNodes[$edge[1]][1] + $h / 2);
        }

        $n += 0.1;

        $canvas->moveCursor(0, 0);
        usleep(50000);
    }
}
