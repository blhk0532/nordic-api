#!/usr/bin/env python3
import sys
import re

def transform_line(line: str) -> str:
    # Replace backticks with double quotes
    line = line.replace('`', '"')
    # Remove IGNORE keyword from INSERT IGNORE INTO
    line = re.sub(r'INSERT\s+IGNORE\s+INTO', 'INSERT INTO', line, flags=re.IGNORECASE)
    return line

def main():
    batch_size = 1000
    rows = []
    columns = None
    insert_prefix = None
    for line in sys.stdin:
        line = transform_line(line)
        # Check if line is the INSERT ... VALUES line
        if line.strip().upper().startswith('INSERT INTO'):
            # This line contains column list and VALUES keyword
            # We'll keep the entire line up to VALUES, store as prefix
            # Actually we need to split at 'VALUES' to get column list
            # For simplicity, we'll keep the whole line as prefix and treat rows separately
            # But we need to extract the part before VALUES and after VALUES?
            # The line may end with VALUES and newline, then rows follow.
            # In our file, INSERT line ends with VALUES and newline, rows on subsequent lines.
            # So we can output this line later when we have rows.
            # We'll store the prefix (including column list) up to VALUES
            # Let's find the word VALUES (case insensitive)
            # We'll split at VALUES, keep left part as prefix, ignore right part (empty)
            match = re.search(r'VALUES\s*$', line, re.IGNORECASE)
            if match:
                insert_prefix = line.rstrip()  # includes VALUES
                # We'll later output insert_prefix + rows
                # But we need to remove VALUES from prefix? Actually we need to output rows after VALUES.
                # Let's keep prefix as line without VALUES? Simpler: keep whole line, and rows will be appended later.
                # We'll treat rows as separate lines, we'll accumulate them.
                # The line ends with VALUES, so we just continue.
                continue
            else:
                # INSERT line includes VALUES and maybe some rows inline; not expected
                pass
        # Check if line is a row line: starts with '(' and ends with '),' or ');'
        if line.strip().startswith('('):
            # Remove trailing comma or semicolon and whitespace
            row_line = line.rstrip().rstrip(',')
            # If ends with semicolon, remove it and mark as last row
            if row_line.endswith(';'):
                row_line = row_line[:-1]
                rows.append(row_line)
                # flush batch
                if rows:
                    # Output INSERT statement with prefix
                    # We need to reconstruct the INSERT line without VALUES? Actually we need to output the original INSERT line with VALUES and rows.
                    # Since we have removed VALUES from the original line, we need to output insert_prefix + ' ' + rows joined by commas + ';'
                    # But we have batch size limit.
                    # Let's output in batches of batch_size
                    # We'll process batches after reading all rows? That's memory heavy.
                    # Instead we'll accumulate rows and flush when batch size reached.
                    pass
                # We'll handle later
            else:
                rows.append(row_line)
                if len(rows) >= batch_size:
                    # Output batch
                    if insert_prefix is None:
                        # Should have been set
                        sys.stderr.write('Error: INSERT prefix not found\n')
                        sys.exit(1)
                    # Output insert_prefix, then rows, then semicolon
                    sys.stdout.write(insert_prefix + '\n')
                    sys.stdout.write(',\n'.join(rows) + ';\n')
                    rows = []
        # Other lines (maybe empty) ignore

    # After processing all lines, flush remaining rows
    if rows and insert_prefix:
        sys.stdout.write(insert_prefix + '\n')
        sys.stdout.write(',\n'.join(rows) + ';\n')

if __name__ == '__main__':
    main()