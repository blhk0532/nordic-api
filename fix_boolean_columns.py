#!/usr/bin/env python3
"""
Fix boolean integer values (0/1) at specific column indices in a SQL INSERT file.
Input: transformed SQL file (already with double quotes and no IGNORE).
Output: fixed SQL file with TRUE/FALSE.
"""

import sys
import re

def split_row(row_str):
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
    for ch in row_str:
        if escape:
            escape = False
            current.append(ch)
            continue
        if ch == '\\':
            escape = True
            current.append(ch)
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
    if current:
        values.append(''.join(current).strip())
    return values

def join_values(values):
    """Join values back into a row string."""
    return '(' + ', '.join(values) + ')'

def process_file(input_path, output_path, bool_indices):
    """
    Process SQL file line by line.
    bool_indices: list of 0-based column indices where 0/1 should be converted.
    """
    with open(input_path, 'r', encoding='utf-8') as inf, open(output_path, 'w', encoding='utf-8') as outf:
        for line in inf:
            stripped = line.strip()
            # Check if line is a row line (starts with '(')
            if stripped.startswith('('):
                # Remove trailing comma or semicolon
                trailing = ''
                if stripped.endswith(';'):
                    trailing = ';'
                    stripped = stripped[:-1]
                elif stripped.endswith(','):
                    trailing = ','
                    stripped = stripped[:-1]
                # Split row
                values = split_row(stripped)
                # Convert boolean values at specified indices
                for idx in bool_indices:
                    if idx < len(values):
                        val = values[idx].strip()
                        if val == '1':
                            values[idx] = 'TRUE'
                        elif val == '0':
                            values[idx] = 'FALSE'
                # Reconstruct row
                new_row = join_values(values)
                # Write with original trailing character
                outf.write(new_row + trailing + '\n')
            else:
                # Write line as-is (INSERT line, etc.)
                outf.write(line)

def main():
    if len(sys.argv) != 4:
        print("Usage: python fix_boolean_columns.py <input.sql> <output.sql> <indices>")
        print("Example: python fix_boolean_columns.py input.sql output.sql 35,36,37,41")
        sys.exit(1)
    
    input_file = sys.argv[1]
    output_file = sys.argv[2]
    indices_str = sys.argv[3]
    bool_indices = [int(idx.strip()) for idx in indices_str.split(',')]
    
    print(f"Processing {input_file} -> {output_file}")
    print(f"Boolean column indices: {bool_indices}")
    
    process_file(input_file, output_file, bool_indices)
    
    print("Done.")

if __name__ == '__main__':
    main()