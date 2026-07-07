<script lang="ts">
  import { PanelLeftClose, PanelLeftOpen, House, Sun, Moon } from "@lucide/svelte";
  import { sidebar } from "@/shared/sidebar.svelte.js";
  import { inertia } from "@inertiajs/svelte";
  import { onMount } from "svelte";

  let mounted = $state(false);
  let isDark = $state(false);

  onMount(() => {
    mounted = true;
    isDark = document.documentElement.classList.contains("dark");
  });

  function toggleSidebar() {
    sidebar.isOpen = !sidebar.isOpen;
  }

  function toggleTheme() {
    isDark = !isDark;
    if (isDark) {
      document.documentElement.classList.add("dark");
      localStorage.setItem("theme", "dark");
    } else {
      document.documentElement.classList.remove("dark");
      localStorage.setItem("theme", "light");
    }
  }
</script>

<header class="sticky top-0 z-50 h-16 bg-zinc-50 dark:bg-zinc-900 transition-colors duration-300">
  <nav
    aria-label="Global"
    class="flex h-full items-center justify-between border-b border-zinc-200 dark:border-zinc-800 p-4 lg:px-8"
  >
    <div class="flex items-center gap-x-2 lg:flex-1">
      <button
        onclick={toggleSidebar}
        type="button"
        class="inline-flex size-10 cursor-pointer items-center justify-center rounded-lg text-zinc-500 hover:text-zinc-900 hover:bg-zinc-200/50 dark:text-zinc-400 dark:hover:text-zinc-100 dark:hover:bg-zinc-800 transition-colors duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-zinc-200 dark:focus-visible:ring-zinc-700"
        aria-label="Toggle Sidebar"
      >
        <span class="sr-only">Toggle Sidebar</span>

        {#if !mounted}
          <PanelLeftOpen class="size-5 lg:hidden" />
          <PanelLeftClose class="size-5 hidden lg:block" />
        {:else if sidebar.isOpen}
          <PanelLeftClose class="size-5" />
        {:else}
          <PanelLeftOpen class="size-5" />
        {/if}
      </button>

      <a
        use:inertia
        href="/"
        class="inline-flex size-10 cursor-pointer items-center justify-center rounded-lg text-zinc-500 hover:text-zinc-900 hover:bg-zinc-200/50 dark:text-zinc-400 dark:hover:text-zinc-100 dark:hover:bg-zinc-800 transition-colors duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-zinc-200 dark:focus-visible:ring-zinc-700"
        aria-label="Home"
      >
        <House class="size-5" />
      </a>
    </div>

    <div class="hidden lg:flex lg:gap-x-12"></div>

    <div class="flex flex-1 items-center justify-end gap-x-3">
      <button
        onclick={toggleTheme}
        type="button"
        class="inline-flex size-10 cursor-pointer items-center justify-center rounded-lg text-zinc-500 hover:text-zinc-900 hover:bg-zinc-200/50 dark:text-zinc-400 dark:hover:text-zinc-100 dark:hover:bg-zinc-800 transition-all duration-200 active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-zinc-200 dark:focus-visible:ring-zinc-700"
        aria-label="Toggle Theme"
      >
        <span class="sr-only">Toggle Theme</span>
        {#if !mounted}
          <div class="size-5"></div>
        {:else if isDark}
          <Sun class="size-5 text-amber-500 transition-transform duration-300 rotate-0 hover:rotate-12" />
        {:else}
          <Moon class="size-5 text-indigo-600 dark:text-indigo-400 transition-transform duration-300 rotate-0 hover:-rotate-12" />
        {/if}
      </button>

      <a
        href="https://github.com/yilanboy/note.docfunc"
        target="_blank"
        rel="noopener noreferrer"
        class="inline-flex size-10 cursor-pointer items-center justify-center rounded-lg text-zinc-500 hover:text-zinc-900 hover:bg-zinc-200/50 dark:text-zinc-400 dark:hover:text-zinc-100 dark:hover:bg-zinc-800 transition-colors duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-zinc-200 dark:focus-visible:ring-zinc-700"
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
          class="size-5"
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
