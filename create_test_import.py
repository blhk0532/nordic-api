#!/usr/bin/env python3
import sys

input_file = 'database/imports/sweden_personer_insert_only.sql'
output_file = 'database/imports/test_import_100_rows.sql'

with open(input_file, 'r', encoding='utf-8') as inf, open(output_file, 'w', encoding='utf-8') as outf:
    # Write PostgreSQL settings
    outf.write('SET standard_conforming_strings = off;\n')
    outf.write('SET escape_string_warning = off;\n')
    
    # Read INSERT line
    insert_line = inf.readline()
    outf.write(insert_line)
    
    # Read first 100 row lines
    rows = []
    for i, line in enumerate(inf):
        if i >= 100:
            break
        rows.append(line)
    
    # Process rows: need to replace last comma with ON CONFLICT clause
    if rows:
        # Remove trailing newline from each row
        rows = [row.rstrip('\n') for row in rows]
        # Last row currently ends with comma (except maybe the very last row in original file)
        # In our extracted 100 rows, last row ends with comma.
        # Replace last row's trailing comma with ON CONFLICT clause
        last_row = rows[-1]
        if last_row.endswith(','):
            last_row = last_row.rstrip(',') + ' ON CONFLICT (adress, fornamn, efternamn) DO NOTHING;'
        else:
            last_row = last_row + ' ON CONFLICT (adress, fornamn, efternamn) DO NOTHING;'
        rows[-1] = last_row
        
        # Write rows
        for row in rows:
            outf.write(row + '\n')
    
    print(f'Created test file with {len(rows)} rows: {output_file}')