# Page and Port Monitor

Monitor if a webpage still loads (and optionally contains certain text) or if a TCP port is accepting connections.  
Sends an email when a fault is detected.

## Requirements
- PHP (with module: openssl) https://www.php.net/
- Can run with cron (Linux/webhosting) or Task Scheduler (Windows) or with batch file [run_monitor.cmd](run_monitor.cmd) (Windows)

## Setup steps
- Copy [config.php.template](config.php.template) to ```config.php```
- Modify ```config.php``` with correct values
- Add monitors ... (no interface yet)
- Run monitor like every minute or so ```php monitor.php```

## Linux setup
Tested on Ubuntu 18.04
```
sudo apt install git php apache2

git clone --recurse-submodules https://github.com/JeftaDirksen/PnPMonitor.git
cd PnPMonitor
cp config.php.template config.php
nano config.php
  # Make necessary changes
php monitor.php

crontab -e
  * * * * * /usr/bin/php ~/PnPMonitor/monitor.php > /dev/null
```
