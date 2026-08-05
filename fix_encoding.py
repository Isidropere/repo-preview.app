import os
import glob

files = glob.glob('resources/views/admin/**/*.blade.php', recursive=True)
for f in files:
    try:
        with open(f, 'r', encoding='utf-8') as file:
            content = file.read()
        if "@extends('layouts.app')" in content:
            content = content.replace("@extends('layouts.app')", "@extends('layouts.admin')")
            with open(f, 'w', encoding='utf-8') as file:
                file.write(content)
            print(f"Updated {f}")
    except Exception as e:
        print(f"Error on {f}: {e}")
