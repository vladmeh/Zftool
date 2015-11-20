# ZF1 Tool Mapper models generator 

ZF1 Tool Mapper model generator - это утилита для быстрого создания стандарных моделй Вашего приложения с помощью 
командной строки по шаблону ["Data Mapper"](http://martinfowler.com/eaaCatalog/dataMapper.html). Создание таких моделей 
подробно описана в ["Учебнике Zend Framework"](http://framework.zend.com/manual/1.12/ru/learning.html) на странице 
["Создание модели и базы данных таблицы"](http://framework.zend.com/manual/1.12/ru/learning.quickstart.create-model.html).

## Установка

Т.к. утилита является расширением Zend Framework 1.X [Zend_Tool CLI](http://framework.zend.com/manual/1.12/ru/zend.tool.usage.cli.html) 
у Вас должен быть установлен и настроен Zend_Tool CLI. Как установить и настроить Zend_Tool подробно читайте [здесь](http://framework.zend.com/manual/1.12/ru/zend.tool.extending.html)

**Git clone**
 
Установка ZF1 Tool Mapper models generator:

	cd <project name>/library
	git clone git@github.com:vladmeh/Zftool.git

У вас должна получиться следующая структура проекта

	<project name>/
        |-- application/
        |-- library/
        |   |-- Zftool/
        |   |   '-- Tool/
        |   |       '-- Project/
        |   |           |-- Context/
        |   |           |   '-- ZF/
        |   |           |       |-- ModelColFile.php
        |   |           |       |-- ModelMapperDirectory.php
        |   |           |       '-- ModelMapperFile.php
        |   |           '-- Provider/
        |   |               |-- Manifest.php
        |   |               '-- ModelMapperProvider.php
        |   |--...
        |-- public/
        |-- ...

**Конфигурация Zend_Tool**

Если вы еще не сделали этого, настройте каталог для хранения `.zf` и конфигурационный файл Zend_Tool `.zf.ini`:
		
	zf --setup storage-directory
    zf --setup config-file
	    
Вносим изменения в созданный файл конфигурации `.zf.ini`

	php.include_path = "...;<the path to your project>/library"
    autoloadernamespaces.0 = "Zftool_"
    basicloader.classes.0 = "Zftool_Tool_Project_Provider_ModelMapperProvider"
    basicloader.classes.1 = "Zftool_Tool_Project_Provider_Manifest"
    
Узнать где находиться файл `.zf.ini` и его текущие настроки можно командой `zf show config`

Проверяем установку

	zf ? modelmapper
	
Вы должны увидеть что-то вроде

	Zend Framework Command Line Console Tool v1.12.13
    Actions supported by provider "Modelmapper"
      Modelmapper
        zf create modelmapper table-name module
        Note: There are specialties, use zf create modelmapper.? to get specific help on them.
        Note: There are specialties, use zf show modelmapper.? to get specific help on them.

	
## Применение

**Основные команды**

	zf create modelmapper Tablename
	
С помощью нее будут созданы три основных файла модели Вашей таблицы `Tablename` базы данных.

	<project name>/
        |-- application/
        |   |-- models/
        |   |   |-- DbTable/
        |   |   |   '-- Tablename.php
        |   |   |-- mappers/
        |   |   |   '-- Tablename.php
        |   |   '-- Tablename.php

Если нужно создать модели в модуле 

	zf create modelmapper Tablename modulename
	
Будут созданы модели таблицы `Tablename` непосредственно в модуле `modulename`.

	<project name>/
        |-- application/
        |   |-- modules/
	    |   |   |-- modulename/
		|   |   |   |-- models/
		|   |   |   |   |-- DbTable/
		|   |   |   |   |   '-- Tablename.php
		|   |   |   |   |-- mappers/
		|   |   |   |   |   '-- Tablename.php
		|   |   |   |   '-- Tablename.php
		
Если файлы моделей уже существуют, Вам будет предложено на выбор перезаписать или сделать бекап старого файла

	This project already has a Mapper model: ...
    Overwrite?(y) Backup old file?(a) Cancel.(n)

**Вспомогательные команды**

	zf create modelmapper.?
	Zend Framework Command Line Console Tool v1.12.13
	Details for action "Create" and provider "Modelmapper"
	  Modelmapper
	    zf create modelmapper table-name module
	    zf create modelmapper.table-model table-name module
	    zf create modelmapper.mapper-model name module
	    zf create modelmapper.db-table name actual-table-name module
	    
	zf show modelmapper.?
    Zend Framework Command Line Console Tool v1.12.13
    Details for action "Show" and provider "Modelmapper"
        zf show modelmapper.table-list
        zf show modelmapper.column-list table-name

Создание только основного файла модели `models/Tablename.php`

	zf create modelmapper.table-model table-name module
	
Создание только Mapper файла модели `models/mappers/Tablename.php`

	zf create modelmapper.mapper-model name module
	
Создание только DbTable модели `models/DbTable/Tablename.php` (аналог команды `zf create db-table name actual-table-name module force-overwrite`)

	zf create modelmapper.db-table name actual-table-name module force-overwrite
	
Список имеющихся таблиц в базе данных

	zf show modelmapper.table-list
	
Список полей в таблице базы данных

	zf show modelmapper.column-list table-name

## License ##

Copyright (c) 2013 [VladMeh](https://github.com/vladmeh)

Distributed under the [MIT License](http://www.opensource.org/licenses/MIT) (MIT-LICENSE.txt)

[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/vladmeh/zftool/trend.png)](https://bitdeli.com/free "Bitdeli Badge")



[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/vladmeh/zftool/trend.png)](https://bitdeli.com/free "Bitdeli Badge")

