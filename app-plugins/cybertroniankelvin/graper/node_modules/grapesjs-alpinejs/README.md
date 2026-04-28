# GrapesJS AlpineJS

A GrapesJS plugin that seamlessly integrates Alpine.js directives into the visual editor, allowing you to build interactive components without writing code.

### Demo

> **Try it live!** [Demo Link](##)

![Preview](https://raw.githubusercontent.com/a-hakim/grapesjs-alpinejs/main/preview.png)

### Quick Start

```html
<link href="https://unpkg.com/grapesjs/dist/css/grapes.min.css" rel="stylesheet">
<script src="https://unpkg.com/grapesjs"></script>
<script src="https://unpkg.com/grapesjs-alpinejs"></script>

<div id="gjs"></div>

<script>
const editor = grapesjs.init({
  container: '#gjs',
  height: '100%',
  fromElement: true,
  storageManager: false,
  plugins: ['grapesjs-alpinejs'],
  pluginsOpts: {
    'grapesjs-alpinejs': {
      alpineCdn: 'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js'
    }
  }
});
</script>
```

## Features

Alpine.js Traits** - Add Alpine.js directives to any component via the Trait Manager
Live Preview** - See Alpine.js interactivity work directly in the canvas
**Clean Export** - Export fully functional HTML with Alpine.js directives

## Summary

* Plugin name: `grapesjs-alpinejs`
* Alpine.js Traits (available for all components)
  * `x-data` - Define reactive data scope
  * `x-show` - Toggle visibility
  * `x-if` - Conditional rendering
  * `x-on:click` - Click event handler
  * `x-on:submit` - Submit event handler
  * `x-bind:class` - Dynamic class binding
  * `x-bind:disabled` - Dynamic disabled state
  * `x-model` - Two-way data binding
  * `x-for` - List rendering
  * `x-text` - Text content binding
  * `x-html` - HTML content binding
  * `x-transition` - Transition effects

## Options


| Option          | Description                          | Default                                                       |
| ----------------- | -------------------------------------- | --------------------------------------------------------------- |
| `alpineCdn`     | CDN URL for Alpine.js library        | `https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js` |
| `categoryLabel` | Label for Alpine.js trait category   | `Alpine.js`                                                   |
| `i18n`          | Custom internationalization messages | `{}`                                                          |

## Download

* CDN
  * `https://unpkg.com/grapesjs-alpinejs`
* NPM
  * `npm i grapesjs-alpinejs`
* GIT
  * `git clone https://github.com/a-hakim/grapesjs-alpinejs.git`

## Usage

### Browser

```html
<link href="https://unpkg.com/grapesjs/dist/css/grapes.min.css" rel="stylesheet"/>
<script src="https://unpkg.com/grapesjs"></script>
<script src="path/to/grapesjs-alpinejs.min.js"></script>

<div id="gjs"></div>

<script type="text/javascript">
  var editor = grapesjs.init({
      container: '#gjs',
      height: '100%',
      fromElement: true,
      storageManager: false,
      plugins: ['grapesjs-alpinejs'],
      pluginsOpts: {
        'grapesjs-alpinejs': {
          // Alpine.js CDN URL (Alpine.js 3.x)
          alpineCdn: 'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js',
          // Category labels
          categoryLabel: 'Interactivity (Alpine.js)',
          // Trait labels
          traitLabels: {
            'x-data': 'Data',
            'x-show': 'Show',
            'x-if': 'If',
            'x-on:click': 'Click',
            'x-on:submit': 'Submit',
            'x-bind:class': 'Bind Class',
            'x-bind:disabled': 'Bind Disabled',
            'x-model': 'Model',
            'x-for': 'For',
            'x-text': 'Text',
            'x-html': 'HTML',
            'x-transition': 'Transition',
          },
        }
      }
  });
</script>
```

### Modern JavaScript

```js
import grapesjs from 'grapesjs';
import plugin from 'grapesjs-alpinejs';
import 'grapesjs/dist/css/grapes.min.css';

const editor = grapesjs.init({
  container : '#gjs',
  height: '100%',
  fromElement: true,
  storageManager: false,
  plugins: [plugin],
  pluginsOpts: {
    [plugin]: {
      alpineCdn: 'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js'
    }
  }
});
```

## How to Use


### 1. Adding Alpine.js Directives to Any Component

1. Select any component in the canvas
2. Open the Trait Manager panel
3. Find the "Alpine.js" category
4. Add any Alpine.js directive (x-data, x-show, x-on:click, etc.)
5. See the interactivity work in real-time!

### 2. Exporting Your Work

When you export your HTML:

- All Alpine.js directives are included in the HTML attributes
- Remember to include Alpine.js in your final webpage:

```html
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
```

## Examples

### Creating a Toggle Button

1. Add a button to the canvas
2. Add a div below it
3. On the parent container, add `x-data` trait: `{ visible: false }`
4. On the button, add `x-on:click` trait: `visible = !visible`
5. On the div, add `x-show` trait: `visible`
6. Done! Click the button to toggle the div

### Creating a Form with Two-way Binding

1. Add a form with an input and a paragraph
2. On the form, add `x-data` trait: `{ name: '' }`
3. On the input, add `x-model` trait: `name`
4. On the paragraph, add `x-text` trait: `'Hello ' + name`
5. Type in the input to see real-time updates!

## Development

Clone the repository

```sh
$ git clone https://github.com/a-hakim/grapesjs-alpinejs.git
$ cd grapesjs-alpinejs
```

Install dependencies

```sh
$ npm i
```

Start the dev server

```sh
$ npm start
```

Build the source

```sh
$ npm run build
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

MIT

## Credits

Built by [A.Hakim](https://github.com/a-hakim/) with ❤️ using [GrapesJS](https://grapesjs.com/) and [Alpine.js](https://alpinejs.dev/)
