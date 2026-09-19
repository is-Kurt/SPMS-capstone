const fs = require('fs');

['app/Views/document/show.php', 'app/Views/templates/editor.php'].forEach(file => {
    console.log(`Checking ${file}...`);
    const content = fs.readFileSync(file, 'utf8');
    const scripts = content.match(/<script[\s\S]*?<\/script>/gi) || [];
    console.log(`Found ${scripts.length} script blocks.`);
    scripts.forEach((sc, i) => {
        let s = sc.replace(/<script.*?>/i, '').replace(/<\/script>/i, '');
        s = s.replace(/<\?=\s*[\s\S]*?\?>/g, '"php_val"');
        s = s.replace(/<\?php[\s\S]*?\?>/g, '/* php code */');
        try {
            new Function(s);
            console.log(`  Block ${i + 1}: Valid`);
        } catch (e) {
            console.error(`  Block ${i + 1} JS Syntax error:`, e.message);
        }
    });
});

