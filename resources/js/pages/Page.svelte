<script module>
    export { default as layout } from '@/components/layouts/main/MainLayout.svelte';
</script>

<script lang="ts">
    import { highlightCodeBlocks } from '@/shared/highlight';
    import { renderMermaidDiagrams } from '@/shared/mermaid';
    import { enhanceImages } from '@/shared/image';
    import { Calendar, CalendarSync } from '@lucide/svelte';

    interface NoteMetadata {
        date: string;
        updated?: string;
        tags?: string[];
    }

    interface Props {
        title: string;
        html: string;
        metadata: NoteMetadata;
    }

    let { title, html, metadata }: Props = $props();

    // Highlights code blocks, renders Mermaid diagrams, and enhances images after mount.
    // This runs when the note changes because the attachment reads `html`.
    function processContent(article: HTMLElement) {
        highlightCodeBlocks(article);
        renderMermaidDiagrams(article);
        enhanceImages(article);
    }
</script>

<main class="mx-auto w-full max-w-3xl px-4 py-8 lg:px-8">
    <article
        {@attach processContent}
        class="prose prose-zinc dark:prose-invert prose-p:wrap-break-word prose-a:text-emerald-600 prose-a:no-underline
           prose-a:underline-offset-2 visited:prose-a:text-emerald-700 prose-a:hover:text-emerald-700 prose-a:hover:underline focus:prose-a:ring-2 focus:prose-a:ring-emerald-300/60 focus:prose-a:outline-none dark:prose-a:text-lividus-400 dark:visited:prose-a:text-lividus-300 dark:prose-a:hover:text-lividus-300 dark:focus:prose-a:ring-lividus-800/60 prose-blockquote:rounded-[0.3rem] prose-blockquote:border-emerald-300
           prose-blockquote:bg-emerald-50 prose-blockquote:px-4 prose-blockquote:py-3 prose-blockquote:font-semibold prose-blockquote:text-emerald-900 prose-blockquote:not-italic dark:prose-blockquote:border-lividus-700 dark:prose-blockquote:bg-lividus-950/30 dark:prose-blockquote:text-lividus-200 max-w-none leading-8"
    >
        <div
            class="not-prose mb-8 flex flex-wrap items-center justify-between gap-x-4 gap-y-3 border-b border-zinc-200 pb-4 text-sm dark:border-zinc-800"
        >
            <div
                class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-zinc-500 sm:text-sm dark:text-zinc-400"
            >
                <span class="inline-flex items-center gap-1.5" title="建立日期">
                    <Calendar
                        class="size-4 shrink-0 text-zinc-400 dark:text-zinc-500"
                    />
                    <span>建立於 {metadata.date}</span>
                </span>

                {#if metadata.updated}
                    <span
                        class="inline-flex items-center gap-1.5"
                        title="更新日期"
                    >
                        <CalendarSync
                            class="size-4 shrink-0 text-zinc-400 dark:text-zinc-500"
                        />
                        <span>更新於 {metadata.updated}</span>
                    </span>
                {/if}
            </div>

            {#if metadata.tags && metadata.tags.length > 0}
                <div class="flex flex-wrap items-center gap-1.5">
                    {#each metadata.tags as tag (tag)}
                        <span
                            class="dark:bg-lividus-950/60 dark:text-lividus-300 dark:ring-lividus-800/60 inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-0.5 text-sm font-medium text-emerald-700 ring-1 ring-emerald-600/20 ring-inset"
                        >
                            # {tag}
                        </span>
                    {/each}
                </div>
            {/if}
        </div>

        <!-- Safe: html is rendered server-side from our own Markdown files with html_input=strip -->
        {@html html}
    </article>
</main>
