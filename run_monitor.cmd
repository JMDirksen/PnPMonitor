@echo off
cd /d %~dp0
title PnPMonitor

:loop
start /normal /wait /b php monitor.php debug
pause
goto loop
