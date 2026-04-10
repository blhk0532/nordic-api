#!/usr/bin/env python3
"""
Fix the last row's boolean values (0/1 to FALSE/TRUE) in the fixed SQL file.
"""

import sys

def split_row(row_str: str):
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

def main():
    input_file = 'database/imports/sweden_personer_fixed.sql'
    output_file = 'database/imports/sweden_personer_ready.sql'
    
    with open(input_file, 'r', encoding='utf-8') as inf, open(output_file, 'w', encoding='utf-8') as outf:
        lines = inf.readlines()
        # Find the last line that contains ON CONFLICT
        for i, line in enumerate(lines):
            if 'ON CONFLICT' in line:
                # This is the last row line
                # Split line into row part and conflict part
                parts = line.split(' ON CONFLICT', 1)
                row_part = parts[0].strip()
                conflict_part = ' ON CONFLICT' + parts[1]
                # Process row part
                if row_part.endswith(')'):
                    row_part = row_part[:-1]
                values = split_row(row_part)
                if len(values) == 42:
                    for idx in [33,34,35,36,37]:
                        val = values[idx]
                        if val == '1':
                            values[idx] = 'TRUE'
                        elif val == '0':
                            values[idx] = 'FALSE'
                    new_row = '(' + ', '.join(values) + ')' + conflict_part
                    lines[i] = new_row + '\n'
                break
    
        outf.writelines(lines)
    print(f'Fixed last row. Output: {output_file}')

if __name__ == '__main__':
    main()