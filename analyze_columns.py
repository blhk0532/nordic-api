#!/usr/bin/env python3
import sys
import re

def split_row(row_str):
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

# Read first row from ratsit_data file
with open('database/imports/ratsit_data_export_2026-04-09_16-28-55.sql', 'r', encoding='utf-8') as f:
    lines = f.readlines()
    # Find INSERT line
    for i, line in enumerate(lines):
        if line.strip().upper().startswith('INSERT'):
            # next line should be a row line (starting with '(')
            for j in range(i+1, min(i+10, len(lines))):
                if lines[j].strip().startswith('('):
                    row = lines[j].strip()
                    # Remove trailing comma or semicolon
                    if row.endswith(','):
                        row = row[:-1]
                    elif row.endswith(';'):
                        row = row[:-1]
                    print(f"Row: {row[:200]}...")
                    values = split_row(row)
                    print(f"Number of columns: {len(values)}")
                    for idx, val in enumerate(values):
                        print(f"{idx}: {repr(val)}")
                    # Identify boolean columns (0/1)
                    bool_indices = []
                    for idx, val in enumerate(values):
                        if val in ('0', '1'):
                            bool_indices.append(idx)
                    print(f"Potential boolean columns (0/1): {bool_indices}")
                    # Also check for TRUE/FALSE
                    tf_indices = []
                    for idx, val in enumerate(values):
                        if val.upper() in ('TRUE', 'FALSE'):
                            tf_indices.append(idx)
                    print(f"Boolean columns (TRUE/FALSE): {tf_indices}")
                    sys.exit(0)

print("Row not found")
sys.exit(1)