#!/usr/bin/env python3
import sys

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

with open('database/imports/ratsit_data_fixed.sql', 'r', encoding='utf-8') as f:
    for line in f:
        if line.strip().startswith('('):
            row = line.strip().rstrip(',;')
            vals = split_row(row)
            print(f"Number of columns: {len(vals)}")
            for i in range(len(vals)-10, len(vals)):
                print(f"{i}: {vals[i]}")
            break