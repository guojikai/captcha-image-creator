Captcha Image Creator(Pcic)
===========================
一个PHP写的中文验证码图片生成工具，仅生成验证码图片不进行缓存，需封装使用。

安装
----
使用 Composer 安装：

```
composer require guojikai/captcha-image-creator
```
在入口文件引入 Composer 启动脚本： (eg. index.php)

```php
require 'vendor/autoload.php';
```

使用
----
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

//将直接显示验证码图片

?>
```

