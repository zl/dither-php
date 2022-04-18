<?php

namespace Dither;

class Dither
{
    protected $palette;

    /**
     * @param array $palette
     */
    public function __construct($palette = [])
    {
        $this->setPalette($palette);
    }

    /**
     * @param resource $image
     * @param int $width
     * @param int $height
     * @return resource
     */
    public function process($image, $width, $height)
    {
        $palette = $this->getPalette();

        $output = imagecreatetruecolor($width, $height);
        imagecopyresized($output, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image));

        $x = $y = $indexA = $indexB = 0;

        $codes = [];
        for ($x = 0; $x < 2; $x++) {
            for ($y = 0; $y < ($width * 3); $y++) {
                $codes[$x][$y] = 0;
            }
        }

        for ($y = 0; $y < $height; $y++) {
            $indexA = (($indexB = $indexA) + 1) & 1;
            for ($x = 0; $x < ($width * 3); $x++) {
                $codes[$indexB][$x] = 0;
            }
            for ($x = 0; $x < $width; $x++) {
                $rgb = imagecolorat($output, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                $r += $codes[$indexA][$x * 3];
                $g += $codes[$indexA][$x * 3 + 1];
                $b += $codes[$indexA][$x * 3 + 2];

                $color = $palette[$this->getColorNear($r, $g, $b, $palette)];
                imagesetpixel($output, $x, $y, ($color & 0xFFFFFF));

                $r -= (($color & 0xFF0000) >> 16);
                $g -= (($color & 0x00FF00) >> 8);
                $b -= ($color & 0x0000FF);

                if ($x == 0) {
                    $this->setColorPlus($codes[$indexB], ($x), $r, $g, $b, 7);
                    $this->setColorPlus($codes[$indexB], ($x + 1), $r, $g, $b, 2);
                    $this->setColorPlus($codes[$indexA], ($x + 1), $r, $g, $b, 7);
                } else if ($x == $width - 1) {
                    $this->setColorPlus($codes[$indexB], ($x - 1), $r, $g, $b, 7);
                    $this->setColorPlus($codes[$indexB], ($x), $r, $g, $b, 9);
                } else {
                    $this->setColorPlus($codes[$indexB], ($x - 1), $r, $g, $b, 3);
                    $this->setColorPlus($codes[$indexB], ($x), $r, $g, $b, 5);
                    $this->setColorPlus($codes[$indexB], ($x + 1), $r, $g, $b, 1);
                    $this->setColorPlus($codes[$indexA], ($x + 1), $r, $g, $b, 7);
                }
            }
        }

        return $output;
    }

    /**
     * @param int $r red
     * @param int $g green
     * @param int $b blue
     * @param array $palette
     * @return int
     */
    private function getColorNear($r, $g, $b, $palette)
    {
        $index = 0;
        $default = $this->getColorCode($r, $g, $b, $palette[0]);
        for ($i = 0; $i < count($palette); $i++) {
            $current = $this->getColorCode($r, $g, $b, $palette[$i]);
            if ($current < $default) {
                $default = $current;
                $index = $i;
            }
        }
        return $index;
    }

    /**
     * @param int $r red
     * @param int $g green
     * @param int $b blue
     * @param int $color
     * @return int
     */
    private function getColorCode($r, $g, $b, $color)
    {
        $r -= (($color & 0xFF0000) >> 16);
        $g -= (($color & 0x00FF00) >> 8);
        $b -= ($color & 0x0000FF);
        return $r * $r + $g * $g + $b * $b;
    }

    /**
     * @param array $c color
     * @param int $i index
     * @param int $r red
     * @param int $g green
     * @param int $b blue
     * @param int $k
     */
    private function setColorPlus(&$c, $i, $r, $g, $b, $k)
    {
        $index = $i * 3;
        $c[$index] = ($r * $k) / 16 + $c[$index];
        $c[$index + 1] = ($g * $k) / 16 + $c[$index + 1];
        $c[$index + 2] = ($b * $k) / 16 + $c[$index + 2];
    }

    /**
     * @param string $color
     * @return int
     */
    private function rgb2hex($color)
    {
        if ($color[0] == '#') $color = substr($color, 1);
        if (strlen($color) == 3) {
            $color = $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
        }

        return hexdec($color);
    }

    /**
     * @param array $palette
     */
    public function setPalette($palette)
    {
        if (!is_array($palette)) {
            $palette = [$palette];
        }

        $palette = array_map([$this, 'rgb2hex'], $palette);

        $this->palette = $palette;
    }

    /**
     * @return array
     */
    private function getPalette()
    {
        return $this->palette;
    }
}