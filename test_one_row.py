#!/usr/bin/env python3
import sys
import re

def transform_line(line: str) -> str:
    line = line.replace('`', '"')
    line = re.sub(r'INSERT\s+IGNORE\s+INTO', 'INSERT INTO', line, flags=re.IGNORECASE)
    return line

with open('database/imports/sweden_personer_export_2026-04-08_22-48-03.sql', 'r', encoding='utf-8') as f:
    lines = []
    for i, line in enumerate(f):
        if i == 0:
            lines.append(transform_line(line))
        elif i == 1:
            # first row line, replace trailing comma with semicolon
            line = transform_line(line)
            line = line.rstrip().rstrip(',') + ';'
            lines.append(line)
            break

# Add settings
print('SET standard_conforming_strings = off;')
print('SET escape_string_warning = off;')
for line in lines:
    print(line, end='')