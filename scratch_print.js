const fs = require('fs');
const content = fs.readFileSync('resources/views/deductions/index.blade.php', 'utf8');
const match = content.match(/<script>([\s\S]*?)<\/script>/);
if (match) {
    const js = match[1].replace(/\{\{.*?\}\}/g, '"test"').replace(/\{!!.*?!!\}/g, '"test"');
    const lines = js.split('\n');
    lines.forEach((line, idx) => {
        try {
            // try evaluating lines incrementally or print
        } catch(e) {}
    });
    // Print the JS lines with numbers
    console.log(lines.map((l, i) => `${i+1}: ${l}`).join('\n'));
}
