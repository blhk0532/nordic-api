import ast
import csv
import sys

def parse_row(line):
    """Parse a SQL tuple line into Python tuple."""
    line = line.rstrip().rstrip(',')
    if not line.startswith('(') or not line.endswith(')'):
        return None
    # Replace SQL literals with Python literals
    line = line.replace('NULL', 'None')
    line = line.replace('TRUE', 'True').replace('FALSE', 'False')
    try:
        return ast.literal_eval(line)
    except (SyntaxError, ValueError) as e:
        # fallback: maybe there's a stray backslash
        # Try to fix common issues
        # 1. Replace \' with ''
        if "\\'" in line:
            line = line.replace("\\'", "''")
        # 2. Replace \\' with ''? Not needed
        # Try again
        try:
            return ast.literal_eval(line)
        except:
            # Return None to indicate failure
            return None

def main():
    sql_path = '/home/baba/apps/api/database/imports/ratsit_data_fixed.sql'
    csv_path = '/home/baba/apps/api/database/imports/ratsit_data.csv'
    failed_path = '/home/baba/apps/api/database/imports/failed_rows.txt'
    
    with open(sql_path, 'r', encoding='utf-8') as f_in, \
         open(csv_path, 'w', encoding='utf-8', newline='') as f_csv, \
         open(failed_path, 'w', encoding='utf-8') as f_fail:
        
        writer = csv.writer(f_csv, delimiter=',', quoting=csv.QUOTE_MINIMAL)
        # Skip INSERT line
        first_line = f_in.readline()
        if not first_line.startswith('INSERT'):
            print('Unexpected first line:', first_line[:100])
            sys.exit(1)
        
        total = 0
        success = 0
        fail = 0
        
        for line in f_in:
            line = line.strip()
            if line == ';':
                break
            if not line:
                continue
            total += 1
            parsed = parse_row(line)
            if parsed is None:
                fail += 1
                f_fail.write(f'Line {total}: {line[:200]}\n')
                continue
            # Convert tuple to list, mapping Python types to PostgreSQL CSV representation
            row = []
            for val in parsed:
                if val is None:
                    row.append('\\N')
                elif isinstance(val, bool):
                    row.append('TRUE' if val else 'FALSE')
                elif isinstance(val, (int, float)):
                    row.append(str(val))
                else:
                    # string: need to escape any embedded quotes and backslashes?
                    # CSV writer will handle quoting
                    row.append(val)
            writer.writerow(row)
            success += 1
            
            if total % 10000 == 0:
                print(f'Processed {total} rows, success {success}, fail {fail}')
        
        print(f'Total rows: {total}, success: {success}, failed: {fail}')
        print(f'CSV saved to {csv_path}')
        print(f'Failed rows saved to {failed_path}')

if __name__ == '__main__':
    main()