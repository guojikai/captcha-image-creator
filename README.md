Captcha Image Creator(Pcic)
===========================
一个PHP写的中文验证码图片生成工具，仅生成验证码图片不进行缓存，需封装使用。

Installation
------------
Install the latest stable version using composer:

```
composer require guojikai/captcha-image-creator
```
And add the require in your index file: (eg. index.php)

```php
require 'vendor/autoload.php';
```

Usege
-----
```php
<?php

use Pcic\Pcic;
use Pcic\PcicException;

try {
	//Print captcha image with params: String, Width, Height (eg. 宫保鸡丁, 180, 60)
	Pcic::createCaptchaImage('宫保鸡丁'); 
} catch (PcicException $e) {
	echo $e->getMessage();
}

//will output a image

?>
```

