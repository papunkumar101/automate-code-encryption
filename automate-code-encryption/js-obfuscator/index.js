const express = require('express');
const path = require('path');
const fs = require('fs');
const JavaScriptObfuscator = require('javascript-obfuscator');
const multer = require('multer');

const app = express();
const port = 3005;

app.use(express.urlencoded({ extended: true }));
app.use(express.static(__dirname));

// HTML Interface
app.get('/', (req, res) => {
  res.send(`
    <h2>JavaScript Obfuscator Tool</h2>
    <form method="POST" action="/obfuscate">
      <label>Enter folder path to obfuscate:</label><br/>
      <input type="text" name="folderPath" style="width: 400px" required />
      <br/><br/>
      <button type="submit">Start Obfuscation</button>
    </form>
  `);
});

// Helper: Recursive JS Obfuscation
function obfuscateDirectory(dir, ignoreList = ['node_modules', '.git']) {
  fs.readdirSync(dir).forEach(file => {
    const fullPath = path.join(dir, file);
    const relPath = path.relative(process.cwd(), fullPath);

    if (ignoreList.some(ignore => relPath.includes(ignore))) return;

    const stat = fs.statSync(fullPath);
    if (stat.isDirectory()) {
      obfuscateDirectory(fullPath, ignoreList);
    } else if (stat.isFile() && path.extname(file) === '.js') {
      const code = fs.readFileSync(fullPath, 'utf8');
      const obfuscatedCode = JavaScriptObfuscator.obfuscate(code, {
        compact: true,
        controlFlowFlattening: true,
      }).getObfuscatedCode();
      fs.writeFileSync(fullPath, obfuscatedCode, 'utf8');
      console.log(`✔ Obfuscated: ${relPath}`);
    }
  });
}

// Handle Obfuscation Request
app.post('/obfuscate', (req, res) => {
  const folderPath = req.body.folderPath;

  if (!fs.existsSync(folderPath)) {
    return res.send(`<p style="color:red;">❌ Folder does not exist: ${folderPath}</p><a href="/">Try again</a>`);
  }

  try {
    obfuscateDirectory(folderPath);
    res.send(`<p style="color:green;">✅ Obfuscation completed for: ${folderPath}</p><a href="/">Go back</a>`);
  } catch (err) {
    console.error(err);
    res.send(`<p style="color:red;">❌ Error: ${err.message}</p><a href="/">Try again</a>`);
  }
});

app.listen(port, () => {
  console.log(`✅ Web Obfuscator running at http://localhost:${port}`);
});
