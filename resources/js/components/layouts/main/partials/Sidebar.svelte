<script lang="ts">
  import { inertia, page } from "@inertiajs/svelte";
  import { ChevronRight } from "@lucide/svelte";
  import { fly, slide } from "svelte/transition";
  import { onMount } from "svelte";
  import { sidebar } from "@/shared/sidebar.svelte.js";
  import type { NoteCategory } from "@/types";

  let slideOffset = $state(0);

  onMount(() => {
    slideOffset = -200;
  });

  let noteTree = $derived((page.props.noteTree ?? []) as NoteCategory[]);
  let activeCategory = $derived(page.url.split("/")[1] ?? "");
  let activePath = $derived(page.url.split("?")[0]);
</script>

<!-- Desktop Sidebar -->
{#if sidebar.isOpen}
  <aside
    transition:fly={{ x: slideOffset, duration: 300 }}
    class="fixed inset-y-0 top-16 z-40 flex w-72 flex-col"
  >
    <div
      class="flex grow flex-col gap-y-5 overflow-y-auto border-r border-zinc-200 bg-white px-6 pb-4"
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
