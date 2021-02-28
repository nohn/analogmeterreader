<?php
/**
 * Analog Meter Reader
 *
 * Library for reading analog meters
 *
 * PHP version 7.4
 *
 * LICENCE: This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author    Sebastian Nohn <sebastian@nohn.net>
 * @copyright 2021 Sebastian Nohn
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 */

namespace nohn\AnalogMeterReader;

use Imagick;
use ImagickDraw;
use ImagickPixel;

class AnalogMeter
{
    private Imagick $inputImage;
    private string $needleColour;
    private ImagickPixel $strokeColor;
    private float $strokeOpacity = 0.7;
    private array $debugData;

    private array $stepMapping = array(
        // @formatter:off
        '1'  => array(                              '4' => 8, '5' => 7, '6' => 7, '7' => 6,                              ),
        '2'  => array(          '2' => 8, '3' => 8, '4' => 8, '5' => 7, '6' => 7, '7' => 6, '8' => 6, '9' => 6,          ),
        '3'  => array('1' => 9, '2' => 8, '3' => 8, '4' => 8,                               '8' => 6, '9' => 6, '10' => 5),
        '4'  => array('1' => 9, '2' => 9,                                                             '9' => 5, '10' => 5),
        '5'  => array('1' => 9, '2' => 9,                                                             '9' => 5, '10' => 5),
        '6'  => array('1' => 0, '2' => 0,                                                             '9' => 4, '10' => 4),
        '7'  => array('1' => 0, '2' => 0,                                                             '9' => 4, '10' => 4),
        '8'  => array(          '2' => 1, '3' => 1,                                         '8' => 3, '9' => 3,          ),
        '9'  => array(          '2' => 1, '3' => 1, '4' => 1, '5' => 2, '6' => 2, '7' => 3, '8' => 3, '9' => 3,          ),
        '10' => array(                              '4' => 2, '5' => 2, '6' => 2, '7' => 3,                              ),
        // @formatter:on
    );

    public function __construct($inputImage, string $needleColour = 'r')
    {
        if (is_a($inputImage, 'Imagick')) {
            $this->inputImage = $inputImage;
        } else {
            $this->inputImage = new Imagick($inputImage);
        }
        $this->needleColour = $needleColour;
        $this->strokeColor = new ImagickPixel('white');
    }

    public function getValue(bool $debug = false): int
    {
        return $this->processImage($debug);
    }

    public function getDebugData(): array
    {
        if (!isset($this->debugData['data'])) {
            $this->processImage(true);
        }
        return $this->debugData['data'];
    }

    public function getDebugImage(): Imagick
    {
        if (!isset($this->debugData['image'])) {
            $this->processImage(true);
        }
        return $this->debugData['image'];
    }

    /**
     * Process the image.
     *
     * (1) Split the image in sub-images.
     *     (1.a) The number of the sub-images is determined by $this->stepMapping
     * (2) For each sub-image, we determine the relative r, g and b values
     * (3) And remember, which sub-image had the highest proximity to $this->needleColour
     * (4) After iterating over all sub-images, we return the corresponding number for the most significant sub-image
     *
     * @param bool $debug
     * @return int
     */
    private function processImage(bool $debug = false): int
    {
        $workImage = clone $this->inputImage;
        // (1) Split the image in sub-images.
        $inputWidth = $workImage->getImageWidth();
        $inputHeight = $workImage->getImageHeight();

        // (1.a) The number of the sub-images is determined by $this->stepMapping
        $stepCount = count($this->stepMapping);
        // Scale to the next $stepCount, so the image can easily be divided
        $workImage->scaleImage(ceil($inputWidth/$stepCount)*$stepCount, ceil($inputHeight/$stepCount)*$stepCount);
        $workImage->setImagePage(0, 0, 0, 0);
        $meterWidth = $workImage->getImageWidth();
        $meterHeight = $workImage->getImageHeight();


        $stepWidth = (int)($meterWidth / $stepCount);
        $stepHeight = (int)($meterHeight / $stepCount);

        $currentYStep = 0;

        $stepWithHighestSignificance = array('value' => 0, 'xStep' => 0, 'yStep' => 0);

        $relativeNeedleSignificance = array('r' => 0, 'g' => 0, 'b' => 0);

        if ($debug) {
            $imageDebug = clone $workImage;
            $xDrawn = array();
            $allStepData = array();
        }

        // (1) Split the image in sub-images. (x)
        for ($y = 0; $y <= $stepCount * $meterHeight; $y += $stepHeight) {
            $currentYStep++;
            $currentXStep = 0;

            if ($debug) {
                $draw = new ImagickDraw();
                $draw->setStrokeColor($this->strokeColor);
                $draw->setStrokeOpacity($this->strokeOpacity);
                $draw->line(0, $y, $meterWidth, $y);
                $draw->setStrokeWidth(1);
                $draw->setFillOpacity(0);
                $imageDebug->drawImage($draw);
            }
            // (1) Split the image in sub-images. (y)
            for ($x = 0; $x <= $stepCount * $meterWidth; $x += $stepWidth) {
                $currentXStep++;
                if ($debug && !isset($xDrawn[$x])) {
                    $draw = new ImagickDraw();
                    $draw->setStrokeColor($this->strokeColor);
                    $draw->setStrokeOpacity($this->strokeOpacity);
                    $draw->line($x, 0, $x, $meterHeight);
                    $draw->setStrokeWidth(1);
                    $imageDebug->drawImage($draw);
                    $xDrawn[$x] = true;
                }
                // We can ignore this step? Cool.
                if (!isset($this->stepMapping[$currentXStep][$currentYStep]))
                    continue;
                // (2) For each sub-image, we determine the relative r, g and b values
                $stepImage = clone $workImage;
                $stepImage->cropImage($stepWidth, $stepHeight, $x, $y);
                $stepHistogram = $stepImage->getImageHistogram();
                $red = 0;
                $green = 0;
                $blue = 0;
                foreach ($stepHistogram as $pixel) {
                    $rgb = $pixel->getColor();
                    $red += $rgb['r'];
                    $green += $rgb['g'];
                    $blue += $rgb['b'];
                }
                $relativeNeedleSignificance['r'] = $red / ($red + $green + $blue);
                $relativeNeedleSignificance['g'] = $green / ($red + $green + $blue);
                $relativeNeedleSignificance['b'] = $blue / ($red + $green + $blue);
                if ($debug) {
                    $allStepData[(string)$relativeNeedleSignificance[$this->needleColour]] = array('xStep' => $currentXStep, 'yStep' => $currentYStep, 'number' => $this->stepMapping[$currentXStep][$currentYStep]);
                }
                // (3) And remember, which sub-image had the highest proximity to $this->needleColour
                if ($stepWithHighestSignificance['value'] <= $relativeNeedleSignificance[$this->needleColour]) {
                    $stepWithHighestSignificance['value'] = $relativeNeedleSignificance[$this->needleColour];
                    $stepWithHighestSignificance['xStep'] = $currentXStep;
                    $stepWithHighestSignificance['yStep'] = $currentYStep;
                }
            }
        }
        if ($debug) {
            krsort($allStepData);
            $this->debugData['image'] = $imageDebug;
            $this->debugData['data'] = $allStepData;
        }
        // (4) After iterating over all sub-images, we return the corresponding number for the most significant sub-image
        return $this->stepMapping[(int)$stepWithHighestSignificance['xStep']][(int)$stepWithHighestSignificance['yStep']];
    }
}
