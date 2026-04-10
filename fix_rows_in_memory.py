#!/usr/bin/env python3
"""
Load entire SQL file into memory, parse rows with state machine, fix booleans.
"""

import sys

def split_rows(content):
    """
    Split SQL content into prefix, rows, suffix.
    Returns (prefix, list_of_row_strings, suffix)
    """
    # Find VALUES keyword (case-insensitive)
    import re
    values_match = re.search(r'VALUES\s*\(', content, re.IGNORECASE)
    if not values_match:
        raise ValueError('VALUES not found')
    start_pos = values_match.end() - 1  # position of '(' after VALUES
    prefix = content[:start_pos]  # includes VALUES and '('
    rest = content[start_pos:]
    
    rows = []
    current_row = []
    depth = 0
    in_string = False
    quote_char = None
    escape = False
    i = 0
    length = len(rest)
    while i < length:
        ch = rest[i]
        if escape:
            escape = False
            current_row.append(ch)
            i += 1
            continue
        if ch == '\\':
            escape = True
            current_row.append(ch)
            i += 1
            continue
        if ch in ("'", '"'):
            if not in_string:
                in_string = True
                quote_char = ch
            elif ch == quote_char:
                in_string = False
            current_row.append(ch)
        elif ch == '(' and not in_string:
            depth += 1
            current_row.append(ch)
            if depth == 1:
                # Start of a row, reset current_row (should already be empty)
                current_row = ['(']
        elif ch == ')' and not in_string:
            depth -= 1
            current_row.append(ch)
            if depth == 0:
                # End of row
                rows.append(''.join(current_row))
                current_row = []
                # skip whitespace and comma
                i += 1
                while i < length and rest[i] in (' ', '\t', '\n', '\r', ','):
                    i += 1
                continue
        else:
            current_row.append(ch)
        i += 1
    
    # After loop, remaining characters are suffix (including ON CONFLICT etc.)
    suffix = ''.join(current_row) if current_row else ''
    # Also need to include any remaining part after the last row?
    # Actually after loop, i == length, and current_row should be empty.
    # If there's leftover, it's suffix (like ON CONFLICT clause).
    # But we already captured suffix as current_row after loop.
    # However, we stopped appending after we finished rows? Need to think.
    # Let's simplify: we'll parse the entire rest content, splitting rows.
    # We'll capture suffix as the part after the last row's closing paren.
    # Instead, we'll modify algorithm to collect rows and suffix separately.
    # Let's implement differently: parse rows and collect suffix after last row.
    # For now, assume suffix is empty.
    return prefix, rows, suffix

def fix_row(row):
    """Fix boolean values in a row."""
    # Remove surrounding parentheses
    row = row.strip()
    if row.startswith('('):
        row = row[1:]
    if row.endswith(')'):
        row = row[:-1]
    
    values = []
    current = []
    in_string = False
    quote_char = None
    escape = False
    i = 0
    while i < len(row):
        ch = row[i]
        if escape:
            escape = False
            current.append(ch)
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
    
    if len(values) == 42:
        for idx in [33, 34, 35, 36, 37]:
            val = values[idx].strip()
            if val == '1':
                values[idx] = 'TRUE'
            elif val == '0':
                values[idx] = 'FALSE'
        return '(' + ', '.join(values) + ')'
    else:
        sys.stderr.write(f'Warning: row has {len(values)} columns, expected 42\n')
        return '(' + row + ')'

def main():
    if len(sys.argv) != 3:
        print("Usage: python fix_rows_in_memory.py <input.sql> <output.sql>")
        sys.exit(1)
    
    input_file = sys.argv[1]
    output_file = sys.argv[2]
    
    print("Reading file...")
    with open(input_file, 'r', encoding='utf-8') as f:
        content = f.read()
    
    print(f"File size: {len(content)} bytes")
    
    # Find VALUES
    import re
    values_match = re.search(r'VALUES\s*\(', content, re.IGNORECASE)
    if not values_match:
        print("VALUES not found")
        sys.exit(1)
    
    prefix = content[:values_match.end()]
    rest = content[values_match.end():]
    
    # Parse rows from rest
    print("Parsing rows...")
    rows = []
    current = []
    depth = 0
    in_string = False
    quote_char = None
    escape = False
    i = 0
    length = len(rest)
    while i < length:
        ch = rest[i]
        if escape:
            escape = False
            current.append(ch)
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
        elif ch == '(' and not in_string:
            depth += 1
            current.append(ch)
            if depth == 1:
                # Start of row, reset current
                current = ['(']
        elif ch == ')' and not in_string:
            depth -= 1
            current.append(ch)
            if depth == 0:
                # End of row
                rows.append(''.join(current))
                current = []
                # skip whitespace and comma
                i += 1
                while i < length and rest[i] in (' ', '\t', '\n', '\r', ','):
                    i += 1
                continue
        else:
            current.append(ch)
        i += 1
    
    # After loop, current contains suffix (ON CONFLICT etc.)
    suffix = ''.join(current)
    
    print(f"Found {len(rows)} rows")
    
    # Fix rows
    print("Fixing boolean values...")
    fixed_rows = []
    for idx, row in enumerate(rows):
        if idx % 100000 == 0:
            print(f"  Processed {idx} rows")
        fixed_rows.append(fix_row(row))
    
    # Write output
    print("Writing output...")
    with open(output_file, 'w', encoding='utf-8') as f:
        f.write(prefix)
        for i, row in enumerate(fixed_rows):
            if i == len(fixed_rows) - 1:
                f.write(row + ' ' + suffix)
            else:
                f.write(row + ',')
        # If suffix already contains rows separator? suffix includes the ON CONFLICT clause.
        # We already appended suffix after last row.
    
    print(f"Output written to {output_file}")

if __name__ == '__main__':
    main()