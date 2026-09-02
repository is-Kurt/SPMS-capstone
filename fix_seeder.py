import re

path = '/home/ubuntu/server/html/SPMS-capstone/app/Database/Seeds/MasterSeeder.php'
with open(path, 'r') as f:
    content = f.read()

pattern = r'<td>\&nbsp;</td>\n\s*<td>\&nbsp;</td>\n\s*<td>\&nbsp;</td>\n\s*(<td class="calc-rating")'
replacement = r'<td class="col-target">&nbsp;</td>\n<td class="col-target">&nbsp;</td>\n<td class="col-eval">&nbsp;</td>\n\1'

new_content = re.sub(pattern, replacement, content)

with open(path, 'w') as f:
    f.write(new_content)

print(f"Replaced {len(re.findall(pattern, content))} occurrences.")
