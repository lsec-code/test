@echo off
echo [INFO] Initalizing Git Repository...
git init
git add .
git commit -m "Release: Monster Stres V2 (Cyberpunk Edition)"
git branch -M main
git remote remove origin
git remote add origin https://github.com/lsec-code/test.git
echo.
echo [INFO] Pushing to GitHub (ensure you are logged in)...
git push -u origin main
echo.
echo [SUCCESS] Project uploaded to https://github.com/lsec-code/test.git
pause
