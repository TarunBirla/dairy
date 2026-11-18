const fs = require('fs');
const vm = require('vm');
const content = fs.readFileSync('resources/views/deductions/index.blade.php', 'utf8');
const match = content.match(/<script>([\s\S]*?)<\/script>/);
let js = match[1].replace('"{{ route(\'deductions.store\') }}"', '"/deductions"');

try {
    new vm.Script(js, { filename: 'deductions_script.js' });
    console.log("SUCCESS: Script compiled cleanly!");
} catch (e) {
    console.error("Syntax Error:", e);
}
