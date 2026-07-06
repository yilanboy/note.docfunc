<script lang="ts">
  import { PanelLeftClose, PanelLeftOpen, House } from "@lucide/svelte";
  import { sidebar } from "@/shared/sidebar.svelte.js";
  import { inertia } from "@inertiajs/svelte";
  import { onMount } from "svelte";

  let mounted = $state(false);

  onMount(() => {
    mounted = true;
  });

  function toggleSidebar() {
    sidebar.isOpen = !sidebar.isOpen;
  }
</script>

<header class="sticky top-0 z-50 h-16 bg-white">
  <nav
    aria-label="Global"
    class="flex h-full items-center justify-between border-b border-zinc-200 p-4 lg:px-8"
  >
    <div class="flex items-center gap-x-4 lg:flex-1">
      <button
        onclick={toggleSidebar}
        type="button"
        class="-m-2.5 inline-flex cursor-pointer items-center justify-center rounded-md p-2.5 text-gray-700 hover:bg-gray-100"
      >
        <span class="sr-only">Toggle Sidebar</span>

        {#if !mounted}
          <PanelLeftOpen class="lg:hidden" />
          <PanelLeftClose class="hidden lg:block" />
        {:else if sidebar.isOpen}
          <PanelLeftClose />
        {:else}
          <PanelLeftOpen />
        {/if}
      </button>
    </div>

    <div class="hidden lg:flex lg:gap-x-12"></div>

    <div class="flex flex-1 items-center justify-end gap-x-4">
      <a use:inertia href="/" class="text-gray-700 transition-colors hover:text-gray-900">
        <House class="size-6" />
      </a>

      <a
        href="https://github.com/yilanboy/note.docfunc"
        target="_blank"
        rel="noopener noreferrer"
        class="text-gray-700 transition-colors hover:text-gray-900"
        aria-label="GitHub Repository"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
          class="size-6"
        >
          <path
            d="M15 22v-4a4.8 4.8 0 0 0-1-3.5c3 0 6-2 6-5.5.08-1.25-.27-2.48-1-3.5.28-1.15.28-2.35 0-3.5 0 0-1 0-3 1.5-2.64-.5-5.36-.5-8 0C6 2 5 2 5 2c-.3 1.15-.3 2.35 0 3.5A5.403 5.403 0 0 0 4 9c0 3.5 3 5.5 6 5.5-.39.49-.68 1.05-.85 1.65-.17.6-.22 1.23-.15 1.85v4"
          />
          <path d="M9 18c-4.51 2-5-2-7-2" />
        </svg>
      </a>
    </div>
  </nav>
</header>
