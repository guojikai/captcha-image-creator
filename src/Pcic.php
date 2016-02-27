<?php

namespace Pcic;

class Pcic
{

    private $width = 180; //default width
    private $height = 65; //default height

    private $fontSize = 35; //defualt font size
    private $fontFamily = 'kangti.ttf'; //default font family

    private $code;
    private $stringType; //1:单字节 2:多字节
    private $captchaTemp;
    private $captchaImage;


    //construct
    function __construct($string, $width, $height)
    {
        if($width) $this->width = $width;
        if($height) $this->height = $height;
        $this->code = $string;

        $this->checkValid();
        $this->fontSize = $this->getFontSize();
    }

    //check parameters is valid
    private function checkValid()
    {
        if(preg_match("/^[0-9a-zA-Z]+$/", $this->code)) {
            $this->stringType = 1;
            if(strlen($this->code) < 4 || strlen($this->code) > 6) {
                throw new PcicException('Pcic Error: 验证码字符串长度不合法，允许4-6个字母或数字');
            }
        } elseif(preg_match("/^[\x{4e00}-\x{9fa5}]+$/u", $this->code)) {
            $this->stringType = 2;
            if(self::cstrlen($this->code) < 4 || self::cstrlen($this->code) > 10) {
                throw new PcicException('Pcic Error: 验证码字符串长度不合法，允许2-4个汉字');
            }
        } else {
            throw new PcicException('Pcic Error: 验证码字符串类型不合法，允许字母加数字或全中文汉字');
        }
    }

    //get string length
    private function getFontSize()
    {
        if($this->stringType == 1) {
            return (int)($this->width / strlen($this->code));
        } else {
            return (int)($this->width / self::cstrlen($this->code)) + 10;
        }
    }

    //get string length
    private function getStringLength()
    {
        return ($this->stringType == 1) ? strlen($this->code) : self::cstrlen($this->code) / 2;
    }

    //create temp image
    private function createTemp()
    {
        $this->captchaTemp = @imagecreate($this->width, $this->height);
        imagecolorallocate($this->captchaTemp, 232, 246, 252);
    }

    //writing the string in image
    private function writeCode()
    {
        if($this->height < $this->fontSize) throw new PcicException('Pcic Error: 验证码图片高度不足');
        for($i=0; $i<$this->getStringLength(); $i++) {
            $color = imagecolorallocate($this->captchaTemp, 255, 0, 0);
            $marginLeft = floor(($this->width - floor($this->fontSize*$this->getStringLength())) / $this->getStringLength()) - 8;
            $x = $marginLeft*$i + $this->fontSize*$i;
            $y = mt_rand($this->fontSize, $this->fontSize + ($this->height-$this->fontSize));
            imagettftext($this->captchaTemp,
                mt_rand($this->fontSize-5, $this->fontSize+5),
                mt_rand(-15, 15),
                $x,
                $y,
                $color,
                self::getFontPath($this->fontFamily),
                self::csubstr($this->code, $i, 1)
            );
        }
    }

    //get real path of the font file
    private static function getFontPath($fontFamily)
    {
        return dirname(__FILE__).'/'.$fontFamily;
    }

  //create image
    private function createImage()
    {
        $this->captchaImage = @imagecreate($this->width, $this->height);
        imagecolorallocate($this->captchaImage, 255, 255, 255);
        for($i=0; $i<$this->width; $i++) {
            for ($j=0; $j<$this->height; $j++) {
                $rgb = imagecolorat($this->captchaTemp, $i , $j);
                if( (int)($i+5+sin($j/$this->height*2*M_PI)*10) <= imagesx($this->captchaImage) &&
                    (int)($i+20+sin($j/$this->height*2*M_PI)*10) >=0
                ) {
                    imagesetpixel($this->captchaImage, (int)($i+5+sin($j/$this->height*2*M_PI-M_PI*0.5)*3), $j, $rgb);
                }
            }
        }
    }

    //disturb image
    private function disturbImage()
    {
        for ($i=0;$i<=100;$i++) { //dot
            $pixel = imagecolorallocate($this->captchaImage, mt_rand(0,255), mt_rand(0,255), mt_rand(0,255));
            imagesetpixel($this->captchaImage, mt_rand(0,$this->width), mt_rand(0,$this->height), $pixel);
        }
        for($i=0; $i <5; $i++) { //line
            $lineColor = imagecolorallocate($this->captchaImage, mt_rand(0,255), mt_rand(0,255), mt_rand(0,255));
            $lefty = mt_rand(1, $this->width-1);
            $righty = mt_rand(1, $this->height-1);
            imageline($this->captchaImage, 0, $lefty, imagesx($this->captchaImage), $righty, $lineColor);
        }
    }

    /**
     * strlen for chinese
     * @param  string $string
     * @return int
     */
    private static function cstrlen($string)
    {
        if( empty($string) )
            return 0;
        return (strlen($string) + mb_strlen($string, 'UTF8')) / 2;
    }

    /**
     * substr for chinese
     * @param   string      $str
     * @param   int         $start
     * @param   int         $len
     * @param   bool        $append
     * @return  string
     */
    private static function csubstr($str, $start = 0, $length, $append = '')
    {
        if(function_exists("mb_substr")) {
            if(mb_strlen($str, 'utf-8') <= $length) return $str;
            $slice = mb_substr($str, $start, $length, 'utf-8');
        } else {
            $re = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
            preg_match_all($re, $str, $match);
            if(count($match[0]) <= $length) return $str;
            $slice = join("",array_slice($match[0], $start, $length));
        }
        return $slice.$append;
    }

    //print captcha image
    public function printImage()
    {
        $this->createTemp();
        $this->writeCode();
        $this->createImage();
        $this->disturbImage();
        header("Content-Type: image/gif");
        imagepng($this->captchaImage);
        imagedestroy($this->captchaTemp);
        imagedestroy($this->captchaImage);
        exit;
    }

    /**
     * create captcha image
     * @param String $string captcha string
     * @param Number $width captcha image width
     * @param Number $height captcha image height
     * @param Number $fontSize captcha image font size
     * @return image
     */
    public static function createCaptchaImage($string = '', $width = 0, $height = 0)
    {
        $pcic = new self($string, $width, $height);
        $pcic->printImage();
    }

}

