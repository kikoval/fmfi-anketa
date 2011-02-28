@echo off
REM See http://www.phpunit.de/manual/current/en/ on how to write tests
setlocal

set SCRIPT_PATH=%~dp0
set TEST_PATH=%SCRIPT_PATH%..
set REPORT_PATH=%SCRIPT_PATH%..\report\tests

if exist %REPORT_PATH%\ rmdir %REPORT_PATH% /S /Q
mkdir %REPORT_PATH%

set PARAMS=--coverage-html %REPORT_PATH%/coverage
set PARAMS=%PARAMS% --process-isolation
set PARAMS=%PARAMS% --testdox-html %REPORT_PATH%/report.html

cd %TEST_PATH%
phpunit %PARAMS% %TEST_PATH%