import grapesjs from 'grapesjs';
import tailwindPlugin from 'grapesjs-tailwind';
import grapesjsBlocksBasic from 'grapesjs-blocks-basic';
import grapesjsPluginForms from 'grapesjs-plugin-forms';
import grapesjsNavbar from 'grapesjs-navbar';
import grapesjsComponentCountdown from 'grapesjs-component-countdown';
import grapesjsTabs from 'grapesjs-tabs';
import grapesjsTooltip from 'grapesjs-tooltip';
import grapesjsCustomCode from 'grapesjs-custom-code';
import grapesjsTouch from 'grapesjs-touch';
import grapesjsTyped from 'grapesjs-typed';
import grapesjsStyleGradient from 'grapesjs-style-gradient';
import grapesjsParserPostcss from 'grapesjs-parser-postcss';
import grapesjsPluginExport from 'grapesjs-plugin-export';
import grapesjsTuiImageEditor from 'grapesjs-tui-image-editor';
import grapesjsComponentCodeEditor from 'grapesjs-component-code-editor';
import grapesjsPresetWebpage from 'grapesjs-preset-webpage';
import grapesjsUiSuggestClasses from 'grapesjs-ui-suggest-classes';
import grapesjsStyleFilter from 'grapesjs-style-filter';
import grapesjsStyleBg from 'grapesjs-style-bg';
import grapesjsBlocksFlexbox from 'grapesjs-blocks-flexbox';
import grapesjsPluginCkeditor from 'grapesjs-plugin-ckeditor';
import grapesjsLorySlider from 'grapesjs-lory-slider';
import grapesjsRulers from 'grapesjs-rulers';
import grapesjsTemplates from 'grapesjs-templates';
import grapesjsUserBlocks from 'grapesjs-user-blocks';
import grapesjsScriptEditor from 'grapesjs-script-editor';
function graperWrapper(el) {
    return el.closest('[wire\\:ignore]');
}
function graperInput(wrapper) {
    return wrapper?.querySelector('input[type="hidden"]') ?? null;
}
function initGraper(graperDiv) {
    const container = '#' + graperDiv.id;
    if (window.graperInstances?.[container]) {
        return;
    }
    const wrapper = graperWrapper(graperDiv);
    const inputEl = graperInput(wrapper);
    const initialState = inputEl?.value || null;
    const editor = grapesjs.init({
        container: container,
        height: '70vh',
        storageManager: {
            type: 'local',
            autosave: true,
            autoload: true,
            stepsBeforeSave: 1,
        },
        plugins: [
            tailwindPlugin,
            grapesjsBlocksBasic,
            grapesjsPluginForms,
            grapesjsNavbar,
            grapesjsComponentCountdown,
            grapesjsTabs,
            grapesjsTooltip,
            grapesjsCustomCode,
            grapesjsTouch,
            grapesjsTyped,
            grapesjsStyleGradient,
            grapesjsParserPostcss,
            grapesjsPluginExport,
            grapesjsTuiImageEditor,
            grapesjsComponentCodeEditor,
            grapesjsPresetWebpage,
            grapesjsUiSuggestClasses,
            grapesjsStyleFilter,
            grapesjsStyleBg,
            grapesjsBlocksFlexbox,
            grapesjsPluginCkeditor,
            grapesjsLorySlider,
            grapesjsRulers,
            grapesjsTemplates,
            grapesjsUserBlocks,
            grapesjsScriptEditor,
        ],
        modal: { appendTo: 'body' },
        canvas: {
            styles: [
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
            ],
        },
        deviceManager: {
            devices: [
                { name: 'Desktop', width: '', widthMedia: '' },
                { name: 'Tablet', width: '768px', widthMedia: '768px' },
                { name: 'Mobile', width: '375px', widthMedia: '480px' },
            ],
        },
    });
    editor.Commands.add('open-code', {
        run(editor, sender) {
            sender?.set?.('active', false);
            const modal = editor.Modal;
            const canvas = editor.Canvas;
            const htmlInput = document.createElement('textarea');
            const cssInput = document.createElement('textarea');
            htmlInput.value = canvas.getHtml();
            cssInput.value = canvas.getCss();
            htmlInput.style.cssText = 'width:100%;height:45%;resize:vertical;font-family:monospace;padding:8px;border:1px solid #ddd;border-radius:4px;';
            cssInput.style.cssText = 'width:100%;height:45%;resize:vertical;font-family:monospace;padding:8px;border:1px solid #ddd;border-radius:4px;margin-top:10px;';
            const labelHtml = document.createElement('div');
            labelHtml.textContent = 'HTML';
            labelHtml.style.cssText = 'font-weight:600;margin-bottom:4px;';
            const labelCss = document.createElement('div');
            labelCss.textContent = 'CSS';
            labelCss.style.cssText = 'font-weight:600;margin-top:8px;margin-bottom:4px;';
            const container = document.createElement('div');
            container.style.cssText = 'padding:10px;height:70vh;overflow:auto;';
            container.appendChild(labelHtml);
            container.appendChild(htmlInput);
            container.appendChild(labelCss);
            container.appendChild(cssInput);
            modal.setTitle('Edit Code');
            modal.setContent(container);
            modal.open();
            modal.onClose(() => {
                const html = htmlInput.value;
                const css = cssInput.value;
                if (html)
                    editor.setComponents(html.replace(/<\/?body[^>]*>/g, ''));
                if (css !== undefined)
                    editor.setStyle(css);
                editor.trigger('update');
            });
        },
    });
    fetch('/graper/api/blocks')
        .then((r) => r.json())
        .then(({ blocks }) => {
        blocks.forEach((block) => {
            editor.BlockManager.add(block.id, {
                label: block.name,
                category: { id: block.category, label: block.category },
                content: block.template,
                media: block.thumbnail ?? '',
                attributes: { 'data-block-id': block.id },
            });
        });
    })
        .catch((err) => {
        console.error('[Graper] Failed to load custom blocks', err);
    });
    if (initialState) {
        try {
            const data = JSON.parse(initialState);
            if (data.project_data && Object.keys(data.project_data).length > 0) {
                editor.loadProjectData(data.project_data);
            }
            else if (data.html) {
                const stripped = data.html.replace(/<\/?body[^>]*>/g, '');
                editor.setComponents(stripped);
                editor.setStyle(data.css ?? '');
            }
        }
        catch {
            // empty canvas is fine
        }
    }
    editor.on('update', () => {
        const payload = JSON.stringify({
            html: editor.getHtml(),
            css: editor.getCss(),
            project_data: editor.getProjectData(),
        });
        if (inputEl) {
            inputEl.value = payload;
        }
        const wireEl = wrapper?.closest('[wire\\:id]');
        if (wireEl) {
            const wireId = wireEl.getAttribute('wire:id');
            const wire = window.Livewire.find(wireId);
            if (wire) {
                wire.set('data.content', payload, false);
            }
        }
    });
    setTimeout(() => {
        editor.trigger('update');
    }, 300);
    window.graperInstances = window.graperInstances ?? {};
    window.graperInstances[container] = editor;
    window.dispatchEvent(new CustomEvent('graper:ready', { detail: { editor, id: container } }));
}
function scanAndInit() {
    const divs = document.querySelectorAll('div[wire\\:ignore] > div[id^="graper-"]');
    divs.forEach((div) => {
        initGraper(div);
    });
}
document.addEventListener('DOMContentLoaded', () => {
    scanAndInit();
    const observer = new MutationObserver(() => {
        scanAndInit();
    });
    document.querySelectorAll('[wire\\:id]').forEach((el) => {
        observer.observe(el, { childList: true, subtree: true });
    });
});
