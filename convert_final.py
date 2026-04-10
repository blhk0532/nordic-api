#!/usr/bin/env python3
"""
Convert MySQL dump to PostgreSQL-compatible SQL with boolean conversion and ON CONFLICT.
Input: original MySQL dump with INSERT IGNORE and backticks.
Output: PostgreSQL-ready SQL.
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

def process_row_line(line: str, last_row: bool = False):
    """Process a single row line, convert boolean values, optionally add ON CONFLICT."""
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
    if last_row:
        new_row += ' ON CONFLICT (adress, fornamn, efternamn) DO NOTHING;'
    else:
        new_row += ','
    return new_row

def main():
    input_file = 'database/imports/sweden_personer_export_2026-04-08_22-48-03.sql'
    output_file = 'database/imports/sweden_personer_postgresql.sql'
    
    with open(input_file, 'r', encoding='utf-8') as inf, open(output_file, 'w', encoding='utf-8') as outf:
        # Read first line (INSERT IGNORE ... VALUES)
        first_line = inf.readline()
        # Transform backticks and IGNORE
        first_line = first_line.replace('`', '"')
        first_line = re.sub(r'INSERT\s+IGNORE\s+INTO', 'INSERT INTO', first_line, flags=re.IGNORECASE)
        # Write settings for PostgreSQL compatibility
        outf.write('SET standard_conforming_strings = off;\n')
        outf.write('SET escape_string_warning = off;\n')
        outf.write(first_line)
        # Process rows
        # We need to know which row is the last (ends with semicolon).
        # Since the file is huge, we can buffer lines? Instead, we'll read line by line
        # and keep previous line, writing it when we know if it's last.
        # However, the last line ends with semicolon, but there may be newline after semicolon.
        # We'll accumulate rows in memory? Not feasible.
        # Simpler: read entire file into memory? Too large (356 MB). We'll stream.
        # We'll detect last row by checking if line ends with ';' after stripping newline.
        # The last line ends with ');' maybe with extra whitespace.
        # Let's just process each line and preserve its trailing punctuation.
        # But we need to add ON CONFLICT only to the last row.
        # We'll keep a buffer of one line: read line, if next line is not starting with '(',
        # then this line is last? Not reliable.
        # Instead, we'll read all lines but that's memory heavy.
        # Since the file has ~676k lines, we can store them in a list? 676k lines * avg 500 chars = ~338 MB, maybe okay.
        # Let's do it line by line and collect row lines, then after reading all lines, we know which is last.
        # But we still need to write as we read to avoid memory. We'll read line by line, store row lines in a list?
        # Actually we can write non-row lines immediately, and buffer row lines until we encounter a non-row line.
        # Since the entire file after INSERT is rows, we can just process each row line and when we reach EOF, we know last row.
        # We'll read line by line, and when we finish reading file, the last processed row is the last one.
        # We'll keep track of previous row line and write it after processing next row.
        prev_row = None
        for line in inf:
            if line.strip().startswith('('):
                # This is a row line
                if prev_row is not None:
                    # previous row is not last, write with comma
                    outf.write(prev_row + '\n')
                prev_row = line.rstrip()
            else:
                # Should not happen, but if there is extra line, write it
                outf.write(line)
        # Now prev_row holds the last row line (ends with ');')
        if prev_row is not None:
            # Process last row with last_row=True
            new_last_row = process_row_line(prev_row, last_row=True)
            outf.write(new_last_row + '\n')
    print(f'Converted SQL written to {output_file}')

if __name__ == '__main__':
    main()