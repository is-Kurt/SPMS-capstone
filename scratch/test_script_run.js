const fs = require('fs');
const html = fs.readFileSync(__dirname + '/doc.html', 'utf8');

const regex = /<script(?![^>]*src=)[^>]*>([\s\S]*?)<\/script>/gi;
let match;
let scripts = [];
while ((match = regex.exec(html)) !== null) {
    scripts.push(match[1]);
}

const vm = require('vm');

const elements = {};

class MockNode {
    constructor(tag, id = '') {
        this.tagName = tag.toUpperCase();
        this.id = id;
        this.className = '';
        this.children = [];
        this.value = '';
        this.innerText = '';
        this.innerHTML = '';
        this.style = {};
        this.classList = {
            add: (c) => { if (!this.className.includes(c)) this.className += ' ' + c; },
            remove: (c) => { this.className = this.className.replace(c, '').trim(); },
            contains: (c) => this.className.includes(c)
        };
        this.dataset = {};
    }

    appendChild(child) {
        this.children.push(child);
        child.parentNode = this;
    }

    remove() {
        if (this.parentNode) {
            const idx = this.parentNode.children.indexOf(this);
            if (idx !== -1) this.parentNode.children.splice(idx, 1);
        }
    }

    querySelectorAll(sel) {
        const results = [];
        const isClass = sel.startsWith('.');
        const targetClass = isClass ? sel.substring(1) : '';
        const isTag = !isClass && !sel.startsWith('#');

        const traverse = (node) => {
            for (const c of node.children) {
                if (isClass && c.className && c.className.split(' ').includes(targetClass)) {
                    results.push(c);
                } else if (isTag && c.tagName.toLowerCase() === sel.toLowerCase()) {
                    results.push(c);
                }
                traverse(c);
            }
        };
        traverse(this);
        return results;
    }

    querySelector(sel) {
        const all = this.querySelectorAll(sel);
        return all.length > 0 ? all[0] : null;
    }

    closest(sel) {
        let p = this.parentNode;
        while (p) {
            if (sel.startsWith('.') && p.className.includes(sel.substring(1))) return p;
            if (p.tagName.toLowerCase() === sel.toLowerCase()) return p;
            p = p.parentNode;
        }
        return null;
    }

    addEventListener() {}
    setAttribute(k, v) { this[k] = v; }
    getAttribute(k) { return this[k] || null; }
}

function getEl(id) {
    if (!elements[id]) {
        elements[id] = new MockNode('div', id);
    }
    return elements[id];
}

const rootDoc = new MockNode('html');
const body = new MockNode('body', 'body');
rootDoc.appendChild(body);

const pf = new MockNode('article', 'printable-form');
body.appendChild(pf);
const tbodyCore = new MockNode('tbody', 'tbody-core');
pf.appendChild(tbodyCore);
const tbodyStrat = new MockNode('tbody', 'tbody-strategic');
pf.appendChild(tbodyStrat);
const tbodySupp = new MockNode('tbody', 'tbody-support');
pf.appendChild(tbodySupp);

elements['printable-form'] = pf;
elements['tbody-core'] = tbodyCore;
elements['tbody-strategic'] = tbodyStrat;
elements['tbody-support'] = tbodySupp;

const sandbox = {
    console: console,
    document: {
        getElementById: (id) => getEl(id),
        querySelectorAll: (sel) => {
            const parts = sel.split(',').map(s => s.trim());
            const res = [];
            for (const p of parts) {
                res.push(...rootDoc.querySelectorAll(p));
            }
            return res;
        },
        querySelector: (sel) => rootDoc.querySelector(sel),
        createElement: (tag) => new MockNode(tag),
        addEventListener: (event, cb) => {
            if (event === 'DOMContentLoaded') {
                sandbox._domContentLoadedCb = cb;
            }
        },
        body: body
    },
    addEventListener: () => {},
    location: { search: '', reload: () => {} },
    URLSearchParams: class { get() { return null; } },
    tinymce: { get: () => null },
    setTimeout: (cb) => { try { cb(); } catch(e) {} },
    clearTimeout: () => {},
    site_url: (s) => s,
    base_url: (s) => s,
    prompt: () => '',
    AppState: { isDirty: false, setDirty: (v) => {} }
};
sandbox.window = sandbox;
sandbox.globalThis = sandbox;

const context = vm.createContext(sandbox);

vm.runInContext(scripts[6], context);
vm.runInContext(scripts[8], context);

if (sandbox._domContentLoadedCb) {
    sandbox._domContentLoadedCb();
}

console.log("After load: categories.core count =", vm.runInContext('tabs[0]?.formData?.categories?.core?.length', context));

// Test 1: User switches tab to 'rubrics-tab'
console.log("\nSwitching tab to 'rubrics-tab'...");
try {
    vm.runInContext("switchEditorTab('rubrics-tab')", context);
    console.log("After switchEditorTab('rubrics-tab'), categories.core count =", vm.runInContext('tabs[0]?.formData?.categories?.core?.length', context));
    console.log("Now activeTabId =", vm.runInContext('activeTabId', context));
    console.log("Now window.isSpmsFormActive =", vm.runInContext('window.isSpmsFormActive', context));
    
    // Now call syncSpmsActiveTab while on rubrics-tab
    console.log("\nCalling syncSpmsActiveTab() while on rubrics-tab...");
    vm.runInContext("window.syncSpmsActiveTab()", context);
    console.log("After syncSpmsActiveTab(), categories.core count =", vm.runInContext('tabs[0]?.formData?.categories?.core?.length', context));
} catch(e) {
    console.error("Error switching tab:", e);
}
