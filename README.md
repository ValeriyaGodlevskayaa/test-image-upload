# :upload-image



## Install

Via Composer

``` bash
$ composer require "lera/test_image_upload @dev"
```

## Usage

``` php
$class = new lera\test_image_upload\ImageUploader;
$uploader = $class->getUploader();
$uploader->addUrl("link to image"); // если хотим добавить одну ссылку, вызываем метод addUrl,
 если несколько сразу, вызываем метод addUrls();
$uploader->addUrls(["linkOne", "linkTwo"]);
//для удаления ссылки из списка 
$uploader->removeUrl("linkOne");
//получить список ссылок
$uploader->getUrls();

//создание папки для загрузки картинок
$uploader->createDir('uploads', $_SERVER['DOCUMENT_ROOT'])
//второй параметр не обязательный, полный путь к корневой папке
$uploader->setDirPath($_SERVER['DOCUMENT_ROOT'].'/'.'uploads')//указываем в каккую папку грузить картинки
$uploader->getDirPath()//получаем путь к папке с загруженами картинками
$uploader->getErrors()//вызываем для вывода ошибок
$uploader->saveImages()//сохраняем картинки в папку
```


