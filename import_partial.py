#!/usr/bin/env python3
import sys
import re

def transform_line(line: str) -> str:
    line = line.replace('`', '"')
    line = re.sub(r'INSERT\s+IGNORE\s+INTO', 'INSERT INTO', line, flags=re.IGNORECASE)
    return line

def main():
    max_rows = 1000
    rows = []
    insert_prefix = None
    row_count = 0
    for line in sys.stdin:
        line = transform_line(line)
        if line.strip().upper().startswith('INSERT INTO'):
            # store prefix
            insert_prefix = line.rstrip()
            continue
        if line.strip().startswith('('):
            row_count += 1
            if row_count > max_rows:
                # end with semicolon
                rows[-1] = rows[-1].rstrip(',') + ';'
                break
            # remove trailing comma/semicolon
            row = line.rstrip().rstrip(',')
            if row.endswith(';'):
                row = row[:-1]
            rows.append(row)
            # if original line ended with semicolon, that's last row
            if line.rstrip().endswith(';'):
                # end of file
                break
    # Output
    if insert_prefix and rows:
        sys.stdout.write(insert_prefix + '\n')
        sys.stdout.write(',\n'.join(rows) + ';\n')

if __name__ == '__main__':
    main()