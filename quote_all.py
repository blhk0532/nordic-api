import csv
import sys

input_path = '/home/baba/apps/api/database/imports/ratsit_data.csv'
output_path = '/home/baba/apps/api/database/imports/ratsit_data_quoted.csv'

with open(input_path, 'r', encoding='utf-8') as f_in, \
     open(output_path, 'w', encoding='utf-8', newline='') as f_out:
    reader = csv.reader(f_in)
    writer = csv.writer(f_out, quoting=csv.QUOTE_NONNUMERIC, delimiter=',')
    for row in reader:
        # Replace '\N' with empty string (NULL)
        new_row = ['' if col == '\\N' else col for col in row]
        writer.writerow(new_row)

print(f'Quoted CSV written to {output_path}')