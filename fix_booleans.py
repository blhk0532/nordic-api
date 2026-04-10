#!/usr/bin/env python3
import sys
import re

def replace_booleans(line: str) -> str:
    # Replace numeric booleans with PostgreSQL boolean literals.
    # We'll replace only when not inside quotes.
    # Simple state machine
    result = []
    in_string = False
    quote_char = None
    i = 0
    while i < len(line):
        ch = line[i]
        if ch == "'" and (i == 0 or line[i-1] != '\\'):
            if not in_string:
                in_string = True
                quote_char = "'"
            elif quote_char == "'":
                in_string = False
        elif ch == '"' and (i == 0 or line[i-1] != '\\'):
            if not in_string:
                in_string = True
                quote_char = '"'
            elif quote_char == '"':
                in_string = False
        if not in_string:
            # Check for pattern , 1, or , 0,
            if ch == ',' and i+2 < len(line):
                # Look ahead for space and digit
                j = i+1
                while j < len(line) and line[j] == ' ':
                    j += 1
                if j < len(line) and line[j] in ('0','1'):
                    # ensure next char is comma or space then comma
                    k = j+1
                    while k < len(line) and line[k] == ' ':
                        k += 1
                    if k < len(line) and line[k] == ',':
                        # replace digit with TRUE/FALSE
                        if line[j] == '1':
                            replacement = 'TRUE'
                        else:
                            replacement = 'FALSE'
                        # replace from i+1 to k (exclusive) with replacement
                        result.append(',')
                        result.append(' ')
                        result.append(replacement)
                        i = k  # skip digit and spaces up to comma
                        continue
        result.append(ch)
        i += 1
    return ''.join(result)

def transform_line(line: str) -> str:
    line = line.replace('`', '"')
    line = re.sub(r'INSERT\s+IGNORE\s+INTO', 'INSERT INTO', line, flags=re.IGNORECASE)
    line = replace_booleans(line)
    return line

def main():
    # Output settings
    sys.stdout.write('SET standard_conforming_strings = off;\n')
    sys.stdout.write('SET escape_string_warning = off;\n')
    for line in sys.stdin:
        sys.stdout.write(transform_line(line))

if __name__ == '__main__':
    main()