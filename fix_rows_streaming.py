#!/usr/bin/env python3
"""
Streaming fix for boolean values in SQL file.
Processes character by character, identifies rows, fixes boolean values on the fly.
"""

import sys

def fix_row(row: str) -> str:
    """Fix boolean values in a single row string."""
    # Parse the row to get column values
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
        # Reconstruct row
        return '(' + ', '.join(values) + ')'
    else:
        # Column mismatch, return original row (with parentheses)
        sys.stderr.write(f'Warning: row has {len(values)} columns, expected 42\n')
        return '(' + row + ')'

def main():
    if len(sys.argv) != 3:
        print("Usage: python fix_rows_streaming.py <input.sql> <output.sql>")
        sys.exit(1)
    
    input_file = sys.argv[1]
    output_file = sys.argv[2]
    
    with open(input_file, 'r', encoding='utf-8') as inf, open(output_file, 'w', encoding='utf-8') as outf:
        # State variables
        in_string = False
        quote_char = None
        escape = False
        depth = 0  # parenthesis depth
        row_start = None
        row_buffer = []
        outside_buffer = []  # characters outside of rows
        
        def flush_outside():
            if outside_buffer:
                outf.write(''.join(outside_buffer))
                outside_buffer.clear()
        
        # Read file in chunks (1MB) to avoid memory issues while still processing char by char
        chunk_size = 1024 * 1024
        while True:
            chunk = inf.read(chunk_size)
            if not chunk:
                break
            for ch in chunk:
                # Handle escape
                if escape:
                    escape = False
                    if row_start is not None:
                        row_buffer.append(ch)
                    else:
                        outside_buffer.append(ch)
                    continue
                
                if ch == '\\':
                    escape = True
                    if row_start is not None:
                        row_buffer.append(ch)
                    else:
                        outside_buffer.append(ch)
                    continue
                
                # Quote handling
                if ch in ("'", '"'):
                    if not in_string:
                        in_string = True
                        quote_char = ch
                    elif ch == quote_char:
                        in_string = False
                    if row_start is not None:
                        row_buffer.append(ch)
                    else:
                        outside_buffer.append(ch)
                    continue
                
                # Parentheses handling
                if ch == '(' and not in_string:
                    depth += 1
                    if depth == 1:
                        # Start of a row
                        row_start = len(outside_buffer)
                        # Flush outside buffer before row
                        flush_outside()
                        row_buffer.append(ch)
                    else:
                        # Nested parenthesis inside row (should not happen)
                        row_buffer.append(ch)
                elif ch == ')' and not in_string:
                    depth -= 1
                    if row_start is not None:
                        row_buffer.append(ch)
                        if depth == 0:
                            # End of row
                            row_str = ''.join(row_buffer)
                            fixed_row = fix_row(row_str)
                            outf.write(fixed_row)
                            row_start = None
                            row_buffer.clear()
                    else:
                        outside_buffer.append(ch)
                else:
                    if row_start is not None:
                        row_buffer.append(ch)
                    else:
                        outside_buffer.append(ch)
        
        # Flush remaining outside buffer
        flush_outside()
        
        # If row_buffer still has data (malformed), write as-is
        if row_buffer:
            outf.write(''.join(row_buffer))
    
    print(f"Output written to {output_file}")

if __name__ == '__main__':
    main()