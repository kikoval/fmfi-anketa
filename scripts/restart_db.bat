@echo off
set CONSOLE=%~dp0..\app\console
php %CONSOLE% doctrine:database:drop --force
php %CONSOLE% doctrine:database:create
php %CONSOLE% doctrine:schema:create
php %CONSOLE% doctrine:data:load
php %CONSOLE% anketa:import-otazky other/anketa.yml