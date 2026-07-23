@echo off
title DigiSejahtera Docker

echo Menghentikan container lama...
docker compose down

echo.
echo Build dan menjalankan container...
docker compose up -d --build

echo.
echo Menampilkan status container...
docker compose ps

echo.
pause