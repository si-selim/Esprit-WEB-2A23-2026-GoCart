@echo off
title BarchaThon - Envoi d'emails automatique
echo Lancement du systeme d'envoi d'emails automatique...
echo Ne fermez pas cette fenetre si vous voulez continuer a envoyer les emails.
echo.

:loop
echo [%date% %time%] Verification des emails a envoyer...
c:\xampp\php\php.exe cron_marathon_emails.php
echo.
echo Attente de 60 secondes avant la prochaine verification...
timeout /t 60 /nobreak > nul
goto loop
