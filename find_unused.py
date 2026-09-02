import os
import re
import subprocess

js_dirs = [
    '/home/ubuntu/server/html/SPMS-capstone/public/assets/js/main',
    '/home/ubuntu/server/html/SPMS-capstone/public/assets/js/editor'
]

search_dirs = [
    '/home/ubuntu/server/html/SPMS-capstone/public/assets/js',
    '/home/ubuntu/server/html/SPMS-capstone/app/Views'
]

functions = set()
for d in js_dirs:
    for root, dirs, files in os.walk(d):
        for file in files:
            if file.endswith('.js'):
                path = os.path.join(root, file)
                with open(path, 'r', encoding='utf-8') as f:
                    content = f.read()
                    for match in re.finditer(r'^(?:async )?function\s+([a-zA-Z0-9_]+)\s*\(', content, re.MULTILINE):
                        functions.add(match.group(1))
                    for match in re.finditer(r'^class\s+([a-zA-Z0-9_]+)\s*(?:extends [a-zA-Z0-9_]+)?\s*\{', content, re.MULTILINE):
                        functions.add(match.group(1))

print(f"Found {len(functions)} functions/classes.")

for func in sorted(functions):
    # Search for usage outside of the definition
    cmd = ['grep', '-r', '-w', func] + search_dirs
    result = subprocess.run(cmd, stdout=subprocess.PIPE, text=True)
    lines = result.stdout.strip().split('\n')
    
    # Filter out definition lines (e.g. 'function foo(' or 'class Foo ')
    usage_count = 0
    for line in lines:
        if not line: continue
        parts = line.split(':', 1)
        if len(parts) == 2:
            code = parts[1].strip()
            if re.match(rf'^(async\s+)?function\s+{func}\s*\(', code) or re.match(rf'^class\s+{func}\s*(extends\s+\w+\s*)?\{{', code):
                continue
            usage_count += 1
            
    if usage_count == 0:
        print(f"UNUSED: {func}")

