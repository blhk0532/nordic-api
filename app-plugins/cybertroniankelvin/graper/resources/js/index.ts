import grapesjs, { type Editor } from 'grapesjs';
// @ts-ignore — grapesjs-tailwind ships no TypeScript types
import tailwindPlugin from 'grapesjs-tailwind';

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

function grapesjsContainer(el: HTMLElement): string {
    return '#' + el.id;
}

function graperWrapper(el: HTMLElement): HTMLElement | null {
    return el.closest('[wire\\:ignore]');
}

function graperInput(wrapper: HTMLElement | null): HTMLInputElement | null {
    return wrapper?.querySelector('input[type="hidden"]') ?? null;
}

function initGraper(graperDiv: HTMLElement): void {
    const container = grapesjsContainer(graperDiv);

    console.log('[Graper] initGraper called for', container);

    if (window.graperInstances?.[container]) {
        console.log('[Graper] Already initialized, skipping');
        return;
    }

    const wrapper = graperWrapper(graperDiv);
    const inputEl = graperInput(wrapper);
    console.log('[Graper] Input element found:', !!inputEl, 'value:', inputEl?.value);
    console.log('[Graper] Wrapper found:', !!wrapper);

    const initialState = inputEl?.value || null;
    console.log('[Graper] initialState:', initialState ? JSON.parse(initialState) : null);

    const editor: Editor = grapesjs.init({
        container: container,
        height: '600px',
        storageManager: false,
        plugins: [tailwindPlugin],
    });

    fetch('/graper/api/blocks')
        .then((r) => r.json())
        .then(({ blocks }: { blocks: BlockDefinition[] }) => {
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
        .catch((err: Error) => {
            console.error('[Graper] Failed to load custom blocks', err);
        });

    if (initialState) {
        try {
            const data = JSON.parse(initialState) as {
                html?: string;
                css?: string;
                project_data?: object;
            };
            if (data.project_data && Object.keys(data.project_data).length > 0) {
                editor.loadProjectData(data.project_data);
            } else if (data.html) {
                const stripped = data.html.replace(/<\/?body[^>]*>/g, '');
                editor.setComponents(stripped);
                editor.setStyle(data.css ?? '');
            }
        } catch {
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
            // @ts-ignore
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

    window.dispatchEvent(
        new CustomEvent('graper:ready', { detail: { editor, id: container } }),
    );
}

function scanAndInit(): void {
    console.log('[Graper] scanAndInit running, DOM ready');
    const divs = document.querySelectorAll<HTMLElement>('div[wire\\:ignore] > div[id^="graper-"]');
    console.log('[Graper] Found graper divs:', divs.length, Array.from(divs).map(d => d.id));
    divs.forEach((div) => {
        initGraper(div);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    console.log('[Graper] DOMContentLoaded');
    scanAndInit();

    const observer = new MutationObserver(() => {
        console.log('[Graper] Mutation observed');
        scanAndInit();
    });
    document.querySelectorAll('[wire\\:id]').forEach((el) => {
        console.log('[Graper] Observing:', el.getAttribute('wire:id'));
        observer.observe(el, { childList: true, subtree: true });
    });
});