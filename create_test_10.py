#!/usr/bin/env python3
import sys

input_file = 'database/imports/sweden_personer_integer_bools.sql'
output_file = 'database/imports/test_10_rows.sql'

with open(input_file, 'r', encoding='utf-8') as inf, open(output_file, 'w', encoding='utf-8') as outf:
    # Write first 3 lines (SET, SET, INSERT ... VALUES)
    for i in range(3):
        line = inf.readline()
        if not line:
            break
        outf.write(line)
    
    # Read next 10 row lines
    rows = []
    for i in range(10):
        line = inf.readline()
        if not line:
            break
        rows.append(line.rstrip('\n'))
    
    # Process rows: need to ensure last row has ON CONFLICT clause and semicolon
    if rows:
        # Remove trailing comma from last row if present
        last_row = rows[-1]
        if last_row.endswith(','):
            last_row = last_row[:-1]
        # Add ON CONFLICT clause
        last_row += ' ON CONFLICT (adress, fornamn, efternamn) DO NOTHING;'
        rows[-1] = last_row
        # Write rows
        for row in rows:
            outf.write(row + '\n')
    
    print(f'Created test file with {len(rows)} rows: {output_file}')