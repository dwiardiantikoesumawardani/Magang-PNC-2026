echo %date% %time% >> C:\xampp\htdocs\monitoring\bat_ran.txt

@echo off
cd /d C:\xampp\htdocs\monitoring
"C:\xampp\php\php.exe" cron_close_session.php >> cron_scheduler.log 2>&1
echo. >> cron_scheduler.log

"C:\xampp\php\php.exe" cron_alert_sosmed.php >> cron_scheduler.log 2>&1
echo. >> cron_scheduler.log