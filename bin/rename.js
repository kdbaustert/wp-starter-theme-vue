#!/usr/bin/env node
// Rebrand the theme for a new project: theme header, text domain, block
// namespace, package names. Safe to rerun; the current slug is read from the
// Text Domain line in style.css.
//
//   npm run rename
//   npm run rename -- --name "Acme" --slug acme --author "Acme Ltd"

import fs from 'node:fs'
import path from 'node:path'
import readline from 'node:readline/promises'
import { parseArgs } from 'node:util'

const root = path.resolve(import.meta.dirname, '..')

const fields = {
  name: { label: 'Theme name', header: 'Theme Name' },
  slug: { label: 'Slug (folder, text domain, block namespace)' },
  description: { label: 'Description', header: 'Description' },
  author: { label: 'Author', header: 'Author' },
  'author-uri': { label: 'Author URI', header: 'Author URI' },
  uri: { label: 'Theme URI', header: 'Theme URI' },
}

// Source files that can hold the text domain or block namespace.
const sources = ['functions.php', 'index.php', 'app', 'resources/views', 'resources/blocks', 'resources/js']
const extensions = ['.php', '.js', '.jsx', '.json']

const read = file => fs.readFileSync(path.join(root, file), 'utf8')
const write = (file, contents) => fs.writeFileSync(path.join(root, file), contents)
const escape = value => value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
const slugify = value =>
  value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '')

function header(css, key) {
  return css.match(new RegExp(`^${escape(key)}:\\s*(.*)$`, 'm'))?.[1].trim() ?? ''
}

function files(entry) {
  const full = path.join(root, entry)
  if (!fs.existsSync(full)) return []
  if (fs.statSync(full).isFile()) return [entry]

  return fs.readdirSync(full).flatMap(child => {
    const relative = path.join(entry, child)
    if (fs.statSync(path.join(root, relative)).isDirectory()) return files(relative)
    return extensions.includes(path.extname(child)) ? [relative] : []
  })
}

async function collect(defaults) {
  const { values } = parseArgs({
    options: Object.fromEntries(Object.keys(fields).map(key => [key, { type: 'string' }])),
  })

  const missing = Object.keys(fields).filter(key => values[key] === undefined)
  if (!missing.length || !process.stdin.isTTY) return { ...defaults, ...values }

  const rl = readline.createInterface({ input: process.stdin, output: process.stdout })
  for (const key of missing) {
    const answer = await rl.question(`${fields[key].label} [${defaults[key]}]: `)
    values[key] = answer.trim() || defaults[key]
  }
  rl.close()

  return { ...defaults, ...values }
}

const css = read('style.css')
const current = header(css, 'Text Domain')

const answers = await collect({
  ...Object.fromEntries(
    Object.entries(fields)
      .filter(([, field]) => field.header)
      .map(([key, field]) => [key, header(css, field.header)])
  ),
  slug: slugify(path.basename(root)),
})

if (!/^[a-z0-9]+(-[a-z0-9]+)*$/.test(answers.slug)) {
  console.error(`"${answers.slug}" is not a valid slug: use lowercase letters, numbers and hyphens.`)
  process.exit(1)
}

// style.css header
let theme = css
for (const [key, field] of Object.entries(fields)) {
  if (!field.header) continue
  theme = theme.replace(new RegExp(`^(${escape(field.header)}:\\s*).*$`, 'm'), `$1${answers[key]}`)
}
theme = theme.replace(/^(Text Domain:\s*).*$/m, `$1${answers.slug}`)
write('style.css', theme)

// Text domain in __() calls and block.json, block namespace in block.json
const domain = new RegExp(`(['"])${escape(current)}\\1`, 'g')
const namespace = new RegExp(`"${escape(current)}/`, 'g')
let changed = 0

for (const file of sources.flatMap(files)) {
  const before = read(file)
  const after = before.replace(domain, `$1${answers.slug}$1`).replace(namespace, `"${answers.slug}/`)
  if (after === before) continue
  write(file, after)
  changed++
}

// package.json and composer.json
const pkg = JSON.parse(read('package.json'))
pkg.name = answers.slug
for (const [script, command] of Object.entries(pkg.scripts)) {
  pkg.scripts[script] = command.replaceAll(`/${current}.pot`, `/${answers.slug}.pot`)
}
write('package.json', `${JSON.stringify(pkg, null, 2)}\n`)

const composer = JSON.parse(read('composer.json'))
composer.name = `${slugify(answers.author) || answers.slug}/${answers.slug}`
composer.description = answers.description
composer.homepage = answers.uri
write('composer.json', `${JSON.stringify(composer, null, 2)}\n`)

console.log(`\nRenamed "${current}" to "${answers.slug}" (${changed} source files updated).`)
if (path.basename(root) !== answers.slug) {
  console.log(`Rename the theme folder to "${answers.slug}" so it matches the text domain.`)
}
