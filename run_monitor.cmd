@echo off
cd /d %~dp0
title PnPMonitor

:loop
start /normal /wait /b php monitor.php
timeout 60 >nul
goto loop
