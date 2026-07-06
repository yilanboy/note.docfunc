<script lang="ts">
  import { inertia, page } from "@inertiajs/svelte";
  import { ChevronRight, X } from "@lucide/svelte";
  import { fly, slide, fade } from "svelte/transition";
  import { onMount, tick, untrack } from "svelte";
  import { sidebar } from "@/shared/sidebar.svelte.js";
  import type { NoteCategory } from "@/types";

  // Must stay in sync with Tailwind's `lg` breakpoint (1024px). Using matchMedia
  // means we share the same breakpoint engine as the `lg:` utility classes.
  const DESKTOP_MEDIA_QUERY = "(min-width: 1024px)";

  let slideOffset = $state(0);
  let transitionDuration = $state(0);
  let mounted = $state(false);

  // Open on desktop, closed on mobile. Called on mount and whenever the viewport
  // crosses the breakpoint. Assigning the same value is a no-op in Svelte's $state,
  // so firing on every resize event is cheap.
  function syncSidebarWithViewport() {
    sidebar.isOpen = window.matchMedia(DESKTOP_MEDIA_QUERY).matches;
  }

  // Auto-close sidebar on mobile when route path changes
  $effect(() => {
    activePath;
    untrack(() => {
      if (!window.matchMedia(DESKTOP_MEDIA_QUERY).matches && sidebar.isOpen) {
        sidebar.isOpen = false;
      }
    });
  });

  onMount(async () => {
    mounted = true;
    syncSidebarWithViewport();

    await tick();

    // Delay transition properties to avoid initial flash animations
    slideOffset = -200;
    transitionDuration = 300;
  });

  let noteTree = $derived((page.props.noteTree ?? []) as NoteCategory[]);
  let activeCategory = $derived(page.url.split("/")[1] ?? "");
  let activePath = $derived(page.url.split("?")[0]);
</script>

<svelte:window onresize={syncSidebarWithViewport} />

<!-- Desktop Sidebar -->
{#if sidebar.isOpen}
  <!-- svelte-ignore a11y_click_events_have_key_events -->
  <!-- svelte-ignore a11y_no_static_element_interactions -->
  <div
    transition:fade={{ duration: transitionDuration }}
    onclick={() => (sidebar.isOpen = false)}
    class={{ "fixed inset-0 z-40 bg-black/70 lg:hidden": mounted }}
  ></div>

  <aside
    transition:fly={{ x: slideOffset, duration: transitionDuration }}
    class={{
      "fixed inset-y-0 top-16 z-40 w-72 flex-col": true,
      "hidden lg:flex": !mounted,
      flex: mounted,
    }}
  >
    <button class="pointer-events-none absolute top-6 -right-12 text-white lg:hidden">
      <X class="size-8" />
    </button>

    <div
      class="z-10 flex grow flex-col gap-y-5 overflow-y-auto border-r border-zinc-200 bg-white px-6 pb-4"
    >
      <nav class="mt-6 flex flex-1 flex-col">
        <ul role="list" class="flex flex-1 flex-col gap-y-7">
          <li>
            <ul role="list" class="-mx-2 space-y-1">
              {#each noteTree as category (category.slug)}
                {@const expanded = activeCategory === category.slug}
                {@const href = `/${category.slug}`}
                <li>
                  <a
                    use:inertia
                    {href}
                    aria-expanded={expanded}
                    class={{
                      "flex w-full cursor-pointer items-center gap-x-2 rounded-md p-2 text-sm font-semibold": true,
                      "text-zinc-700 hover:bg-zinc-100": href !== activePath,
                      "pointer-events-none bg-zinc-100 font-medium text-zinc-900":
                        href === activePath,
                    }}
                  >
                    <ChevronRight
                      class={{
                        "size-4 shrink-0 transition-transform": true,
                        "rotate-90": expanded,
                      }}
                    />
                    {category.displayName}
                  </a>

                  {#if expanded}
                    <ul
                      transition:slide={{ duration: 150 }}
                      class="mt-1 space-y-1 border-l border-zinc-200 pl-4"
                    >
                      {#each category.notes as note (note.slug)}
                        {@const href = `/${category.slug}/${note.slug}`}
                        <li>
                          <a
                            use:inertia
                            {href}
                            class={{
                              "block truncate rounded-md p-2 text-sm text-zinc-600 hover:bg-zinc-100": true,
                              "pointer-events-none bg-zinc-100 font-medium text-zinc-900":
                                href === activePath,
                            }}
                            title={note.title}
                          >
                            {note.title}
                          </a>
                        </li>
                      {/each}
                    </ul>
                  {/if}
                </li>
              {/each}
            </ul>
          </li>
        </ul>
      </nav>
    </div>
  </aside>
{/if}
