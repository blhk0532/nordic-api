import grapesjs, { type Editor } from 'grapesjs';
import tailwindPlugin from 'grapesjs-tailwind';
import grapesjsCustomCode from 'grapesjs-custom-code';
import grapesjsNavbar from 'grapesjs-navbar';
import grapesjsTabs from 'grapesjs-tabs';
import gjsForms from 'grapesjs-plugin-forms';
import grapesjsBlocksBasic from 'grapesjs-blocks-basic';
import grapesjsTemplates from 'grapesjs-templates';
import grapesjsUserBlocks from 'grapesjs-user-blocks';
import grapesjsIndexeddb from 'grapesjs-indexeddb';
import grapesjsPresetWebpage from 'grapesjs-preset-webpage';
import grapesjsPresetNewsletter from 'grapesjs-preset-newsletter';
import grapesjsComponentCodeEditor from 'grapesjs-component-code-editor';
import grapesjsChartjs from 'grapesjs-chartjs-plugin';
import grapesjsMonacoEditor from 'grapesjs-custom-code-monaco-editor';
import grapesjsDataSource from '@silexlabs/grapesjs-data-source';
import grapesjsRulers from 'grapesjs-rulers';
import grapesjsScriptEditor from 'grapesjs-script-editor';
import grapesjsAlpinejs from 'grapesjs-alpinejs';
import grapesjsMjml from 'grapesjs-mjml';

interface BlockDefinition {
    id: string;
    name: string;
    category: string;
    template: string;
    order: number;
    thumbnail: string | null;
}

declare global {
    interface Window {
        graperInstances: Record<string, Editor>;
    }
}

type GrapesPlugin = (editor: Editor, options?: Record<string, unknown>) => void;

function safePlugin(name: string, plugin: unknown, options?: Record<string, unknown>): GrapesPlugin {
    return (editor: Editor) => {
        try {
            if (typeof plugin === 'function') {
                (plugin as GrapesPlugin)(editor, options);
                return;
            }

            const defaultExport = (plugin as { default?: unknown })?.default;
            if (typeof defaultExport === 'function') {
                (defaultExport as GrapesPlugin)(editor, options);
                return;
            }

            console.warn(`[Graper] Plugin "${name}" is not a callable plugin export`);
        } catch (error) {
            console.error(`[Graper] Plugin "${name}" failed during init`, error);
        }
    };
}

function graperWrapper(el: HTMLElement): HTMLElement | null {
    return el.closest('[wire\\:ignore]');
}

function graperInput(graperDiv: HTMLElement, wrapper: HTMLElement | null): HTMLInputElement | null {
    const byId = document.getElementById(`${graperDiv.id}-input`) as HTMLInputElement | null;
    if (byId) {
        return byId;
    }

    return wrapper?.parentElement?.querySelector('input[type="hidden"]') ?? null;
}

function initGraper(graperDiv: HTMLElement): void {
    const container = '#' + graperDiv.id;

    if (window.graperInstances?.[container]) {
        return;
    }

    const wrapper = graperWrapper(graperDiv);
    const inputEl = graperInput(graperDiv, wrapper);
    const statePath = inputEl?.dataset?.statePath || 'data.content';
    const initialState = inputEl?.value || null;

const editor: Editor = grapesjs.init({
    container: container,
    height: '70vh',
    storageManager: false,
    plugins: [
        safePlugin('grapesjs-tailwind', tailwindPlugin),
        safePlugin('grapesjs-preset-webpage', grapesjsPresetWebpage),
        safePlugin('grapesjs-preset-newsletter', grapesjsPresetNewsletter),
        safePlugin('grapesjs-component-code-editor', grapesjsComponentCodeEditor),
        safePlugin('grapesjs-chartjs-plugin', grapesjsChartjs),
        safePlugin('grapesjs-custom-code-monaco-editor', grapesjsMonacoEditor),
        safePlugin('@silexlabs/grapesjs-data-source', grapesjsDataSource),
        safePlugin('grapesjs-rulers', grapesjsRulers),
        safePlugin('grapesjs-script-editor', grapesjsScriptEditor),
        safePlugin('grapesjs-alpinejs', grapesjsAlpinejs),
        safePlugin('grapesjs-mjml', grapesjsMjml),
        safePlugin('grapesjs-custom-code', grapesjsCustomCode),
        safePlugin('grapesjs-navbar', grapesjsNavbar),
        safePlugin('grapesjs-tabs', grapesjsTabs),
        safePlugin('grapesjs-plugin-forms', gjsForms),
        safePlugin('grapesjs-blocks-basic', grapesjsBlocksBasic),
        safePlugin('grapesjs-templates', grapesjsTemplates),
        safePlugin('grapesjs-user-blocks', grapesjsUserBlocks),
        safePlugin('grapesjs-indexeddb', grapesjsIndexeddb),
    ],
    modal: {},
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
                if (html) editor.setComponents(html.replace(/<\/?body[^>]*>/g, ''));
                if (css !== undefined) editor.setStyle(css);
                editor.trigger('update');
            });
        },
    });

    const basicCategory = { id: 'basic', label: 'Basic' };
    const mediaCategory = { id: 'media', label: 'Media' };

    editor.BlockManager.add('heading', {
        label: 'Heading',
        category: basicCategory,
        content: '<h1 class="text-4xl font-bold text-gray-900">Heading</h1>',
    });
    editor.BlockManager.add('text', {
        label: 'Text',
        category: basicCategory,
        content: '<p class="text-base text-gray-700">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>',
    });
    editor.BlockManager.add('image', {
        label: 'Image',
        category: basicCategory,
        content: '<img src="https://via.placeholder.com/800x400" alt="Image" class="w-full h-auto" />',
    });
    editor.BlockManager.add('button', {
        label: 'Button',
        category: basicCategory,
        content: '<a href="#" class="inline-block bg-blue-600 text-white font-semibold py-3 px-8 rounded-lg hover:bg-blue-700 transition">Button</a>',
    });
    editor.BlockManager.add('divider', {
        label: 'Divider',
        category: basicCategory,
        content: '<hr class="my-8 border-t border-gray-300" />',
    });
    editor.BlockManager.add('spacer', {
        label: 'Spacer',
        category: basicCategory,
        content: '<div style="height:60px"></div>',
    });
    editor.BlockManager.add('video', {
        label: 'Video',
        category: mediaCategory,
        content: '<div class="aspect-video"><iframe class="w-full h-full" src="https://www.youtube.com/embed/dQw4w9WgXcQ" frameborder="0" allowfullscreen></iframe></div>',
    });
    editor.BlockManager.add('maps', {
        label: 'Maps',
        category: mediaCategory,
        content: '<div class="aspect-video"><iframe class="w-full h-full" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3022.966309591938!2d-73.9857!3d40.7484!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNDDCsDQ0JzU0LjIiTiA3M8KwNTknMDguNCJX!5e0!3m2!1sen!2sus!4v1" allowfullscreen loading="lazy"></iframe></div>',
    });
    editor.BlockManager.add('link', {
        label: 'Link',
        category: basicCategory,
        content: '<a href="#" class="text-blue-600 hover:underline">Link text</a>',
    });
    editor.BlockManager.add('list', {
        label: 'List',
        category: basicCategory,
        content: '<ul class="list-disc list-inside text-gray-700"><li>Item 1</li><li>Item 2</li><li>Item 3</li></ul>',
    });
    editor.BlockManager.add('quote', {
        label: 'Quote',
        category: basicCategory,
        content: '<blockquote class="border-l-4 border-gray-300 pl-4 italic text-gray-600">Quote text here</blockquote>',
    });

    const loadRemoteBlocks = async (): Promise<void> => {
        try {
            const response = await fetch('/graper/api/blocks', {
                headers: {
                    Accept: 'application/json',
                },
            });

            if (!response.ok) {
                console.error('[Graper] Failed to load custom blocks', response.status, response.statusText);
                return;
            }

            const payload = await response.json() as { blocks?: BlockDefinition[] };
            const blocks = Array.isArray(payload.blocks) ? payload.blocks : [];

            let addedCount = 0;

            blocks.forEach((block) => {
                if (!block?.id || !block?.template) {
                    return;
                }

                editor.BlockManager.add(block.id, {
                    label: block.name,
                    category: { id: block.category, label: block.category },
                    content: block.template,
                    media: block.thumbnail ?? '',
                    attributes: { 'data-block-id': block.id },
                });

                addedCount++;
            });

            console.info(`[Graper] Loaded ${addedCount} custom blocks`);
        } catch (err) {
            console.error('[Graper] Failed to load custom blocks', err);
        }
    };

    const applyInitialState = () => {
        if (!initialState) {
            return;
        }

        try {
            const data = JSON.parse(initialState) as {
                html?: string;
                css?: string;
                project_data?: object;
            };

            if (data.project_data && Object.keys(data.project_data).length > 0) {
                editor.loadProjectData(data.project_data);
                return;
            }

            if (data.html) {
                const stripped = data.html.replace(/<\/?body[^>]*>/g, '');
                editor.setComponents(stripped);
                editor.setStyle(data.css ?? '');
            }
        } catch {
            // empty canvas is fine
        }
    };

    editor.on('load', () => {
        void loadRemoteBlocks();
        applyInitialState();
    });
    setTimeout(applyInitialState, 50);

    let saveTimer: number | null = null;

    const syncContent = () => {
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
            const wire = (window as any).Livewire?.find(wireId);
            if (wire) {
                wire.set(statePath, payload);
            }
        }
    };

    editor.on('update', () => {
        if (saveTimer) clearTimeout(saveTimer);
        saveTimer = window.setTimeout(syncContent, 300);
    });

    const form = wrapper?.closest('form');
    if (form) {
        form.addEventListener('submit', () => {
            if (saveTimer) clearTimeout(saveTimer);
            syncContent();
        }, true);
    }

    setTimeout(() => {
        editor.trigger('update');
    }, 300);

    window.graperInstances = window.graperInstances ?? {};
    window.graperInstances[container] = editor;

    window.dispatchEvent(
        new CustomEvent('graper:ready', { detail: { editor, id: container } }),
    );
}

function scanAndInit(): void {
    const divs = document.querySelectorAll<HTMLElement>('div[wire\\:ignore] > div[id^="graper-"]');
    divs.forEach((div) => {
        initGraper(div);
    });
}

function setupObserver(): void {
    const observer = new MutationObserver(() => {
        scanAndInit();
    });
    observer.observe(document.body, { childList: true, subtree: true });
}

document.addEventListener('DOMContentLoaded', () => {
    scanAndInit();
    setupObserver();
});

document.addEventListener('livewire:navigated', () => {
    scanAndInit();
});
