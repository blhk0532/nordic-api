#!/usr/bin/env python3
"""
Fix boolean values in SQL file with proper handling of multi-line rows.
Reads entire file, parses the INSERT statement, converts integer booleans.
"""

import sys
import re

def parse_rows(sql_content: str):
    """
    Extract rows from INSERT statement.
    Returns list of row strings (including parentheses).
    """
    # Find VALUES keyword (case-insensitive)
    values_match = re.search(r'VALUES\s*\(', sql_content, re.IGNORECASE)
    if not values_match:
        raise ValueError('VALUES not found')
    start_pos = values_match.end() - 1  # position of '(' after VALUES
    # Parse from start_pos, find matching closing parenthesis for the whole list
    # Actually we need to parse individual rows separated by commas outside quotes/parentheses.
    # We'll parse token by token.
    pass

def parse_rows_simple(sql_content: str):
    """
    Simpler: Split by rows using comma outside quotes, ignoring newlines.
    Use a state machine.
    """
    rows = []
    current = []
    depth = 0  # parenthesis depth
    in_string = False
    quote_char = None
    escape = False
    i = 0
    length = len(sql_content)
    while i < length:
        ch = sql_content[i]
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
        elif ch == ')' and not in_string:
            depth -= 1
            current.append(ch)
            if depth == 0:
                # End of a row (assuming rows are top-level parentheses)
                rows.append(''.join(current))
                current = []
                # skip whitespace and comma
                i += 1
                while i < length and sql_content[i] in (' ', '\t', '\n', '\r', ','):
                    i += 1
                continue
        elif ch == ',' and not in_string and depth == 0:
            # Should not happen because we skip after row
            pass
        else:
            current.append(ch)
        i += 1
    return rows

def main():
    if len(sys.argv) != 3:
        print("Usage: python fix_multiline_booleans.py <input.sql> <output.sql>")
        sys.exit(1)
    
    input_file = sys.argv[1]
    output_file = sys.argv[2]
    
    with open(input_file, 'r', encoding='utf-8') as f:
        content = f.read()
    
    print(f"Read {len(content)} bytes")
    
    # Find the INSERT line and everything before VALUES
    lines = content.split('\n')
    header = []
    rows_start = None
    for i, line in enumerate(lines):
        if line.strip().upper().startswith('INSERT'):
            header = lines[:i+1]
            rows_start = i
            break
    
    if rows_start is None:
        print("INSERT statement not found")
        sys.exit(1)
    
    # Rest of the file is rows
    rows_content = '\n'.join(lines[rows_start:])
    # Find VALUES keyword
    values_idx = rows_content.upper().find('VALUES')
    if values_idx == -1:
        print("VALUES not found")
        sys.exit(1)
    
    insert_header = rows_content[:values_idx + 6]  # include 'VALUES'
    rows_part = rows_content[values_idx + 6:]  # after 'VALUES'
    
    # Now parse rows_part: list of rows separated by commas, ending with semicolon or ON CONFLICT
    # We'll use a state machine to split rows
    rows = []
    current = []
    depth = 0
    in_string = False
    quote_char = None
    escape = False
    i = 0
    length = len(rows_part)
    while i < length:
        ch = rows_part[i]
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
        elif ch == ')' and not in_string:
            depth -= 1
            current.append(ch)
            if depth == 0:
                # End of row
                rows.append(''.join(current))
                current = []
                # skip whitespace and comma
                i += 1
                while i < length and rows_part[i] in (' ', '\t', '\n', '\r', ','):
                    i += 1
                continue
        else:
            current.append(ch)
        i += 1
    
    print(f"Parsed {len(rows)} rows")
    
    # Process each row to fix boolean values
    fixed_rows = []
    for row in rows:
        # Remove surrounding parentheses
        row = row.strip()
        if row.startswith('('):
            row = row[1:]
        if row.endswith(')'):
            row = row[:-1]
        # Split by commas outside quotes
        values = []
        cur = []
        in_str = False
        qchar = None
        esc = False
        j = 0
        while j < len(row):
            c = row[j]
            if esc:
                esc = False
                cur.append(c)
                j += 1
                continue
            if c == '\\':
                esc = True
                cur.append(c)
                j += 1
                continue
            if c in ("'", '"'):
                if not in_str:
                    in_str = True
                    qchar = c
                elif c == qchar:
                    in_str = False
                cur.append(c)
            elif c == ',' and not in_str:
                values.append(''.join(cur).strip())
                cur = []
            else:
                cur.append(c)
            j += 1
        if cur:
            values.append(''.join(cur).strip())
        
        if len(values) == 42:
            for idx in [33, 34, 35, 36, 37]:
                val = values[idx].strip()
                if val == '1':
                    values[idx] = 'TRUE'
                elif val == '0':
                    values[idx] = 'FALSE'
            # Reconstruct row
            fixed_rows.append('(' + ', '.join(values) + ')')
        else:
            # Keep original row if column count mismatch
            sys.stderr.write(f'Warning: row has {len(values)} columns, expected 42\n')
            fixed_rows.append('(' + row + ')')
    
    # Reconstruct SQL
    # Find ON CONFLICT clause at the end of rows_part (after last row)
    # Actually the last row may already have ON CONFLICT appended.
    # We'll check if rows_part contains 'ON CONFLICT'
    on_conflict = ''
    if 'ON CONFLICT' in rows_part:
        # Extract from the occurrence
        idx = rows_part.find('ON CONFLICT')
        on_conflict = rows_part[idx:]
    
    # Build output
    with open(output_file, 'w', encoding='utf-8') as f:
        f.write('\n'.join(header))
        f.write('\n')
        f.write(insert_header)
        f.write('\n')
        # Write rows separated by commas
        for i, row in enumerate(fixed_rows):
            if i == len(fixed_rows) - 1:
                f.write(row + ' ' + on_conflict + '\n')
            else:
                f.write(row + ',\n')
    
    print(f"Output written to {output_file}")

if __name__ == '__main__':
    main()