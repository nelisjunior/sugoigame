@echo off
echo 🚀 Iniciando MySQL do XAMPP...

cd /d "C:\xampp\mysql\bin"

echo Parando qualquer instância MySQL existente...
taskkill /f /im mysqld.exe 2>nul

echo Iniciando MySQL em background...
start /B "" mysqld.exe --defaults-file="C:\xampp\mysql\bin\my.ini" --standalone

echo Aguardando MySQL inicializar...
timeout /t 5 /nobreak >nul

echo Testando conexão...
mysql.exe -u root -h localhost -e "CREATE DATABASE IF NOT EXISTS sugoi_v2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; SHOW DATABASES;"

echo ✅ MySQL configurado e rodando!
echo 📍 Servidor: localhost:3306
echo 📍 Usuário: root (sem senha)
echo 📍 Banco: sugoi_v2

pause