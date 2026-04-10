#!/usr/bin/env python3
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

def join_values(values):
    """Join values back into a row string."""
    # Ensure proper quoting? values are already strings with quotes if needed.
    return '(' + ', '.join(values) + ')'

def transform_values(values):
    """Replace boolean values at positions 33-37 (0-index)."""
    for idx in [33, 34, 35, 36, 37]:  # is_hus to is_done
        if idx < len(values):
            val = values[idx]
            if val == '1':
                values[idx] = 'TRUE'
            elif val == '0':
                values[idx] = 'FALSE'
    return values

def process_row_line(line: str):
    line = line.rstrip()
    if not line.startswith('('):
        return line
    # Remove trailing comma or semicolon
    line = line.rstrip(',;')
    values = split_row(line)
    if len(values) != 42:
        # fallback: return original line (should not happen)
        sys.stderr.write(f'WARNING: row has {len(values)} columns, expected 42\n')
        return line
    values = transform_values(values)
    new_row = join_values(values)
    return new_row

def main():
    input_file = 'database/imports/sweden_personer_export_2026-04-08_22-48-03.sql'
    output_file = 'database/imports/sweden_personer_final.sql'
    
    with open(input_file, 'r', encoding='utf-8') as inf, open(output_file, 'w', encoding='utf-8') as outf:
        first_line = inf.readline()
        # Transform backticks and IGNORE
        first_line = first_line.replace('`', '"')
        first_line = re.sub(r'INSERT\s+IGNORE\s+INTO', 'INSERT INTO', first_line, flags=re.IGNORECASE)
        # Write settings
        outf.write('SET standard_conforming_strings = off;\n')
        outf.write('SET escape_string_warning = off;\n')
        outf.write(first_line)
        # Process rows
        for line in inf:
            if line.strip().startswith('('):
                new_line = process_row_line(line)
                # preserve trailing comma or semicolon from original line
                if line.rstrip().endswith(';'):
                    # This is the last row, need to add ON CONFLICT before semicolon
                    new_line = new_line.rstrip(';') + ' ON CONFLICT (adress, fornamn, efternamn) DO NOTHING;'
                elif line.rstrip().endswith(','):
                    new_line += ','
                outf.write(new_line + '\n')
            else:
                # Should not happen, but write as-is
                outf.write(line)
    print(f'Transformed SQL written to {output_file}')

if __name__ == '__main__':
    main()