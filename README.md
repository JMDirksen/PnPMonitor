# Page and Port Monitor

Monitor if a webpage still loads (and optionally contains certain text) or if a TCP port is accepting connections.  
Sends an email when a fault is detected.

## Requirements
- MySQL Database https://mariadb.org/
- PHP (with modules: mysqli, openssl) https://www.php.net/
- Can run with cron (Linux/webhosting) or Task Scheduler (Windows) or with batch file [run_monitor.cmd](run_monitor.cmd) (Windows)

## Setup
- Create database with supplied [database.sql](database.sql)
- Copy [config.php.template](config.php.template) to ```config.php```
- Modify ```config.php``` with correct values
- Add monitors in database (no interface yet)
- Run monitor like every minute or so ```php monitor.php```
