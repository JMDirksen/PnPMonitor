# Page and Port Monitor

Monitor if a webpage still loads (and optionally contains certain text) or if a TCP port is accepting connections.  
Sends an email when a fault is detected.

## Requirements
- PHP (with module: openssl) https://www.php.net/
- Can run with cron (Linux/webhosting) or Task Scheduler (Windows) or with batch file [run.cmd](run.cmd) (Windows)

## Setup steps
- Copy [config.php.template](config.php.template) to ```config.php```
- Modify ```config.php``` with correct values
- Run monitor like every minute or so ```php run.php```
- Go to the website, register an account and add monitors
