<?php
namespace App\Service\Captcha;

/**
 * Class Captcha
 * @Copyright:1Room
 * @Author:D.C
 * @editer:Morgan
 * @Version:1.0
 */
class Captcha{
    protected static  $image  = null;
    protected static $fonts  = array('CeruleanNF.otf','droidsansmono__.ttf','Route66NF.otf','TuringCarNF.otf');
    public  static $width  = 100;
    public static $height = 30;
    protected static $length = 4;
    protected  static $phrase = null;
    protected static $string = array('A','B','C','D','E','F','H','J','K','L','M','N','P','R','S','T','U','V','W','X','Y','Z');
    protected static $session = null;
    protected static $requset = null;

    /**
     * Random String
     * @author D.C
     * @return string
     */
    public static function Phrase()
    {
        if (strlen(self::$phrase) != self::$length) {
            $phrase = null;
            $rand = array_rand(self::$string, self::$length);
            foreach ($rand as $index)
                $phrase .= self::$string[$index];
            self::$phrase = $phrase;
        }
        return self::$phrase;
    }

    /**
     * Generate Captcha
     * @update D.C
     * @return null
     */
    public static function Generate()
    {
        if (!extension_loaded("gd")) exit("Captcha Unable Load GD Library Copyright 1Room");
        self::$phrase = self::Phrase();

        $image = is_resource(self::$image) ? self::$image : self::GenerateImage(self::$phrase);
        imagepng($image);
        imagedestroy($image);
        return $image;
    }


    /**
     * Use GD Library Create Image
     * @author D.C
     * version 1.0
     * @return resource
     */
    private static  function CreateImage()
    {
        if (!is_resource(self::$image)) {

            self::$image = imagecreatetruecolor(self::$width, self::$height);
            $color1 = imagecolorallocate(self::$image, mt_rand(200, 255),mt_rand(200, 255), mt_rand(150, 255));
            $color2 = imagecolorallocate(self::$image, mt_rand(200, 255),mt_rand(200, 255), mt_rand(150, 255));
            $color1 = imagecolorsforindex(self::$image, $color1);
            $color2 = imagecolorsforindex(self::$image, $color2);
            $steps = self::$width;

            $r1 = ($color1['red'] - $color2['red']) / $steps;
            $g1 = ($color1['green'] - $color2['green']) / $steps;
            $b1 = ($color1['blue'] - $color2['blue']) / $steps;

            $x1 = 0; $y1 =& $i; $x2 = self::$width; $y2 =& $i;

            for ($i = 0; $i <= $steps; $i++) {
                $r2 = $color1['red'] - floor($i * $r1);
                $g2 = $color1['green'] - floor($i * $g1);
                $b2 = $color1['blue'] - floor($i * $b1);
                $color = imagecolorallocate(self::$image, $r2, $g2, $b2);
                imageline(self::$image, $x1, $y1, $x2, $y2, $color);
            }

            for ($i = 0, $count = mt_rand(10, 20); $i < $count; $i++) {
                $color = imagecolorallocatealpha(self::$image, mt_rand(20, 255), mt_rand(20, 255),
                    mt_rand(100, 255), mt_rand(80, 120));
                imageline(self::$image, mt_rand(0, self::$width), 0,
                    mt_rand(0, self::$width), self::$height, $color);
            }
        }
        return self::$image;
    }

    /**
     * Merge Random String and Created Image and Set Session
     * @author D.C
     * @version 1.1
     * @param null $phrase
     * @return resource
     */
    public static  function GenerateImage($phrase = null)
    {
        $image = self::CreateImage();
        $phrase == null && $phrase = self::Phrase();
        $fontsize = min(self::$width, self::$height * 2) / (strlen($phrase));
        $spacing = (integer) (self::$width * 0.9 / strlen($phrase));
        $font = __SERVICE__.'/Captcha/Fonts/'.self::$fonts[array_rand(self::$fonts)];

        for ($i = 0, $strlen = strlen($phrase); $i < $strlen; $i++) {
            $color = imagecolorallocate($image, mt_rand(0, 160), mt_rand(0, 160), mt_rand(0, 160));
            $angle = mt_rand(-30, 30);
            $size = $fontsize / 12 * mt_rand(12, 14);
            $box = imageftbbox($size, $angle, $font, $phrase[$i]);
            $x = $spacing / 4 + $i * $spacing + 2;
            $y = self::$height / 2 + ($box[2] - $box[5]) / 4;
            imagefttext($image, $size, $angle, $x, $y, $color, $font, $phrase[$i]);
        }
        self::$image = $image;
        return self::$image;
    }
}