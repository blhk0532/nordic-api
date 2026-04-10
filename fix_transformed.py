#!/usr/bin/env python3
"""
Fix boolean values in transformed SQL file and add ON CONFLICT clause.
"""

import sys
import re

def split_row(row_str: str):
    """Split a row string like (val1, val2, ...) into list of string values."""
    # Remove surrounding parentheses
    row_str = row_str.strip()
    if row_str.startswith('('):
        row_str = row_str[1:]
    if row_str.endswith(')'):
        row_str = row_str[:-1]
    values = []
    current = []
    in_string = False
    quote_char = None
    escape = False
    i = 0
    while i < len(row_str):
        ch = row_str[i]
        if escape:
            current.append(ch)
            escape = False
            i += 1
            continue
        if ch == '\\':
            escape = True
            current.append(ch)
            i += 1
            continue
        if ch in ("'", '"'):
            if not in_string:
                in_string = True
                quote_char = ch
            elif ch == quote_char:
                in_string = False
            current.append(ch)
        elif ch == ',' and not in_string:
            values.append(''.join(current).strip())
            current = []
        else:
            current.append(ch)
        i += 1
    if current:
        values.append(''.join(current).strip())
    return values

def process_row_line(line: str):
    line = line.rstrip()
    if not line.startswith('('):
        return line
    # Remove trailing comma or semicolon
    line = line.rstrip(',;')
    values = split_row(line)
    if len(values) != 42:
        sys.stderr.write(f'WARNING: row has {len(values)} columns, expected 42\n')
        return line
    # Boolean columns at positions 33-37 (0-indexed)
    for idx in [33, 34, 35, 36, 37]:
        val = values[idx]
        if val == '1':
            values[idx] = 'TRUE'
        elif val == '0':
            values[idx] = 'FALSE'
    new_row = '(' + ', '.join(values) + ')'
    return new_row

def main():
    input_file = 'database/imports/sweden_personer_transformed.sql'
    output_file = 'database/imports/sweden_personer_final.sql'
    
    with open(input_file, 'r', encoding='utf-8') as inf, open(output_file, 'w', encoding='utf-8') as outf:
        for line in inf:
            if line.strip().startswith('('):
                new_line = process_row_line(line)
                # preserve trailing comma or semicolon from original line
                if line.rstrip().endswith(';'):
                    # last row, add ON CONFLICT before semicolon
                    new_line = new_line.rstrip(';') + ' ON CONFLICT (adress, fornamn, efternamn) DO NOTHING;'
                elif line.rstrip().endswith(','):
                    new_line += ','
                outf.write(new_line + '\n')
            else:
                outf.write(line)
    print(f'Fixed boolean values and added ON CONFLICT. Output: {output_file}')

if __name__ == '__main__':
    main()