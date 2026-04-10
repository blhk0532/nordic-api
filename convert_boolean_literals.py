#!/usr/bin/env python3
"""
Convert boolean literals TRUE/FALSE to integers 1/0 in SQL file.
"""

import sys
import re

def convert_line(line: str) -> str:
    # Replace TRUE with 1, FALSE with 0, but only outside quotes.
    # Simple approach: replace standalone words TRUE/FALSE that are not inside quotes.
    # We'll use a state machine for this line only (assuming quotes don't span lines).
    result = []
    in_string = False
    quote_char = None
    escape = False
    i = 0
    while i < len(line):
        ch = line[i]
        if escape:
            escape = False
            result.append(ch)
            i += 1
            continue
        if ch == '\\':
            escape = True
            result.append(ch)
            i += 1
            continue
        if ch in ("'", '"'):
            if not in_string:
                in_string = True
                quote_char = ch
            elif ch == quote_char:
                in_string = False
            result.append(ch)
            i += 1
            continue
        if not in_string:
            # Check for TRUE
            if line[i:i+4] == 'TRUE' and (i+4 == len(line) or not line[i+4].isalnum()):
                result.append('1')
                i += 4
                continue
            # Check for FALSE
            if line[i:i+5] == 'FALSE' and (i+5 == len(line) or not line[i+5].isalnum()):
                result.append('0')
                i += 5
                continue
        result.append(ch)
        i += 1
    return ''.join(result)

def main():
    if len(sys.argv) != 3:
        print("Usage: python convert_boolean_literals.py <input.sql> <output.sql>")
        sys.exit(1)
    
    input_file = sys.argv[1]
    output_file = sys.argv[2]
    
    with open(input_file, 'r', encoding='utf-8') as inf, open(output_file, 'w', encoding='utf-8') as outf:
        for line in inf:
            outf.write(convert_line(line))
    
    print(f"Converted file written to {output_file}")

if __name__ == '__main__':
    main()