import os

with open('.gitignore', 'r', encoding='utf-8', errors='ignore') as f:
    lines = f.read().replace('\0', '').splitlines()

with open('.gitignore', 'w', encoding='utf-8') as f:
    for line in lines:
        if line.strip(): 
            f.write(line + '\n')
    f.write('mototracker-76e10-firebase-adminsdk-fbsvc-9973fd805a.json\n')
