#!/usr/bin/env python3
import sys
import os

def main():
    input_file = 'database/imports/sweden_personer_integer_bools.sql'
    output_dir = 'database/imports/batches'
    
    os.makedirs(output_dir, exist_ok=True)
    
    # Read all lines
    print("Reading input file...")
    with open(input_file, 'r', encoding='utf-8') as f:
        lines = f.readlines()
    
    # First 3 lines: SET, SET, INSERT ... VALUES
    header = lines[:3]
    rows = lines[3:]  # each row line
    
    total_rows = len(rows)
    print(f"Total rows: {total_rows}")
    
    # Determine batch size (10 batches)
    batch_count = 10
    rows_per_batch = total_rows // batch_count
    remainder = total_rows % batch_count
    
    print(f"Splitting into {batch_count} batches of ~{rows_per_batch} rows each")
    
    start = 0
    for batch_num in range(1, batch_count + 1):
        # Determine rows for this batch
        if batch_num <= remainder:
            batch_size = rows_per_batch + 1
        else:
            batch_size = rows_per_batch
        
        if batch_size == 0:
            continue
        
        end = start + batch_size
        batch_rows = rows[start:end]
        
        output_file = os.path.join(output_dir, f'sweden_personer_batch_{batch_num}.sql')
        print(f"Writing batch {batch_num}: rows {start+1} to {end} -> {output_file}")
        
        with open(output_file, 'w', encoding='utf-8') as f:
            # Write header
            f.writelines(header)
            # Write rows
            for i, row in enumerate(batch_rows):
                if i == len(batch_rows) - 1:
                    # Last row of batch: ensure it has ON CONFLICT clause
                    row = row.rstrip()
                    if row.endswith(','):
                        row = row[:-1]
                    # Remove any existing ON CONFLICT clause (should not exist)
                    if 'ON CONFLICT' in row:
                        # Split at ON CONFLICT
                        idx = row.find('ON CONFLICT')
                        row = row[:idx].rstrip()
                    # Add ON CONFLICT clause and semicolon
                    row += ' ON CONFLICT (adress, fornamn, efternamn) DO NOTHING;'
                    f.write(row + '\n')
                else:
                    # Ensure row ends with comma (should already)
                    row = row.rstrip()
                    if not row.endswith(','):
                        row += ','
                    f.write(row + '\n')
        
        start = end
    
    print("Batch files created successfully.")

if __name__ == '__main__':
    main()