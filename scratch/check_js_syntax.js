const fs = require('fs');
const html = fs.readFileSync(__dirname + '/doc.html', 'utf8');

const regex = /<script(?![^>]*src=)[^>]*>([\s\S]*?)<\/script>/gi;
let match;
let i = 0;
while ((match = regex.exec(html)) !== null) {
    i++;
    const code = match[1];
    try {
        new Function(code);
        console.log(`Script ${i}: syntax OK (length: ${code.length})`);
    } catch (e) {
        console.error(`Script ${i}: SYNTAX ERROR:`, e.message);
    }
}
