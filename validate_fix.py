#!/usr/bin/env python3
"""
Validate that all rows have 42 columns and boolean values are TRUE/FALSE.
Output problematic rows.
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
    input_file = 'database/imports/sweden_personer_ready.sql'
    problem_count = 0
    with open(input_file, 'r', encoding='utf-8') as inf:
        for lineno, line in enumerate(inf, 1):
            stripped = line.strip()
            if stripped.startswith('('):
                # Remove ON CONFLICT part if present
                if 'ON CONFLICT' in stripped:
                    # Keep only row part
                    row_part = stripped.split('ON CONFLICT')[0].strip()
                    if row_part.endswith(')'):
                        row_part = row_part[:-1]
                else:
                    row_part = stripped.rstrip(',;')
                values = split_row(row_part)
                if len(values) != 42:
                    print(f'Line {lineno}: has {len(values)} columns')
                    problem_count += 1
                else:
                    for idx in [33,34,35,36,37]:
                        val = values[idx]
                        if val not in ('TRUE', 'FALSE', 'NULL'):
                            print(f'Line {lineno}: boolean column {idx} has value "{val}"')
                            problem_count += 1
                            break
    print(f'Total problems: {problem_count}')

if __name__ == '__main__':
    main()