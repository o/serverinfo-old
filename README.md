Server Info PHP ; is an open source web based interface that displays various information about your Linux server. It written in PHP 5 and licenced with GNU General Public License v3. It now also supports all major mobile browsers like iPhone, Android, Opera Mini and Internet Explorer Mobile.

Features
--------

It displays information about ;

* Load averages
* Memory usage
* Disk usage
* Uptime
* CPU information
* Linux Distribution
* Kernel version
* Active ports
* Alternative PHP Cache statistics

You need to allow shell_exec() and fsockopen() functions from php.ini for collecting information from your system. It tested with CentOS and Ubuntu (I need a feedback about behaviors on different Linux distributions).

![Desktop](http://osman.gen.tr/hq/serverinfo-desktop.png)
![Mobile](http://osman.gen.tr/hq/serverinfo-iphone.png)

Todo List
---------

This project is no longer maintained. I'll start a fresh version of it.

Installation
------------

Unzip all files and upload to a browsable path of your server. You can configure some parameters from config.php