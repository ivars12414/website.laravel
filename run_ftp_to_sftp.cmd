@echo off

if not exist "C:\temp\ftp_download" mkdir "C:\temp\ftp_download"

"C:\Program Files (x86)\WinSCP\WinSCP.com" ^
  /script="C:\Users\Ivars\PhpstormProjects\website.laravel\website.laravel\ftp_to_sftp.txt" ^
  /log="C:\Users\Ivars\PhpstormProjects\website.laravel\website.laravel\transfer.log"

echo Готово.
pause
