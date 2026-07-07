<script module>
  export { default as layout } from "@/components/layouts/main/MainLayout.svelte";
</script>

<script lang="ts">
  import { highlightCodeBlocks } from "@/shared/highlight";

  interface Props {
    title: string;
    html: string;
  }

  let { title, html }: Props = $props();

  // Highlights code blocks after mount and re-runs when the note changes,
  // because the attachment reads `html`. Client-side only.
  function highlight(article: HTMLElement) {
    highlightCodeBlocks(article);
  }
</script>

<svelte:head>
  <title>{title}</title>
</svelte:head>

<main class="mx-auto w-full max-w-3xl px-4 py-8 lg:px-8">
  <article
    {@attach highlight}
    class="prose max-w-none leading-8 prose-zinc dark:prose-invert prose-p:wrap-break-word
           prose-a:text-emerald-600 dark:prose-a:text-indigo-400 prose-a:no-underline prose-a:underline-offset-2 visited:prose-a:text-emerald-700 dark:visited:prose-a:text-indigo-300 prose-a:hover:text-emerald-700 dark:prose-a:hover:text-indigo-300 prose-a:hover:underline focus:prose-a:ring-2 focus:prose-a:ring-emerald-300/60 dark:focus:prose-a:ring-indigo-800/60 focus:prose-a:outline-none
           prose-blockquote:rounded-[0.3rem] prose-blockquote:border-emerald-300 dark:prose-blockquote:border-indigo-700 prose-blockquote:bg-emerald-50 dark:prose-blockquote:bg-indigo-950/30 prose-blockquote:px-4 prose-blockquote:py-3 prose-blockquote:font-semibold prose-blockquote:text-emerald-900 dark:prose-blockquote:text-indigo-200 prose-blockquote:not-italic"
  >
    <!-- Safe: html is rendered server-side from our own Markdown files with html_input=strip -->
    {@html html}
  </article>
</main>
