#!/usr/bin/env python3
"""
Fix boolean values (1/0 -> TRUE/FALSE) and add ON CONFLICT clause to transformed SQL file.
Input: transformed SQL file (already has backticks replaced and IGNORE removed).
Output: final SQL file ready for PostgreSQL import.
"""

import sys
import re

def process_file(input_path: str, output_path: str):
    with open(input_path, 'r', encoding='utf-8') as inf, open(output_path, 'w', encoding='utf-8') as outf:
        # Read entire file as lines
        lines = inf.readlines()
        
        # Process each line, looking for INSERT statement and rows
        insert_started = False
        insert_lines = []
        other_lines = []
        
        for line in lines:
            stripped = line.strip()
            # Detect INSERT INTO line (case-insensitive)
            if re.match(r'INSERT\s+INTO', stripped, re.IGNORECASE):
                insert_started = True
                # We'll collect this line and subsequent row lines
                # But we need to split the line because VALUES may be on same line
                # For simplicity, we'll treat the whole INSERT as one block.
                # Instead, we'll process the file by scanning for rows and modifying them in place.
                # Let's change approach: write lines as we go, modifying row lines.
                pass
            # We'll handle inline below
        
        # Since the file is huge, we need to process streaming.
        # Let's rewind and process streaming.
    
    # Actually, let's do simpler: read file, find INSERT line, then process each row line.
    # Because file is large, we need to stream.
    with open(input_path, 'r', encoding='utf-8') as inf, open(output_path, 'w', encoding='utf-8') as outf:
        for line in inf:
            # Replace boolean values in rows that start with '('
            if line.strip().startswith('('):
                # This is a row line
                # Need to replace 1/0 at positions of boolean columns.
                # Since columns count is fixed, we can use regex to replace patterns like
                # ", 1," or ", 0," but careful with JSON values.
                # Better: split by commas but respect quoted strings and JSON.
                # We'll reuse split_row from convert_full.py but simplified.
                # Let's implement a state machine.
                new_line = transform_row_line(line)
                outf.write(new_line)
            else:
                # Write line as-is
                outf.write(line)

def transform_row_line(line: str) -> str:
    """
    Transform a single row line, converting boolean values 1/0 to TRUE/FALSE.
    Preserve trailing comma or semicolon.
    """
    # Remove trailing newline and possible comma/semicolon
    original_line = line.rstrip('\n')
    trailing = ''
    if original_line.endswith(';'):
        trailing = ';'
        original_line = original_line[:-1]
    elif original_line.endswith(','):
        trailing = ','
        original_line = original_line[:-1]
    
    # Now original_line is something like "(26, 'Muggehult 19', ..., 1, 0, 1, 1, 0, ...)"
    # We'll parse the tuple, but we need to handle nested commas inside JSON.
    # Instead, we can use a simple approach: locate the last five numeric values before
    # the timestamp columns? That's risky.
    # Let's assume the structure is consistent: 42 columns.
    # We'll split by commas ignoring those inside quotes and brackets? This is complex.
    # Given time, we'll do a hack: replace " 1," with " TRUE," and " 0," but only for the
    # boolean columns. Since boolean columns are at positions 34-38 (1-indexed) and
    # are followed by a comma and then a timestamp or another number.
    # We'll use regex to replace the nth occurrence of a pattern.
    # We'll count commas from the start of the line.
    # Let's implement a simple parser that splits by commas but skips inside quotes and brackets.
    # We'll reuse the split_row function from convert_full.py by importing it.
    # Instead, copy the function here.
    values = split_row(original_line)
    if len(values) == 42:
        # positions 33-37 (0-indexed)
        for idx in [33, 34, 35, 36, 37]:
            val = values[idx].strip()
            if val == '1':
                values[idx] = 'TRUE'
            elif val == '0':
                values[idx] = 'FALSE'
        # Reconstruct row
        new_row = '(' + ', '.join(values) + ')'
    else:
        # Fallback: try regex replacement on the whole line for patterns
        # that match the boolean columns (after the 33rd comma).
        # For safety, we'll keep original line.
        new_row = original_line
        sys.stderr.write(f'Warning: row has {len(values)} columns, expected 42\n')
    
    return new_row + trailing + '\n'

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

def main():
    if len(sys.argv) != 3:
        print("Usage: python fix_boolean_and_conflict.py <input.sql> <output.sql>")
        sys.exit(1)
    input_file = sys.argv[1]
    output_file = sys.argv[2]
    
    # Also add ON CONFLICT clause to the INSERT statement.
    # We'll read the file, find the INSERT line, and append ON CONFLICT before semicolon.
    with open(input_file, 'r', encoding='utf-8') as inf, open(output_file, 'w', encoding='utf-8') as outf:
        lines = inf.readlines()
        # We'll process row lines as we go, and also modify the final INSERT line.
        # Find the line that contains "INSERT INTO"
        for i, line in enumerate(lines):
            if re.match(r'INSERT\s+INTO', line.strip(), re.IGNORECASE):
                # This is the INSERT line; we'll write it as-is but later need to add ON CONFLICT
                # after all rows, before the semicolon.
                # Since the semicolon appears at the end of the last row line, we need to handle that.
                # Instead, we'll collect rows and reconstruct.
                # Given the file size, we'll just write the INSERT line, then process rows,
                # and when we encounter a line ending with ';', we add ON CONFLICT before the semicolon.
                # Let's do a second pass: write all lines, but when we see a line ending with ';',
                # we replace ';' with ' ON CONFLICT (adress, fornamn, efternamn) DO NOTHING;'
                # However, there might be multiple INSERT statements; we assume only one.
                pass
        
        # Simpler: read entire file as string, replace the final semicolon after INSERT.
        # But file is huge (356 MB), might be okay in memory? Possibly.
        # Let's try streaming with state.
    
    # Let's implement streaming with state.
    with open(input_file, 'r', encoding='utf-8') as inf, open(output_file, 'w', encoding='utf-8') as outf:
        for line in inf:
            # Check if line ends with ';' and previous line was INSERT start?
            # We'll just write line, and after writing the entire file, we need to modify the last line.
            # We'll buffer the last line? Instead, we'll write all lines to a list, but that's memory heavy.
            # Let's just write lines, and after loop, we can't modify already written lines.
            # So we need to detect when we are at the last line (the one with semicolon).
            # We'll read the file twice: first to count lines? Not good.
            # Let's assume the file ends with a row line that ends with ';' and there is only one INSERT.
            # We'll keep track of whether we've seen INSERT line, and when we see a line ending with ';',
            # we replace the ';' with conflict clause.
            pass
    
    # Given complexity, let's do a simpler approach: use the existing transformed file,
    # run a sed-like replacement for boolean values using regex that matches the specific positions.
    # We'll write a separate script that uses awk or Python with careful parsing.
    # Let's write a robust parser using Python's csv module? Not CSV.
    # I'll implement a finite-state parser that streams.
    
    print("Not implemented yet")

if __name__ == '__main__':
    main()