#!/usr/bin/env python3
import re
with open('database/imports/sweden_personer_export_2026-04-08_22-48-03.sql', 'r') as f:
    first_line = f.readline().strip()
    # extract column list between parentheses
    match = re.search(r'\((`[^`]+`(?:,\s*`[^`]+`)*)\)', first_line)
    if match:
        cols = match.group(1)
        cols = [c.strip('` ') for c in cols.split(',')]
        print(f'Total columns: {len(cols)}')
        for i, col in enumerate(cols, start=1):
            print(f'{i}: {col}')
    else:
        print('No match')