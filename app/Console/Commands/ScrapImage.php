<?php

namespace Tide\Application\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use Imagick;
use ImagickPixel;
use Tide\Application\Tide;

class ScrapImage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tide';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display an inspiring quote';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $imagick = new Imagick("http://www.ntslf.org/files/ntslf_php/pltdata_tgi.php?port=Lowestoft&span=1");

        $size = $imagick->getImageGeometry();
        $width = $size['width'];
        $height = $size['height'];
        unset($size);


        $black = new ImagickPixel('#000000');
        $gray = new ImagickPixel('#C0C0C0');

        $textRight = 427 + 150;
        $textLeft = 427;
        $textBottom = 3 + 12;
        $textTop = 3;

        $foundGray = false;

        for ($x = 0; $x < $width; ++$x) {
            for ($y = 0; $y < $height; ++$y) {
                $pixel = $imagick->getImagePixelColor($x, $y);
                $color = $pixel->getColor();
                // remove alpha component
                $pixel->setColor('rgb(' . $color['r'] . ','
                    . $color['g'] . ','
                    . $color['b'] . ')');

                // find the first gray pixel and ignore pixels below the gray
                if ($pixel->isSimilar($gray, .25)) {
                    $foundGray = true;
                    break;
                }

                // find the text boundaries
                if ($foundGray && $pixel->isSimilar($black, .25)) {
                    if ($textLeft === 0) {
                        $textLeft = $x;
                    } else {
                        $textRight = $x;
                    }

                    if ($y < $textTop) {
                        $textTop = $y;
                    }

                    if ($y > $textBottom) {
                        $textBottom = $y;
                    }
                }
            }
        }

        $textWidth = $textRight - $textLeft;
        $textHeight = $textBottom - $textTop;
        $imagick->cropImage($textWidth + 10, $textHeight + 10, $textLeft - 5, $textTop - 5);
        $imagick->scaleImage($textWidth * 10, $textHeight * 10, true);

        $textFilePath = tempnam('/temp', 'text-ocr-') . '.png';
        $imagick->writeImage($textFilePath);

        $text = str_replace(' ', ' ', shell_exec('gocr ' . escapeshellarg($textFilePath)));
        unlink($textFilePath);

        list($height, $time) = $this->processText($text);

        Tide::firstOrCreate(['height' => $height, 'time' => $time]);
    }

    /**
     * @param $text
     * @return array
     */
    private function processText($text)
    {
        list($height, $time) = (explode('at', $text));
        $height = str_replace('m', '', strtolower($height));
        $height = $this->formatNumber($height);
        list($hour, $minute) = explode(':', $this->formatNumber($time));

        $hour = trim($hour);
        $minute = str_replace(" GmT\n", '', $minute);
        if ($minute == 60) {
            $hour++;
            $minute = 0;
        }
        $time = Carbon::createFromTime($hour, $minute, 0, 'GMT');

        return array(trim($height), $time);
    }

    /**
     * @param $raw
     * @return mixed
     */
    private function formatNumber($raw)
    {
        return str_replace('o', 0, $raw);
    }
}
