#!/usr/bin/env python3
"""
Final fix for boolean values and ON CONFLICT in the transformed SQL file.
"""

import sys
import re

def split_row(row_str: str):
    """Split a row string like (val1, val2, ...) into list of string values."""
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

def transform_values(values):
    """Replace boolean values at positions 33-37 (0-index)."""
    for idx in [33, 34, 35, 36, 37]:
        if idx < len(values):
            val = values[idx]
            if val == '1':
                values[idx] = 'TRUE'
            elif val == '0':
                values[idx] = 'FALSE'
    return values

def main():
    input_file = 'database/imports/sweden_personer_final.sql'
    output_file = 'database/imports/sweden_personer_fixed.sql'
    
    with open(input_file, 'r', encoding='utf-8') as inf, open(output_file, 'w', encoding='utf-8') as outf:
        for line in inf:
            stripped = line.strip()
            if stripped.startswith('('):
                # Remove trailing comma/semicolon
                line = line.rstrip()
                # Check if line already has ON CONFLICT
                if 'ON CONFLICT' in line:
                    # Already fixed, write as-is
                    outf.write(line + '\n')
                    continue
                # Determine if this is the last row (ends with ';')
                is_last = line.endswith(';')
                # Remove trailing comma/semicolon
                line = line.rstrip(',;')
                values = split_row(line)
                if len(values) == 42:
                    values = transform_values(values)
                    new_row = '(' + ', '.join(values) + ')'
                    if is_last:
                        new_row += ' ON CONFLICT (adress, fornamn, efternamn) DO NOTHING;'
                    else:
                        new_row += ','
                    outf.write(new_row + '\n')
                else:
                    sys.stderr.write(f'WARNING: row has {len(values)} columns, expected 42\n')
                    outf.write(line + '\n')
            else:
                outf.write(line)
    print(f'Fixed SQL written to {output_file}')

if __name__ == '__main__':
    main()