@echo off
REM
REM This is a wrapper for symfony asset:install
REM @copyright   Copyright (c) 2011 Martin Sucha, Martin Kralik
REM @author      Martin Sucha <anty.sk+svt+anketa@gmail.com>
REM @author      Martin Kralik <majak47@gmail.com>
REM

set ROOT=%~dp0..
php %ROOT%\app\console assets:install %ROOT%\web
