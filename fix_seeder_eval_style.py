import re

path = '/home/ubuntu/server/html/SPMS-capstone/app/Database/Seeds/MasterSeeder.php'
with open(path, 'r') as f:
    content = f.read()

pattern = r'<td class="col-eval">\&nbsp;</td>'
replacement = r'<td class="col-eval" style="background-color: rgba(59, 130, 246, 0.15);">\&nbsp;</td>'

matches = len(re.findall(pattern, content))
new_content = re.sub(pattern, replacement, content)

with open(path, 'w') as f:
    f.write(new_content)

print(f"Replaced {matches} occurrences.")
