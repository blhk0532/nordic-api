#!/usr/bin/env python3
"""
Fix remaining integer boolean values (1/0) in the final import SQL file.
Processes the file line by line, converting 1/0 to TRUE/FALSE for boolean columns
at positions 33-37 (0-indexed) of 42 columns.
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

def process_line(line: str, line_num: int):
    """Process a line, fix boolean values if it's a row line."""
    line = line.rstrip('\n')
    if not line.startswith('('):
        return line + '\n'
    
    # Check if line ends with ON CONFLICT clause (last row)
    on_conflict = ''
    if 'ON CONFLICT' in line:
        # Extract the ON CONFLICT part
        idx = line.find('ON CONFLICT')
        on_conflict = line[idx:]
        line = line[:idx].rstrip()
        # Remove trailing comma if present
        if line.endswith(','):
            line = line[:-1]
        # Remove trailing semicolon if present (should not)
        if line.endswith(';'):
            line = line[:-1]
    
    # Remove trailing comma or semicolon (for non-last rows)
    trailing = ''
    if line.endswith(';'):
        trailing = ';'
        line = line[:-1]
    elif line.endswith(','):
        trailing = ','
        line = line[:-1]
    
    # Split row
    values = split_row(line)
    if len(values) != 42:
        # If column count mismatch, return original line (should not happen)
        sys.stderr.write(f'Line {line_num}: Warning: row has {len(values)} columns, expected 42\n')
        # Reconstruct original line with trailing and on_conflict
        return line + trailing + (' ' + on_conflict if on_conflict else '') + '\n'
    
    # Boolean columns at positions 33-37 (0-indexed)
    for idx in [33, 34, 35, 36, 37]:
        val = values[idx].strip()
        if val == '1':
            values[idx] = 'TRUE'
        elif val == '0':
            values[idx] = 'FALSE'
        # Note: values could already be TRUE/FALSE, keep them
    
    # Reconstruct row
    new_row = '(' + ', '.join(values) + ')'
    # Add trailing punctuation and ON CONFLICT clause
    result = new_row + trailing
    if on_conflict:
        result += ' ' + on_conflict
    return result + '\n'

def main():
    if len(sys.argv) != 3:
        print("Usage: python fix_remaining_booleans.py <input.sql> <output.sql>")
        sys.exit(1)
    
    input_file = sys.argv[1]
    output_file = sys.argv[2]
    
    with open(input_file, 'r', encoding='utf-8') as inf, open(output_file, 'w', encoding='utf-8') as outf:
        line_num = 0
        processed_rows = 0
        for line in inf:
            line_num += 1
            if line.startswith('('):
                processed_rows += 1
                if processed_rows % 100000 == 0:
                    sys.stderr.write(f'Processed {processed_rows} rows...\n')
            outf.write(process_line(line, line_num))
    
    print(f'Finished processing {processed_rows} rows.')
    print(f'Output written to {output_file}')

if __name__ == '__main__':
    main()