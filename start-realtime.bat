@echo off
echo Starting Farm Monitoring System with Real-time Features...
echo.

set WEB_PORT=%1
set WS_PORT=%2
if "%WEB_PORT%"=="" set WEB_PORT=8000
if "%WS_PORT%"=="" set WS_PORT=8080

echo Starting Web Server on port %WEB_PORT%...
start "Farm Web Server" cmd /k "php -S localhost:%WEB_PORT%"

timeout /t 3 /nobreak > nul

echo Starting WebSocket Server on port %WS_PORT%...
set WS_PORT_ENV=%WS_PORT%
start "WebSocket Server" cmd /k "set WS_PORT=%WS_PORT_ENV% && php websocket-server.php"

echo.
echo Both servers are starting...
echo Web Dashboard: http://localhost:%WEB_PORT%/mwaba/
echo WebSocket Server: ws://localhost:%WS_PORT%
echo.
echo Press any key to exit...
pause > nul
