@echo off
REM
REM This is a wrapper for symfony asset:install
REM @copyright   Copyright (c) 2011 Martin Sucha, Martin Kralik
REM @author      Martin Sucha <anty.sk+svt+anketa@gmail.com>
REM @author      Martin Kralik <majak47@gmail.com>
REM

set ROOT=%~dp0..
set PREFIX=%ROOT%\web\bundles
set SUFFIX=Resources\public\
set SF_BUNDLES=%ROOT%\vendor\symfony\src\Symfony\Bundle

if exist %PREFIX%\anketa rmdir %PREFIX%\anketa /S /Q
if exist %PREFIX%\framework rmdir %PREFIX%\framework /S /Q
if exist %PREFIX%\webprofiler rmdir %PREFIX%\webprofiler /S /Q
if exist %PREFIX%\symfonywebconfigurator rmdir %PREFIX%\symfonywebconfigurator /S /Q

svn export %ROOT%\src\AnketaBundle\%SUFFIX% %PREFIX%\anketa
svn export %SF_BUNDLES%\FrameworkBundle\%SUFFIX% %PREFIX%\framework
svn export %SF_BUNDLES%\WebProfilerBundle\%SUFFIX% %PREFIX%\webprofiler
svn export %ROOT%\vendor\bundles\Symfony\Bundle\WebConfiguratorBundle\%SUFFIX% %PREFIX%\symfonywebconfigurator